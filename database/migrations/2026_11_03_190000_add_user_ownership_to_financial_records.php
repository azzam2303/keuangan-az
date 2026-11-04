<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        $hasUnownedRecords = DB::table('expenses')->whereNull('user_id')->exists()
            || DB::table('incomes')->whereNull('user_id')->exists();

        if ($hasUnownedRecords) {
            $legacyOwnerId = DB::table('users')->where('username', 'alber23')->value('id');

            if (! $legacyOwnerId) {
                throw new RuntimeException('Cannot assign existing financial records: user alber23 was not found.');
            }

            DB::table('expenses')->whereNull('user_id')->update(['user_id' => $legacyOwnerId]);
            DB::table('incomes')->whereNull('user_id')->update(['user_id' => $legacyOwnerId]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
