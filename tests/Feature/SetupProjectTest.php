<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SetupProjectTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/featbrands-setup-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($this->directory.'/uploads');
        $this->app->useEnvironmentPath($this->directory);
        $this->app->loadEnvironmentFrom('.env');
        config([
            'app.key' => '',
            'database.connections.sqlite.database' => $this->directory.'/database.sqlite',
            'filesystems.links' => [$this->directory.'/storage' => $this->directory.'/uploads'],
        ]);
        DB::purge('sqlite');

        $this->beforeApplicationDestroyed(function (): void {
            DB::disconnect('sqlite');
            File::deleteDirectory($this->directory);
        });
    }

    public function test_creates_a_database_key_and_storage_link_on_a_fresh_install(): void
    {
        File::put($this->directory.'/.env', "APP_KEY=\n");

        $this->artisan('app:setup', ['--no-interaction' => true])->assertSuccessful();

        $this->assertFileExists($this->directory.'/database.sqlite');
        $this->assertStringContainsString('APP_KEY=base64:', File::get($this->directory.'/.env'));
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertSame($this->directory.'/uploads', readlink($this->directory.'/storage'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_preserves_the_existing_key_and_users_when_setup_is_repeated(): void
    {
        File::put($this->directory.'/.env', "APP_KEY=\n");
        $this->artisan('app:setup', ['--no-interaction' => true])->assertSuccessful();
        $environment = File::get($this->directory.'/.env');
        $user = User::factory()->create();

        $this->artisan('app:setup', ['--no-interaction' => true])->assertSuccessful();

        $this->assertSame($environment, File::get($this->directory.'/.env'));
        $this->assertModelExists($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_stops_when_the_environment_file_cannot_receive_a_key(): void
    {
        File::put($this->directory.'/.env', "APP_NAME=Featbrands\n");

        $this->artisan('app:setup', ['--no-interaction' => true])->assertFailed();

        $this->assertFileDoesNotExist($this->directory.'/database.sqlite');
    }
}
