<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Traits\PostgresGrammar;

return new class extends Migration
{
    use PostgresGrammar;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->enumExists();

        if (!Schema::hasTable('system.system_users_notifications_messages'))
        {
            Schema::create('system.system_users_notifications_messages', function (Blueprint $table) {
                $table->comment('Сообщения уведомлений');
                $table->increments('message_id')->comment('Инкремент');
                $table->addColumn('system.system_notifications_types', 'notification_type')->nullable(false)->default('application_created')->comment('Тип уведомления');
                $table->string('message_description', 255)->nullable(false)->comment('Системное описание');
                $table->string('message_title_front', 55)->nullable(false)->comment('Заголовок сообщения для отправки на фронт');
                $table->string('message_title_email', 55)->nullable(false)->comment('Заголовок сообщения электронного письма');
                $table->text('message_text_front')->nullable(false)->comment('Текст сообщения для отправки на фронт');
                $table->text('message_text_email')->nullable(false)->comment('Текст сообщения электронного письма');
                $table->addColumn('system.system_status', 'message_status_front')->nullable(false)->default('enabled')->comment('Флаг работы с фронтом');
                $table->addColumn('system.system_status', 'message_status_email')->nullable(false)->default('enabled')->comment('Флаг работы с электронной почтой');
                $table->addColumn('system.system_status', 'message_status')->nullable(false)->default('enabled')->comment('Статус уведомления');
                $table->timestamp('message_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_users_notifications_messages');
    }
};
