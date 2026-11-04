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
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Nama pengeluaran (cth: Makan Siang)
        $table->decimal('amount', 12, 2); // Jumlah uang
        $table->date('date'); // Tanggal pengeluaran
        $table->text('description')->nullable(); // Keterangan opsional
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
