#!/usr/bin/php

<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 12/11/18
 * Time: 14:45
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

require 'vendor/autoload.php';

use \raphaeu\Expurgo;
use \raphaeu\Table;
use \raphaeu\Database;
use \raphaeu\ParseFile;
use \raphaeu\Colorize;

ParseFile::setFile(__DIR__.'/database.conf');

// BANDO DE DADOS



$i++;// D-7
$periods[$i]['name'] = '7 dias';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-7 days")); // 18-01-2019 25-01-2019
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("+0 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('history', 'host'), ParseFile::get('history', 'user'), ParseFile::get('history', 'password'), ParseFile::get('history', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d30', 'host'), ParseFile::get('d30', 'user'), ParseFile::get('d30', 'password'), ParseFile::get('d30', 'db'));


// TABELAS


$tables[] = $table = new Table('integration_results', 'created_at');
$tables[] = $table = new Table('interactions', 'start');
$tables[] = $table = new Table('calls', 'start');
$tables[] = $table = new Table('steps', 'start');

// INICIO

echo(Colorize::yellow().Colorize::bold());
echo(str_pad(' _______  __   __  _______  __   __  ______    _______  _______ ', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|       ||  |_|  ||       ||  | |  ||    _ |  |       ||       |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|    ___||       ||    _  ||  | |  ||   | ||  |    ___||   _   |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|   |___ |       ||   |_| ||  |_|  ||   |_||_ |   | __ |  | |  |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|    ___| |     | |    ___||       ||    __  ||   ||  ||  |_|  |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|   |___ |   _   ||   |    |       ||   |  | ||   |_| ||       |', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(str_pad('|_______||__| |__||___|    |_______||___|  |_||_______||_______|', 100, ' ', STR_PAD_BOTH).PHP_EOL);
echo(Colorize::clear().PHP_EOL);



foreach($periods as $period) {

    echo(PHP_EOL);
    echo(Colorize::blue(true). str_pad(" ", 100, ' ', STR_PAD_BOTH).Colorize::clear().PHP_EOL);
    echo(Colorize::blue(true).Colorize::bold(). str_pad($period['name'], 101, ' ', STR_PAD_BOTH).Colorize::clear().PHP_EOL);
    echo(Colorize::blue(true).str_pad("Periodo " . $period['date']['from'] . " até " .  $period['date']['to'], 101, ' ', STR_PAD_BOTH).Colorize::clear().PHP_EOL);
    echo(Colorize::blue(true).str_pad(" ", 100, ' ', STR_PAD_BOTH).Colorize::clear().PHP_EOL);
    echo(PHP_EOL);

    foreach ($tables as $table) {
        try {

            $expurgo = new Expurgo($table, $period['database']['from'], $period['database']['to']);
            $expurgo->setFileDump(ParseFile::get('files', 'dump') . "/".str_replace(['-', ' ', ':'], '', $period['date']['from'])."_to_".str_replace(['-', ' ', ':'], '',$period['date']['to'])."_{$table->name}.sql");
            $expurgo->setDateTimeStart($period['date']['from']);
            $expurgo->setDateTimeEnd($period['date']['to']);
            $expurgo->go();
        } catch (Exception $e) {
            file_put_contents(ParseFile::get('files', 'log'), PHP_EOL . "[" . date('Y-m-d H:i:s') . "][{$table->name}] " . $e->getMessage(), FILE_APPEND);
            echo("Erro:" . $e->getMessage());
        }

    }

}


