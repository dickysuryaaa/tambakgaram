#!/usr/bin/env python3
"""Analisis hasil k6 (--out json=...) untuk penelitian:

"Evaluasi Waktu Pemulihan dan Ketersediaan Layanan Kubernetes pada
Skenario Kegagalan Pod dan Worker Node"

Laporan eksplisit:
  a. Durasi gangguan yang dialami klien (gagal pertama -> sukses konsisten,
     gabungan gap < --gap detik dianggap satu gangguan), per jendela dan
     terpanjang.
  b. Jumlah & persentase request gagal vs sukses.
  c. Latency normal (di luar gangguan) vs selama gangguan:
     avg, p50, p95, p99, max.
  d. Distribusi header X-App-Instance secara keseluruhan dan terpisah
     SEBELUM / SELAMA / SETELAH gangguan (bukti failover antar pod).

Pemakaian:
  python loadtest/analyze.py hasil/s1-r1-01.json [--gap 1.0] [--csv hasil/ringkasan.csv]
"""
import argparse
import csv
import json
import os
import sys
from collections import Counter
from datetime import datetime


def parse_time(s):
    # k6 menulis waktu ISO-8601 dengan Z dan zona; fromisoformat butuh +00:00.
    return datetime.fromisoformat(s.replace("Z", "+00:00")).timestamp()


def percentile(sorted_vals, p):
    if not sorted_vals:
        return float("nan")
    k = (len(sorted_vals) - 1) * p / 100
    lo = int(k)
    hi = min(lo + 1, len(sorted_vals) - 1)
    return sorted_vals[lo] + (sorted_vals[hi] - sorted_vals[lo]) * (k - lo)


def stats(sorted_vals):
    if not sorted_vals:
        return {"n": 0, "avg": float("nan"), "p50": float("nan"),
                "p95": float("nan"), "p99": float("nan"), "max": float("nan")}
    return {
        "n": len(sorted_vals),
        "avg": sum(sorted_vals) / len(sorted_vals),
        "p50": percentile(sorted_vals, 50),
        "p95": percentile(sorted_vals, 95),
        "p99": percentile(sorted_vals, 99),
        "max": sorted_vals[-1],
    }


