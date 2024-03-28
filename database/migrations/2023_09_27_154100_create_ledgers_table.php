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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('kind', [
                'CAPITAL',
                'BANK',
                'WALLET',
                'DEPOSIT',
                'CASH',
                'PAYABLE',
                'RECEIVABLE',
                'EXPENSE',
                'INCOME',
                'PURCHASE AC',
                'SALES AC',
                'DUTIES AND TAXES'
            ]);
            $table->boolean('can_receive_payment')->default(false);
$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
