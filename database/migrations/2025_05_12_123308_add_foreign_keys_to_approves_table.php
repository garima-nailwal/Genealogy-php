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
        Schema::table('approves', function (Blueprint $table) {
            $table->foreign(['user_id'], 'approves_ibfk_1')->references(['user_id'])->on('users_registration')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approves', function (Blueprint $table) {
            $table->dropForeign('approves_ibfk_1');
        });
    }
};
