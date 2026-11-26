/* SIMTAG: interaksi ringan tanpa pustaka luar. Tidak menyimpan apa pun di peramban atau server. */
(function () {
    'use strict';

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Pesan konfirmasi (toast): hilang sendiri, kecuali pesan galat ---- */
    var stack = document.querySelector('.toast-stack');
    if (stack) {
        stack.querySelectorAll('.toast-simtag').forEach(function (t) {
            var hide = function () {
                t.classList.add('out');
                setTimeout(function () { t.remove(); }, 300);
            };
            var close = t.querySelector('button');
            if (close) { close.addEventListener('click', hide); }
            if (t.dataset.auto !== 'off') { setTimeout(hide, 7000); }
        });
    }

    /* ---- Penanda sambungan internet di bilah atas ---- */
    document.querySelectorAll('[data-online]').forEach(function (pill) {
        var txt = pill.querySelector('.txt');
        function update() {
            var on = navigator.onLine !== false;
            pill.classList.toggle('off', !on);
            if (txt) { txt.textContent = on ? 'Tersambung' : 'Tidak ada sinyal'; }
            pill.setAttribute('aria-label', on ? 'Tersambung ke internet' : 'Tidak ada sinyal internet');
        }
        window.addEventListener('online', update);
        window.addEventListener('offline', update);
        update();
    });

    /* ---- Laci menu (HP) ---- */
    var lastOpener = null;
    function closeDrawer(d) {
        d.classList.remove('open');
        document.body.style.overflow = '';
        if (lastOpener) { lastOpener.focus(); }
    }
    document.querySelectorAll('[data-drawer-open]').forEach(function (btn) {
        var d = document.getElementById(btn.getAttribute('data-drawer-open'));
        if (!d) { return; }
        btn.addEventListener('click', function () {
            lastOpener = btn;
            d.classList.add('open');
            document.body.style.overflow = 'hidden';
            var first = d.querySelector('a, button');
            if (first) { first.focus(); }
        });
        d.querySelectorAll('[data-drawer-close]').forEach(function (c) {
            c.addEventListener('click', function () { closeDrawer(d); });
        });
        d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeDrawer(d); } });
    });

    /* ---- Tombol tampilkan/sembunyikan kata sandi ---- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        var input = document.getElementById(btn.getAttribute('data-toggle-password'));
        if (!input) { return; }
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            var icon = btn.querySelector('.bi');
            if (icon) { icon.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye'); }
        });
    });

    /* ---- Cegah kirim ganda: tombol simpan berubah jadi "Menyimpan..." ---- */
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function (f) {
        f.addEventListener('submit', function () {
            var b = f.querySelector('button[type="submit"][data-busy]');
            if (b && !f.dataset.sent) {
                f.dataset.sent = '1';
                setTimeout(function () { b.disabled = true; b.textContent = b.getAttribute('data-busy'); }, 0);
            }
        });
    });

    /* ---- Filter cepat, pencarian, dan pilihan tampilan tambak (tanpa memuat ulang halaman) ---- */
    var board = document.querySelector('[data-pond-board]');
    if (!board) { return; }

    var cards = Array.prototype.slice.call(board.querySelectorAll('.pond-item'));
    var rows = Array.prototype.slice.call(board.querySelectorAll('.pond-row'));
    var items = cards.concat(rows);
    var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-filter]'));
    var search = document.getElementById('cari-tambak');
    var clear = document.getElementById('cari-hapus');
    var note = document.getElementById('hasil-cari');
    var empty = document.getElementById('kosong-cari');
    var state = { filter: 'semua', q: '' };

    function norm(s) { return String(s || '').toLowerCase().replace(/\s+/g, ' ').trim(); }

    function apply() {
        var shown = 0;
        items.forEach(function (el) {
            var tags = (el.dataset.tags || '').split(' ');
            var okFilter = state.filter === 'semua' || tags.indexOf(state.filter) !== -1;
            var okSearch = !state.q || norm(el.dataset.search).indexOf(state.q) !== -1;
            el.hidden = !(okFilter && okSearch);
            if (!el.hidden && el.classList.contains('pond-item')) { shown++; }
        });
        triggers.forEach(function (b) {
            b.setAttribute('aria-pressed', b.dataset.filter === state.filter ? 'true' : 'false');
        });
        if (note) { note.textContent = 'Menampilkan ' + shown + ' dari ' + cards.length + ' tambak'; }
        if (empty) { empty.hidden = shown !== 0 || cards.length === 0; }
        if (clear) { clear.hidden = !state.q; }
    }

    triggers.forEach(function (b) {
        b.addEventListener('click', function () {
            state.filter = b.dataset.filter;
            apply();
            // Kartu ringkasan di atas: bawa pengguna ke daftar di bawahnya.
            if (b.classList.contains('stat-btn')) {
                board.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
            }
        });
    });

    if (search) {
        search.addEventListener('input', function () { state.q = norm(search.value); apply(); });
    }
    if (clear) {
        clear.addEventListener('click', function () { search.value = ''; state.q = ''; apply(); search.focus(); });
    }
    apply();

    var viewBtns = Array.prototype.slice.call(board.querySelectorAll('[data-view]'));
    viewBtns.forEach(function (b) {
        b.addEventListener('click', function () {
            var v = b.dataset.view;
            viewBtns.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
            board.querySelectorAll('[data-view-pane]').forEach(function (p) { p.hidden = p.dataset.viewPane !== v; });
        });
    });

    /* ---- Panel detail dan input cepat ---- */
    var dlg = document.getElementById('panel-tambak');
    if (!dlg || typeof dlg.showModal !== 'function') { return; }

    var opener = null;
    function fill(el, act) {
        dlg.querySelector('[data-f=nama]').textContent = el.dataset.name;
        dlg.querySelector('[data-f=kode]').textContent = 'Kode ' + el.dataset.code;
        dlg.querySelector('[data-f=luas]').textContent = el.dataset.area + ' m²';
        dlg.querySelector('[data-f=tahap]').textContent = el.dataset.stage;
        dlg.querySelector('[data-f=kendala]').textContent = el.dataset.issues === '0' ? 'Tidak ada' : el.dataset.issues + ' belum selesai';
        var badge = el.querySelector('[data-badge]');
        dlg.querySelector('[data-f=status]').innerHTML = badge ? badge.innerHTML : '';
        dlg.querySelectorAll('[data-act]').forEach(function (a) {
            var url = a.dataset.act === 'detail' ? el.dataset.showUrl : a.dataset.base + '?tambak=' + encodeURIComponent(el.dataset.id);
            a.setAttribute('href', url);
            var main = a.dataset.act === act;
            a.classList.toggle('btn-primary', main);
            a.classList.toggle('btn-outline-primary', !main);
        });
    }

    board.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-open]');
        if (!btn) { return; }
        var el = btn.closest('.pond-item');
        if (!el) { return; }
        opener = btn;
        fill(el, btn.dataset.open);
        dlg.showModal();
    });
    dlg.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', function () { dlg.close(); }); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); } });
    dlg.addEventListener('close', function () { if (opener) { opener.focus(); } });
})();
