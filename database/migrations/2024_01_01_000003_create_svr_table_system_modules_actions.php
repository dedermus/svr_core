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

        if (!Schema::hasTable('system.system_modules_actions'))
        {
            Schema::create('system.system_modules_actions', function (Blueprint $table) {
                $table->comment('Модули');
                $table->increments('right_id')->comment('Инкремент');
                $table->string('module_slug', 32)->nullable(false)->comment(
                    'MODULE_SLUG в таблице SYSTEM.SYSTEM_MODULES'
                );
                $table->string('right_action', 200)->nullable(false)->comment('Экшен');
                $table->string('right_name', 200)->nullable(false)->comment('Имя экшена');
                $table->string('right_slug', 32)->nullable(false)->default('admin')->unique()->comment(
                    'Слаг для экшена (уникальный идентификатор)'
                );
                $table->string('right_content_type', 32)->nullable(false)->default('json')->comment('Тип запроса');
                $table->addColumn('system.system_status', 'right_log_write')->nullable(false)->default('disabled')
                    ->comment('Флаг записи данных запроса в таблицу логов'
                );
                $table->timestamp('right_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment(
                    'Дата создания записи'
                );
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment(
                    'Дата удаления записи'
                );
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


