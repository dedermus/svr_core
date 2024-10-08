<?php

namespace Svr\Core\Extensions\Show;

use OpenAdminCore\Admin\Show\AbstractField;

/**
 *
 * XxDateTimeFormatter класс для кастомного поля формата даты в форме Show (details)
 * @package Svr\Core\Extensions\Show
 */
class XxDateTimeFormatter extends AbstractField
{
    public function render($arg = 'Y-m-d / H:i')
    {
        $formtter = $arg;
        return $this->value ? date($formtter, strtotime($this->value)) : '';
    }
}
