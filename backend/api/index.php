<?php

// Ensure SQLite database exists in the writeable /tmp directory of the serverless function
$dbSource = __DIR__ . '/../database/database.sqlite';
$dbPath = '/tmp/database.sqlite';

if (!file_exists($dbPath)) {
    if (!file_exists($dbSource)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: text/plain');
        echo "Source database not found at: $dbSource\n\n";
        echo "Listing contents of parent directory: " . dirname($dbSource) . "\n";
        if (is_dir(dirname($dbSource))) {
            print_r(scandir(dirname($dbSource)));
        } else {
            echo "Directory does not exist. Listing root directory /var/task/user:\n";
            print_r(scandir('/var/task/user'));
        }
        exit(1);
    }
    
    // Create database file in /tmp
    touch($dbPath);
    // Copy the pre-migrated, pre-seeded SQLite database from the repo
    copy($dbSource, $dbPath);
}

// Set database connection details for runtime
putenv("DB_CONNECTION=sqlite");
putenv("DB_DATABASE=" . $dbPath);
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $dbPath;
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = $dbPath;

// Configure Laravel to write logs to stderr instead of the read-only storage directory
putenv("LOG_CHANNEL=stderr");
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

// Configure Laravel to write compiled views to a writeable directory in /tmp
$viewsPath = '/tmp/views';
if (!file_exists($viewsPath)) {
    mkdir($viewsPath, 0755, true);
}
putenv("VIEW_COMPILED_PATH=" . $viewsPath);
$_ENV['VIEW_COMPILED_PATH'] = $viewsPath;

// Load the standard bootstrap entrypoint with error catching
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain');
    echo "Bootstrap Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
