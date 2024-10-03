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
        if (!Schema::hasTable('system.system_modules'))
        {
            Schema::create('system.system_modules', function (Blueprint $table) {
                $table->comment('Модули');
                $table->increments('module_id')->comment('Инкремент');
                $table->string('module_name', 64)->nullable(false)->comment('Название модуля');
                $table->string('module_description', 100)->nullable(false)->comment('Описание модуля');
                $table->string('module_class_name', 32)->nullable(false)->comment('Имя класса модуля');
                $table->string('module_slug', 32)->nullable(false)->unique()->comment('Слаг для модуля (уникальный идентификатор)');
                $table->timestamp('module_created_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата создания записи');
                $table->timestamp('update_at')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Дата удаления записи');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.system_modules');
    }
};


