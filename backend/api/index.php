<?php

// Ensure SQLite database exists in the writeable /tmp directory of the serverless function
$dbPath = '/tmp/database.sqlite';

if (!file_exists($dbPath)) {
    // Create database file in /tmp
    touch($dbPath);
    // Copy the pre-migrated, pre-seeded SQLite database from the repo
    copy(__DIR__ . '/../database/database.sqlite', $dbPath);
}

// Set database connection details for runtime
putenv("DB_CONNECTION=sqlite");
putenv("DB_DATABASE=" . $dbPath);
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $dbPath;

// Load the standard bootstrap entrypoint
require __DIR__ . '/../public/index.php';
