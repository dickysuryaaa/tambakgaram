# Uji beban SIMTAG

Target: `GET /test/read-only/dashboard` (tanpa login, hanya baca DB). Jalankan dari luar cluster.

## k6

```bash
mkdir -p hasil
k6 run -e BASE_URL=http://IP-NODE:30080 -e RATE=50 -e DURATION=120s \
  --out json=hasil/s2-r3-01.json loadtest/dashboard.js
python loadtest/analyze.py hasil/s2-r3-01.json --csv hasil/ringkasan.csv
```

Penamaan berkas: `s<skenario 1-3>-r<replika 1|3>-<pengulangan 01-10>.json`.
Laju (`RATE`), durasi, dan `TIMEOUT` harus sama di semua 60 run (variabel kontrol PRD 12.3).
Suntik gangguan beberapa detik setelah run dimulai; catat timestamp injeksi sendiri.

`analyze.py` melaporkan: total dan persen request gagal, latency rata-rata dan p95,
durasi gangguan terpanjang yang dirasakan client, dan distribusi `X-App-Instance`.

## hey

```bash
# 20 worker x 5 rps = ~100 rps selama 120 detik, timeout 3 detik, simpan CSV per request
hey -z 120s -c 20 -q 5 -t 3 -o csv http://IP-NODE:30080/test/read-only/dashboard > hasil/hey-s2-r3-01.csv
```

Ringkasan `hey` memuat latency (avg, p95) dan sebaran status code (non-200 dan error koneksi = gagal).
`hey` tidak merekam header, jadi distribusi `X-App-Instance` diambil terpisah:

```bash
for i in $(seq 1 300); do
  curl -s -m 3 -o /dev/null -D - http://IP-NODE:30080/test/read-only/dashboard \
    | tr -d '\r' | awk -F': ' 'tolower($1)=="x-app-instance"{print $2}'
done | sort | uniq -c
```

## Catatan pengamatan Kubernetes (di terminal lain)

```bash
kubectl get pods -o wide -w
kubectl get events --sort-by=.lastTimestamp -w
```
