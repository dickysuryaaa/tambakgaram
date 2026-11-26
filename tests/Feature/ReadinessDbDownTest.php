<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Tanpa RefreshDatabase: koneksi sengaja dimatikan.
class ReadinessDbDownTest extends TestCase
{
    public function test_ready_503_when_database_down(): void
    {
        // Arahkan koneksi ke port yang tidak ada agar DB tidak terjangkau.
        config(['database.connections.mysql.port' => 1, 'database.connections.mysql.host' => '127.0.0.1']);
        DB::purge('mysql');

        try {
            $this->get('/health/ready')
                ->assertStatus(503)
                ->assertHeader('X-App-Instance');
        } finally {
            DB::purge('mysql');
        }
    }
}
