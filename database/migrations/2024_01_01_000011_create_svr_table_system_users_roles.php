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
        if (!Schema::hasTable('system.system_users_roles'))
        {
            Schema::create('system.system_users_roles', function (Blueprint $table) {
                $table->comment('Сопоставление пользователей и ролей');
                $table->increments('user_role_id')->comment('Инкремент');
                $table->bigInteger('user_id')->nullable('false')->comment('USER_ID в таблице SYSTEM.SYSTEM_USERS');
                $table->string('role_slug', 32)->nullable(false)->comment('ROLE_SLUG в таблице SYSTEM.SYSTEM_ROLES');
                // Это поля "created_at" и "updated_at".
                $table->timestamps();

                $table->unique(['user_id', 'role_slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_users_roles');
    }
};


