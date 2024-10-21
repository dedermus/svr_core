<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Svr\Core\Traits\PostgresGrammar;

return new class extends Migration
{
    use PostgresGrammar;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->enumExists();

        if (!Schema::hasTable('system.system_roles'))
        {
            Schema::create('system.system_roles', function (Blueprint $table) {
                $table->comment('Список ролей');
                $table->increments('role_id')->comment('Инкремент');
                $table->string('role_name_long', 64)->nullable(false)->comment('Длинное название роли');
                $table->string('role_name_short', 32)->nullable(false)->comment('Короткое название роли');
                $table->string('role_slug', 32)->nullable(false)->comment('Слаг для роли (уникальный идентификатор)');
                $table->addColumn('system.system_status', 'role_status')->nullable(false)->default('enabled')->comment('Статус роли');
                $table->addColumn('system.system_status_delete', 'role_status_delete')->nullable(false)->default('active')->comment('Флаг удаления роли');
                // Это поля "created_at" и "updated_at".
                $table->timestamps();

                $table->unique('role_slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_roles');
    }
};


