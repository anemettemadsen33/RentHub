<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create admin user
$admin = User::create([
    'name' => 'Admin RentHub',
    'email' => 'admin@renthub.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);

echo "✓ Admin user created successfully!\n";
echo "Email: admin@renthub.com\n";
echo "Password: admin123\n";
echo "Role: admin\n";
echo "ID: {$admin->id}\n";
