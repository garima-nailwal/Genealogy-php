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
        Schema::create('occupation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('occupation_name', 50)->unique('occupation_name');
            $table->string('description', 50)->nullable();
            $table->string('created_by')->nullable()->default('system');
            $table->date('created_on')->nullable()->default('CURRENT_DATE');
            $table->string('updated_by')->nullable()->default('system');
            $table->timestamp('updated_on')->useCurrentOnUpdate()->useCurrent();
            $table->char('status', 1)->nullable()->default('A');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupation');
    }
};
