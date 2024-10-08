<?php

namespace Svr\Core\Extensions\Show;

use OpenAdminCore\Admin\Show\AbstractField;

/**
 * XxHelp класс добавляет вывод блока help(подсказка) к полям формы Show (details)
 * @package Svr\Core\Extensions\Show
 */
class XxHelp extends AbstractField
{
    public $border = true;
    public $escape = false;


    /**
     * Set help block
     *
     * @param string $msg
     * @param string $icon
     * @return string
     */
    public function render($msg = 'hepl message', $icon= 'icon-info-circle'): string
    {
         $this->border = true;
         $this->escape = false;

        return $this->value . '<br> <span class="help-block">
                    <i class="'. $icon.'"></i> &nbsp;'. $msg.'</span>';

    }
}
