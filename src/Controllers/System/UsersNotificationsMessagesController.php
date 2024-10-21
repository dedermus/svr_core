<?php

namespace Svr\Core\Controlles\System;

use Svr\Core\Models\System\SystemUsersNotificationsMessages;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;
use Svr\Core\Enums\SystemNotificationsTypesEnum;
use Svr\Core\Enums\SystemStatusEnum;

class UsersNotificationsMessagesController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemUsersNotificationsMessages
     */
    private SystemUsersNotificationsMessages $systemUsersNotificationsMessages;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemUsersNotificationsMessages = new SystemUsersNotificationsMessages();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'UsersNotificationsMessages';

    /**
     * Основной интерфейс.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content): Content
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('svr-core-lang::svr.users_notifications_messages.title'));
            $content->description(trans('svr-core-lang::svr.users_notifications_messages.description'));
            $content->body($this->grid());
        });
    }

    /**
     * Интерфейс создания новой записи.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content): Content
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('svr-core-lang::svr.users_notifications_messages.create'));
            $content->description(trans('svr-core-lang::svr.users_notifications_messages.description'));
            $content->body($this->form());
        });
    }

    /**
     * Интерфейс детального просмотра.
     *
     * @param         $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content): Content
    {
        return $content
            ->title(trans('svr-core-lang::svr.users_notifications_messages.show'))
            ->description(trans('svr-core-lang::svr.users_notifications_messages.description'))
            ->body($this->detail($id));
    }

    /**
     * Интерфейс редактирования записи.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content): Content
    {
        return $content
            ->title(trans('svr-core-lang::svr.users_notifications_messages.title'))
            ->description(trans('svr-core-lang::svr.users_notifications_messages.edit'))
            ->row($this->form($id)->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemUsersNotificationsMessages = $this->systemUsersNotificationsMessages;
        $grid = new Grid($systemUsersNotificationsMessages);
        $grid->model()->orderBy('message_id', 'asc');

        // ID пользователя
        $grid->column('message_id', trans('svr-core-lang::svr.users_notifications_messages.message_id'))
            ->help(__('user_id'))->sortable();

        // Тип уведомления
        $grid->column('notification_type', trans('svr-core-lang::svr.users_notifications_messages.notification_type'))
            ->help(__('notification_type'))->sortable();

        // Системное описание
        $grid->column('message_description', trans('svr-core-lang::svr.users_notifications_messages.message_description'))
            ->help(__('message_description'))->sortable();

        // Заголовок сообщения для отправки на фронт
        $grid->column('message_title_front', trans('svr-core-lang::svr.users_notifications_messages.message_title_front'))
            ->help(__('message_title_front'))->sortable();

        // Заголовок сообщения электронного письма
        $grid->column('message_title_email', trans('svr-core-lang::svr.users_notifications_messages.message_title_email'))
            ->help(__('message_title_email'))->sortable();

        // Текст сообщения для отправки на фронт
        $grid->column('message_text_front', trans('svr-core-lang::svr.users_notifications_messages.message_text_front'))
            ->help(__('message_text_front'))->sortable();

        // Текст сообщения электронного письма
        $grid->column('message_text_email', trans('svr-core-lang::svr.users_notifications_messages.message_text_email'))
            ->help(__('message_text_email'))->sortable();

        // Флаг работы с фронтом
        $grid->column('message_status_front', trans('svr-core-lang::svr.users_notifications_messages.message_status_front'))
            ->help(__('message_status_front'))->sortable();

        // Флаг работы с электронной почтой
        $grid->column('message_status_email', trans('svr-core-lang::svr.users_notifications_messages.message_status_email'))
            ->help(__('message_status_email'))->sortable();

        // Статус уведомления
        $grid->column('message_status', trans('svr-core-lang::svr.users_notifications_messages.message_status'))
            ->help(__('message_status'))->sortable();


        // Дата создания
        $grid->column('created_at', trans('svr-core-lang::svr.users_notifications_messages.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemUsersNotificationsMessages) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotificationsMessages->getDateFormat()
                );
            })->sortable();

        // Дата обновления
        $grid->column('updated_at', trans('svr-core-lang::svr.users_notifications_messages.updated_at'))
            ->display(function ($value) use ($systemUsersNotificationsMessages) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotificationsMessages->getDateFormat()
                );
            })->help(__('updated_at'))
            ->sortable();

        return $grid;
    }

    /**
     * Создайте шоу-конструктор для детального просмотра.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail(mixed $id): Show
    {
        $show = new Show(SystemUsersNotificationsMessages::findOrFail($id));
        $show->field('message_id', trans('svr-core-lang::svr.users_notifications_messages.message_id'));
        $show->field('notification_type', trans('svr-core-lang::svr.users_notifications_messages.notification_type'));
        $show->field('message_description', trans('svr-core-lang::svr.users_notifications_messages.message_description'));
        $show->field('message_title_front', trans('svr-core-lang::svr.users_notifications_messages.message_title_front'));
        $show->field('message_title_email', trans('svr-core-lang::svr.users_notifications_messages.message_title_email'));
        $show->field('message_text_front', trans('svr-core-lang::svr.users_notifications_messages.message_text_front'));
        $show->field('message_text_email', trans('svr-core-lang::svr.users_notifications_messages.message_text_email'));
        $show->field('message_status_front', trans('svr-core-lang::svr.users_notifications_messages.message_status_front'));
        $show->field('message_status_email', trans('svr-core-lang::svr.users_notifications_messages.message_status_email'));
        $show->field('message_status', trans('svr-core-lang::svr.users_notifications_messages.message_status'));
        $show->field('created_at', trans('svr-core-lang::svr.users_notifications_messages.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.users_notifications_messages.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @param $id
     *
     * @return Form
     */
    protected function form($id = false): Form
    {
        $systemUsersNotificationsMessages = $this->systemUsersNotificationsMessages;

        $form = new Form($this->systemUsersNotificationsMessages);

        // 	Инкремент
        $form->display('message_id', trans('svr-core-lang::svr.users_notifications_messages.message_id'))
            -> help(__('message_id'));

        $form->hidden('message_id', trans('svr-core-lang::svr.users_notifications_messages.message_id'))
            -> help(__('message_id'));

        // Тип уведомления
        $form->select('notification_type', trans('svr-core-lang::svr.users_notifications_messages.notification_type'))
            ->required()
            ->options(SystemNotificationsTypesEnum::get_option_list())
            ->default('application_created')->rules('required')
            ->help(__('notification_type'));

        // Системное описание
        $form->text('message_description', trans('svr-core-lang::svr.users_notifications_messages.message_description'))
            ->required()
            ->help(__('message_description'));

        // Заголовок сообщения для отправки на фронт
        $form->text('message_title_front', trans('svr-core-lang::svr.users_notifications_messages.message_title_front'))
            ->required()
            ->help(__('message_title_front'));

        // Заголовок сообщения электронного письма
        $form->text('message_title_email', trans('svr-core-lang::svr.users_notifications_messages.message_title_email'))
            ->help(__('message_title_email'));

        // Текст сообщения для отправки на фронт
        $form->text('message_text_front', trans('svr-core-lang::svr.users_notifications_messages.message_text_front'))
            ->required()
            ->help(__('message_text_front'));

        // Текст сообщения электронного письма
        $form->text('message_text_email', trans('svr-core-lang::svr.users_notifications_messages.message_text_email'))
            ->help(__('message_text_email'));

        // Флаг работы с фронтом
        $form->select('message_status_front', trans('svr-core-lang::svr.users_notifications_messages.message_status_front'))
            ->required()
            ->options(SystemStatusEnum::get_option_list())
            ->default('enabled')->rules('required')
            ->help(__('message_status_front'));

        // Флаг работы с электронной почтой
        $form->select('message_status_email', trans('svr-core-lang::svr.users_notifications_messages.message_status_email'))
            ->required()
            ->options(SystemStatusEnum::get_option_list())
            ->default('enabled')->rules('required')
            ->help(__('message_status_email'));

        // Статус уведомления
        $form->select('message_status', trans('svr-core-lang::svr.users_notifications_messages.message_status'))
            ->required()
            ->options(SystemStatusEnum::get_option_list())
            ->default('enabled')->rules('required')
            ->help(__('message_status'));

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.users_notifications_messages.created_at'))
            ->help(__('created_at'))
            ->disable();

        // Дата обновления
        $form->datetime('updated_at', trans('svr-core-lang::svr.users_notifications_messages.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        // обработка формы
        $form->saving(function (Form $form) use ($systemUsersNotificationsMessages)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                 $systemUsersNotificationsMessages->notificationsCreate(request());
            }
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                $systemUsersNotificationsMessages->notificationsUpdate(request());
            }
        });

        return $form;
    }
}
