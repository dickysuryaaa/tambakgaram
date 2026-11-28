# Panduan Eksekusi Eksperimen SIMTAG

**Judul:** Evaluasi Waktu Pemulihan dan Ketersediaan Layanan Kubernetes pada
Skenario Kegagalan Pod dan Worker Node.

**Rumusan masalah:** Bagaimana jumlah replika dan penempatan pod memengaruhi
dampak gangguan serta waktu pemulihan layanan?

**Arsitektur:** aplikasi stateless (Laravel, session/cache di database) pada
cluster Kind 1 control-plane + 2 worker node, MySQL eksternal di host laptop
(`host.docker.internal`), Service NodePort 30080 -> `http://localhost:30080`.

Matriks: **2 skenario replika x 3 jenis gangguan x 10 pengulangan = 60 run**
(nama file `s<skenario>-r<replika>-<ulangan>.json`, ulangan `01`-`10`).

Variabel kontrol (WAJIB sama di semua run):

| Variabel | Nilai |
|---|---|
| RATE | 50 req/s |
| DURATION | 120s |
| TIMEOUT | 3s |
| TARGET_PATH | /test/read-only/dashboard |
| Gangguan disuntikkan | ~30 detik setelah run k6 dimulai |

---

## 0. Persiapan (sekali saja)

```bash
# 1. Build & muat image ke Kind
docker build -t dickysuryaaa/simtag:1.0.0 .
kind load docker-image dickysuryaaa/simtag:1.0.0 --name simtag
#   (atau: docker push dickysuryaaa/simtag:1.0.0 lalu kubelet menarik sendiri)

# 2. Buat cluster + deploy
kind create cluster --config k8s/kind-config.yaml
kubectl apply -f k8s/configmap-secret.yaml
kubectl apply -f k8s/deployment.yaml
kubectl apply -f k8s/service.yaml

# 3. Siapkan database (MySQL di host laptop, di luar cluster)
#    Pastikan bind-address mengizinkan koneksi dari kontainer Kind dan
#    user MySQL diizinkan dari subnet node (lihat komentar di kind-config.yaml).
php artisan migrate --seed   # di laptop, ke DB yang sama

# 4. Verifikasi
kubectl get pods -o wide
kubectl get nodes
curl -s http://localhost:30080/health/ready
curl -sI http://localhost:30080/test/read-only/dashboard | grep -i x-app-instance

# 5. Nama worker (untuk skenario 3, opsional agar nama konsisten di laporan)
docker ps --format '{{.Names}}'
docker rename simtag-worker  simtag-worker-1
docker rename simtag-worker2 simtag-worker-2
```

Sebelum setiap sesi pengulangan, catat `kubectl get pods -o wide` sebagai
dokumentasi penempatan pod antarnode.

---

## 1. Dua terminal pemantauan paralel (JALAN TERUS di setiap run)

Terminal A: rekam penempatan pod + peristiwa siklus hidup pod.

```bash
kubectl get pods -o wide -w | tee hasil/mon-pods-s1-r1-01.log
```

Terminal B: rekam event (pod pengganti dibuat, Running, Ready, probe gagal).

```bash
kubectl get events -w --sort-by=.lastTimestamp | tee hasil/mon-events-s1-r1-01.log
```

Dari kedua log ini dicatat timestamp komponen: (1) pod pengganti dibuat,
(2) `Running`, (3) `Ready` — lalu dibandingkan dengan durasi gangguan yang
dirasakan klien dari output `analyze.py` (biasanya Ready tercapai lebih
dulu daripada pulihnya trafik karena endpoints Service baru diperbarui
saat Ready).

---

## 2. Menjalankan satu run (pola dasar)

Terminal utama:

```bash
mkdir -p hasil
k6 run -e BASE_URL=http://localhost:30080 -e RATE=50 -e DURATION=120s \
  --out json=hasil/s1-r1-01.json loadtest/dashboard.js
python loadtest/analyze.py hasil/s1-r1-01.json --csv hasil/ringkasan.csv
```

Skenario 2: ganti `s1-r1-01` menjadi `s2-r3-01` dst, dan set replika dulu
(lihat bawah). Suntikkan gangguan (bagian 4) ~30 detik setelah k6 mulai dan
catat sendiri timestamp injeksinya.

---

## 3. Skenario replika

### Skenario 1: 1 replika

```bash
kubectl scale deployment/simtag --replicas=1
kubectl get pods -o wide   # dokumentasikan: 1 pod di worker-1 atau worker-2
```

### Skenario 2: 3 replika

```bash
kubectl scale deployment/simtag --replicas=3
kubectl get pods -o wide   # dokumentasikan: tersebar 2+1 di dua worker
```

`topologySpreadConstraints` (maxSkew=1, DoNotSchedule) pada
`k8s/deployment.yaml` menjamin 3 pod tidak menumpuk di satu node.

---

## 4. Tiga jenis gangguan

### Gangguan A: mematikan proses aplikasi di dalam container

