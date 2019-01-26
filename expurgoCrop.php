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
$onlyDump = true;
ParseFile::setFile(__DIR__.'/database.conf');
$dirDump = ParseFile::get('files', 'dump');

// BANDO DE DADOS
$i++;// D-7
$periods[$i]['name'] = '7 dias';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-7 days"));
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("+0 days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('history', 'host'), ParseFile::get('history', 'user'), ParseFile::get('history', 'password'), ParseFile::get('history', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d7', 'host'), ParseFile::get('d7', 'user'), ParseFile::get('d7', 'password'), ParseFile::get('d7', 'db'));


// TABELAS
$tables[] = $table = new Table('integration_results', 'created_at');
$tables[] = $table = new Table('interactions', 'start');
$tables[] = $table = new Table('calls', 'start');
$tables[] = $table = new Table('steps', 'start');

// RUN
include(__DIR__.'/expurgoCore.php');
