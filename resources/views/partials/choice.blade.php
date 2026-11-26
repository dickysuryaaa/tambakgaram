{{--
    Kartu pilihan (radio-card) pengganti <select>. Mudah diketuk jari, label selalu terlihat.
    Perlu: $name, $legend, $options ([nilai => ['label' => .., 'icon' => .., 'tone' => ok|warn|bad|sea|mute, 'desc' => ..]]),
    $selected. Opsional: $required (default true), $cols (cols-2|cols-3|cols-auto), $help.
--}}
@php
    $required = $required ?? true;
    $cols = $cols ?? 'cols-2';
    $idBase = $idBase ?? $name;
@endphp
<fieldset class="field" @error($name) aria-describedby="{{ $idBase }}-error" @enderror>
    <legend class="form-label fs-6">{{ $legend }} @if ($required)<span class="req" aria-hidden="true">*</span>@endif</legend>
    @if (! empty($help))<p class="form-text mt-0 mb-2">{{ $help }}</p>@endif
    <div class="choice-grid {{ $cols }} @error($name) is-invalid-group @enderror">
        @foreach ($options as $value => $o)
            <label class="choice tone-{{ $o['tone'] ?? 'mute' }}">
                <input type="radio" name="{{ $name }}" id="{{ $idBase }}-{{ $value }}" value="{{ $value }}" @checked((string) $selected === (string) $value) @if ($required) required @endif>
                <span class="choice-body">
                    <span class="ic"><i class="bi {{ $o['icon'] ?? 'bi-circle' }}" aria-hidden="true"></i></span>
                    <span>
                        <span class="t">{{ $o['label'] }}</span>
                        @if (! empty($o['desc']))<span class="d">{{ $o['desc'] }}</span>@endif
                    </span>
                </span>
            </label>
        @endforeach
    </div>
    @error($name)<div id="{{ $idBase }}-error" class="invalid-feedback d-flex" role="alert">{{ $message }}</div>@enderror
</fieldset>
