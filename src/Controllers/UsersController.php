<?php

namespace Svr\Core\Controllers;

use Svr\Core\Models\SystemRoles;
use Svr\Core\Models\SystemUsers;
use Svr\Core\Models\SystemUsersRoles;
use Svr\Core\Enums\SystemSexEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;
use Svr\Core\Enums\SystemStatusConfirmEnum;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Svr\Core\Enums\SystemStatusNotificationEnum;

class UsersController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemUsers
     */
    private SystemUsers $systemUsers;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemUsers = new SystemUsers();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'User';

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
            $content->header(trans('svr-core-lang::svr.user.title'));
            $content->description(trans('svr-core-lang::svr.user.description'));
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
            $content->header(trans('svr-core-lang::svr.user.create'));
            $content->description(trans('svr-core-lang::svr.user.description'));
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
            ->title(trans('svr-core-lang::svr.user.show'))
            ->description(trans('svr-core-lang::svr.user.description'))
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
            ->title(trans('svr-core-lang::svr.user.title'))
            ->description(trans('svr-core-lang::svr.user.edit'))
            ->row($this->form($id)->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemUsers = $this->systemUsers;
        $grid = new Grid($systemUsers);
        $grid->model()->orderBy('user_id', 'asc');

        // ID пользователя
        $grid->column('user_id', trans('svr-core-lang::svr.user.user_id'))
            ->help(__('user_id'))->sortable();

        // Базовый индекс
        $grid->column('user_base_index', trans('svr-core-lang::svr.user.base_index'))
            ->help(__('user_base_index'))->sortable();

        // Аватар пользователя
        $grid->column('user_avatar', trans('svr-core-lang::svr.user.user_avatar'))
            //->image(asset($systemUsers->getPathAvatar()))
            ->display(function ($item) use ($systemUsers) {
                return '<a href="'.$systemUsers->getUrlAvatar($item).'" target="_blank"><img alt=""  src="'.$systemUsers->getUrlAvatar($item).'" height="40" ></a>';
            })
            ->help(__('user_avatar'))->sortable();

        // GUID пользователя
        $grid->column('user_guid', trans('svr-core-lang::svr.user.user_guid'))
            ->help(__('user_guid'))->sortable();

        // Имя пользователя
        $grid->column('user_first', trans('svr-core-lang::svr.user.user_first'))
            ->help(__('user_first'))->sortable();

        // Отчество пользователя
        $grid->column('user_middle', trans('svr-core-lang::svr.user.user_middle'))
            ->help(__('user_middle'))->sortable();

        // Фамилия пользователя
        $grid->column('user_last', trans('svr-core-lang::svr.user.user_last'))
            ->help(__('user_last'))->sortable();

        $grid->column('roles', trans('svr-core-lang::svr.user.user_role'))
            ->pluck('role_slug')
            ->label();

        // Пол пользователя
        $grid->column('user_sex', trans('svr-core-lang::svr.user.user_sex'))
            ->help(__('user_sex'))->sortable();

        // Email пользователя
        $grid->column('user_email', trans('svr-core-lang::svr.user.user_email'))
            ->help(__('user_email'))->sortable();

        // Логин в API Хорриот пользователя
        $grid->column('user_herriot_login', trans('svr-core-lang::svr.user.user_herriot_login'))
            ->help(__('user_herriot_login'))->sortable();

        // Пароль в API Хорриот пользователя
        $grid->column('user_herriot_password', trans('svr-core-lang::svr.user.user_herriot_password'))
            ->help(__('user_herriot_password'))->sortable();

        // Логин в WEB Хорриот пользователя
        $grid->column('user_herriot_web_login', trans('svr-core-lang::svr.user.user_herriot_web_login'))
            ->help(__('user_herriot_web_login'))->sortable();

        // Пароль в WEB Хорриот пользователя
        $grid->column('user_herriot_web_password', trans('svr-core-lang::svr.user.user_herriot_web_password'))
            ->help(__('user_herriot_web_password'))->sortable();

        // APIKey Хорриот пользователя
        $grid->column('user_herriot_apikey', trans('svr-core-lang::svr.user.user_herriot_apikey'))
            ->help(__('user_herriot_apikey'))->sortable();

        // IssuerId Хорриот пользователя
        $grid->column('user_herriot_issuerid', trans('svr-core-lang::svr.user.user_herriot_issuerid'))
            ->help(__('user_herriot_issuerid'))->sortable();

        // ServiceId Хорриот пользователя
        $grid->column('user_herriot_serviceid', trans('svr-core-lang::svr.user.user_herriot_serviceid'))
            ->help(__('user_herriot_serviceid'))->sortable();

        // Статус email пользователя
        $grid->column('user_email_status', trans('svr-core-lang::svr.user.user_email_status'))
            ->help(__('user_email_status'))->sortable();

        // Телефон пользователя
        $grid->column('user_phone', trans('svr-core-lang::svr.user.user_phone'))
            ->help(__('user_phone'))->sortable();

        // Статус телефона пользователя
        $grid->column('user_phone_status', trans('svr-core-lang::svr.user.user_phone_status'))
            ->help(__('user_phone_status'))->sortable();

        // Приоритетный способ получения уведомлений
        $grid->column('user_notifications', trans('svr-core-lang::svr.user.user_notifications'))
            ->help(__('user_notifications'))->sortable();

        // Статус записи (активна/не активна)
        $grid->column('user_status', trans('svr-core-lang::svr.user.user_status'))
            ->help(__('user_status'))->sortable();

        // Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)
        $grid->column('user_status_delete', trans('svr-core-lang::svr.user.user_status_delete'))
            ->help(__('user_status_delete'))->sortable();

        // Дата создания пользователя
        $grid->column('created_at', trans('svr-core-lang::svr.user.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemUsers) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsers->getDateFormat()
                );
            })->sortable();

        // Дата обновления пользователя
        $grid->column('updated_at', trans('svr-core-lang::svr.user.updated_at'))
            ->display(function ($value) use ($systemUsers) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsers->getDateFormat()
                );
            })->help(__('updated_at'))
            ->sortable();

        // Дата создания пользователя
        $grid->column('user_date_created', trans('svr-core-lang::svr.user.user_date_created'))
            ->display(function ($value) use ($systemUsers) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsers->getDateFormat()
                );
            })->help(__('user_date_created'))
            ->sortable();

        // Дата обновления пользователя
        $grid->column('user_date_update', trans('svr-core-lang::svr.user.user_date_update'))
            ->display(function ($value) use ($systemUsers) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                    $systemUsers->getDateFormat()
                );
            })->help(__('user_date_update'))
            ->sortable();

        // Дата блокировки пользователя
        $grid->column('user_date_block', trans('svr-core-lang::svr.user.user_date_block'))
            ->help(__('user_date_block'))
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
        $model = $this->systemUsers;
        $show = new Show(SystemUsers::findOrFail($id));
        $show->field('user_id', trans('svr-core-lang::svr.user.user_id'));
        $show->field('user_base_index', trans('svr-core-lang::svr.user.base_index'));
        $show->field('user_guid', trans('svr-core-lang::svr.user.user_guid'));
        $show->field('user_first', trans('svr-core-lang::svr.user.user_first'));
        $show->field('user_middle', trans('svr-core-lang::svr.user.user_middle'));
        $show->field('user_last', trans('svr-core-lang::svr.user.user_last'));
        $show->field('user_roles_list', trans('svr-core-lang::svr.user.user_role'))->as(function () use ($id) {
            return SystemRoles::All(['role_id', 'role_slug'])
                ->whereIn('role_id', SystemUsersRoles::userRolesGet($id))
                ->pluck('role_slug');
        })->label();
        $show->field('user_avatar', trans('svr-core-lang::svr.user.user_avatar'))
            ->unescape()->as(function ($user_avatar) use ($model) {
                return '<a href="'.$model->getUrlAvatar($user_avatar).'" target="_blank">
                <img style="min-width: 200px !important; max-width: 320px !important;" alt="" src="'.$model->getUrlAvatar($user_avatar).'"/></a>';
            });
        $show->field('user_sex', trans('svr-core-lang::svr.user.user_sex'));
        $show->field('user_email', trans('svr-core-lang::svr.user.user_email'));
        $show->field('user_herriot_login', trans('svr-core-lang::svr.user.user_herriot_login'));
        $show->field('user_herriot_password', trans('svr-core-lang::svr.user.user_herriot_password'));
        $show->field('user_herriot_web_login', trans('svr-core-lang::svr.user.user_herriot_web_login'));
        $show->field('user_herriot_web_password', trans('svr-core-lang::svr.user.user_herriot_web_password'));
        $show->field('user_herriot_apikey', trans('svr-core-lang::svr.user.user_herriot_apikey'));
        $show->field('user_herriot_issuerid', trans('svr-core-lang::svr.user.user_herriot_issuerid'));
        $show->field('user_herriot_serviceid', trans('svr-core-lang::svr.user.user_herriot_serviceid'));
        $show->field('user_email_status', trans('svr-core-lang::svr.user.user_email_status'));
        $show->field('user_phone', trans('svr-core-lang::svr.user.user_phone'));
        $show->field('user_phone_status', trans('svr-core-lang::svr.user.user_phone_status'));
        $show->field('user_notifications', trans('svr-core-lang::svr.user.user_notifications'));
        $show->field('user_status', trans('svr-core-lang::svr.user.user_status'));
        $show->field('user_status_delete', trans('svr-core-lang::svr.user.user_status_delete'));
        $show->field('created_at', trans('svr-core-lang::svr.user.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.user.updated_at'));
        $show->field('user_date_created', trans('svr-core-lang::svr.user.user_date_created'));
        $show->field('user_date_update', trans('svr-core-lang::svr.user.user_date_update'));
        $show->field('user_date_block', trans('svr-core-lang::svr.user.user_date_block'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @param $id
     *
     * @return Form
     * @throws \Exception
     */
    protected function form($id = false): Form
    {
        $model = $this->systemUsers;

        $form = new Form($this->systemUsers);

        // 	Инкремент
        $form->display('user_id', trans('svr-core-lang::svr.user.user_id'))
            -> help(__('user_id'));

        $form->hidden('user_id', trans('svr-core-lang::svr.user.user_id'))
            -> help(__('user_id'));

        // Базовый индекс
        $form->text('user_base_index', trans('svr-core-lang::svr.user.base_index'))
            ->help(__('user_base_index'));

        // GUID пользователя
        $form->text('user_guid', trans('svr-core-lang::svr.user.user_guid'))
            //  ->disable()
            ->help(__('user_guid'));

        // Имя пользователя
        $form->text('user_first', trans('svr-core-lang::svr.user.user_first'))
            ->help(__('user_first'));

        // Отчество пользователя
        $form->text('user_middle', trans('svr-core-lang::svr.user.user_middle'))
            ->help(__('user_middle'));

        // Фамилия пользователя
        $form->text('user_last', trans('svr-core-lang::svr.user.user_last'))
            ->help(__('user_last'));

        // Иконка (аватар)
        $form->image('user_avatar', trans('svr-core-lang::svr.user.user_avatar'))
            ->customFormat(function ($item) use ($model) {
                return $model->getUrlAvatar($item);
            })
            ->help(__('user_avatar'));

        // Роли пользователя
        $form->multipleSelect('user_roles_list', trans('svr-core-lang::svr.user.user_role'))
            ->help(__('user_role'))
            ->value(SystemUsersRoles::userRolesGet($id))
            ->options(function(){
                return SystemRoles::All(['role_slug', 'role_id'])->pluck('role_slug', 'role_id');
            });
        $form->ignore(['user_roles_list']);

        // Пароль пользователя
        $form->password('user_password', trans('svr-core-lang::svr.user.user_password'))
            ->required()
            ->help(__('user_password'));

        // Пол пользователя
        $form->select('user_sex', trans('svr-core-lang::svr.user.user_sex'))
            ->required()
            ->options(SystemSexEnum::get_option_list())
            ->default('male')
            ->help(__('user_sex'));

        // Логин в API Хорриот пользователя
        $form->text('user_herriot_login', trans('svr-core-lang::svr.user.user_herriot_login'))
            ->help(__('user_herriot_login'));

        // Пароль в API Хорриот пользователя
        $form->text('user_herriot_password', trans('svr-core-lang::svr.user.user_herriot_password'))
            ->help(__('user_herriot_password'));

        // Логин в WEB Хорриот пользователя
        $form->text('user_herriot_web_login', trans('svr-core-lang::svr.user.user_herriot_web_login'))
            ->help(__('user_herriot_web_login'));

        // Пароль в WEB Хорриот пользователя
        $form->text('user_herriot_web_password', trans('svr-core-lang::svr.user.user_herriot_web_password'))
            ->help(__('user_herriot_web_password'));

        // APIKey Хорриот пользователя
        $form->text('user_herriot_apikey', trans('svr-core-lang::svr.user.user_herriot_apikey'))
            ->help(__('user_herriot_apikey'));

        // IssuerId Хорриот пользователя
        $form->text('user_herriot_issuerid', trans('svr-core-lang::svr.user.user_herriot_issuerid'))
            ->help(__('user_herriot_issuerid'));

        // ServiceId Хорриот пользователя
        $form->text('user_herriot_serviceid', trans('svr-core-lang::svr.user.user_herriot_serviceid'))
            ->help(__('user_herriot_serviceid'));

        // Email пользователя
        $form->email('user_email', trans('svr-core-lang::svr.user.user_email'))
            ->required()
            ->help(__('user_email'));

        // Статус email пользователя
        $form->select('user_email_status', trans('svr-core-lang::svr.user.user_email_status'))
            ->options(SystemStatusConfirmEnum::get_option_list())
            ->required()
            ->default('changed')
            ->help(__('user_email_status'));

        // Телефон пользователя
        $form->phonenumber('user_phone', trans('svr-core-lang::svr.user.user_phone'))
            ->options(['mask' => '+9 (999) 999-99-99'])
            ->help(__('user_phone'));

        // Статус телефона пользователя
        $form->select('user_phone_status', trans('svr-core-lang::svr.user.user_phone_status'))
            ->options(SystemStatusConfirmEnum::get_option_list())
            ->default('changed')
            ->help(__('user_phone_status'));

        // Приоритетный способ получения уведомлений
        $form->select('user_notifications', trans('svr-core-lang::svr.user.user_notifications'))
            ->options(SystemStatusNotificationEnum::get_option_list())
            ->required()
            ->default('email')
            ->help(__('user_notifications'));

        // Статус записи (активна/не активна)
        $form->select('user_status', trans('svr-core-lang::svr.user.user_status'))
            ->options(SystemStatusEnum::get_option_list())
            ->required()
            ->default('enabled')
            ->help(__('user_status'));

        // Статус псевдо-удаленности записи (активна - не удалена/не активна - удалена)
        $form->select('user_status_delete', trans('svr-core-lang::svr.user.user_status_delete'))
            ->options(SystemStatusDeleteEnum::get_option_list())
            ->required()
            ->default('active')
            ->help(__('user_status_delete'));

        // Дата создания
        $form->datetime('user_date_created', trans('svr-core-lang::svr.user.user_date_created'))
            ->help(__('user_date_created'))
            ->disable();

        // Дата обновления
        $form->datetime('user_date_update', trans('svr-core-lang::svr.user.user_date_update'))
            ->help(__('user_date_update'))
            ->disable();

        // Дата блокировки
        $form->datetime('user_date_block', trans('svr-core-lang::svr.user.user_date_block'))
            ->help(__('user_date_block'))
            ->disable();

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.user.created_at'))
            ->help(__('created_at'))
            ->disable();

        // Дата обновления
        $form->datetime('updated_at', trans('svr-core-lang::svr.user.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        // обработка формы
        $form->saving(function (Form $form) use ($model)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $form->user_password = Hash::make($form->user_password);
                $form->user_guid = Str::uuid();
                request()->request->add([
                    'user_password' => $form->user_password,
                    'user_guid' => $form->user_guid,
                ]);
                $model->userCreate(request());
            }
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                // если пароль был изменен
                if ($form->user_password && $form->model()->user_password != $form->user_password) {
                    $form->user_password = Hash::make($form->user_password);
                    request()->request->add([
                        'user_password' => $form->user_password,
                    ]);
                }
                $model->userUpdate(request());
            }
            return redirect(admin_url($form->resource(0)));
        });

        return $form;
    }
}
