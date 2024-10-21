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
        if (!Schema::hasTable('system.system_modules'))
        {
            Schema::create('system.system_modules', function (Blueprint $table) {
                $table->comment('Модули');
                $table->increments('module_id')->comment('Инкремент');
                $table->string('module_name', 64)->nullable(true)->comment('Название модуля');
                $table->string('module_description', 100)->nullable(true)->comment('Описание модуля');
                $table->string('module_class_name', 32)->nullable(true)->comment('Имя класса модуля');
                $table->string('module_slug', 32)->nullable(true)->comment('Слаг для модуля (уникальный идентификатор)');
                // Это поля "created_at" и "updated_at".
                $table->timestamps();

                $table->unique('module_slug');
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


