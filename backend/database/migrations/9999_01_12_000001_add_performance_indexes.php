<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip in testing environment to avoid table not exists errors
        if (app()->environment('testing')) {
            return;
        }
        
        // Note: Many indexes already exist. Only adding missing ones.
        
        // Properties table - additional indexes
        if (!$this->indexExists('properties', 'properties_created_at_index')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->index('created_at', 'properties_created_at_index');
            });
        }

        // Bookings table indexes
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'user_id')) {
            if (!$this->indexExists('bookings', 'bookings_user_id_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('user_id', 'bookings_user_id_index');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_property_id_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('property_id', 'bookings_property_id_index');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_status_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('status', 'bookings_status_index');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_user_status_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index(['user_id', 'status'], 'bookings_user_status_index');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_property_status_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index(['property_id', 'status'], 'bookings_property_status_index');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_created_at_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('created_at', 'bookings_created_at_index');
                });
            }
        }

        // Reviews table indexes
        if (Schema::hasTable('reviews')) {
            if (!$this->indexExists('reviews', 'reviews_property_id_index')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->index('property_id', 'reviews_property_id_index');
                });
            }
            if (!$this->indexExists('reviews', 'reviews_user_id_index')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->index('user_id', 'reviews_user_id_index');
                });
            }
            if (Schema::hasColumn('reviews', 'approved') && !$this->indexExists('reviews', 'reviews_approved_index')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->index('approved', 'reviews_approved_index');
                });
            }
            if (!$this->indexExists('reviews', 'reviews_created_at_index')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->index('created_at', 'reviews_created_at_index');
                });
            }
        }

        // Users table - email already indexed by unique constraint
        if (Schema::hasTable('users') && !$this->indexExists('users', 'users_created_at_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('created_at', 'users_created_at_index');
            });
        }

        // Conversations table indexes
        if (Schema::hasTable('conversations')) {
            if (!$this->indexExists('conversations', 'conversations_updated_at_index')) {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->index('updated_at', 'conversations_updated_at_index');
                });
            }
        }

        // Messages table indexes
        if (Schema::hasTable('messages')) {
            if (!$this->indexExists('messages', 'messages_conversation_id_index')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index('conversation_id', 'messages_conversation_id_index');
                });
            }
            if (Schema::hasColumn('messages', 'is_read') && !$this->indexExists('messages', 'messages_is_read_index')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index('is_read', 'messages_is_read_index');
                });
            }
        }

        // Property Images indexes
        if (Schema::hasTable('property_images')) {
            if (!$this->indexExists('property_images', 'property_images_property_id_index')) {
                Schema::table('property_images', function (Blueprint $table) {
                    $table->index('property_id', 'property_images_property_id_index');
                });
            }
        }

        // Payments table indexes
        if (Schema::hasTable('payments')) {
            if (!$this->indexExists('payments', 'payments_booking_id_index')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->index('booking_id', 'payments_booking_id_index');
                });
            }
            if (!$this->indexExists('payments', 'payments_user_id_index')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->index('user_id', 'payments_user_id_index');
                });
            }
            if (!$this->indexExists('payments', 'payments_status_index')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->index('status', 'payments_status_index');
                });
            }
            if (!$this->indexExists('payments', 'payments_created_at_index')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->index('created_at', 'payments_created_at_index');
                });
            }
        }

        // Maintenance requests indexes
        if (Schema::hasTable('maintenance_requests')) {
            if (!$this->indexExists('maintenance_requests', 'maintenance_requests_property_id_index')) {
                Schema::table('maintenance_requests', function (Blueprint $table) {
                    $table->index('property_id', 'maintenance_requests_property_id_index');
                });
            }
            if (!$this->indexExists('maintenance_requests', 'maintenance_requests_status_index')) {
                Schema::table('maintenance_requests', function (Blueprint $table) {
                    $table->index('status', 'maintenance_requests_status_index');
                });
            }
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop only the indexes we added
        $indexesToDrop = [
            'properties' => ['properties_created_at_index'],
            'bookings' => ['bookings_user_id_index', 'bookings_property_id_index', 'bookings_status_index', 
                          'bookings_user_status_index', 'bookings_property_status_index', 'bookings_created_at_index'],
            'reviews' => ['reviews_property_id_index', 'reviews_user_id_index', 'reviews_approved_index', 'reviews_created_at_index'],
            'users' => ['users_created_at_index'],
            'conversations' => ['conversations_updated_at_index'],
            'messages' => ['messages_conversation_id_index', 'messages_is_read_index'],
            'property_images' => ['property_images_property_id_index'],
            'payments' => ['payments_booking_id_index', 'payments_user_id_index', 'payments_status_index', 'payments_created_at_index'],
            'maintenance_requests' => ['maintenance_requests_property_id_index', 'maintenance_requests_status_index'],
        ];

        foreach ($indexesToDrop as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexes) {
                    foreach ($indexes as $index) {
                        if ($this->indexExists($table->getTable(), $index)) {
                            $table->dropIndex($index);
                        }
                    }
                });
            }
        }
    }
};
