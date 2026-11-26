<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthAndReadOnlyEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_returns_alive_with_instance_header(): void
    {
        $this->getJson('/health/live')
            ->assertOk()
            ->assertExactJson(['status' => 'alive'])
            ->assertHeader('X-App-Instance', gethostname());
    }

    public function test_ready_ok_when_database_up(): void
    {
        $this->get('/health/ready')->assertOk()->assertHeader('X-App-Instance');
    }

    public function test_read_only_endpoints_are_public_json_and_write_nothing(): void
    {
        $this->seed();
        $sessionsBefore = DB::table('sessions')->count();

        $this->get('/test/read-only/ponds')
            ->assertOk()
            ->assertJsonPath('count', 5)
            ->assertJsonStructure(['data' => [['pond_id', 'pond_code', 'operational_status']]])
            ->assertHeader('X-App-Instance');

        $this->get('/test/read-only/dashboard')
            ->assertOk()
            ->assertJsonPath('total_active_ponds', 3)
            ->assertJsonPath('total_harvest_kg', 13100)
            ->assertJsonPath('open_issues', 1);

        $this->assertSame($sessionsBefore, DB::table('sessions')->count());
    }

    public function test_header_is_added_on_regular_web_routes(): void
    {
        $this->get('/')->assertHeader('X-App-Instance', gethostname());
    }
}
