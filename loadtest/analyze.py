#!/usr/bin/env python3
"""Analisis hasil k6 (--out json=...) untuk matriks pengujian PRD 12.4.

Keluaran: distribusi X-App-Instance, jumlah request gagal, latency
(rata-rata, p95), dan durasi gangguan yang dirasakan client.

Definisi durasi gangguan: rentang waktu terpanjang dari request gagal
pertama sampai request sukses berikutnya (gangguan terputus-putus yang
dipisahkan sukses kurang dari --gap detik dianggap satu gangguan).

Pemakaian: python analyze.py hasil.json [--gap 1.0] [--csv ringkasan.csv]
"""
import argparse
import csv
import json
import os
import sys
from collections import Counter
from datetime import datetime


def parse_time(s):
    return datetime.fromisoformat(s).timestamp()


def percentile(sorted_vals, p):
    if not sorted_vals:
        return float("nan")
    k = (len(sorted_vals) - 1) * p / 100
    lo = int(k)
    hi = min(lo + 1, len(sorted_vals) - 1)
    return sorted_vals[lo] + (sorted_vals[hi] - sorted_vals[lo]) * (k - lo)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("file")
    ap.add_argument("--gap", type=float, default=1.0)
    ap.add_argument("--csv", help="tambahkan satu baris ringkasan ke file CSV ini")
    args = ap.parse_args()

    events = []  # (waktu, instance, ok, latency_ms)
    with open(args.file, encoding="utf-8") as f:
        for line in f:
            if '"req_latency_ms"' not in line:
                continue
            o = json.loads(line)
            if o.get("type") != "Point":
                continue
            d = o["data"]
            tags = d.get("tags", {})
            if o["metric"] == "req_latency_ms":
                events.append((parse_time(d["time"]), tags.get("instance", "none"),
                               tags.get("ok") == "true", d["value"]))
    if not events:
        sys.exit("Tidak ada data req_result/req_latency_ms. Pakai loadtest/dashboard.js dengan --out json.")

    events.sort(key=lambda e: e[0])
    t0 = events[0][0]
    total = len(events)
    failed = [e for e in events if not e[2]]
    lat_all = sorted(e[3] for e in events)
    lat_ok = sorted(e[3] for e in events if e[2])

    per_instance = Counter(e[1] for e in events if e[2])

    # Durasi gangguan: kelompokkan kegagalan yang berdekatan.
    windows = []
    start = last_fail = None
    for t, _, ok, _ in events:
        if not ok:
            if start is None:
                start = t
            last_fail = t
        elif start is not None and t - last_fail >= args.gap:
            windows.append((start, t))  # berakhir saat sukses berikutnya
            start = None
    if start is not None:
        windows.append((start, events[-1][0]))
    longest = max(((b - a) for a, b in windows), default=0.0)

    print(f"Berkas            : {os.path.basename(args.file)}")
    print(f"Total request     : {total}")
    print(f"Request gagal     : {len(failed)} ({len(failed) / total * 100:.2f}%)")
    print(f"Latency semua     : avg {sum(lat_all) / total:.1f} ms | p95 {percentile(lat_all, 95):.1f} ms")
    if lat_ok:
        print(f"Latency sukses    : avg {sum(lat_ok) / len(lat_ok):.1f} ms | p95 {percentile(lat_ok, 95):.1f} ms")
    print(f"Gangguan terlihat : {len(windows)} jendela, terpanjang {longest:.2f} detik")
    for a, b in windows:
        print(f"  mulai t+{a - t0:.1f}s sampai t+{b - t0:.1f}s ({b - a:.2f} detik)")
    print("Distribusi X-App-Instance (request sukses):")
    ok_total = sum(per_instance.values()) or 1
    for inst, n in per_instance.most_common():
        print(f"  {inst:<40} {n:>7}  {n / ok_total * 100:5.1f}%")

    if args.csv:
        new = not os.path.exists(args.csv)
        with open(args.csv, "a", newline="", encoding="utf-8") as f:
            w = csv.writer(f)
            if new:
                w.writerow(["berkas", "total", "gagal", "persen_gagal", "avg_ms", "p95_ms",
                            "gangguan_terpanjang_detik", "instance_distribusi"])
            w.writerow([os.path.basename(args.file), total, len(failed),
                        f"{len(failed) / total * 100:.2f}", f"{sum(lat_all) / total:.1f}",
                        f"{percentile(lat_all, 95):.1f}", f"{longest:.2f}",
                        ";".join(f"{k}={v}" for k, v in per_instance.most_common())])


if __name__ == "__main__":
    main()
