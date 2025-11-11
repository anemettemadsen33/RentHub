<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class E2ESeeder extends Seeder
{
    public function run(): void
    {
        // Create test user if Users table exists
        if ($this->tableExists('users')) {
            $user = DB::table('users')->where('email', 'test@example.com')->first();
            if (! $user) {
                DB::table('users')->insert([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => Hash::make('password123'),
                    'role' => 'owner', // Set role to owner for property creation
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = DB::table('users')->where('email', 'test@example.com')->first();
            } else {
                // Update existing user to owner role if not already
                if ($user->role !== 'owner' && $user->role !== 'admin') {
                    DB::table('users')
                        ->where('email', 'test@example.com')
                        ->update(['role' => 'owner']);
                }
            }
        }

        // Create a sample property
        if ($this->tableExists('properties')) {
            $property = DB::table('properties')->first();
            if (! $property) {
                DB::table('properties')->insert([
                    'title' => 'E2E Test Property',
                    'slug' => 'e2e-test-property',
                    'description' => 'Property created by E2ESeeder',
                    'price' => 150,
                    'currency' => 'USD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $property = DB::table('properties')->first();
            }
        }

        // Create a booking linking user & property if tables exist
        if ($this->tableExists('bookings') && $this->tableExists('users') && $this->tableExists('properties')) {
            $userId = DB::table('users')->where('email', 'test@example.com')->value('id');
            $propertyId = DB::table('properties')->value('id');
            $booking = DB::table('bookings')->where('user_id', $userId)->where('property_id', $propertyId)->first();
            if (! $booking) {
                DB::table('bookings')->insert([
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                    'status' => 'confirmed',
                    'check_in' => now()->toDateString(),
                    'check_out' => now()->addDay()->toDateString(),
                    'guests' => 2,
                    'nights' => 1,
                    'price_per_night' => 150.00,
                    'subtotal' => 150.00,
                    'cleaning_fee' => 0.00,
                    'security_deposit' => 0.00,
                    'taxes' => 0.00,
                    'total_amount' => 150.00,
                    'guest_name' => 'Test User',
                    'guest_email' => 'test@example.com',
                    'guest_phone' => '+40123456789',
                    'payment_status' => 'paid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Optional: Invoice seed if invoices table exists
        if ($this->tableExists('invoices') && $this->tableExists('bookings')) {
            $bookingId = DB::table('bookings')->value('id');
            $userId = DB::table('users')->where('email', 'test@example.com')->value('id');
            $propertyId = DB::table('properties')->value('id');
            $invoice = DB::table('invoices')->where('booking_id', $bookingId)->first();
            if (! $invoice) {
                DB::table('invoices')->insert([
                    'invoice_number' => 'E2E-INV-'.Str::upper(Str::random(6)),
                    'booking_id' => $bookingId,
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'status' => 'paid',
                    'subtotal' => 150.00,
                    'cleaning_fee' => 0.00,
                    'security_deposit' => 0.00,
                    'taxes' => 0.00,
                    'total_amount' => 150.00,
                    'currency' => 'USD',
                    'customer_name' => 'Test User',
                    'customer_email' => 'test@example.com',
                    'customer_phone' => '+40123456789',
                    'property_title' => 'E2E Test Property',
                    'paid_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return \Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
