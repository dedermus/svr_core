<?php

namespace Svr\Core\Controllers;

use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersNotifications;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;
use Svr\Core\Enums\SystemNotificationsTypesEnum;

class UsersNotificationsController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemUsersNotifications
     */
    private SystemUsersNotifications $systemUsersNotifications;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemUsersNotifications = new SystemUsersNotifications();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'UsersNotifications';

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
            $content->header(trans('svr-core-lang::svr.users_notifications.title'));
            $content->description(trans('svr-core-lang::svr.users_notifications.description'));
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
            $content->header(trans('svr-core-lang::svr.users_notifications.create'));
            $content->description(trans('svr-core-lang::svr.users_notifications.description'));
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
            ->title(trans('svr-core-lang::svr.users_notifications.show'))
            ->description(trans('svr-core-lang::svr.users_notifications.description'))
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
            ->title(trans('svr-core-lang::svr.users_notifications.title'))
            ->description(trans('svr-core-lang::svr.users_notifications.edit'))
            ->row($this->form($id)->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemUsersNotifications = $this->systemUsersNotifications;
        $grid = new Grid($systemUsersNotifications);
        $grid->model()->orderBy('notification_id', 'asc');

        // ID сообщения
        $grid->column('notification_id', trans('svr-core-lang::svr.users_notifications.notification_id'))
            ->help(__('notification_id'))->sortable();

        // ID пользователя, чье уведомление
        $grid->column('user_id', trans('svr-core-lang::svr.users_notifications.user_id'))
            ->link(function ($value){
                return '/admin/core/users/'.$value['user_id'];
            }, '_blank')
            ->help(__('user_id'))
            ->sortable();

        // ID пользователя, создавшего уведомление. Если NULL, то уведомление создал система
        $grid->column('author_id', trans('svr-core-lang::svr.users_notifications.author_id'))
            ->link(function ($value){
                return '/admin/core/users/'.$value['author_id'];
            }, '_blank')
            ->help(__('author_id'))
            ->sortable();

        // Тип уведомления
        $grid->column('notification_type', trans('svr-core-lang::svr.users_notifications.notification_type'))
            ->help(__('notification_type'))->sortable();

        // Заголовок сообщения
        $grid->column('notification_title', trans('svr-core-lang::svr.users_notifications.notification_title'))
            ->help(__('notification_title'))->sortable();

        // Текст сообщения
        $grid->column('notification_text', trans('svr-core-lang::svr.users_notifications.notification_text'))
            ->help(__('notification_text'))->sortable();

        // Дата создания уведомления
        $grid->column('notification_date_add', trans('svr-core-lang::svr.users_notifications.notification_date_add'))
            ->help(__('notification_date_add'))
            ->display(function ($value) use ($systemUsersNotifications) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotifications->getDateFormat()
                );
            })->sortable();

        // Дата просмотра уведомления. Если NULL, то уведомление еще не просмотрено
        $grid->column('notification_date_view', trans('svr-core-lang::svr.users_notifications.notification_date_view'))
            ->help(__('notification_date_view'))
            ->display(function ($value) use ($systemUsersNotifications) {
                return (!is_null($value)) ? Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotifications->getDateFormat()) : null;
            })->sortable();

        // Дата создания
        $grid->column('created_at', trans('svr-core-lang::svr.users_notifications.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemUsersNotifications) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotifications->getDateFormat()
                );
            })->sortable();

        // Дата обновления
        $grid->column('updated_at', trans('svr-core-lang::svr.users_notifications.updated_at'))
            ->display(function ($value) use ($systemUsersNotifications) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsersNotifications->getDateFormat()
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
        $show = new Show(SystemUsersNotifications::findOrFail($id));
        $data = $this->systemUsersNotifications->find($id)->toArray();
        $show->field('notification_id', trans('svr-core-lang::svr.users_notifications.notification_id'));
        $show->field('user_id', trans('svr-core-lang::svr.users_notifications.user_id'))
            ->link('/admin/core/users/'.$data['user_id'], '_blank');
        $show->field('author_id', trans('svr-core-lang::svr.users_notifications.author_id'))
            ->link('/admin/core/users/'.$data['author_id'], '_blank');
        $show->field('notification_type', trans('svr-core-lang::svr.users_notifications.notification_type'));
        $show->field('notification_title', trans('svr-core-lang::svr.users_notifications.notification_title'));
        $show->field('notification_text', trans('svr-core-lang::svr.users_notifications.notification_text'));
        $show->field('notification_date_add', trans('svr-core-lang::svr.users_notifications.notification_date_add'));
        $show->field('notification_date_view', trans('svr-core-lang::svr.users_notifications.notification_date_view'));
        $show->field('created_at', trans('svr-core-lang::svr.users_notifications.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.users_notifications.updated_at'));

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
        $systemUsersNotifications = $this->systemUsersNotifications;
        $usersModel = new SystemUsers();
        $usersPrimaryKey = $usersModel->getPrimaryKey();
        $form = new Form($this->systemUsersNotifications);

        // 	Инкремент
        $form->display('notification_id', trans('svr-core-lang::svr.users_notifications.notification_id'))
            -> help(__('message_id'));

        $form->hidden('notification_id', trans('svr-core-lang::svr.users_notifications.notification_id'))
            -> help(__('notification_id'));

        // Идентификатор пользователя
        $form->select('user_id', trans('svr-core-lang::svr.users_notifications.user_id'))
            ->required()
            ->options(function() use ($usersPrimaryKey){
                return SystemUsers::All([$usersPrimaryKey, $usersPrimaryKey])->pluck($usersPrimaryKey, $usersPrimaryKey);
            })
            ->help(__('user_id'));

        // Идентификатор автора
        $form->select('author_id', trans('svr-core-lang::svr.users_notifications.author_id'))
            ->options(function() use ($usersPrimaryKey){
                return SystemUsers::All([$usersPrimaryKey, $usersPrimaryKey])->pluck($usersPrimaryKey, $usersPrimaryKey);
            })
            ->help(__('author_id'));

        // Тип уведомления
        $form->select('notification_type', trans('svr-core-lang::svr.users_notifications.notification_type'))
            ->required()
            ->options(SystemNotificationsTypesEnum::get_option_list())
            ->default('application_created')->rules('required')
            ->help(__('notification_type'));

        // Заголовок уведомления
        $form->text('notification_title', trans('svr-core-lang::svr.users_notifications.notification_title'))
            ->required()
            ->help(__('notification_title'));

        // Текст сообщения
        $form->textarea('notification_text', trans('svr-core-lang::svr.users_notifications.notification_text'))
            ->required()
            ->help(__('notification_text'));

        // Дата создания уведомления
        $form->datetime('notification_date_add', trans('svr-core-lang::svr.users_notifications.notification_date_add'))
            ->help(__('notification_date_add'))
            ->readonly();

        // Дата просмотра уведомления. Если NULL, то уведомление еще не просмотрено
        $form->datetime('notification_date_view', trans('svr-core-lang::svr.users_notifications.notification_date_view'))
            ->help(__('notification_date_view'));

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.users_notifications.created_at'))
            ->help(__('created_at'))
            ->disable();

        // Дата обновления
        $form->datetime('updated_at', trans('svr-core-lang::svr.users_notifications.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        // обработка формы
        $form->saving(function (Form $form) use ($systemUsersNotifications)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $systemUsersNotifications->userNotificationsCreate(request());
            }
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                $systemUsersNotifications->userNotificationsUpdate(request());
            }
        });

        return $form;
    }
}
