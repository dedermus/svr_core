<?php

namespace Svr\Core\Controlles\System;

use Svr\Core\Models\System\SystemRoles;
use Svr\Core\Models\System\SystemModulesActions;
use Svr\Core\Models\System\SystemRolesRights;
use Svr\Core\Enums\SystemStatusDeleteEnum;
use Svr\Core\Enums\SystemStatusEnum;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;

class RolesController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemRoles
     */
    private SystemRoles $systemRoles;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemRoles = new SystemRoles();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'Role';

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
            ->header(trans('svr-core-lang::svr.role.title'))
            ->description(trans('svr-core-lang::svr.role.description'))
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
            ->title(trans('svr-core-lang::svr.role.title'))
            ->description(trans('svr-core-lang::svr.role.show'))
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
            ->title(trans('svr-core-lang::svr.role.title'))
            ->description(trans('svr-core-lang::svr.role.create'))
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
            ->title(trans('svr-core-lang::svr.role.title'))
            ->description(trans('svr-core-lang::svr.role.edit'))
            ->row($this->form($id)->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = Admin::grid(SystemRoles::class, function(Grid $grid) {
            $systemRoles = $this->systemRoles;
            $grid->model()->orderBy('role_id', 'asc');

            // ID роли
            $grid->column('role_id', trans('svr-core-lang::svr.role.role_id'))
                ->help(__('role_id'))
                ->sortable();

            // Длинное название роли
            $grid->column('role_name_long', trans('svr-core-lang::svr.role.role_name_long'))
                ->help(__('role_name_long'))
                ->sortable();

            // Короткое название роли
            $grid->column('role_name_short', trans('svr-core-lang::svr.role.role_name_short'))
                ->help(__('role_name_short'))
                ->sortable();

            // Слаг для роли (уникальный идентификатор)
            $grid->column('role_slug', trans('svr-core-lang::svr.role.role_slug'))
                ->help(__('role_slug'))
                ->sortable();

            // Права роли
            $grid->column('permissions', trans('svr-core-lang::svr.role.role_rights_list'))
                ->pluck('right_slug')
                ->label();

            // Статус роли
            $grid->column('role_status', trans('svr-core-lang::svr.role.role_status'))
                ->help(__('role_status'))
                ->sortable();

            // Флаг удаления роли
            $grid->column('role_status_delete', trans('svr-core-lang::svr.role.role_status_delete'))
                ->help(__('role_status_delete'))
                ->sortable();

            // Дата создания
            $grid->column('created_at', trans('svr-core-lang::svr.role.created_at'))
                ->help(__('created_at'))
                ->display(function ($value) use ($systemRoles) {
                    return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                        $systemRoles->getDateFormat()
                    );
                })->sortable();

            // Дата обновления
            $grid->column('updated_at', trans('svr-core-lang::svr.role.updated_at'))
                ->display(function ($value) use ($systemRoles) {
                    return Carbon::parse($value)->timezone(config('app.timezone'))->format(
                        $systemRoles->getDateFormat()
                    );
                })->help(__('updated_at'))
                ->sortable();
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
        $show = new Show($this->systemRoles::findOrFail($id));

        $show->field('role_id', trans('svr-core-lang::svr.role.role_id'));
        $show->field('role_name_long', trans('svr-core-lang::svr.role.role_name_long'));
        $show->field('role_name_short', trans('svr-core-lang::svr.role.role_name_short'));
        $show->field('role_slug', trans('svr-core-lang::svr.role.role_slug'));
        $show->field('role_status', trans('svr-core-lang::svr.role.role_status'));
        $show->field('role_status_delete', trans('svr-core-lang::svr.role.role_status_delete'));
        $show->field('role_status_delete', trans('svr-core-lang::svr.role.role_status_delete'));
        $show->field('role_rights_list', trans('svr-core-lang::svr.role.role_rights_list'))->as(function () use ($id) {
            return SystemModulesActions::All(['right_id', 'right_slug'])
                ->whereIn('right_id', systemRolesRights::roleRightsGet($id))
                ->pluck('right_slug');
        })->label();
        $show->field('created_at', trans('svr-core-lang::svr.role.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.role.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @param false $id
     *
     * @return Form
     */
    protected function form($id = false): Form
    {
        $model = $this->systemRoles;

        $form			= new Form($this->systemRoles);

        // 	Инкремент
        $form->display('role_id', trans('svr-core-lang::svr.role.role_id'))
            -> help(__('role_id'));

        // 	Инкремент
		$form->hidden('role_id', trans('svr-core-lang::svr.role.role_id'))
            -> help(__('role_id'));

        // Длинное название
        $form->text('role_name_long', trans('svr-core-lang::svr.role.role_name_long'))
            ->help(__('role_name_long'))
            ->required();

        // Короткое название роли
        $form->text('role_name_short', trans('svr-core-lang::svr.role.role_name_short'))
            ->help('role_name_short')
            ->required();

        // Слаг для роли (уникальный идентификатор)
        $form->text('role_slug', trans('svr-core-lang::svr.role.role_slug'))
            ->help(__('role_slug'))
            ->required();

        // Статус роли
        $form->select('role_status', trans('svr-core-lang::svr.role.role_status'))
            ->help(__('role_status'))
            ->required()
            ->options(SystemStatusEnum::get_option_list())
            ->default('enabled');

        // Флаг удаления роли
        $form->select('role_status_delete', trans('svr-core-lang::svr.role.role_status_delete'))
            ->help(__('role_status_delete'))
            ->required()
            ->options(SystemStatusDeleteEnum::get_option_list())
            ->default('active')
            ->readonly();

        // Права роли
        $form->multipleSelect('role_rights_list', trans('svr-core-lang::svr.role.role_rights_list'))
            ->help(__('role_rights_list'))
			->value(systemRolesRights::roleRightsGet($id))
            ->options(function(){
                return SystemModulesActions::All(['right_name', 'right_id'])->pluck('right_name', 'right_id');
            });

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.role.created_at'))
            ->help(__('created_at'))
            ->disable();

        // Дата обновления
        $form->datetime('updated_at', trans('svr-core-lang::svr.role.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        // обработка формы
        $form->saving(function (Form $form) use ($model)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $model->roleCreate(request());
            }
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                $model->roleUpdate(request());
            }
            return redirect(admin_url('svr_roles'));
        });

        return $form;
    }
}
