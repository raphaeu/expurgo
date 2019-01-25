<?php

require __DIR__.'/../vendor/autoload.php';

use \raphaeu\Database;
use \raphaeu\ParseFile;

ParseFile::setFile(__DIR__.'/../database.conf');


$databaseFrom = new Database(ParseFile::get('d1', 'host'), ParseFile::get('d1', 'user'), ParseFile::get('d1', 'password'), ParseFile::get('d1', 'db'));


for($day = 0; $day <= 90; $day++)
{
    $data = date('Y-m-d', strtotime("+{$day} days"));
    echo "Dia:{$data} ".PHP_EOL;
    for($x = 1; $x <= 5; $x++)
    {
        $valor = md5(time() * rand(0,1000));
        $databaseFrom->connect()->exec("INSERT INTO registro(data, valor) VALUES ('{$data}', '{$valor}')");
    }
}

