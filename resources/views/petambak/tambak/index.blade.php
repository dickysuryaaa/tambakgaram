@extends('layouts.petambak')
@section('title', 'Tambak Saya')

@section('content')
<div class="page-head">
    <div>
        <h1>Tambak Saya</h1>
        <p class="lead-text">Cari, saring, dan buka detail setiap petak tambak Anda.</p>
    </div>
</div>
@include('partials.pond-board', ['ponds' => $ponds, 'showStats' => false])
@endsection
