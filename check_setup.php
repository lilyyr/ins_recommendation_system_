<?php
echo "=== System Diagnostic ===\n\n";

// Check Python
echo "1. Python Check:\n";
exec('python --version 2>&1', $pythonVersion);
echo "   " . implode("\n   ", $pythonVersion) . "\n\n";

// Check Database
echo "2. Database Check:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=insurance_cbr', 'root', '');
    echo "   ✓ Database connection successful\n";

    $tables = ['products', 'weights', 'customers', 'cases'];
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "   ✓ $table: $count records\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}
echo "\n";

// Check Files
echo "3. Files Check:\n";
$files = [
    'python/cbr_system.py',
    'python/models/rf_model.pkl',
    'python/models/leaf_cache.json'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file\n";
    } else {
        echo "   ✗ $file (MISSING!)\n";
    }
}
echo "\n";

// Check Directories
echo "4. Directory Check:\n";
$dir = 'storage/app/temp';
if (is_dir($dir)) {
    echo "   ✓ $dir exists\n";
    if (is_writable($dir)) {
        echo "   ✓ $dir is writable\n";
    } else {
        echo "   ✗ $dir is NOT writable\n";
    }
} else {
    echo "   ✗ $dir does not exist\n";
    mkdir($dir, 0755, true);
    echo "   ✓ Created $dir\n";
}

echo "\n=== End Diagnostic ===\n";
