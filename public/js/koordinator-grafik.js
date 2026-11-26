// Grafik batang hasil panen per bulan. Data dibaca dari atribut data-chart (tanpa skrip inline).
(function () {
    var el = document.getElementById('grafik-produksi');
    if (!el || typeof Chart === 'undefined') { return; }

    var rows = JSON.parse(el.getAttribute('data-chart') || '[]');
    var fmt = new Intl.NumberFormat('id-ID');

    new Chart(el, {
        type: 'bar',
        data: {
            labels: rows.map(function (r) { return r.label; }),
            datasets: [{
                label: 'Hasil panen (kg)',
                data: rows.map(function (r) { return r.kg; }),
                backgroundColor: '#0284C7',
                borderColor: '#0369A1',
                borderWidth: 0,
                borderRadius: 8,
                maxBarThickness: 48
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function (c) { return fmt.format(c.parsed.y) + ' kg'; } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#334155', font: { size: 14 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0' },
                    title: { display: true, text: 'kg', color: '#334155', font: { size: 14 } },
                    ticks: { color: '#334155', font: { size: 14 }, callback: function (v) { return fmt.format(v); } }
                }
            }
        }
    });
})();
