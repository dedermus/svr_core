<?php

use OpenAdminCore\Admin\Grid\Column;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Form;
/**
 * Open-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * OpenAdminCore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * OpenAdminCore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Form::forget(['map', 'editor']);

Column::extend('xx_datetime', Svr\Core\Extensions\Column\XxDateTimeFormatter::class);      // Вывод даты в солонке grid
Show::extend('xx_datetime', Svr\Core\Extensions\Show\XxDateTimeFormatter::class);           // Вывод даты в show
Show::extend('xx_help', Svr\Core\Extensions\Show\XxHelp::class);                            // Вывод подсказки в show

Form::extend('xx_input', Svr\Core\Extensions\Form\XxInput::class);                          // Кастомное поле ввода текста с поддержкой подсказок и валидацией JS Bootstrap
