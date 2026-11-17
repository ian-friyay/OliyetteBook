<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$schema = getenv('DB_SCHEMA') ?: 'oliyettebook';

if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $schema)) {
    fwrite(STDERR, "DB_SCHEMA must be a valid PostgreSQL schema identifier.\n");
    exit(1);
}

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

DB::statement('CREATE SCHEMA IF NOT EXISTS "'.$schema.'"');
