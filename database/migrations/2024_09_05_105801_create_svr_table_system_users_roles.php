<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
                $table->bigInteger('user_id')->nullable('false')->comment('ID пользователя');
                $table->string('role_slug', 32)->nullable(false)->comment('Слаг для роли (уникальный идентификатор)');
                $table->timestamp('user_role_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
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


