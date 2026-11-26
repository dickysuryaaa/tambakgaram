{{-- Pesan konfirmasi tampil sebagai toast di atas layar; simtag.js menutupnya otomatis (kecuali pesan galat). --}}
<div class="toast-stack">
@if (session('success'))
    <div class="toast-simtag toast-ok" role="status">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i><span class="msg">{{ session('success') }}</span>
        <button type="button" aria-label="Tutup pesan"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
@endif
@if (session('status'))
    <div class="toast-simtag toast-info" role="status">
        <i class="bi bi-info-circle-fill" aria-hidden="true"></i><span class="msg">{{ session('status') }}</span>
        <button type="button" aria-label="Tutup pesan"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
@endif
@if ($errors->any() && ! $errors->has('username'))
    <div class="toast-simtag toast-bad" role="alert" data-auto="off">
        <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i><span class="msg">Catatan belum tersimpan. Mohon periksa isian yang ditandai di bawah.</span>
        <button type="button" aria-label="Tutup pesan"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
@endif
@if (session('error'))
    <div class="toast-simtag toast-bad" role="alert" data-auto="off">
        <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i><span class="msg">{{ session('error') }}</span>
        <button type="button" aria-label="Tutup pesan"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
@endif
</div>
