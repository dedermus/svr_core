<?php

namespace Svr\Core\Controllers;

use Svr\Core\Models\SystemModules;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;

/**
 * Контроллер Modules
 */
class ModulesController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemModules
     */
    private SystemModules $systemModules;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemModules = new SystemModules();
    }

    /**
     * Название текущего ресурса.
     *
     * @var string
     */
    protected $title = 'Module';

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
            ->header(trans('svr-core-lang::svr.module.title'))
            ->description(trans('svr-core-lang::svr.module.description'))
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
            ->title(trans('svr-core-lang::svr.module.title'))
            ->description(trans('svr-core-lang::svr.module.show'))
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
            ->title(trans('svr-core-lang::svr.module.title'))
            ->description(trans('svr-core-lang::svr.module.create'))
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
            ->title(trans('svr-core-lang::svr.module.title'))
            ->description(trans('svr-core-lang::svr.module.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemModules = $this->systemModules;
        $grid = new Grid($systemModules);
        $grid->model()->orderBy('module_id', 'asc');
        $grid->column('module_id', trans('svr-core-lang::svr.module.module_id'))
            ->help(__('module_id'))
            ->sortable();
        $grid->column('module_name', trans('svr-core-lang::svr.module.module_name'))
            ->help(__('module_name'))
            ->sortable();
        $grid->column('module_description', trans('svr-core-lang::svr.module.module_description'))
            ->help(__('module_description'))
            ->sortable();
        $grid->column('module_class_name', trans('svr-core-lang::svr.module.module_class_name'))
            ->help(__('module_class_name'))
            ->sortable();
        $grid->column('module_slug', trans('svr-core-lang::svr.module.module_slug'))
            ->help(__('module_slug'))
            ->sortable();
        $grid->column('created_at', trans('svr-core-lang::svr.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemModules) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemModules->getDateFormat());
            })->sortable();
        $grid->column('updated_at', trans('svr-core-lang::svr.updated_at'))
            ->help(__('updated_at'))
            ->display(function ($value) use ($systemModules) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemModules->getDateFormat());
            })->sortable();
        // включение фильтров
        $grid->filter(function (Grid\Filter $filter) {
            // Имя класса модуля
            $filter->equal('module_class_name', trans('svr-core-lang::svr.module.module_class_name'))
                ->select($this->systemModules->all()->pluck('module_class_name', 'module_class_name'));
            // Слаг модуля (уникальный)
            $filter->equal('module_slug', trans('svr-core-lang::svr.module.module_slug'))
                ->select($this->systemModules->all()->pluck('module_slug', 'module_slug'));
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
        $show = new Show($this->systemModules->findOrFail($id));

        $show->field('module_id', trans('svr-core-lang::svr.module.module_id'));
        $show->field('module_name', trans('svr-core-lang::svr.module.module_name'));
        $show->field('module_description', trans('svr-core-lang::svr.module.module_description'));
        $show->field('module_class_name', trans('svr-core-lang::svr.module.module_class_name'));
        $show->field('module_slug', trans('svr-core-lang::svr.module.module_slug'));
        $show->field('created_at', trans('svr-core-lang::svr.module.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.module.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @return Form
     */
    protected function form(): Form
    {
        $model = $this->systemModules;

        $form = new Form($this->systemModules);

        // 	Инкремент
        $form->display('module_id', trans('svr-core-lang::svr.module.module_id'))
            ->help(__('module_id'));

        $form->hidden('module_id', trans('svr-core-lang::svr.module.module_id'))
            ->help(__('module_id'));

        // Название модуля
        $form->text('module_name', trans('svr-core-lang::svr.module.module_name'))
            ->help(__('module_name'));

        // Описание модуля
        $form->text('module_description', trans('svr-core-lang::svr.module.module_description'))
            ->help(__('module_description'));

        // Имя класса модуля
        $form->text('module_class_name', trans('svr-core-lang::svr.module.module_class_name'))
            ->help(__('module_class_name'));

        // Слаг модуля
        $form->text('module_slug', trans('svr-core-lang::svr.module.module_slug'))
            ->help(__('module_slug'));

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.created_at'))
            ->disable()
            ->help(__('created_at'));

        // Дата обновления
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
