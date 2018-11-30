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
    public $id = 'id';

    function __construct($name, $fieldDateTime)
    {
        $this->name = $name;
        $this->fieldDateTime = $fieldDateTime;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }


}
