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
        if (!Schema::hasTable('system.system_settings'))
        {
            Schema::create('system.system_settings', function (Blueprint $table) {
                $table->comment('Список ролей');
                $table->increments('setting_id')->comment('Инкремент');
                $table->string('owner_type', 255)->nullable(false)->default('system')->comment('признак принадлежности записи');
                $table->bigInteger('owner_id')->nullable(false)->default(0)->comment('идентификатор принадлежности записи');
                $table->string('setting_code', 50)->nullable(false)->comment('код записи');
                $table->text('setting_value')->nullable(false)->comment('значение');
                $table->string('setting_value_alt', 255)->nullable(true)->comment('альтернативное значение');
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
        Schema::dropIfExists('system.system_settings');
    }
};
