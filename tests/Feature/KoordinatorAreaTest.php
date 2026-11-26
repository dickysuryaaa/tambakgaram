<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KoordinatorAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function koordinator(): static
    {
        return $this->actingAs(Member::where('username', 'koordinator')->firstOrFail());
    }

    private function pondData(array $over = []): array
    {
        return $over + [
            'pond_code' => 'TG-099', 'pond_name' => 'Tambak Uji',
            'member_id' => Member::where('username', 'petambak1')->value('member_id'),
            'village' => 'Desa A', 'district' => 'Kec B', 'regency' => 'Kab C',
            'latitude' => '', 'longitude' => '', 'area_m2' => '1.200',
            'operational_status' => 'persiapan', 'notes' => '',
        ];
    }

    public function test_every_coordinator_page_requires_coordinator_role(): void
    {
        $urls = ['/koordinator', '/koordinator/anggota', '/koordinator/tambak', '/koordinator/kendala', '/koordinator/siklus',
            '/koordinator/panen', '/koordinator/pengamatan-air', '/koordinator/rekap', '/koordinator/anggota/create', '/koordinator/tambak/create'];

        foreach ($urls as $u) {
            $this->get($u)->assertRedirect('/login');
        }
        $this->actingAs(Member::where('username', 'petambak1')->firstOrFail());
        foreach ($urls as $u) {
            $this->get($u)->assertForbidden();
        }
        $this->post('/koordinator/anggota', [])->assertForbidden();
        $this->delete('/koordinator/tambak/1')->assertForbidden();
        $this->put('/koordinator/kendala/1', [])->assertForbidden();
    }

    public function test_every_coordinator_page_renders(): void
    {
        $this->koordinator();
        $issue = Issue::firstOrFail();
        $pond = Pond::firstOrFail();
        $member = Member::where('username', 'petambak1')->firstOrFail();
        $urls = ['/koordinator', '/koordinator/anggota', '/koordinator/tambak', '/koordinator/kendala', '/koordinator/siklus',
            '/koordinator/panen', '/koordinator/pengamatan-air', '/koordinator/rekap', '/koordinator/anggota/create', '/koordinator/tambak/create',
            "/koordinator/anggota/{$member->member_id}/edit", "/koordinator/tambak/{$pond->pond_id}", "/koordinator/tambak/{$pond->pond_id}/edit",
            '/koordinator/kendala/'.$issue->issue_id];
        foreach ($urls as $u) {
            $this->get($u)->assertOk();
        }
    }

    public function test_menu_only_has_mvp_items(): void
    {
        $page = $this->koordinator()->get('/koordinator')->assertOk()->getContent();
        preg_match('/<nav class="kelola-nav".*?<\/nav>/s', $page, $nav);
        $html = $nav[0] ?? '';
        $this->assertNotSame('', $html);
        foreach (['Dashboard', 'Anggota', 'Tambak', 'Siklus &amp; Kegiatan', 'Hasil Panen', 'Pengamatan Air', 'Kendala', 'Rekap Periode'] as $m) {
            $this->assertStringContainsString($m, $html);
        }
        foreach (['Cuaca', 'Peta', 'K-Means', 'Ekspor', 'Foto'] as $m) {
            $this->assertStringNotContainsString($m, $html);
        }
    }

    public function test_dashboard_follows_prd_order_and_totals(): void
    {
        $from = Carbon::today()->subDays(89)->toDateString();
        $to = Carbon::today()->toDateString();
        $html = $this->koordinator()->get("/koordinator?dari=$from&sampai=$to")->assertOk()->getContent();

        $order = ['Dari tanggal', 'Total Anggota', 'Total Tambak', 'Tambak Berdasarkan Status', 'Total Hasil Panen',
            'Kendala yang Belum Selesai', 'Grafik Produksi per Bulan', 'Aktivitas Terbaru'];
        $last = -1;
        foreach ($order as $t) {
            $pos = strpos($html, $t);
            $this->assertNotFalse($pos, "Komponen '$t' tidak ada");
            $this->assertGreaterThan($last, $pos, "Urutan salah pada '$t'");
            $last = $pos;
        }

        // 4200 kg + 3,1 ton (3100 kg) di dalam 90 hari; 5800 kg (95 hari lalu) di luar periode.
        $this->assertStringContainsString('7.300', $html);
        $this->assertStringContainsString('data contoh', $html);
        $this->assertStringContainsString('Sumber data', $html);
        $this->assertStringContainsString('chart.umd.js', $html);
        $this->assertStringContainsString('data-chart=', $html);
        // 3 petambak; 2 kendala belum selesai (terbuka + diproses).
        $this->assertStringContainsString('Kendala yang Belum Selesai (2)', $html);
    }

    public function test_dashboard_period_changes_harvest_total_and_bad_dates_fall_back(): void
    {
        $from = Carbon::today()->subDays(30)->toDateString();
        $to = Carbon::today()->toDateString();
        $this->koordinator()->get("/koordinator?dari=$from&sampai=$to")->assertOk()->assertSee('3.100')->assertDontSee('7.300');

        $this->koordinator()->get('/koordinator?dari=bukan-tanggal&sampai=2026-02-30')->assertOk()->assertSee('90 hari terakhir');
        $this->koordinator()->get("/koordinator?dari=$to&sampai=$from")->assertOk()->assertSee('tidak sesuai');
    }

    public function test_member_crud_and_validation(): void
    {
        $this->koordinator();
        $this->post('/koordinator/anggota', ['name' => '', 'username' => 'a b', 'password' => '123', 'role' => 'x', 'status' => 'y'])
            ->assertSessionHasErrors(['name', 'username', 'password', 'role', 'status']);

        $this->post('/koordinator/anggota', ['name' => 'Anggota Baru', 'username' => 'baru1', 'password' => 'rahasia123', 'phone' => '0812-3456', 'role' => 'petambak', 'status' => 'aktif'])
            ->assertRedirect('/koordinator/anggota');
        $m = Member::where('username', 'baru1')->firstOrFail();
        $this->assertNotSame('rahasia123', $m->password);

        $this->post('/koordinator/anggota', ['name' => 'Dobel', 'username' => 'baru1', 'password' => 'rahasia123', 'role' => 'petambak', 'status' => 'aktif'])
            ->assertSessionHasErrors('username');

        // Kata sandi kosong saat ubah = tidak berubah
        $hash = $m->password;
        $this->put("/koordinator/anggota/{$m->member_id}", ['name' => 'Nama Ubah', 'username' => 'baru1', 'password' => '', 'phone' => '', 'role' => 'petambak', 'status' => 'nonaktif'])
            ->assertRedirect('/koordinator/anggota');
        $m->refresh();
        $this->assertSame('Nama Ubah', $m->name);
        $this->assertSame($hash, $m->password);
        $this->assertNull($m->phone);
        $this->assertSame('nonaktif', $m->status);

        $this->get('/koordinator/anggota?q=Nama+Ubah')->assertSee('Nama Ubah')->assertDontSee('Petambak Satu');

        $this->delete("/koordinator/anggota/{$m->member_id}")->assertRedirect('/koordinator/anggota');
        $this->assertDatabaseMissing('members', ['username' => 'baru1']);
    }

    public function test_member_with_records_cannot_be_deleted_and_self_is_protected(): void
    {
        $this->koordinator();
        $owner = Member::where('username', 'petambak1')->firstOrFail();
        $this->delete("/koordinator/anggota/{$owner->member_id}")->assertSessionHas('error');
        $this->assertDatabaseHas('members', ['username' => 'petambak1']);

        $me = Member::where('username', 'koordinator')->firstOrFail();
        $this->delete("/koordinator/anggota/{$me->member_id}")->assertSessionHas('error');
        $this->put("/koordinator/anggota/{$me->member_id}", ['name' => $me->name, 'username' => 'koordinator', 'role' => 'koordinator_admin', 'status' => 'nonaktif'])
            ->assertSessionHas('error');
        $this->assertSame('aktif', $me->fresh()->status);

        // Pemilik tambak tidak boleh diubah menjadi koordinator
        $this->put("/koordinator/anggota/{$owner->member_id}", ['name' => $owner->name, 'username' => 'petambak1', 'role' => 'koordinator_admin', 'status' => 'aktif'])
            ->assertSessionHasErrors('role');
    }

    public function test_pond_crud_with_null_coordinates_and_comma_numbers(): void
    {
        $this->koordinator();
        $this->post('/koordinator/tambak', $this->pondData(['pond_code' => 'TG-001']))->assertSessionHasErrors('pond_code');
        $this->post('/koordinator/tambak', $this->pondData(['member_id' => Member::where('username', 'koordinator')->value('member_id')]))->assertSessionHasErrors('member_id');

        $this->post('/koordinator/tambak', $this->pondData(['latitude' => '-7,5', 'longitude' => '113,25']))->assertRedirect();
        $pond = Pond::where('pond_code', 'TG-099')->firstOrFail();
        $this->assertEquals(-7.5, (float) $pond->latitude);
        $this->assertSame(1200, $pond->area_m2); // "1.200" dibaca sebagai seribu dua ratus

        $this->put("/koordinator/tambak/{$pond->pond_id}", $this->pondData(['operational_status' => 'penguapan', 'latitude' => '', 'longitude' => '']))
            ->assertRedirect("/koordinator/tambak/{$pond->pond_id}");
        $pond->refresh();
        $this->assertSame('penguapan', $pond->operational_status);
        $this->assertNull($pond->latitude);
        $this->assertNull($pond->longitude);

        $this->post('/koordinator/tambak', $this->pondData(['pond_code' => 'TG-098', 'latitude' => '95']))->assertSessionHasErrors('latitude');
        $this->post('/koordinator/tambak', $this->pondData(['pond_code' => 'TG-098', 'operational_status' => 'ngawur']))->assertSessionHasErrors('operational_status');

        $this->delete("/koordinator/tambak/{$pond->pond_id}")->assertRedirect('/koordinator/tambak');
        $this->assertDatabaseMissing('ponds', ['pond_code' => 'TG-099']);
    }

    public function test_pond_with_records_cannot_be_deleted(): void
    {
        $pond = Pond::where('pond_code', 'TG-001')->firstOrFail();
        $this->koordinator()->delete("/koordinator/tambak/{$pond->pond_id}")->assertSessionHas('error');
        $this->assertDatabaseHas('ponds', ['pond_code' => 'TG-001']);
    }

    public function test_pond_filters(): void
    {
        $this->koordinator();
        $this->get('/koordinator/tambak?status=jeda')->assertSee('TG-004')->assertDontSee('TG-001');
        $this->get('/koordinator/tambak?q=TG-003')->assertSee('TG-003')->assertDontSee('TG-002');
        $owner = Member::where('username', 'petambak3')->value('member_id');
        $this->get("/koordinator/tambak?anggota=$owner")->assertSee('TG-005')->assertDontSee('TG-001');
    }

    public function test_issue_follow_up_sets_and_clears_resolved_time(): void
    {
        $this->koordinator();
        $issue = Issue::where('status', 'terbuka')->firstOrFail();

        $this->put("/koordinator/kendala/{$issue->issue_id}", ['status' => 'ngawur', 'priority' => 'sedang'])->assertSessionHasErrors('status');

        $this->put("/koordinator/kendala/{$issue->issue_id}", ['status' => 'diproses', 'priority' => 'tinggi', 'follow_up_notes' => 'Dicek Kamis'])
            ->assertRedirect("/koordinator/kendala/{$issue->issue_id}");
        $issue->refresh();
        $this->assertSame('diproses', $issue->status);
        $this->assertSame('tinggi', $issue->priority);
        $this->assertNull($issue->resolved_at);

        $this->put("/koordinator/kendala/{$issue->issue_id}", ['status' => 'selesai', 'priority' => 'tinggi', 'follow_up_notes' => ''])->assertRedirect();
        $issue->refresh();
        $this->assertNotNull($issue->resolved_at);
        $this->assertNull($issue->follow_up_notes);

        $this->put("/koordinator/kendala/{$issue->issue_id}", ['status' => 'terbuka', 'priority' => 'tinggi'])->assertRedirect();
        $this->assertNull($issue->fresh()->resolved_at);

        // Petambak melihat status terbaru pada riwayatnya
        $this->actingAs($issue->pond->member)->get('/beranda')->assertOk();
    }

    public function test_issue_filters_by_pond_member_status_category_and_period(): void
    {
        $this->koordinator();
        $codes = fn (string $qs) => collect($this->get('/koordinator/kendala'.$qs)->assertOk()->viewData('issues')->items())
            ->map(fn ($i) => $i->pond->pond_code)->sort()->values()->all();

        $tg2 = Pond::where('pond_code', 'TG-002')->value('pond_id');
        $this->assertSame(['TG-001', 'TG-002', 'TG-003'], $codes(''));
        $this->assertSame(['TG-002'], $codes("?tambak=$tg2"));
        $this->assertSame(['TG-003'], $codes('?status=terbuka'));
        $this->assertSame(['TG-001'], $codes('?kategori=tanggul_bocor'));
        $owner = Member::where('username', 'petambak2')->value('member_id');
        $this->assertSame(['TG-003'], $codes("?anggota=$owner"));

        // Hanya kendala TG-003 (1 hari lalu) yang masuk dua hari terakhir.
        $from = Carbon::today()->subDays(2)->toDateString();
        $this->assertSame(['TG-003'], $codes("?dari=$from"));
        $to = Carbon::today()->subDays(5)->toDateString();
        $this->assertSame(['TG-001'], $codes("?sampai=$to"));
    }

    public function test_record_lists_filter_and_convert_ton_only_in_total(): void
    {
        $this->koordinator();
        $html = $this->get('/koordinator/panen')->assertOk()->getContent();
        $this->assertStringContainsString('3,1 ton', $html);   // angka asli tidak diubah
        $this->assertStringContainsString('13.100', $html);     // 4200 + 3100 + 5800

        $tg1 = Pond::where('pond_code', 'TG-001')->value('pond_id');
        $this->get("/koordinator/panen?tambak=$tg1")->assertSee('4.200')->assertDontSee('3,1 ton');

        // Nilai tidak diukur tampil "-", bukan 0
        $this->get('/koordinator/pengamatan-air')->assertOk()->assertSee('Tanda "-"', false);
        $statuses = collect($this->get('/koordinator/siklus?status=berjalan')->assertOk()->viewData('cycles')->items())->pluck('cycle_status')->unique()->all();
        $this->assertSame(['berjalan'], $statuses);
    }

    public function test_recap_shows_period_source_and_null_safe_averages(): void
    {
        $from = Carbon::today()->subDays(89)->toDateString();
        $to = Carbon::today()->toDateString();
        $html = $this->koordinator()->get("/koordinator/rekap?dari=$from&sampai=$to")->assertOk()->getContent();

        $this->assertStringContainsString('Periode:', $html);
        $this->assertStringContainsString('Sumber data:', $html);
        $this->assertStringContainsString('data contoh', $html);
        $this->assertStringContainsString('7.300', $html);
        // TG-004 hanya punya pengamatan tanpa angka: rata-rata harus "-", bukan 0.
        $this->assertMatchesRegularExpression('/TG-004\)<\/td>\s*<td class="table-num">1<\/td>\s*<td class="table-num">-<\/td>\s*<td class="table-num">-<\/td>\s*<td class="table-num">-<\/td>/', $html);
    }

    public function test_recap_member_filter_limits_to_that_members_ponds(): void
    {
        $owner = Member::where('username', 'petambak3')->value('member_id');
        $this->koordinator()->get("/koordinator/rekap?anggota=$owner")->assertOk()->assertSee('TG-005')->assertDontSee('TG-001');
    }

    public function test_source_label_is_dropped_when_data_is_not_simulated(): void
    {
        config(['simtag.demo_banner' => false]);
        $html = $this->koordinator()->get('/koordinator/rekap')->assertOk()->getContent();
        $this->assertStringNotContainsString('data contoh', $html);
        $this->assertStringContainsString('Sumber data:', $html);
    }
}
