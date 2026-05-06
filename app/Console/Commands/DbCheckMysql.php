<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class DbCheckMysql extends Command
{
    protected $signature = 'db:check-mysql
                            {--host= : MySQL-Host (default: DB_HOST)}
                            {--port= : MySQL-Port (default: DB_PORT)}
                            {--database= : Datenbankname (default: DB_DATABASE)}
                            {--username= : Benutzername (default: DB_USERNAME)}
                            {--password= : Passwort (default: DB_PASSWORD)}';

    protected $description = 'Testet die MySQL-Datenbankverbindung mit den aktuellen .env-Werten';

    public function handle(): int
    {
        $host     = $this->option('host')     ?? env('DB_HOST', '127.0.0.1');
        $port     = $this->option('port')     ?? env('DB_PORT', '3306');
        $database = $this->option('database') ?? env('DB_DATABASE', 'pagescan');
        $username = $this->option('username') ?? env('DB_USERNAME', 'root');
        $password = $this->option('password') ?? env('DB_PASSWORD', '');

        $this->line("Verbinde mit MySQL: {$username}@{$host}:{$port}/{$database}");

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $this->info("MySQL-Verbindung OK -- Server-Version: {$version}");

            $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
            $this->line("Tabellen in der Datenbank: {$tables}");

            return self::SUCCESS;
        } catch (PDOException $e) {
            $this->error("MySQL-Verbindung FEHLGESCHLAGEN: " . $e->getMessage());
            $this->line('');
            $this->line('Stelle sicher dass in .env folgende Werte gesetzt sind:');
            $this->line('  DB_CONNECTION=mysql');
            $this->line('  DB_HOST=127.0.0.1');
            $this->line('  DB_PORT=3306');
            $this->line('  DB_DATABASE=pagescan');
            $this->line('  DB_USERNAME=root');
            $this->line('  DB_PASSWORD=...');

            return self::FAILURE;
        }
    }
}