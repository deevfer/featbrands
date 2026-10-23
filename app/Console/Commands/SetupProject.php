<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('app:setup')]
#[Description('Prepara la clave, la base de datos y el administrador de Featbrands')]
class SetupProject extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (blank(config('app.key'))) {
            $this->call('key:generate', ['--force' => true]);

            if (blank(config('app.key'))) {
                return self::FAILURE;
            }
        }

        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'sqlite' && $database !== ':memory:' && ! File::exists($database)) {
            File::ensureDirectoryExists(dirname($database));
            File::put($database, '');
        }

        if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $missingLinks = array_filter(
            config('filesystems.links', []),
            fn (string $target, string $link): bool => ! file_exists($link) && ! is_link($link),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($missingLinks !== []) {
            $this->call('storage:link');
        }

        if (! User::query()->exists()) {
            if ($this->input->isInteractive() && ! getenv('COMPOSER_NO_INTERACTION') && defined('STDIN') && stream_isatty(STDIN)) {
                return $this->call('make:filament-user', ['--panel' => 'admin']);
            }

            $this->info('Crea tu administrador con: php artisan make:filament-user --panel=admin');
        }

        $this->info('Featbrands está configurado. Inicia el servidor con: php artisan serve');

        return self::SUCCESS;
    }
}
