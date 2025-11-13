#!/usr/bin/env php
<?php
// Test MySQL connection
try {
    $pdo = new PDO(
        'mysql:host=renthub-mysql;port=3306;dbname=renthub',
        'root',
        'secret'
    );
    echo "✅ MySQL connection successful!" . PHP_EOL;
    
    // Test Redis connection
    if (class_exists('Redis')) {
        $redis = new Redis();
        if ($redis->connect('renthub-redis', 6379)) {
            $redis->auth('secret');
            echo "✅ Redis connection successful!" . PHP_EOL;
        } else {
            echo "❌ Redis connection failed" . PHP_EOL;
        }
    } else {
        echo "⚠️  Redis extension not installed" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
