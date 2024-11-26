<?php

namespace Svr\Core\Traits;

trait GetTableName
{
    /**
     * Возвращает название таблицы.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return with(new static)->getTable();
    }

}
