<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->change();
        });

        DB::table('users')->whereNull('username')->update([
            'username' => DB::raw('email'),
        ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNull('email')
            ->get(['id', 'username'])
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['email' => $user->username.'@users.invalid']);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->string('email')->nullable(false)->change();
        });
    }
};
