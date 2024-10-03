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
        if (!Schema::hasTable('system.system_settings'))
        {
            Schema::create('system.system_settings', function (Blueprint $table) {
                $table->comment('Список ролей');
                $table->increments('setting_id')->comment('Инкремент');
                $table->string('owner_type', 255)->nullable(false)->default('system')->comment('признак принадлежности записи');
                $table->bigInteger('owner_id')->nullable(false)->default(0)->comment('идентификатор принадлежности записи');
                $table->string('setting_code', 50)->nullable(false)->comment('код записи');
                $table->text('setting_value')->nullable(false)->comment('значение');
                $table->string('setting_value_alt', 255)->nullable(false)->comment('альтернативное значение');
                $table->timestamp('setting_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_settings');
    }
};
