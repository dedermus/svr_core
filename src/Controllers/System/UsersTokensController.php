<?php

namespace Svr\Core\Controlles\System;

use Svr\Core\Models\System\SystemUsers;
use Svr\Core\Models\System\SystemUsersToken;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;
use Svr\Core\Enums\SystemStatusEnum;

/**
 * Контроллер UsersTokens
 */
class UsersTokensController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemUsersToken
     */
    public SystemUsersToken $systemUsersToken;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemUsersToken = new SystemUsersToken();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'Setting';

    /**
     * Основной интерфейс.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content): Content
    {
        return $content
            ->header(trans('svr-core-lang::svr.users_token.users_token'))
            ->description(trans('svr-core-lang::svr.users_token.description'))
            ->body($this->grid());
    }

    /**
     * Интерфейс детального просмотра.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content): Content
    {
        return $content
            ->title(trans('svr-core-lang::svr.users_token.users_token'))
            ->description(trans('svr-core-lang::svr.users_token.show'))
            ->body($this->detail($id));
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
        return $content
            ->title(trans('svr-core-lang::svr.users_token.users_token'))
            ->description(trans('svr-core-lang::svr.users_token.create'))
            ->body($this->form());
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
            ->title(trans('svr-core-lang::svr.users_token.users_token'))
            ->description(trans('svr-core-lang::svr.users_token.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemUsersToken = $this->systemUsersToken;
        $grid = new Grid($systemUsersToken);
        $grid->model()->orderBy('token_id', 'asc');
        $grid->column('token_id', trans('svr-core-lang::svr.users_token.token_id'))
            ->help(__('token_id'))
            ->sortable();
        $grid->column('user_id', trans('svr-core-lang::svr.users_token.user_id'))
            ->link(function ($value){
                    return '/admin/svr_users/'.$value['user_id'];
                }, '_blank')
            ->help(__('user_id'))
            ->sortable();
        $grid->column('participation_id', trans('svr-core-lang::svr.users_token.participation_id'))
            ->help(__('participation_id'))
            ->sortable();
        $grid->column('token_value', trans('svr-core-lang::svr.users_token.token_value'))
            ->help(__('token_value'))
            ->sortable();
        $grid->column('token_client_ip', trans('svr-core-lang::svr.users_token.token_client_ip'))
            ->help(__('token_client_ip'))
            ->sortable();
        $grid->column('token_client_agent', trans('svr-core-lang::svr.users_token.token_client_agent'))
            ->help(__('token_client_agent'))
            ->sortable();
        $grid->column('browser_name', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('browser_name'))
            ->sortable();
        $grid->column('browser_version', trans('svr-core-lang::svr.users_token.browser_version'))
            ->help(__('browser_version'))
            ->sortable();
        $grid->column('platform_name', trans('svr-core-lang::svr.users_token.platform_name'))
            ->help(__('platform_name'))
            ->sortable();
        $grid->column('platform_version', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('platform_version'))
            ->sortable();
        $grid->column('device_type', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('device_type'))
            ->sortable();
        $grid->column('token_last_login', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('token_last_login'))
            ->sortable();
        $grid->column('token_last_action', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('token_last_action'))
            ->sortable();
        $grid->column('token_status', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('token_status'))
            ->sortable();
        $grid->column('created_at', trans('svr-core-lang::svr.users_token.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemUsersToken) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemUsersToken->getDateFormat());
            })->sortable();
        $grid->column('updated_at', trans('svr-core-lang::svr.users_token.updated_at'))
            ->help(__('updated_at'))
            ->display(function ($value) use ($systemUsersToken) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemUsersToken->getDateFormat());
            })->sortable();
        // включение фильтров
        $grid->filter(function (Grid\Filter $filter) {
            // Идентификатор пользователя
            $filter->equal('user_id', trans('svr-core-lang::svr.users_token.user_id'))
                ->select($this->systemUsersToken->all()->pluck('user_id', 'user_id'));
            // Идентификатор типа привязки
            $filter->equal('participation_id', trans('svr-core-lang::svr.users_token.participation_id'))
                ->select($this->systemUsersToken->all()->pluck('participation_id', 'participation_id'));
            // IP адрес пользователя
            $filter->equal('token_client_ip', trans('svr-core-lang::svr.users_token.token_client_ip'))
                ->select($this->systemUsersToken->all()->pluck('token_client_ip', 'token_client_ip'));
            // Тип устройства
            $filter->equal('device_type', trans('svr-core-lang::svr.users_token.device_type'))
                ->select($this->systemUsersToken->all()->pluck('device_type', 'device_type'));
            // Статус токена
            $filter->equal('token_status', trans('svr-core-lang::svr.users_token.token_status'))
                ->select($this->systemUsersToken->all()->pluck('token_status', 'token_status'));
        });
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
        $show = new Show($this->systemUsersToken->findOrFail($id));
        $data = $this->systemUsersToken->find($id)->toArray();

        $show->field('token_id', trans('svr-core-lang::svr.users_token.token_id'));
        $show->field('user_id', trans('svr-core-lang::svr.users_token.user_id'))
            ->link('/admin/svr_users/'.$data['user_id'], '_blank');
        $show->field('participation_id', trans('svr-core-lang::svr.users_token.participation_id'));
        $show->field('token_value', trans('svr-core-lang::svr.users_token.token_value'));
        $show->field('token_client_ip', trans('svr-core-lang::svr.users_token.token_client_ip'));
        $show->field('token_client_agent', trans('svr-core-lang::svr.users_token.token_client_agent'));
        $show->field('browser_name', trans('svr-core-lang::svr.users_token.browser_name'));
        $show->field('browser_version', trans('svr-core-lang::svr.users_token.browser_version'));
        $show->field('platform_name', trans('svr-core-lang::svr.users_token.platform_name'));
        $show->field('platform_version', trans('svr-core-lang::svr.users_token.platform_version'));
        $show->field('device_type', trans('svr-core-lang::svr.users_token.device_type'));
        $show->field('token_last_login', trans('svr-core-lang::svr.users_token.token_last_login'));
        $show->field('token_last_action', trans('svr-core-lang::svr.users_token.token_last_action'));
        $show->field('token_status', trans('svr-core-lang::svr.users_token.token_status'));
        $show->field('created_at', trans('svr-core-lang::svr.users_token.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.users_token.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @return Form
     */
    protected function form(): Form
    {
        $model = $this->systemUsersToken;
        $usersModel = new SystemUsers();
        $usersPrimaryKey = $usersModel->getPrimaryKey();
        $form = new Form($this->systemUsersToken);

        // 	Инкремент
        $form->display('token_id', trans('svr-core-lang::svr.users_token.token_id'))
            -> help(__('token_id'));

        // Инкремент
        $form->hidden('token_id', trans('svr-core-lang::svr.users_token.token_id'))
            ->help(__('token_id'));
        // Идентификатор пользователя
        $form->select('user_id', trans('svr-core-lang::svr.users_token.user_id'))
            ->required()
            ->options(function() use ($usersPrimaryKey){
                return SystemUsers::All([$usersPrimaryKey, $usersPrimaryKey])->pluck($usersPrimaryKey, $usersPrimaryKey);
            })
            ->help(__('user_id'));
        // Идентификатор типа привязки
        $form->select('participation_id', trans('svr-core-lang::svr.users_token.participation_id'))
            ->options(function() use ($usersPrimaryKey){
                return SystemUsersToken::All(['participation_id', 'participation_id'])->pluck('participation_id', 'participation_id');
            })
            ->help(__('participation_id'));
        // Значение токена
        $form->text('token_value', trans('svr-core-lang::svr.users_token.token_value'))
            ->required()
            ->help(__('token_value'));
        // IP адрес пользователя
        $form->ip('token_client_ip', trans('svr-core-lang::svr.users_token.token_client_ip'))
            ->required()
            ->help(__('token_client_ip'));
        // Агент пользователя
        $form->text('token_client_agent', trans('svr-core-lang::svr.users_token.token_client_agent'))
            ->required()
            ->help(__('token_client_agent'));
        // Название браузера
        $form->text('browser_name', trans('svr-core-lang::svr.users_token.browser_name'))
            ->help(__('browser_name'));
        // Версия браузера
        $form->text('browser_version', trans('svr-core-lang::svr.users_token.browser_version'))
            ->help(__('browser_version'));
        // Имя платформы
        $form->text('platform_name', trans('svr-core-lang::svr.users_token.platform_name'))
            ->help(__('platform_name'));
        // Версия платформы
        $form->text('platform_version', trans('svr-core-lang::svr.users_token.platform_version'))
            ->help(__('platform_version'));
        // Тип устроиства
        $form->text('device_type', trans('svr-core-lang::svr.users_token.'))
            ->default('desktop')
            ->required()
            ->help(__('device_type'));
        // Таймстамп последнего входа
        $form->number('token_last_login', trans('svr-core-lang::svr.users_token.token_last_login'))
            ->required()
            ->help(__('token_last_login'));
        // Таймстамп последнего действия
        $form->number('token_last_action', trans('svr-core-lang::svr.users_token.token_last_action'))
            ->required()
            ->help(__('token_last_action'));
        // Статус токена
        $form->select('token_status', trans('svr-core-lang::svr.users_token.token_status'))
            ->required()
            ->options(SystemStatusEnum::get_option_list())
            ->default('enabled')->rules('required')
            ->help(__('token_status'));
        // Дата создания записи
        $form->datetime('created_at', trans('svr-core-lang::svr.users_token.created_at'))
            ->disable()
            ->help(__('created_at'));
        // Дата удаления записи
        $form->datetime('updated_at', trans('svr-core-lang::svr.users_token.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        $form->saving(function (Form $form) use ($model)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $model->settingCreate(request());
            }
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                $model->settingUpdate(request());
            }
        });

        return $form;
    }
}
