<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Tanpa RefreshDatabase: koneksi sengaja dimatikan.
class ReadinessDbDownTest extends TestCase
{
    public function test_ready_503_when_database_down(): void
    {
        // Arahkan koneksi default ke tujuan yang tidak ada agar DB tidak terjangkau.
        $name = config('database.default');
        if (config("database.connections.$name.driver") === 'sqlite') {
            config(["database.connections.$name.database" => sys_get_temp_dir().'/tidak-ada/simtag.sqlite']);
        } else {
            config(["database.connections.$name.port" => 1, "database.connections.$name.host" => '127.0.0.1']);
        }
        DB::purge($name);

        try {
            $this->get('/health/ready')
                ->assertStatus(503)
                ->assertHeader('X-App-Instance');
        } finally {
            DB::purge($name);
        }
    }
}
