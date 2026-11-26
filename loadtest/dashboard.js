// Uji beban kontinu ke GET /test/read-only/dashboard (PRD 12.4 / 12.6).
//
// Contoh:
//   k6 run -e BASE_URL=http://IP-NODE:30080 -e RATE=50 -e DURATION=120s \
//     --out json=hasil/s2-r3-01.json loadtest/dashboard.js
//
// Variabel kontrol (harus sama di semua run): RATE, DURATION, TIMEOUT.
import http from 'k6/http';
import { check } from 'k6';
import { Counter, Trend } from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:30080';
const PATH = __ENV.TARGET_PATH || '/test/read-only/dashboard';
const RATE = parseInt(__ENV.RATE || '50'); // request per detik, tetap
const DURATION = __ENV.DURATION || '120s';
const TIMEOUT = __ENV.TIMEOUT || '3s';

// Counter/Trend diberi tag `instance` (nilai header X-App-Instance) dan `ok`.
// Request yang gagal sebelum ada respons diberi instance "none".
const results = new Counter('req_result');
const latency = new Trend('req_latency_ms', true);

export const options = {
  scenarios: {
    kontinu: {
      // Laju tetap walau server lambat/mati, sehingga data antar run sebanding.
      executor: 'constant-arrival-rate',
      rate: RATE,
      timeUnit: '1s',
      duration: DURATION,
      preAllocatedVUs: Math.max(20, RATE),
      maxVUs: Math.max(100, RATE * 4),
    },
  },
  // Tidak menghentikan run saat ada kegagalan; hanya ringkasan.
  thresholds: {
    http_req_failed: [{ threshold: 'rate<1', abortOnFail: false }],
  },
  summaryTrendStats: ['avg', 'min', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

export default function () {
  const res = http.get(`${BASE_URL}${PATH}`, {
    timeout: TIMEOUT,
    tags: { name: 'dashboard' },
  });

  const ok = check(res, { 'status 200': (r) => r.status === 200 });
  const instance = res.headers['X-App-Instance'] || 'none';
  const tags = { instance, ok: ok ? 'true' : 'false' };

  results.add(1, tags);
  latency.add(res.timings.duration, tags);
}

export function handleSummary(data) {
  const m = data.metrics;
  const fmt = (v) => (v === undefined ? 'n/a' : v.toFixed(2));
  const dur = m.http_req_duration.values;
  const failed = m.http_req_failed ? m.http_req_failed.values : { passes: 0, rate: 0 };
  const total = m.http_reqs.values.count;
  const text = [
    '',
    `Total request   : ${total}`,
    `Request gagal   : ${failed.passes} (${(failed.rate * 100).toFixed(2)}%)`,
    `Latency avg     : ${fmt(dur.avg)} ms`,
    `Latency p95     : ${fmt(dur['p(95)'])} ms`,
    `Latency p99     : ${fmt(dur['p(99)'])} ms`,
    `Latency max     : ${fmt(dur.max)} ms`,
    '',
    'Distribusi X-App-Instance dan durasi gangguan: jalankan',
    '  python loadtest/analyze.py <file hasil json>',
    '',
  ].join('\n');
  return { stdout: text };
}
