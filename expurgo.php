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
$interval_days = 1;
$dirDump = ParseFile::get('files', 'dump');

$i=0; // D-1
$periods[$i]['name'] = '1 dia';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-".(1 + $interval_days)." days"));
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("-".( 1 )." days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d1', 'host'), ParseFile::get('d1', 'user'), ParseFile::get('d1', 'password'), ParseFile::get('d1', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d7', 'host'), ParseFile::get('d7', 'user'), ParseFile::get('d7', 'password'), ParseFile::get('d7', 'db'));

$i++;// D-7
$periods[$i]['name'] = '7 dias';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-".(7 + $interval_days)." days"));
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("-".( 7 )." days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d7', 'host'), ParseFile::get('d7', 'user'), ParseFile::get('d7', 'password'), ParseFile::get('d7', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d30', 'host'), ParseFile::get('d30', 'user'), ParseFile::get('d30', 'password'), ParseFile::get('d30', 'db'));

$i++;// D-30
$periods[$i]['name'] = '30 dias';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-".(30 + $interval_days)." days"));
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("-".( 30 )." days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d30', 'host'), ParseFile::get('d30', 'user'), ParseFile::get('d30', 'password'), ParseFile::get('d30', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('d90', 'host'), ParseFile::get('d90', 'user'), ParseFile::get('d90', 'password'), ParseFile::get('d90', 'db'));

$i++;// D-90
$periods[$i]['name'] = '90 dias';
$periods[$i]['date']['from'] = date('Y-m-d 23:59:59', strtotime("-".(90 + $interval_days)." days"));
$periods[$i]['date']['to'] = date('Y-m-d 23:59:59', strtotime("-".( 90 )." days"));
$periods[$i]['database']['from'] = new Database(ParseFile::get('d90', 'host'), ParseFile::get('d90', 'user'), ParseFile::get('d90', 'password'), ParseFile::get('d90', 'db'));
$periods[$i]['database']['to'] = new Database(ParseFile::get('history', 'host'), ParseFile::get('history', 'user'), ParseFile::get('history', 'password'), ParseFile::get('history', 'db'));


// TABELAS


$tables[] = $table = new Table('integration_results', 'created_at');
$tables[] = $table = new Table('interactions', 'start');
$tables[] = $table = new Table('calls', 'start');
$tables[] = $table = new Table('steps', 'start');

// RUN
include(__DIR__.'/expurgoCore.php');