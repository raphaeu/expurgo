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

use \raphaeu\Table;
use \raphaeu\Database;
use \raphaeu\ParseFile;

ParseFile::setFile(__DIR__.'/database.conf');

// BANDO DE DADOS
$onlyDump = false;
$interval_days = 10;
$dirDump = ParseFile::get('files', 'dump');

$i=0; // D-1
$periods[$i]['name'] = '1 dia';
$periods[$i]['date']['deadline'] = date('Y-m-d', strtotime("-1 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d1', 'host'), ParseFile::get('d1', 'user'), ParseFile::get('d1', 'password'), ParseFile::get('d1', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d7', 'host'), ParseFile::get('d7', 'user'), ParseFile::get('d7', 'password'), ParseFile::get('d7', 'db'));

$i=1; // D-7
$periods[$i]['name'] = '7 dia';
$periods[$i]['date']['deadline'] = date('Y-m-d', strtotime("-8 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d7', 'host'), ParseFile::get('d7', 'user'), ParseFile::get('d7', 'password'), ParseFile::get('d7', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d30', 'host'), ParseFile::get('d30', 'user'), ParseFile::get('d30', 'password'), ParseFile::get('d30', 'db'));

$i=2; // D-30
$periods[$i]['name'] = '30 dia';
$periods[$i]['date']['deadline'] = date('Y-m-d', strtotime("-39 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d30', 'host'), ParseFile::get('d30', 'user'), ParseFile::get('d30', 'password'), ParseFile::get('d30', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d90', 'host'), ParseFile::get('d90', 'user'), ParseFile::get('d90', 'password'), ParseFile::get('d90', 'db'));

$i=3; // D-90
$periods[$i]['name'] = '90 dia';
$periods[$i]['date']['deadline'] = date('Y-m-d', strtotime("-139 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d90', 'host'), ParseFile::get('d90', 'user'), ParseFile::get('d90', 'password'), ParseFile::get('d90', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('history', 'host'), ParseFile::get('history', 'user'), ParseFile::get('history', 'password'), ParseFile::get('history', 'db'));



// TABELAS
$table = new Table($argv[1], $argv[2], @$argv[3]);

// RUN
include(__DIR__.'/expurgoCore.php');