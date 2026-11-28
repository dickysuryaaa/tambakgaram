# Uji beban SIMTAG

Target: `GET /test/read-only/dashboard` (tanpa login, hanya baca DB). Jalankan dari luar cluster lewat NodePort `http://localhost:30080`.

Lihat `PANDUAN_EKSPERIMEN.md` untuk matriks eksperimen lengkap
(2 skenario replika x 3 gangguan x 10 pengulangan).

## k6

```bash
mkdir -p hasil
k6 run -e BASE_URL=http://localhost:30080 -e RATE=50 -e DURATION=120s \
  --out json=hasil/s1-r1-01.json loadtest/dashboard.js
python loadtest/analyze.py hasil/s1-r1-01.json --csv hasil/ringkasan.csv
```

Penamaan berkas: `s<skenario 1-2>-r<replika 1|3>-<pengulangan 01-10>.json`.
`RATE`, `DURATION`, dan `TIMEOUT` harus sama di semua run (variabel kontrol).
Suntikkan gangguan ~30 detik setelah run dimulai; catat timestamp injeksi sendiri.

`analyze.py` melaporkan:
- jumlah & persentase request gagal vs sukses;
- latency normal vs selama gangguan (avg, p50, p95, p99, max);
- durasi gangguan yang dirasakan klien (gagal pertama -> sukses stabil), per jendela dan terpanjang;
- distribusi `X-App-Instance` sebelum / selama / sesudah gangguan, plus pod baru yang muncul (bukti failover).

## hey (pembanding)

```bash
# 20 worker x 5 rps = ~100 rps selama 120 detik, timeout 3 detik, simpan CSV per request
hey -z 120s -c 20 -q 5 -t 3 -o csv http://localhost:30080/test/read-only/dashboard > hasil/hey-s1-r1-01.csv
```

Ringkasan `hey` memuat latency (avg, p95) dan sebaran status code (non-200 dan error koneksi = gagal).
`hey` tidak merekam header, jadi distribusi `X-App-Instance` diambil terpisah:

```bash
for i in $(seq 1 300); do
  curl -s -m 3 -o /dev/null -D - http://localhost:30080/test/read-only/dashboard \
    | tr -d '\r' | awk -F': ' 'tolower($1)=="x-app-instance"{print $2}'
done | sort | uniq -c
```

## Catatan pengamatan Kubernetes (di terminal lain)

```bash
kubectl get pods -o wide -w | tee hasil/mon-pods-s1-r1-01.log
kubectl get events -w --sort-by=.lastTimestamp | tee hasil/mon-events-s1-r1-01.log
```
