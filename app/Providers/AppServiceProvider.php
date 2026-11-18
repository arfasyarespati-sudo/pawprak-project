<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        $host = (string) config('database.connections.mysql.host', '127.0.0.1');
        $port = (string) config('database.connections.mysql.port', '3307');
        $database = (string) config('database.connections.mysql.database', 'drawchaos_db');
        $username = (string) config('database.connections.mysql.username', 'root');
        $password = (string) config('database.connections.mysql.password', '');

        if ($this->tryCreateMySqlDatabase($host, $port, $username, $password, $database)) {
            return;
        }

        $xamppMysqld = 'C:\\xampp\\mysql\\bin\\mysqld.exe';
        $xamppMyIni = 'C:\\xampp\\mysql\\bin\\my.ini';
        $fallbackPort = '3307';

        if (PHP_OS_FAMILY === 'Windows' && file_exists($xamppMysqld) && file_exists($xamppMyIni)) {
            if (! $this->tryCreateMySqlDatabase($host, $fallbackPort, 'root', '', $database)) {
                $cmd = sprintf(
                    'start /B "" "%s" --defaults-file="%s" --port=%s --skip-grant-tables >NUL 2>&1',
                    $xamppMysqld,
                    $xamppMyIni,
                    $fallbackPort
                );
                @pclose(@popen($cmd, 'r'));

                for ($attempt = 0; $attempt < 8; $attempt++) {
                    usleep(500000);
                    if ($this->tryCreateMySqlDatabase($host, $fallbackPort, 'root', '', $database)) {
                        break;
                    }
                }
            }

            config([
                'database.connections.mysql.port' => $fallbackPort,
                'database.connections.mysql.username' => 'root',
                'database.connections.mysql.password' => '',
            ]);
            DB::purge('mysql');
        }
    }

    /**
     * Attempt to connect to MySQL and ensure the target database exists.
     */
    private function tryCreateMySqlDatabase(
        string $host,
        string $port,
        string $username,
        string $password,
        string $database
    ): bool {
        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [\PDO::ATTR_TIMEOUT => 2, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
