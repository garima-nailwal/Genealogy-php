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
        Schema::table('relatives', function (Blueprint $table) {
            $table->foreign(['primary_user_id'], 'relatives_ibfk_1')->references(['user_id'])->on('users_registration')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['relative_id'], 'relatives_ibfk_2')->references(['user_id'])->on('users_registration')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['relationship_id'], 'relatives_ibfk_3')->references(['id'])->on('relationship')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relatives', function (Blueprint $table) {
            $table->dropForeign('relatives_ibfk_1');
            $table->dropForeign('relatives_ibfk_2');
            $table->dropForeign('relatives_ibfk_3');
        });
    }
};
