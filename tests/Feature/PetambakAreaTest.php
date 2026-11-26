<?php

namespace Tests\Feature;

use App\Models\Harvest;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Models\ProductionActivity;
use App\Models\ProductionCycle;
use App\Models\WaterObservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetambakAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $username): Member
    {
        return Member::where('username', $username)->firstOrFail();
    }

    private function pond(string $code): Pond
    {
        return Pond::where('pond_code', $code)->firstOrFail();
    }

    private function runningCycle(string $code): ProductionCycle
    {
        return ProductionCycle::where('pond_id', $this->pond($code)->pond_id)->where('cycle_status', 'berjalan')->firstOrFail();
    }

    // ---- Login dan role (FR-01) ----

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/beranda')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Nama pengguna')->assertSee('Kata sandi');
    }

    public function test_login_with_username_and_password(): void
    {
        $this->post('/login', ['username' => 'petambak1', 'password' => 'password'])->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user('petambak1'));
        $this->get('/')->assertRedirect('/beranda');
    }

    public function test_wrong_password_is_rejected_with_simple_message(): void
    {
        $this->from('/login')->post('/login', ['username' => 'petambak1', 'password' => 'salah'])
            ->assertRedirect('/login')->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->user('petambak1')->update(['status' => 'nonaktif']);
        $this->post('/login', ['username' => 'petambak1', 'password' => 'password'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_account_deactivated_mid_session_is_logged_out(): void
    {
        $member = $this->user('petambak1');
        $this->actingAs($member)->get('/beranda')->assertOk();
        $member->update(['status' => 'nonaktif']);
        $this->get('/beranda')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_roles_are_separated(): void
    {
        $this->actingAs($this->user('koordinator'))->get('/beranda')->assertForbidden();
        $this->post('/catat/kendala', [])->assertForbidden();
        $this->get('/koordinator')->assertOk();

        $this->actingAs($this->user('petambak1'))->get('/koordinator')->assertForbidden();
    }

    public function test_coordinator_login_lands_on_coordinator_area(): void
    {
        $this->post('/login', ['username' => 'koordinator', 'password' => 'password']);
        $this->get('/')->assertRedirect('/koordinator');
    }

    public function test_logout(): void
    {
        $this->actingAs($this->user('petambak1'))->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ---- Beranda dan navigasi (Bagian 7.3) ----

    public function test_home_has_components_in_prd_order_and_only_own_ponds(): void
    {
        $html = $this->actingAs($this->user('petambak1'))->get('/beranda')->assertOk()->getContent();

        $order = ['Selamat datang', 'Tambak Saya', 'Status Tambak', 'Catat Panen', 'Catat Pengamatan Air', 'Laporkan Kendala', 'Catatan Terakhir'];
        $last = -1;
        foreach ($order as $text) {
            // Cari setelah menu navigasi (nav memuat kata "Tambak Saya" lebih awal).
            $pos = strpos($html, $text, $text === 'Tambak Saya' ? strpos($html, 'Selamat datang') : 0);
            $this->assertNotFalse($pos, "Komponen '$text' tidak ada");
            $this->assertGreaterThan($last, $pos, "Urutan salah pada '$text'");
            $last = $pos;
        }

        // Petambak 1 punya TG-001 dan TG-002: 1 perlu perhatian (TG-002 ada kendala diproses), 1 aktif.
        $this->assertStringContainsString('1 Aktif', $html);
        $this->assertStringContainsString('1 Perlu Perhatian', $html);
    }

    public function test_navigation_has_exactly_five_menus(): void
    {
        $html = $this->actingAs($this->user('petambak1'))->get('/beranda')->getContent();
        preg_match('/<nav class="app-nav".*?<\/nav>/s', $html, $m);
        $this->assertSame(5, substr_count($m[0], '<a '));
        foreach (['Beranda', 'Tambak Saya', 'Catat', 'Riwayat', 'Akun'] as $label) {
            $this->assertStringContainsString("<span>$label</span>", $m[0]);
        }
    }

    public function test_demo_label_is_shown(): void
    {
        $this->actingAs($this->user('petambak1'))->get('/beranda')->assertSee('data contoh');
    }

    // ---- Pembatasan data (FR-02) ----

    public function test_petambak_sees_only_own_ponds(): void
    {
        $this->actingAs($this->user('petambak1'))->get('/tambak')
            ->assertSee('TG-001')->assertSee('TG-002')
            ->assertDontSee('TG-003')->assertDontSee('TG-004')->assertDontSee('TG-005');
    }

    public function test_other_members_pond_is_not_found(): void
    {
        $other = $this->pond('TG-003');
        $this->actingAs($this->user('petambak1'))->get('/tambak/'.$other->pond_id)->assertNotFound();
        $this->get('/tambak/'.$this->pond('TG-001')->pond_id)->assertOk()->assertSee('TG-001');
    }

    public function test_cannot_change_status_of_other_members_pond(): void
    {
        $other = $this->pond('TG-003');
        $before = $other->operational_status;

        $this->actingAs($this->user('petambak1'))
            ->post('/catat/status', ['pond_id' => $other->pond_id, 'operational_status' => 'jeda'])
            ->assertSessionHasErrors('pond_id');

        $this->assertSame($before, $other->fresh()->operational_status);
    }

    public function test_cannot_record_on_other_members_pond_or_cycle(): void
    {
        $this->actingAs($this->user('petambak1'));
        $otherPond = $this->pond('TG-003');
        $otherCycle = ProductionCycle::where('pond_id', $otherPond->pond_id)->firstOrFail();
        $mine = $this->pond('TG-001');

        $this->post('/catat/panen', ['pond_id' => $otherPond->pond_id, 'cycle_id' => $otherCycle->cycle_id, 'harvest_date' => now()->toDateString(), 'quantity' => 10, 'unit' => 'kg'])
            ->assertSessionHasErrors('pond_id');
        // Tambak sendiri tetapi siklus milik orang lain.
        $this->post('/catat/panen', ['pond_id' => $mine->pond_id, 'cycle_id' => $otherCycle->cycle_id, 'harvest_date' => now()->toDateString(), 'quantity' => 10, 'unit' => 'kg'])
            ->assertSessionHasErrors('cycle_id');
        $this->post('/catat/air', ['pond_id' => $otherPond->pond_id, 'observed_at' => now()->toDateTimeString(), 'ph_value' => 7])
            ->assertSessionHasErrors('pond_id');
        $this->post('/catat/kendala', ['pond_id' => $otherPond->pond_id, 'category' => 'lainnya', 'priority' => 'sedang', 'description' => 'Uji coba kendala'])
            ->assertSessionHasErrors('pond_id');
        $this->post('/catat/kegiatan', ['pond_id' => $mine->pond_id, 'cycle_id' => $otherCycle->cycle_id, 'activity_type' => 'lainnya', 'activity_date' => now()->toDateString()])
            ->assertSessionHasErrors('cycle_id');
        $this->get('/siklus/'.$otherCycle->cycle_id.'/ubah')->assertNotFound();
        $this->put('/siklus/'.$otherCycle->cycle_id, ['cycle_status' => 'selesai'])->assertNotFound();

        $this->assertSame(3, Harvest::count());
        $this->assertSame(7, WaterObservation::count());
        $this->assertSame(3, Issue::count());
    }

    public function test_history_never_shows_other_members_records(): void
    {
        $this->actingAs($this->user('petambak3'))->get('/riwayat')->assertDontSee('TG-001')->assertDontSee('TG-003');
        $this->actingAs($this->user('petambak1'))->get('/riwayat?tambak='.$this->pond('TG-003')->pond_id)
            ->assertDontSee('TG-003');
    }

    // ---- Formulir ----

    public function test_all_forms_render(): void
    {
        $this->actingAs($this->user('petambak1'));
        foreach (['catat', 'catat/panen', 'catat/air', 'catat/kendala', 'catat/kegiatan', 'catat/status', 'catat/siklus', 'akun', 'riwayat', 'tambak'] as $path) {
            $this->get("/$path")->assertOk();
        }
        $this->get('/siklus/'.$this->runningCycle('TG-001')->cycle_id.'/ubah')->assertOk();
    }

    public function test_status_update(): void
    {
        $pond = $this->pond('TG-001');
        $this->actingAs($this->user('petambak1'))
            ->post('/catat/status', ['pond_id' => $pond->pond_id, 'operational_status' => 'jeda'])
            ->assertRedirect('/tambak/'.$pond->pond_id);
        $this->assertSame('jeda', $pond->fresh()->operational_status);

        $this->post('/catat/status', ['pond_id' => $pond->pond_id, 'operational_status' => 'ngawur'])->assertSessionHasErrors('operational_status');
    }

    public function test_harvest_in_ton_is_stored_as_entered_and_converted_in_recap(): void
    {
        $cycle = $this->runningCycle('TG-001');
        $this->actingAs($this->user('petambak1'))->post('/catat/panen', [
            'pond_id' => $cycle->pond_id, 'cycle_id' => $cycle->cycle_id,
            'harvest_date' => now()->toDateString(), 'quantity' => '1,5', 'unit' => 'ton',
        ])->assertRedirect('/beranda')->assertSessionHas('success');

        $h = Harvest::latest('harvest_id')->first();
        $this->assertSame('ton', $h->unit);
        $this->assertEquals(1.5, (float) $h->quantity);
        $this->assertEquals(1500.0, $h->quantity_kg);
        $this->assertSame($this->user('petambak1')->member_id, $h->recorded_by);

        $this->get('/riwayat?jenis=panen')->assertSee('1,5 ton')->assertSee('1.500 kg');
    }

    public function test_harvest_validation(): void
    {
        $cycle = $this->runningCycle('TG-001');
        $base = ['pond_id' => $cycle->pond_id, 'cycle_id' => $cycle->cycle_id, 'harvest_date' => now()->toDateString(), 'quantity' => 100, 'unit' => 'kg'];
        $this->actingAs($this->user('petambak1'));

        $this->post('/catat/panen', ['quantity' => 'abc'] + $base)->assertSessionHasErrors('quantity');
        $this->post('/catat/panen', ['quantity' => 0] + $base)->assertSessionHasErrors('quantity');
        $this->post('/catat/panen', ['quantity' => -5] + $base)->assertSessionHasErrors('quantity');
        $this->post('/catat/panen', ['unit' => 'kuintal'] + $base)->assertSessionHasErrors('unit');
        $this->post('/catat/panen', ['harvest_date' => now()->addDay()->toDateString()] + $base)->assertSessionHasErrors('harvest_date');
        $this->post('/catat/panen', ['harvest_date' => $cycle->start_date->copy()->subDay()->toDateString()] + $base)->assertSessionHasErrors('harvest_date');
        $this->assertSame(3, Harvest::count());
    }

    public function test_water_observation_keeps_unmeasured_values_null(): void
    {
        $pond = $this->pond('TG-001');
        $this->actingAs($this->user('petambak1'))->post('/catat/air', [
            'pond_id' => $pond->pond_id, 'observed_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'salinity_value' => '', 'temperature_c' => '31,5', 'ph_value' => '', 'instrument' => '', 'notes' => '',
        ])->assertRedirect('/beranda');

        $o = WaterObservation::latest('observation_id')->first();
        $this->assertNull($o->salinity_value);
        $this->assertNull($o->ph_value);
        $this->assertNull($o->instrument);
        $this->assertEquals(31.5, (float) $o->temperature_c);
    }

    public function test_water_observation_requires_at_least_one_value_and_range(): void
    {
        $pond = $this->pond('TG-001');
        $base = ['pond_id' => $pond->pond_id, 'observed_at' => now()->subHour()->format('Y-m-d\TH:i')];
        $this->actingAs($this->user('petambak1'));

        $this->post('/catat/air', $base)->assertSessionHasErrors('salinity_value');
        $this->post('/catat/air', $base + ['ph_value' => 15])->assertSessionHasErrors('ph_value');
        $this->post('/catat/air', $base + ['ph_value' => 0])->assertSessionHasNoErrors();
        $this->assertSame(0.0, (float) WaterObservation::latest('observation_id')->first()->ph_value);
    }

    public function test_issue_report_starts_open_without_photo_field(): void
    {
        $pond = $this->pond('TG-001');
        $this->actingAs($this->user('petambak1'))->post('/catat/kendala', [
            'pond_id' => $pond->pond_id, 'category' => 'tanggul_bocor', 'priority' => 'tinggi', 'description' => 'Tanggul timur bocor',
            'status' => 'selesai', // tidak boleh bisa diatur petambak
        ])->assertRedirect('/beranda');

        $i = Issue::latest('issue_id')->first();
        $this->assertSame('terbuka', $i->status);
        $this->assertSame($this->user('petambak1')->member_id, $i->reported_by);
        $this->assertNull($i->resolved_at);

        $this->get('/catat/kendala')->assertDontSee('type="file"', false);
    }

    public function test_activity_and_new_cycle(): void
    {
        $cycle = $this->runningCycle('TG-001');
        $this->actingAs($this->user('petambak1'));

        $this->post('/catat/kegiatan', [
            'pond_id' => $cycle->pond_id, 'cycle_id' => $cycle->cycle_id, 'activity_type' => 'perawatan_tanggul',
            'activity_date' => now()->toDateString(), 'description' => 'Menambal tanggul',
        ])->assertRedirect('/beranda');
        $this->assertSame(1, ProductionActivity::where('description', 'Menambal tanggul')->count());

        $this->post('/catat/siklus', ['pond_id' => $cycle->pond_id, 'start_date' => now()->toDateString(), 'cycle_status' => 'selesai'])
            ->assertSessionHasErrors('cycle_status');
        $this->post('/catat/siklus', ['pond_id' => $cycle->pond_id, 'start_date' => now()->toDateString(), 'cycle_status' => 'persiapan'])
            ->assertRedirect('/tambak/'.$cycle->pond_id);
        $this->assertSame(1, ProductionCycle::where('pond_id', $cycle->pond_id)->where('cycle_status', 'persiapan')->count());
    }

    public function test_closing_a_cycle_sets_end_date(): void
    {
        $cycle = $this->runningCycle('TG-001');
        $this->actingAs($this->user('petambak1'))
            ->put('/siklus/'.$cycle->cycle_id, ['cycle_status' => 'selesai'])->assertRedirect();
        $cycle->refresh();
        $this->assertSame('selesai', $cycle->cycle_status);
        $this->assertSame(now()->toDateString(), $cycle->end_date->toDateString());
    }

}
