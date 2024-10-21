<?php

namespace Svr\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель Setting
 */
class SystemUsersNotificationsMessages extends Model
{
    use HasFactory;

    /**
     * Точное название таблицы с учетом схемы
     *
     * @var string
     */
    protected $table = 'system.system_users_notifications_messages';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'message_id';

    /**
     * Поле даты создания строки
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * Поле даты обновления строки
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Атрибуты, которые можно назначать массово.
     *
     * @var array
     */
    protected $fillable
        = [
            'notification_type',    //Тип уведомления
            'message_description',  //Системное описание
            'message_title_front',  //Заголовок сообщения для отправки на фронт
            'message_title_email',  //Заголовок сообщения электронного письма
            'message_text_front',   //Текст сообщения для отправки на фронт
            'message_text_email',   //Текст сообщения электронного письма
            'message_status_front', //Флаг работы с фронтом
            'message_status_email', //Флаг работы с электронной почтой
            'message_status',       //Статус уведомления
            'created_at',           //Дата создания
            'updated_at',           //Дата обновления
        ];

    /**
     * @var array|string[]
     */
    protected array $dates
        = [
            'created_at',                   // Дата создания записи
            'updated_at',                   // Дата редактирования записи
        ];

    /**
     * Формат хранения столбцов даты модели.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var bool
     */
    public $timestamps = true;


    /**
     * Создать запись
     *
     * @param $request
     *
     * @return void
     */
    public function notificationsCreate($request): void
    {
        $this->rules($request);
        $this->fill($request->all());
        $this->save();
    }

    /**
     * Обновить запись
     * @param $request
     *
     * @return void
     */
    public function notificationsUpdate($request): void
    {
        // валидация
        $this->rules($request);
        // получаем массив полей и значений и з формы
        $data = $request->all();
        // получаем id
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;
        // готовим сущность для обновления
        $modules_data = $this->find($id);
        // обновляем запись
        $modules_data->update($data);
    }

    /**
     * Валидация входных данных
     * @param $request
     *
     * @return void
     */
    private function rules($request): void
    {
        // получаем поля со значениями
        $data = $request->all();

        // получаем значение первичного ключа
        $id = (isset($data[$this->primaryKey])) ? $data[$this->primaryKey] : null;

        // id - Первичный ключ
        if (!is_null($id)) {
            $request->validate(
                [$this->primaryKey => 'required|exists:.'.$this->getTable().','.$this->primaryKey],
                [$this->primaryKey => trans('svr-core-lang::validation.required')],
            );
        }

        // notification_type - 	Тип уведомления
        $request->validate(
            ['notification_type' => 'required|string|max:255'],
            ['notification_type' => trans('svr-core-lang::validation')],
        );

        // message_description - Системное описание
        $request->validate(
            ['message_description' => 'required|string|max:255'],
            ['message_description' => trans('svr-core-lang::validation')],
        );

        // message_title_front - Заголовок сообщения для отправки на фронт
        $request->validate(
            ['message_title_front' => 'required|string|max:55'],
            ['message_title_front' => trans('svr-core-lang::validation')],
        );

        // message_title_email - Заголовок сообщения электронного письма
        $request->validate(
            ['message_title_email' => 'nullable|string|max:55'],
            ['message_title_email' => trans('svr-core-lang::validation')],
        );

        // message_text_front - Текст сообщения для отправки на фронт
        $request->validate(
            ['message_text_front' => 'required|string'],
            ['message_text_front' => trans('svr-core-lang::validation')],
        );

        // message_text_email - Текст сообщения электронного письма
        $request->validate(
            ['message_text_email' => 'nullable|string'],
            ['message_text_email' => trans('svr-core-lang::validation')],
        );

        // message_status_front - Флаг работы с фронтом
        $request->validate(
            ['message_status_front' => 'required|string'],
            ['message_status_front' => trans('svr-core-lang::validation')],
        );

        // message_status_email - Флаг работы с электронной почтой
        $request->validate(
            ['message_status_email' => 'required|string'],
            ['message_status_email' => trans('svr-core-lang::validation')],
        );

        // message_status - Статус уведомления
        $request->validate(
            ['message_status' => 'required|string'],
            ['message_status' => trans('svr-core-lang::validation')],
        );
    }
}
