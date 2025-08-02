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
        Schema::create('config_promptpays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('config_id')->nullable();
            $table->text('promptpay')->nullable(); // เบอร์โทรหรือ ID สำหรับ PromptPay
            $table->text('bank_name')->nullable(); // ชื่อธนาคาร 
            $table->text('account_name')->nullable(); // ชื่อบัญชี 
            $table->tinyInteger('is_active')->default(1); // สถานะการใช้งาน
            $table->timestamps();
            
            // Foreign key
            $table->foreign('config_id')->references('id')->on('configs')->onDelete('cascade');
            
            // Index
            $table->index('config_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_promptpays');
    }
};