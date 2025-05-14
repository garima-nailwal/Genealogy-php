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
        Schema::table('users_registration', function (Blueprint $table) {
            $table->foreign(['caste_id'], 'fk_users_caste')->references(['id'])->on('castes')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['district_id'], 'fk_users_district')->references(['id'])->on('districts')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['gender'], 'fk_users_gender')->references(['id'])->on('gender')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['maritial_status'], 'fk_users_maritial_status')->references(['id'])->on('maritial_status')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['occupation_id'], 'fk_users_occupation')->references(['id'])->on('occupation')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['religion_id'], 'fk_users_religion')->references(['id'])->on('religions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['state_id'], 'fk_users_state')->references(['id'])->on('state')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['suggested_approval_id'], 'users_registration_ibfk_1')->references(['id'])->on('approves')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_registration', function (Blueprint $table) {
            $table->dropForeign('fk_users_caste');
            $table->dropForeign('fk_users_district');
            $table->dropForeign('fk_users_gender');
            $table->dropForeign('fk_users_maritial_status');
            $table->dropForeign('fk_users_occupation');
            $table->dropForeign('fk_users_religion');
            $table->dropForeign('fk_users_state');
            $table->dropForeign('users_registration_ibfk_1');
        });
    }
};
