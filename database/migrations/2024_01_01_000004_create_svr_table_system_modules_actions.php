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
        if (!Schema::hasTable('system.system_modules_actions'))
        {
            Schema::create('system.system_modules_actions', function (Blueprint $table) {
                $table->comment('Экшены прав');
                $table->increments('right_id')->comment('Инкремент');
                $table->string('module_slug', 32)->nullable(true)->comment(
                    'MODULE_SLUG в таблице SYSTEM.SYSTEM_MODULES'
                );
                $table->string('right_action', 200)->nullable(true)->comment('Экшен');
                $table->string('right_name', 200)->nullable(true)->comment('Имя экшена');
                $table->string('right_slug', 65)->nullable(true)->comment(
                    'Слаг для экшена (уникальный составной идентификатор из module_slag + right_slug)'
                );
                $table->string('right_content_type', 32)->nullable(true)->default('json')->comment('Тип запроса');
                $table->addColumn('system.system_status', 'right_log_write')->nullable(false)->default('disabled')
                    ->comment('Флаг записи данных запроса в таблицу логов'
                    );
                // Это поля "created_at" и "updated_at".
                $table->timestamps();

                $table->unique(['module_slug', 'right_slug']);
                $table->unique('right_slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_modules_actions');
    }
};


