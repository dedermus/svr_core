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

        if (!Schema::hasTable('system.system_users_tokens'))
        {
            Schema::create('system.system_users_tokens', function (Blueprint $table) {
                $table->comment('Токены и связь их с пользователем');
                $table->increments('token_id')->comment('Инкремент');
                $table->integer('user_id')->nullable(false)->comment('Идентификатор пользователя');
                $table->integer('participation_id')->nullable(true)->default(null)->comment('Идентификатор типа привязки');
                $table->string('token_value', 72)->nullable(false)->unique()->comment('Значение токена');
                $table->string('token_client_ip', 32)->nullable(false)->comment('IP адрес пользователя');
                $table->string('token_client_agent', 256)->nullable(false)->comment('Агент пользователя');
                $table->string('browser_name', 32)->nullable(true)->comment('Название браузера');
                $table->string('browser_version', 32)->nullable(true)->comment('Версия браузера');
                $table->string('platform_name', 32)->nullable(true)->comment('Имя платформы');
                $table->string('platform_version', 32)->nullable(true)->comment('Версия платформы');
                $table->string('device_type', 32)->nullable(false)->default('desktop')->comment('Тип устроиства');
                $table->integer('token_last_login')->nullable(false)->comment('Таймстамп последнего входа');
                $table->integer('token_last_action')->nullable(false)->comment('Таймстамп последнего действия');
                $table->addColumn('system.system_status', 'token_status')->nullable(false)->default('enabled')->comment('Статус токена');
                // Это поля "created_at" и "updated_at".
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_users_tokens');
    }
};
