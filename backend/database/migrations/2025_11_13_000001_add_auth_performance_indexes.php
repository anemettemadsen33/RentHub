<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("PRAGMA index_list('{$table}')");
        foreach ($indexes as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for authentication performance
        Schema::table('users', function (Blueprint $table) {
            // Index for email lookups during login (only if not exists)
            if (!$this->indexExists('users', 'users_email_index')) {
                $table->index('email');
            }
            
            // Composite index for email + verification status (only if not exists)
            if (!$this->indexExists('users', 'users_email_email_verified_at_index')) {
                $table->index(['email', 'email_verified_at']);
            }
            
            // Index for phone verification (if used in auth and not exists)
            if (Schema::hasColumn('users', 'phone_verified_at') && !$this->indexExists('users', 'users_phone_verified_at_index')) {
                $table->index('phone_verified_at');
            }
            
            // Index for 2FA status (if frequently queried and not exists)
            if (Schema::hasColumn('users', 'two_factor_enabled') && !$this->indexExists('users', 'users_two_factor_enabled_index')) {
                $table->index('two_factor_enabled');
            }
        });

        // Add indexes for personal access tokens (Sanctum)
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                if (!$this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_id_index')) {
                    $table->index('tokenable_id');
                }
                if (!$this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_type_index')) {
                    $table->index('tokenable_type');
                }
                if (!$this->indexExists('personal_access_tokens', 'personal_access_tokens_last_used_at_index')) {
                    $table->index('last_used_at');
                }
                if (!$this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_id_tokenable_type_index')) {
                    $table->index(['tokenable_id', 'tokenable_type']);
                }
            });
        }

        // Add indexes for password reset tokens
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                if (!$this->indexExists('password_reset_tokens', 'password_reset_tokens_email_index')) {
                    $table->index('email');
                }
                if (!$this->indexExists('password_reset_tokens', 'password_reset_tokens_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Add indexes for two-factor authentication table
        if (Schema::hasTable('two_factor_auth')) {
            Schema::table('two_factor_auth', function (Blueprint $table) {
                if (!$this->indexExists('two_factor_auth', 'two_factor_auth_user_id_index')) {
                    $table->index('user_id');
                }
                if (!$this->indexExists('two_factor_auth', 'two_factor_auth_expires_at_index')) {
                    $table->index('expires_at');
                }
                if (!$this->indexExists('two_factor_auth', 'two_factor_auth_user_id_expires_at_index')) {
                    $table->index(['user_id', 'expires_at']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_email_index')) {
                $table->dropIndex(['email']);
            }
            if ($this->indexExists('users', 'users_email_email_verified_at_index')) {
                $table->dropIndex(['email', 'email_verified_at']);
            }
            
            if (Schema::hasColumn('users', 'phone_verified_at') && $this->indexExists('users', 'users_phone_verified_at_index')) {
                $table->dropIndex(['phone_verified_at']);
            }
            
            if (Schema::hasColumn('users', 'two_factor_enabled') && $this->indexExists('users', 'users_two_factor_enabled_index')) {
                $table->dropIndex(['two_factor_enabled']);
            }
        });

        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                if ($this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_id_index')) {
                    $table->dropIndex(['tokenable_id']);
                }
                if ($this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_type_index')) {
                    $table->dropIndex(['tokenable_type']);
                }
                if ($this->indexExists('personal_access_tokens', 'personal_access_tokens_last_used_at_index')) {
                    $table->dropIndex(['last_used_at']);
                }
                if ($this->indexExists('personal_access_tokens', 'personal_access_tokens_tokenable_id_tokenable_type_index')) {
                    $table->dropIndex(['tokenable_id', 'tokenable_type']);
                }
            });
        }

        if (Schema::hasTable('password_reset_tokens')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                if ($this->indexExists('password_reset_tokens', 'password_reset_tokens_email_index')) {
                    $table->dropIndex(['email']);
                }
                if ($this->indexExists('password_reset_tokens', 'password_reset_tokens_created_at_index')) {
                    $table->dropIndex(['created_at']);
                }
            });
        }

        if (Schema::hasTable('two_factor_auth')) {
            Schema::table('two_factor_auth', function (Blueprint $table) {
                if ($this->indexExists('two_factor_auth', 'two_factor_auth_user_id_index')) {
                    $table->dropIndex(['user_id']);
                }
                if ($this->indexExists('two_factor_auth', 'two_factor_auth_expires_at_index')) {
                    $table->dropIndex(['expires_at']);
                }
                if ($this->indexExists('two_factor_auth', 'two_factor_auth_user_id_expires_at_index')) {
                    $table->dropIndex(['user_id', 'expires_at']);
                }
            });
        }
    }
};