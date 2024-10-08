<?php

namespace Svr\Core\Extensions\Column;

use OpenAdminCore\Admin\Grid\Displayers\AbstractDisplayer;

/**
 * XxDateTimeFormatter класс для кастомного поля формата даты в солонке grid
 * @package Svr\Core\Extensions\Column
 */
class XxDateTimeFormatter extends AbstractDisplayer
{
    public function display($formtter = 'Y-m-d / H:i')
    {
        return $this->value ? date($formtter, strtotime($this->value)) : '';
    }
}
