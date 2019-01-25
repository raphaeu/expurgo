<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 25/01/19
 * Time: 18:57
 */
use \raphaeu\Expurgo;
use \raphaeu\Colorize;
use \raphaeu\ParseFile;


echo(Colorize::magenta(true).Colorize::bold());
echo(Colorize::white().str_pad("Start: ".date("Y-m-d H:i:s"), 100, ' ', STR_PAD_BOTH));
echo(Colorize::clear().PHP_EOL);

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

echo(Colorize::magenta(true).Colorize::bold());
echo(Colorize::white().str_pad("End: ".date('%Y-%m-%d %H:%i:%s'), 100, ' ', STR_PAD_BOTH));
echo(Colorize::clear().PHP_EOL);


