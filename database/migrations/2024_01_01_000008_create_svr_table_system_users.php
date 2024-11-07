<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
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

        if (!Schema::hasTable('system.system_users'))
        {
            Schema::create('system.system_users', function (Blueprint $table) {
                $table->comment('Пользователь');
                $table->increments('user_id')->comment('Инкремент');
                $table->string('user_guid', 64)->nullable(true)->comment('UUID4');
                $table->string('user_first', 32)->nullable(true)->comment('Имя');
                $table->string('user_middle', 32)->nullable(true)->comment('Отчество');
                $table->string('user_last', 32)->nullable(true)->comment('Фамилия');
                $table->string('user_avatar', 255)->nullable(true)->comment('Иконка (аватар)'); // null
                $table->string('user_password', 64)->nullable(false)->comment('Пароль');
                $table->addColumn('system.system_sex','user_sex', 12)->nullable(false)->default('male')->comment('Пол (гендерная принадлежность)'); // null
                $table->string('user_email', 64)->nullable(false)->comment('Электронный адрес');
                $table->string('user_phone', 18)->nullable(true)->comment('Телефон'); // null
                $table->string('user_base_index', 16)->nullable(true)->default(null)->comment('Базовый индекс хозяйства при автоматическом создании пользователей');
                $table->string('user_herriot_login', 64)->nullable(true)->default(null)->comment('Логин в API herriot');
                $table->string('user_herriot_password', 64)->nullable(true)->default(null)->comment('Пароль в API herriot');
                $table->string('user_herriot_web_login', 64)->nullable(true)->default(null)->comment('Логин в WEB herriot');
                $table->string('user_herriot_web_password', 64)->nullable(true)->default(null)->comment('Пароль в WEB herriot');
                $table->string('user_herriot_apikey', 255)->nullable(true)->default(null)->comment('apikey в herriot');
                $table->string('user_herriot_issuerid', 255)->nullable(true)->default(null)->comment('issuerid в herriot');
                $table->string('user_herriot_serviceid', 255)->nullable(true)->default(null)->comment('serviceid в herriot');
                $table->addColumn('system.system_status_confirm','user_email_status')->nullable(false)->default('changed')->comment('Статус электронного адреса');
                $table->addColumn('system.system_status_confirm','user_phone_status')->nullable(false)->default('changed')->comment('Статус телефона');
                $table->addColumn('system.system_status_notification','user_notifications')->nullable(false)->default('email')->comment('Подтверждение');
                $table->addColumn('system.system_status','user_status')->nullable(false)->default('enabled')->comment('Статус записи (активна/не активна)');
                $table->addColumn('system.system_status_delete','user_status_delete')->nullable(false)->default('active')->comment('Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)');
                $table->timestamp('user_date_created')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания');
                $table->timestamp('user_date_update')->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата обновления');
                $table->timestamp('user_date_block')->nullable(true)->default(null)->comment('Дата блокировки');
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
        Schema::dropIfExists('system.system_users');
    }
};
