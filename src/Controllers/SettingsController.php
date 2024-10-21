<?php

namespace Svr\Core\Controllers;

use Svr\Core\Models\SystemSetting;
use Illuminate\Support\Carbon;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Show;

/**
 * Контроллер Settings
 */
class SettingsController extends AdminController
{
    /**
     * Экземпляр класса модели
     * @var SystemSetting
     */
    public SystemSetting $systemSetting;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->systemSetting = new SystemSetting();
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
            ->header(trans('svr-core-lang::svr.setting.title'))
            ->description(trans('svr-core-lang::svr.setting.description'))
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
            ->title(trans('svr-core-lang::svr.setting.title'))
            ->description(trans('svr-core-lang::svr.setting.show'))
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
            ->title(trans('svr-core-lang::svr.setting.title'))
            ->description(trans('svr-core-lang::svr.setting.create'))
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
            ->title(trans('svr-core-lang::svr.setting.title'))
            ->description(trans('svr-core-lang::svr.setting.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Интерфейс сетки (таблицы).
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $systemSetting = $this->systemSetting;
        $grid = new Grid($systemSetting);
        $grid->model()->orderBy('setting_id', 'asc');
        $grid->column('setting_id', trans('svr-core-lang::svr.setting.setting_id'))
            ->help(__('setting_id'))
            ->sortable();

        //  признак принадлежности записи
        $grid->column('owner_type', trans('svr-core-lang::svr.setting.owner_type'))
            ->help(__('owner_type'))
            ->sortable();

        // идентификатор принадлежности записи
        $grid->column('owner_id', trans('svr-core-lang::svr.setting.owner_id'))
            ->help(__('owner_id'))
            ->sortable();

        // код записи
        $grid->column('setting_code', trans('svr-core-lang::svr.setting.setting_code'))
            ->help(__('setting_code'))
            ->sortable();

        // значение
        $grid->column('setting_value', trans('svr-core-lang::svr.setting.setting_value'))
            ->help(__('setting_value'))
            ->sortable();

        // альтернативное значение
        $grid->column('setting_value_alt', trans('svr-core-lang::svr.setting.setting_value_alt'))
            ->help(__('setting_value_alt'))
            ->sortable();

        // Дата создания
        $grid->column('created_at', trans('svr-core-lang::svr.created_at'))
            ->help(__('created_at'))
            ->display(function ($value) use ($systemSetting) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemSetting->getDateFormat());
            })->sortable();

        // Дата обновления
        $grid->column('updated_at', trans('svr-core-lang::svr.updated_at'))
            ->help(__('updated_at'))
            ->display(function ($value) use ($systemSetting) {
                return Carbon::parse($value)->timezone(config('app.timezone'))->format($systemSetting->getDateFormat());
            })->sortable();

        // включение фильтров
        $grid->filter(function (Grid\Filter $filter) {
            // признак принадлежности записи
            $filter->equal('owner_type', trans('svr-core-lang::svr.setting.owner_type'))
                ->select($this->systemSetting->all()->pluck('owner_type', 'owner_type'));
            // идентификатор принадлежности записи
            $filter->equal('owner_id', trans('svr-core-lang::svr.setting.owner_id'))
                ->select($this->systemSetting->all()->pluck('owner_id', 'owner_id'));
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
        $show = new Show($this->systemSetting->findOrFail($id));

        $show->field('setting_id', trans('svr-core-lang::svr.setting.setting_id'));
        $show->field('owner_type', trans('svr-core-lang::svr.setting.owner_type'));
        $show->field('owner_id', trans('svr-core-lang::svr.setting.owner_id'));
        $show->field('setting_code', trans('svr-core-lang::svr.setting.setting_code'));
        $show->field('setting_value', trans('svr-core-lang::svr.setting.setting_value'));
        $show->field('setting_value_alt', trans('svr-core-lang::svr.setting.setting_value_alt'));
        $show->field('created_at', trans('svr-core-lang::svr.setting.created_at'));
        $show->field('updated_at', trans('svr-core-lang::svr.setting.updated_at'));

        return $show;
    }

    /**
     * Форма для создания/редактирования
     *
     * @return Form
     */
    protected function form(): Form
    {
        $model = $this->systemSetting;

        $form = new Form($this->systemSetting);

        // 	Инкремент
        $form->display('setting_id', trans('svr-core-lang::svr.setting.setting_id'))
            -> help(__('setting_id'));

        $form->hidden('setting_id', trans('svr-core-lang::svr.setting.setting_id'))
            ->help(__('setting_id'));

        // признак принадлежности записи
        $form->text('owner_type', trans('svr-core-lang::svr.setting.owner_type'))
            ->required()
            ->help(__('owner_type'));

        // идентификатор принадлежности записи
        $form->number('owner_id', trans('svr-core-lang::svr.setting.owner_id'))
            ->required()
            ->help(__('owner_id'));

        // код записи
        $form->text('setting_code', trans('svr-core-lang::svr.setting.setting_code'))
            ->required()
            ->help(__('setting_code'));

        // значение
        $form->text('setting_value', trans('svr-core-lang::svr.setting.setting_value'))
            ->required()
            ->help(__('setting_value'));

        // альтернативное значение
        $form->text('setting_value_alt', trans('svr-core-lang::svr.setting.setting_value_alt'))
            ->help(__('setting_value_alt'));

        // Дата создания
        $form->datetime('created_at', trans('svr-core-lang::svr.created_at'))
            ->disable()
            ->help(__('created_at'));

        // Дата обновления
        $form->datetime('updated_at', trans('svr-core-lang::svr.updated_at'))
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
