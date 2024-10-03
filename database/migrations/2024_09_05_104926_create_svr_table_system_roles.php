<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->id('role_id')->comment('Инкремент');
                $table->string('role_name_long', 64)->nullable(false)->comment('Длинное название роли');
                $table->string('role_name_short', 32)->nullable(false)->comment('Короткое название роли');
                $table->string('role_slug', 32)->nullable(false)->unique()->comment('Слаг для роли (уникальный идентификатор)');
                $table->addColumn('system.system_status', 'role_status')->nullable(false)->default('enabled')->comment('Статус записи');
                $table->addColumn('system.system_status_delete', 'role_status_delete')->nullable(false)->default('active')->comment('Статус удаления записи');
                $table->timestamp('role_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
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