def fmt_stats(s, unit="ms"):
    if s["n"] == 0:
        return "n/a"
    return (f"n={s['n']:<6} avg {s['avg']:7.1f} {unit} | p50 {s['p50']:7.1f} | "
            f"p95 {s['p95']:7.1f} | p99 {s['p99']:7.1f} | max {s['max']:7.1f} {unit}")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("file")
    ap.add_argument("--gap", type=float, default=1.0,
                    help="sukses yang memisahkan dua gangguan minimal selama ini (detik)")
    ap.add_argument("--csv", help="tambahkan satu baris ringkasan ke file CSV ini")
    args = ap.parse_args()

    events = []  # (waktu, instance, ok, latency_ms)
    with open(args.file, encoding="utf-8") as f:
        for line in f:
            if '"req_latency_ms"' not in line:
                continue
            o = json.loads(line)
            if o.get("type") != "Point" or o.get("metric") != "req_latency_ms":
                continue
            d = o["data"]
            tags = d.get("tags", {})
            events.append((parse_time(d["time"]), tags.get("instance", "none"),
                           tags.get("ok") == "true", d["value"]))
    if not events:
        sys.exit("Tidak ada data req_latency_ms. Jalankan k6 dengan --out json=... "
                 "memakai loadtest/dashboard.js.")

    events.sort(key=lambda e: e[0])
    t0 = events[0][0]
    total = len(events)
    failed = [e for e in events if not e[2]]
    succeeded = [e for e in events if e[2]]

    # ---- Jendela gangguan: gagal pertama -> sukses pertama yang stabil ----
    windows = []  # (start, end_recovery, n_fail di jendela)
    start = last_fail = None
    n_fail_win = 0
    for t, _, ok, _ in events:
        if not ok:
            if start is None:
                start = t
            last_fail = t
            n_fail_win += 1
        elif start is not None and t - last_fail >= args.gap:
            windows.append((start, t, n_fail_win))
            start = None
            n_fail_win = 0
    if start is not None:
        # Tidak pernah pulih sampai akhir run; akhir = request terakhir.
        windows.append((start, events[-1][0], n_fail_win))
        start = None

    longest = max(((b - a) for a, b, _ in windows), default=0.0)
    total_disruption = sum(b - a for a, b, _ in windows)

    # ---- Klasifikasi event: sebelum / selama / sesudah gangguan ----
    def phase(t):
        for a, b, _ in windows:
            if a <= t <= b:
                return "during"
        # setelah jendela pertama yang sudah lewat = sesudah gangguan
        if windows and t > windows[0][1]:
            return "after"
        return "before"

    phases = [phase(e[0]) for e in events]

    lat_normal = sorted(e[3] for e, ph in zip(events, phases) if ph != "during")
    lat_during = sorted(e[3] for e, ph in zip(events, phases) if ph == "during")

    def inst_counter(sel):
        c = Counter(e[1] for e, ph in zip(events, phases)
                    if sel(ph))
        return c

    per_instance_all_ok = Counter(e[1] for e in succeeded)
    per_instance_before = inst_counter(lambda ph: ph == "before")
    per_instance_during = inst_counter(lambda ph: ph == "during")
    per_instance_after = inst_counter(lambda ph: ph == "after")

    # ---- Cetak laporan ----
    print("=" * 78)
    print(f"Berkas                : {os.path.basename(args.file)}")
    print(f"Total request         : {total}")
    print(f"  Sukses              : {len(succeeded)} ({len(succeeded) / total * 100:.2f}%)")
    print(f"  Gagal               : {len(failed)} ({len(failed) / total * 100:.2f}%)")
    print()

    print("--- A. Durasi gangguan yang dialami klien ---")
    if not windows:
        print("Tidak ada gangguan terdeteksi (semua request sukses).")
    else:
        print(f"Jumlah jendela gangguan : {len(windows)}")
        print(f"Total durasi gangguan   : {total_disruption:.2f} detik")
        print(f"Gangguan terpanjang     : {longest:.2f} detik "
              "(gagal pertama -> sukses stabil berikutnya)")
        for i, (a, b, n) in enumerate(windows, 1):
            print(f"  jendela {i}: t+{a - t0:7.1f}s .. t+{b - t0:7.1f}s "
                  f" durasi {b - a:7.2f}s  gagal {n}")
    print()

    print("--- B. Latency (kondisi normal vs selama gangguan) ---")
    print(f"  Normal (di luar jendela) : {fmt_stats(stats(lat_normal))}")
    print(f"  Selama gangguan          : {fmt_stats(stats(lat_during))}")
    print()

    print("--- C. Distribusi X-App-Instance (bukti failover) ---")
    print(f"  SEMUA sukses ({len(succeeded)} request):")
    for inst, n in per_instance_all_ok.most_common():
        print(f"    {inst:<40} {n:>7}  {n / (len(succeeded) or 1) * 100:5.1f}%")
    print(f"  SEBELUM gangguan:")
    for inst, n in per_instance_before.most_common():
        print(f"    {inst:<40} {n:>7}")
    print(f"  SELAMA gangguan (sukses yang lolos / none):")
    for inst, n in per_instance_during.most_common():
        print(f"    {inst:<40} {n:>7}")
    print(f"  SETELAH gangguan:")
    for inst, n in per_instance_after.most_common():
        print(f"    {inst:<40} {n:>7}")
    pods_before = set(per_instance_before) - {"none"}
    pods_during = set(per_instance_during) - {"none"}
    pods_after = set(per_instance_after) - {"none"}
    new_after = pods_after - pods_before
    if new_after:
        print(f"  >> Pod BARU yang muncul setelah gangguan (failover terbukti): "
              f"{', '.join(sorted(new_after))}")
    elif windows:
        print("  >> Tidak ada instance baru terdeteksi (pod lama kembali melayani).")
    print("=" * 78)

    # ---- CSV ringkasan ----
    if args.csv:
        s_n, s_d = stats(lat_normal), stats(lat_during)
        new = not os.path.exists(args.csv)
        with open(args.csv, "a", newline="", encoding="utf-8") as f:
            w = csv.writer(f)
            if new:
                w.writerow(["berkas", "total", "sukses", "gagal", "persen_gagal",
                            "jendela_gangguan", "total_gangguan_s", "gangguan_terpanjang_s",
                            "lat_normal_avg_ms", "lat_normal_p95_ms", "lat_normal_p99_ms",
                            "lat_during_avg_ms", "lat_during_p95_ms", "lat_during_p99_ms",
                            "instance_baru_setelah_gangguan", "instance_distribusi"])
            w.writerow([os.path.basename(args.file), total, len(succeeded), len(failed),
                        f"{len(failed) / total * 100:.2f}",
                        len(windows), f"{total_disruption:.2f}", f"{longest:.2f}",
                        f"{s_n['avg']:.1f}", f"{s_n['p95']:.1f}", f"{s_n['p99']:.1f}",
                        f"{s_d['avg']:.1f}", f"{s_d['p95']:.1f}", f"{s_d['p99']:.1f}",
                        ";".join(sorted(new_after)) if windows else "",
                        ";".join(f"{k}={v}" for k, v in per_instance_all_ok.most_common())])


if __name__ == "__main__":
    main()
