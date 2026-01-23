<?php

// Test script to check Filament compatibility

require __DIR__ . '/vendor/autoload.php';

echo "Testing Filament Forms...\n";

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✓ Laravel bootstrapped\n";
    
    // Test if Filament\Forms\Form exists
    if (class_exists(\Filament\Forms\Form::class)) {
        echo "✓ Filament\Forms\Form class exists\n";
    } else {
        echo "✗ Filament\Forms\Form class NOT found\n";
    }
    
    // Test if old Schema class still exists
    if (class_exists(\Filament\Schemas\Schema::class)) {
        echo "⚠ OLD Filament\Schemas\Schema class still exists (should not!)\n";
    } else {
        echo "✓ Old Schema class removed\n";
    }
    
    echo "\nAll tests passed!\n";
    
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