Image menjalankan php-fpm dan nginx dalam satu container via
`docker/entrypoint.sh`: jika salah satu mati, entrypoint keluar non-nol dan
kubelet me-restart container (deteksi kegagalan level proses, bukan pod).

Cari nama pod lebih dulu, lalu matikan prosesnya:

```bash
POD=$(kubectl get pods -l app=simtag -o jsonpath='{.items[0].metadata.name}')

# Matikan php-fpm (alpine: PID 1 = entrypoint, php-fpm jalan sebagai background job)
kubectl exec -n default "$POD" -- sh -c 'pkill -QUIT php-fpm || kill -QUIT $(pgrep -f "php-fpm: master" | head -1)'
#   pidof juga bisa: kubectl exec "$POD" -- sh -c 'kill -QUIT $(pidof php-fpm)'

# Alternatif: matikan nginx
kubectl exec -n default "$POD" -- sh -c 'nginx -s stop'
#   atau: kubectl exec "$POD" -- sh -c 'kill -QUIT $(cat /run/nginx.pid)'
```

Yang diharapkan: livenessProbe `/health/live` gagal -> container restart ->
readiness menahan trafik sampai benar-benar siap. Bandingkan waktu restart
container (Terminal B: `Started container`, `Unhealthy`, `Killing`) dengan
durasi gangguan di `analyze.py`.

Catatan: `kill -QUIT` pada master php-fpm mematikan seluruh worker FPM.
Kalau pgrep tidak menemukan apa pun, periksa `kubectl exec "$POD" -- ps aux`
(tambahkan procps pada image bila perlu, atau gunakan `ls /proc`).

### Gangguan B: menghapus paksa pod

```bash
POD=$(kubectl get pods -l app=simtag -o jsonpath='{.items[0].metadata.name}')
kubectl delete pod "$POD" --grace-period=0 --force
```

Pod pengganti dibuat Deployment seketika; `topologySpreadConstraints`
menentukan node tujuannya. Pada 3 replika, service tetap dilayani 2 pod lain.

### Gangguan C: menghentikan satu worker node

Kind (node = kontainer Docker):

```bash
docker stop simtag-worker-1        # atau simtag-worker / simtag-worker2 jika belum direname
# pulihkan setelah run selesai:
docker start simtag-worker-1
kubectl uncordon <nama-node>       # hanya jika node sempat masuk SchedulingDisabled
```

Yang diharapkan: node jadi `NotReady`, pod di node itu dicabut setelah
`tolerationSeconds: 10` (lihat deployment.yaml), pod pengganti dijadwalkan
di worker-2 (skenario 2), NodePort tetap hidup karena 30080 dipetakan lewat
control-plane yang tidak pernah dimatikan.

Catatan untuk VM/cluster sungguhan: padanannya

```bash
sudo poweroff                        # dari dalam VM worker
# atau matikan lewat hypervisor: virsh shutdown <vm-worker-1>
kubectl drain <nama-node> --ignore-daemonsets --force   # cara sopan (untuk pembanding)
```

---

## 5. Urutan lengkap satu sel matriks (contoh: Skenario 1, Gangguan A, ulangan 01)

1. Pastikan replika sesuai skenario: `kubectl scale deployment/simtag --replicas=1`
2. Tunggu semua Ready: `kubectl wait --for=condition=ready pod -l app=simtag --timeout=120s`
3. Dokumentasikan: `kubectl get pods -o wide`
4. Nyalakan Terminal A dan B (pemantauan, bagian 1) dengan nama log run ini.
5. Jalankan k6 (bagian 2) di terminal utama.
6. Setelah ~30 detik, suntikkan gangguan (bagian 4), catat timestamp.
7. Tunggu k6 selesai (total 120s), matikan pemantauan (Ctrl+C).
8. `python loadtest/analyze.py hasil/s1-r1-01.json --csv hasil/ringkasan.csv`
9. Untuk gangguan C, jangan lupa `docker start` + tunggu node Ready lagi
   sebelum run berikutnya.

Ulangi 10x per sel; total 60 run.

---

## 6. Metrik yang dibandingkan antar skenario

Dari `analyze.py` per run:

- **Durasi gangguan klien** (a): gagal pertama -> sukses stabil, terpanjang.
- **Jumlah/persen gagal vs sukses** (b).
- **Latency normal vs selama gangguan**: avg, p50, p95, p99, max (c).
- **Distribusi `X-App-Instance` sebelum/selama/setelah gangguan** dan pod
  baru yang muncul (d) — bukti failover.

Dari log pemantauan Kubernetes, timestamp: pod pengganti dibuat, `Running`,
`Ready` — dibandingkan dengan (a) untuk memisahkan waktu pemulihan kontrol
plane dan waktu pemulihan yang benar-benar dirasakan klien.

Ekspektasi hipotesis: pada 3 replika dengan pod tersebar di 2 worker,
gangguan A dan B nyaris tanpa dampak ke klien (2 pod lain melayani), dan
hanya gangguan C yang menurunkan kapasitas; pada 1 replika semua gangguan
menyebabkan jendela gagal yang terukur.
