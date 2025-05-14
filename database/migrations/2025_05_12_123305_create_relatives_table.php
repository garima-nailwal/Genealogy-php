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
        Schema::create('relatives', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('primary_user_id')->nullable()->index('primary_user_id');
            $table->integer('relative_id')->nullable()->index('relative_id');
            $table->integer('relationship_id')->nullable()->index('relationship_id');
            $table->text('relationship_nickname')->nullable();
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
        Schema::dropIfExists('relatives');
    }
};
