#!/bin/sh
# Menjalankan PHP-FPM dan Nginx dalam satu container.
# Jika salah satu proses mati, script keluar dengan kode non-nol sehingga
# kubelet me-restart container (perilaku yang diharapkan pada eksperimen).
set -eu

cd /var/www/html

# Cache config dibuat saat start dari env pod (ConfigMap/Secret), bukan saat build.
# Catatan: route:cache tidak dipakai karena routes/test.php memakai closure.
php artisan config:cache

php-fpm -F &
FPM_PID=$!
nginx -g 'daemon off;' &
NGINX_PID=$!

stop() {
    kill -QUIT "$NGINX_PID" 2>/dev/null || true
    kill -QUIT "$FPM_PID" 2>/dev/null || true
    wait "$NGINX_PID" 2>/dev/null || true
    wait "$FPM_PID" 2>/dev/null || true
    exit 0
}
trap stop TERM INT

while kill -0 "$FPM_PID" 2>/dev/null && kill -0 "$NGINX_PID" 2>/dev/null; do
    sleep 0.2
done

echo "Salah satu proses (php-fpm/nginx) berhenti, container keluar." >&2
kill -TERM "$NGINX_PID" "$FPM_PID" 2>/dev/null || true
exit 1
