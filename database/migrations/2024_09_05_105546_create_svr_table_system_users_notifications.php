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

        if (!Schema::hasTable('system.system_users_notifications'))
        {
            Schema::create('system.system_users_notifications', function (Blueprint $table) {
                $table->comment('Уведомления пользователей');
                $table->increments('notification_id')->comment('Инкремент');
                $table->integer('user_id')->nullable(false)->comment('ID пользователя, чье уведомление');
                $table->integer('author_id')->nullable(true)->default(null)->comment('ID пользователя, создавшего уведомление. Если NULL, то уведомление создал система');
                $table->addColumn('system.system_notifications_types', 'notification_type')->nullable(false)->default('application_created')->comment('Тип уведомления');
                $table->string('notification_title', 55)->nullable(false)->comment('Заголовок уведомления');
                $table->text('notification_text')->nullable(false)->comment('Текст уведомления');
                $table->timestamp('notification_date_add')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания уведомления');
                $table->timestamp('notification_date_view')->nullable(true)->default(null)->comment('Дата просмотра уведомления. Если NULL, то уведомление еще не просмотрено');
                $table->timestamp('notification_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_users_notifications');
    }
};
