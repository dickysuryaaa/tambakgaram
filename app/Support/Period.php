<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Rentang tanggal dari query `dari` dan `sampai` (format YYYY-MM-DD). Nilai tidak valid diganti bawaan dan diberi catatan. */
class Period
{
    public function __construct(public ?Carbon $from, public ?Carbon $to, public ?string $notice = null) {}

    /** Rentang wajib: bila kosong atau tidak valid, pakai N hari terakhir. */
    public static function required(Request $request, int $defaultDays = 90): self
    {
        $default = [Carbon::today()->subDays($defaultDays - 1), Carbon::today()];
        [$from, $to, $bad] = self::parse($request);

        if ($bad || ($from && $to && $from->gt($to))) {
            return new self($default[0], $default[1], 'Tanggal yang dimasukkan tidak sesuai, jadi ditampilkan '.$defaultDays.' hari terakhir.');
        }

        return new self($from ?? $default[0], $to ?? $default[1]);
    }

    /** Rentang opsional untuk filter daftar: kosong berarti semua tanggal. */
    public static function optional(Request $request): self
    {
        [$from, $to, $bad] = self::parse($request);

        if ($bad || ($from && $to && $from->gt($to))) {
            return new self(null, null, 'Tanggal yang dimasukkan tidak sesuai, jadi semua tanggal ditampilkan.');
        }

        return new self($from, $to);
    }

    private static function parse(Request $request): array
    {
        $bad = false;
        $read = function (string $key) use ($request, &$bad): ?Carbon {
            $v = $request->query($key);
            if ($v === null || $v === '') {
                return null;
            }
            if (! is_string($v) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $bad = true;

                return null;
            }
            try {
                $d = Carbon::createFromFormat('Y-m-d', $v)->startOfDay();
            } catch (\Throwable) {
                $bad = true;

                return null;
            }
            if ($d->format('Y-m-d') !== $v) {
                $bad = true;

                return null;
            }

            return $d;
        };

        return [$read('dari'), $read('sampai'), $bad];
    }

    /** Untuk kolom DATE. */
    public function whereDate($query, string $column)
    {
        return $query
            ->when($this->from, fn ($q) => $q->where($column, '>=', $this->from->toDateString()))
            ->when($this->to, fn ($q) => $q->where($column, '<=', $this->to->toDateString()));
    }

    /** Untuk kolom DATETIME (hari terakhir ikut dihitung penuh). */
    public function whereDateTime($query, string $column)
    {
        return $query
            ->when($this->from, fn ($q) => $q->where($column, '>=', $this->from->copy()->startOfDay()))
            ->when($this->to, fn ($q) => $q->where($column, '<', $this->to->copy()->addDay()->startOfDay()));
    }

    public function label(): string
    {
        if (! $this->from && ! $this->to) {
            return 'Semua tanggal';
        }

        $f = fn (?Carbon $d) => $d?->translatedFormat('j F Y');

        return match (true) {
            $this->from && $this->to => $f($this->from).' sampai '.$f($this->to),
            (bool) $this->from => 'Mulai '.$f($this->from),
            default => 'Sampai '.$f($this->to),
        };
    }

    /** Daftar bulan (YYYY-MM) yang tercakup rentang wajib. */
    public function months(): array
    {
        $out = [];
        $d = $this->from->copy()->startOfMonth();
        while ($d->lte($this->to)) {
            $out[] = $d->format('Y-m');
            $d->addMonth();
        }

        return $out;
    }
}
