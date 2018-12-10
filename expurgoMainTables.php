#!/usr/bin/php

<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 12/11/18
 * Time: 14:45
 */

set_time_limit(0);

require 'vendor/autoload.php';

use \raphaeu\Expurgo;
use \raphaeu\Table;
use \raphaeu\Database;
use \raphaeu\ParseFile;
use \raphaeu\Colorize;

ParseFile::setFile('database.conf');



$date = date('Y-m-d', strtotime("-1 days"));
$databaseFrom = new Database(ParseFile::get('from', 'host'), ParseFile::get('from', 'user'), ParseFile::get('from', 'password'), ParseFile::get('from', 'db'));
$databaseTo = new Database(ParseFile::get('to', 'host'), ParseFile::get('to', 'user'), ParseFile::get('to', 'password'), ParseFile::get('to', 'db'));


$tables[] = $table = new Table('integration_results', 'created_at');
$tables[] = $table = new Table('interactions', 'start');
$tables[] = $table = new Table('calls', 'start');
$tables[] = $table = new Table('steps', 'start');



echo(Colorize::yellow().Colorize::bold());
echo(str_pad(' _______  __   __  _______  __   __  ______    _______  _______ ', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|       ||  |_|  ||       ||  | |  ||    _ |  |       ||       |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|    ___||       ||    _  ||  | |  ||   | ||  |    ___||   _   |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|   |___ |       ||   |_| ||  |_|  ||   |_||_ |   | __ |  | |  |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|    ___| |     | |    ___||       ||    __  ||   ||  ||  |_|  |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|   |___ |   _   ||   |    |       ||   |  | ||   |_| ||       |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|_______||__| |__||___|    |_______||___|  |_||_______||_______|', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(Colorize::clear().PHP_EOL);


foreach ($tables as $table)
{
    try
    {

        $expurgo = new Expurgo($table, $databaseFrom, $databaseTo);

        $expurgo->setFileDump(ParseFile::get('files', 'dump')."/{$date}_{$table->name}.sql");
        $expurgo->setDateTimeStart("{$date} 00:00:00");
        $expurgo->setDateTimeEnd("{$date} 23:59:59");

        $expurgo->go();
    }catch (Exception $e){
        file_put_contents(ParseFile::get('files', 'log'),PHP_EOL."[".date('Y-m-d H:i:s'). "][{$table->name}] ". $e->getMessage() , FILE_APPEND);
        echo ("Erro:" .$e->getMessage());
    }

}




