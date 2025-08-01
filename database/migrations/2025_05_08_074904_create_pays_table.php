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
        Schema::table('pays', function (Blueprint $table) {
            if (!Schema::hasColumn('pays', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('table_id');
            }
            
            if (!Schema::hasColumn('pays', 'is_type')) {
                $table->tinyInteger('is_type')->default(0)->after('total');
            }
            
            $table->decimal('total', 10, 2)->nullable()->change();
            
            if (!Schema::hasColumn('pays', 'user_id')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pays', function (Blueprint $table) {
            if (Schema::hasColumn('pays', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('pays', 'is_type')) {
                $table->dropColumn('is_type');
            }
            
            $table->text('total')->nullable()->change();
        });
    }
};