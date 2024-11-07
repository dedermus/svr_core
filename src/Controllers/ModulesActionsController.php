<?php

namespace Svr\Core\Controllers;

use Svr\Core\Models\SystemModules;
use Svr\Core\Models\SystemModulesActions;
use Svr\Core\Enums\SystemStatusEnum;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;

/**
 * Контроллер ModulesActions
 */
class ModulesActionsController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemModulesActions
     */
    private SystemModulesActions $systemModulesActions;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemModulesActions = new SystemModulesActions();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'ModuleAction';

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
            ->header(trans('svr-core-lang::svr.modules_actions.title'))
            ->description(trans('svr-core-lang::svr.modules_actions.description'))
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
            ->title(trans('svr-core-lang::svr.modules_actions.title'))
            ->description(trans('svr-core-lang::svr.modules_actions.show'))
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
            ->header(trans('svr-core-lang::svr.modules_actions.title'))
            ->description(trans('svr-core-lang::svr.modules_actions.create'))
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
            ->title(trans('svr-core-lang::svr.modules_actions.title'))
            ->description(trans('svr-core-lang::svr.modules_actions.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemModulesActions = $this->systemModulesActions;
        $grid = new Grid($this->systemModulesActions);
        $grid->model()->orderBy('right_id', 'asc');

        // ID права
        $grid->column('right_id', trans('svr-core-lang::svr.modules_actions.right_id'))
            ->help(__('right_id'))
            ->sortable();

        // Слаг модуля
        $grid->column('module_slug', trans('svr-core-lang::svr.modules_actions.module_slug'))
            ->help(__('module_slug'))
            ->sortable();

        // Экшен
        $grid->column('right_action', trans('svr-core-lang::svr.modules_actions.right_action'))
            ->help(__('right_action'))
            ->sortable();

        // Имя экшена
        $grid->column('right_name', trans('svr-core-lang::svr.modules_actions.right_name'))
            ->help(__('right_name'))
            ->sortable();

        // Слаг для экшена
        $grid->column('right_slug', trans('svr-core-lang::svr.modules_actions.right_slug'))
            ->help(__('right_slug'))
            ->sortable();

        // Тип контента ответа
        $grid->column('right_content_type', trans('svr-core-lang::svr.modules_actions.right_content_type'))
            ->help(__('right_content_type'));

        // Флаг записи логов
		$grid->column('right_log_write', trans('svr-core-lang::svr.modules_actions.right_log_write'))
            ->help(__('right_log_write'))
            ->sortable();

        // Дата создания
		$grid->column('created_at', trans('svr-core-lang::svr.modules_actions.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemModulesActions) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemModulesActions->getDateFormat());
            })->sortable();

        // Дата обновления
        $grid->column('updated_at', trans('svr-core-lang::svr.modules_actions.updated_at'))
            ->display(function ($value) use ($systemModulesActions) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemModulesActions->getDateFormat());
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
        $show = new Show($this->systemModulesActions::findOrFail($id));

        $show->field('right_id', trans('svr-core-lang::svr.modules_actions.right_id'));
        $show->field('module_slug', trans('svr-core-lang::svr.modules_actions.module_slug'));
        $show->field('right_action', trans('svr-core-lang::svr.modules_actions.right_action'));
        $show->field('right_name', trans('svr-core-lang::svr.modules_actions.right_name'));
        $show->field('right_slug', trans('svr-core-lang::svr.modules_actions.right_slug'));
        $show->field('right_content_type', trans('svr-core-lang::svr.modules_actions.right_content_type'));
        $show->field('right_log_write', trans('svr-core-lang::svr.modules_actions.right_log_write'));
		$show->field('created_at', trans('svr-core-lang::svr.modules_actions.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.modules_actions.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @return Form
     */
    protected function form(): Form
    {
        $model = $this->systemModulesActions;

        $form = new Form($this->systemModulesActions);

        // 	Инкремент
        $form->display('right_id', trans('svr-core-lang::svr.modules_actions.right_id'))
            -> help(__('right_id'));

        // 	Инкремент
        $form->hidden('right_id', trans('svr-core-lang::svr.modules_actions.right_id'))
            ->help(__('right_id'));

        // Слаг модуля
        $form->select('module_slug', trans('svr-core-lang::svr.modules_actions.module_slug'))
            ->required()
            ->options(function() {
                return SystemModules::All(['module_slug', 'module_slug'])->pluck('module_slug', 'module_slug');
            })
            ->help(__('module_slug'));

        // Экшен
		$form->text('right_action', trans('svr-core-lang::svr.modules_actions.right_action'))
            ->required()
            ->help(__('right_action'));

		// Имя экшена
        $form->text('right_name', trans('svr-core-lang::svr.modules_actions.right_name'))
            ->required()
            ->help(__('right_name'));

        // Слаг для экшена
		$form->text('right_slug', trans('svr-core-lang::svr.modules_actions.right_slug'))
            ->required()
            ->help(__('right_slug'));

        // Тип запроса
        $form->text('right_content_type', trans('svr-core-lang::svr.modules_actions.right_content_type'))
            ->required()
            ->default('json')
            ->help(__('right_content_type'));

        // Флаг записи логов
        $form->text('right_log_write', trans('svr-core-lang::svr.modules_actions.right_log_write'))
            ->required()
            //->options(SystemStatusEnum::get_option_list())
            ->default('disabled')
            //->help(__('right_log_write'))
        ;

        $form->datetime('created_at', trans('svr-core-lang::svr.created_at'))
            ->disable()
            ->help(__('created_at'));

        $form->datetime('updated_at', trans('svr-core-lang::svr.updated_at'))
            ->disable()
            ->help(__('updated_at'));

        // обработка формы
        $form->saving(function (Form $form) use ($model)
        {
            // создается текущая страница формы.
            if ($form->isCreating())
            {
                $model->moduleCreate(request());
            } else
            // обновляется текущая страница формы.
            if ($form->isEditing())
            {
                $model->moduleUpdate(request());
            }
        });

        return $form;
    }
}
