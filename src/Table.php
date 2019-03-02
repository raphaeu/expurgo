<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 29/11/18
 * Time: 16:17
 */

namespace raphaeu;


class Table
{
    public $name;
    public $fieldDateTime;
    public $id;

    function __construct($name, $fieldDateTime, $id)
    {
        $this->name = $name;
        $this->fieldDateTime = $fieldDateTime;
        $this->id  = $id?$id:'id';
    }
}
