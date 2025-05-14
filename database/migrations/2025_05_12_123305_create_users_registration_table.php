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
        Schema::create('users_registration', function (Blueprint $table) {
            $table->integer('user_id', true);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('birth_date')->nullable();
            $table->string('email')->unique('email');
            $table->string('mobile_number', 15)->unique('mobile_number');
            $table->integer('state_id')->nullable()->index('fk_users_state');
            $table->integer('district_id')->nullable()->index('fk_users_district');
            $table->integer('religion_id')->nullable()->index('fk_users_religion');
            $table->integer('caste_id')->nullable()->index('fk_users_caste');
            $table->text('permanent_address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->integer('occupation_id')->nullable()->index('fk_users_occupation');
            $table->integer('approving_authority_id')->nullable()->index('fk_users_approving_authority');
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('approval_type', 50)->nullable();
            $table->integer('approved_by')->nullable();
            $table->string('possible_sibling_match')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->integer('gender')->nullable()->index('fk_users_gender');
            $table->integer('maritial_status')->nullable()->index('fk_users_maritial_status');
            $table->integer('user_type')->nullable()->index('fk_user_type');
            $table->string('created_by')->nullable()->default('system');
            $table->date('created_on')->nullable()->default('CURRENT_DATE');
            $table->string('updated_by')->nullable()->default('system');
            $table->timestamp('updated_on')->useCurrentOnUpdate()->useCurrent();
            $table->char('status', 1)->nullable()->default('A');
            $table->integer('suggested_approval_id')->nullable()->index('suggested_approval_id');
            $table->enum('is_approver', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_registration');
    }
};
