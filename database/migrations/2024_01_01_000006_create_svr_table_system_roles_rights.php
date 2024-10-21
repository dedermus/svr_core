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
        if (!Schema::hasTable('system.system_roles_rights'))
        {
            Schema::create('system.system_roles_rights', function (Blueprint $table) {
                $table->comment('Сопоставление ролей и прав');
                $table->increments('role_right_id')->comment('Инкремент');
                $table->string('role_slug', 64)->nullable(false)->comment('ROLE_SLUG в таблице SYSTEM.SYSTEM_ROLES');
                $table->string('right_slug', 65)->nullable(true)->comment('RIGHT_SLUG в таблице SYSTEM.SYSTEM_MODULES_ACTIONS');
                // Это поля "created_at" и "updated_at".
                $table->timestamps();

                $table->unique(['role_slug', 'right_slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_roles_rights');
    }
};
