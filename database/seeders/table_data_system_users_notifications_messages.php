<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Created seeds for table system.system_users_notifications_messages
 */
class table_data_system_users_notifications_messages extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_users_notifications_messages')->truncate();

        DB::table('system.system_users_notifications_messages')->insert([
            [
                "notification_type" => "application_complete_full",
                "message_description" => "Заявка полностью отработана",
                "message_title_front" => "Заявка {{application_id}} сменила статус",
                "message_text_front" => "Уникальный номер Хорриот присвоен записям животных в СВР Передано {{animals_count_total}} записей.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_complete_partial",
                "message_description" => "Заявка частично отработана",
                "message_title_front" => "Заявка {{application_id}} сменила статус",
                "message_text_front" => "Ошибка присвоения уникального номера Хорриот записям животных в СВР. Передано {{animals_count_good}} записей из {{animals_count_total}}.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_animal_add",
                "message_description" => "Добавление животного в заявку",
                "message_title_front" => "Добавление записей в заявку {{application_id}}",
                "message_text_front" => "Записи животных добавлены в заявку {{application_id}}",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_animal_delete",
                "message_description" => "Удаление животного из заявки",
                "message_title_front" => "Удаление записей из заявки {{application_id}}",
                "message_text_front" => "Записи животных удалены из заявки {{application_id}}",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "user_password_restore",
                "message_description" => "Восстановление пароля пользователя",
                "message_title_email" => "Восстановление пароля",
                "message_text_email" => "Уважаемый(ая) {{user_last}} {{user_first}} {{user_middle}}.
	Ссылка для восстановления пароля: {{url_user_password_restore}}
	Если вы не запрашивали изменение пароля, не переходите по ссылке и обратитесь в службу технической поддержки клиентов.
	{{email_support}}",
                "message_status_front" => "disabled",
                "message_status_email" => "enabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "user_password_change",
                "message_description" => "Смена пароля пользователя",
                "message_title_email" => "Изменение пароля",
                "message_text_email" => "Уважаемый(ая) {{user_last}} {{user_first}} {{user_middle}}.
	Ваш пароль для учетной записи СВР был недавно изменен. Новый пароль: {{user_password}}
	Если вы не меняли пароль, обратитесь в службу технической поддержки клиентов.
	{{email_support}}",
                "message_status_front" => "disabled",
                "message_status_email" => "enabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "user_create",
                "message_description" => "Создание пользователя",
                "message_title_email" => "Аккаунт создан",
                "message_text_email" => "Уважаемый(ая) {{user_last}} {{user_first}} {{user_middle}}.
	Ваша учетная запись в Системе ветеринарной регистрации – «СВР» создана.
	Ваш логин: {{user_email}}
	Ваш пароль {{user_password}}
	для входа пройдите по ссылке {{url_login}}",
                "message_status_front" => "disabled",
                "message_status_email" => "enabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_created",
                "message_description" => "Создание заявки",
                "message_title_front" => "Заявка {{application_id}} сменила статус",
                "message_text_front" => "Создана заявка {{application_id}}",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_prepared",
                "message_description" => "Формирование заявки",
                "message_title_front" => "Заявка {{application_id}} сменила статус",
                "message_text_front" => "Сформирована заявка {{application_id}}",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "application_sent",
                "message_description" => "Отправка заявки",
                "message_title_front" => "Заявка {{application_id}} сменила статус",
                "message_text_front" => "Отправлена заявка {{application_id}} в Хорриот",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_selex_good",
                "message_description" => "Получение животных из СЕЛЕКСА - УСПЕХ",
                "message_title_front" => "Интеграция из СЕЛЭКС в СВР",
                "message_text_front" => "Записи животных успешно переданы из СЕЛЭКС в СВР. Передано {{animals_count_total}} записей.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_selex_guid_good",
                "message_description" => "Отправка GUID в СЕЛЕКС - УСПЕХ",
                "message_title_front" => "Передача уникального номера из СВР в СЕЛЭКС",
                "message_text_front" => "Передача уникальных номеров из СВР в ИАС «СЕЛЭКС» завершена. Передано {{animals_count_total}} записей.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_herriot_good",
                "message_description" => "Отправка данных в Хорриот - УСПЕХ",
                "message_title_front" => "Интеграция из СВР в Хорриот",
                "message_text_front" => "Записи животных из заявки {{application_id}} переданы в систему Хорриот Передано {{animals_count_total}} записей.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_selex_bad",
                "message_description" => "Получение животных из СЕЛЕКСА - НЕУДАЧА",
                "message_title_front" => "Интеграция из СЕЛЭКС в СВР",
                "message_text_front" => "Ошибка передачи данных из СЕЛЭКС в СВР ",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_selex_guid_bad",
                "message_description" => "Отправка GUID в СЕЛЕКС - НЕУДАЧА",
                "message_title_front" => "Передача уникального номера из СВР в СЕЛЭКС",
                "message_text_front" => "Ошибка Передача уникальных номеров из СВР в ИАС «СЕЛЭКС» завершена.",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "notification_type" => "integration_herriot_bad",
                "message_description" => "Отправка данных в Хорриот - НЕУДАЧА",
                "message_title_front" => "Интеграция из СВР в Хорриот",
                "message_text_front" => "Ошибка передачи данных в систему Хорриот заявки. ",
                "message_status_front" => "enabled",
                "message_status_email" => "disabled",
                "message_status" => "enabled",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
