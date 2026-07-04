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

// Configure Laravel to write logs to stderr instead of the read-only storage directory
putenv("LOG_CHANNEL=stderr");
$_ENV['LOG_CHANNEL'] = 'stderr';

// Configure Laravel to write compiled views to a writeable directory in /tmp
$viewsPath = '/tmp/views';
if (!file_exists($viewsPath)) {
    mkdir($viewsPath, 0755, true);
}
putenv("VIEW_COMPILED_PATH=" . $viewsPath);
$_ENV['VIEW_COMPILED_PATH'] = $viewsPath;

// Load the standard bootstrap entrypoint
require __DIR__ . '/../public/index.php';
