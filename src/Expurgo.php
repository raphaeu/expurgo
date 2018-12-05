<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 12/11/18
 * Time: 11:01
 */

namespace raphaeu;

use \SebastianBergmann\Timer\Timer;

class Expurgo
{
    private $table;
    private $dbFrom;
    private $dbTo;
    private $dateTimeStart;
    private $dateTimeEnd;
    private $fileDump;


    function __construct(Table $table, Database $dbFrom, Database $dbTo)
    {
        $this->table = $table;
        $this->dbFrom =$dbFrom;
        $this->dbTo =$dbTo;
        $this->validate();
    }

    public function go()
    {


        echo(Colorize::white(1).Colorize::bold().Colorize::black());
        echo(str_pad("{$this->table->name}", 100, ' ', STR_PAD_BOTH));
        echo(Colorize::clear().PHP_EOL.PHP_EOL);

        Timer::start();

        //verifica id do ultimo registro
        echo('[    ] Verificando ultimo ID');

        $registersFrom = $this->dbFrom->connect()->query("SELECT {$this->table->id} FROM {$this->table->name} where {$this->table->fieldDateTime} >= '{$this->dateTimeStart}' and {$this->table->fieldDateTime} <= '{$this->dateTimeEnd}'")->fetchAll();

        $firstIdRegister = $lastIdRegister = $firstIdRegister = $totalRegister  = 0;
        $ids = [];
        //montando array de ids a serem insertidos e id do ultimo registro
        foreach ($registersFrom as $register)
        {
            if ($firstIdRegister == 0) $firstIdRegister = $register[$this->table->id];
            if ($register[$this->table->id] > $lastIdRegister) $lastIdRegister = $register[$this->table->id];
            if ($register[$this->table->id] < $firstIdRegister) $firstIdRegister = $register[$this->table->id];
            $ids[] = $register[$this->table->id];
            $totalRegister++;
        }
        echo("(".Colorize::bold().$firstIdRegister.Colorize::clear()."/".Colorize::bold().$lastIdRegister.Colorize::clear().") | Total register: ".Colorize::bold().$totalRegister.Colorize::clear()."  \r[ ".Colorize::green()."OK".Colorize::clear()." ] " . PHP_EOL);

        if (count($ids ) > 0)
        {
            echo('[    ] Verificando itens no destino ');
            $idOks = $this->checkDumpImported($firstIdRegister, $lastIdRegister);
            echo (" | Total ".Colorize::bold().count($idOks).Colorize::clear()."\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);

            if (count($idOks) > 0 )
            {
                echo('[    ] Excluindo itens ja expurgados anteriormente. ');
                $this->deleteDump($ids, $idOks, $firstIdRegister,$lastIdRegister );
                echo (" | Excluidos ".count($idOks)."\r[ OK ]". PHP_EOL);
            }

            //verifica se sobrou dados a serem expurgado
            if ((count($ids) > count($idOks)) and count($ids) > 0)
            {
                echo('[    ] Gerando dump da tabela');
                $this->makeDump($firstIdRegister, $lastIdRegister);
                echo ("\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);

                echo('[    ] Importando dump da tabela');
                $this->importDump();
                echo ("\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);

                echo('[    ] Verificando registros expurgados ');
                $idOks = $this->checkDumpImported($firstIdRegister, $lastIdRegister);
                echo ("\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);


                if (count($idOks) > 0 )
                {
                    echo('[    ] Excluindo registros ');
                    $this->deleteDump($ids, $idOks, $firstIdRegister,$lastIdRegister );
                    echo ("\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);
                }
            }else {

                echo PHP_EOL;
                echo(Colorize::yellow());
                echo(str_pad("Info: Não sobrou itens a serem expurgado!", 100, ' ', STR_PAD_BOTH));
                echo(Colorize::clear());
                echo PHP_EOL.PHP_EOL;
            }

        }else{
            echo PHP_EOL;
            echo(Colorize::yellow());
            echo(str_pad("Info: Não existe itens a serem expurgado", 100, ' ', STR_PAD_BOTH));
            echo(Colorize::clear());
            echo PHP_EOL;
        }


        echo(PHP_EOL.PHP_EOL.Colorize::black(1).Colorize::white());
        echo(str_pad(Timer::timeSinceStartOfRequest(), 100, ' ', STR_PAD_BOTH));
        echo(Colorize::clear().PHP_EOL.PHP_EOL);

    }

    private function importDump()
    {

        shell_exec('export MYSQL_PWD='.$this->dbTo->password.' && mysql -h '.$this->dbTo->host.' -u '.$this->dbTo->user.' --force -s -D '.$this->dbTo->db.'  <  '.$this->getFileDump());
    }


    private  function checkDumpImported($firstIdRegister, $lastIdRegister)
    {
        $ids = [];
        $registers = $this->dbTo->connect()->query("SELECT {$this->table->id} FROM {$this->table->name} where {$this->table->id} >= {$firstIdRegister} and {$this->table->id} <= '{$lastIdRegister}'")->fetchAll();

        foreach ($registers as $register)
        {
            $ids[] = $register[$this->table->id];
        }
        return $ids;
    }


    private function deleteDump($ids, $idOks, $firstIdRegister,$lastIdRegister)
    {
        $idDiff = array_diff($ids, $idOks);
        $this->dbFrom->connect()->exec("set foreign_key_checks=0");
        $this->dbFrom->connect()->exec("delete from  {$this->table->name} where {$this->table->id} >= {$firstIdRegister} and {$this->table->id} <= {$lastIdRegister} " . (count($idDiff) > 0 ? " and {$this->table->id} NOT IN(" . implode(',', $idDiff) . ")" : ""));
        $this->dbFrom->connect()->exec("set foreign_key_checks=1");
    }


    private function makeDump($firstIdRegister, $lastIdRegister)
    {
        shell_exec('export MYSQL_PWD='.$this->dbFrom->password.' && mysqldump -h '.$this->dbFrom->host.' -u '.$this->dbFrom->user.' '.$this->dbFrom->db.' '.$this->table->name.' --quick  --no-create-info --single-transaction --where="'.$this->table->id.' >= '.$firstIdRegister.' and '.$this->table->id.' <= '.$lastIdRegister.'"  >  '.$this->getFileDump());
    }


    public function setFileDump($fileDump)
    {
        $this->fileDump = $fileDump;
    }

    private function getFileDump()
    {
        return $this->fileDump = $this->fileDump?$this->fileDump:md5(time())."_{$this->table->name}_".date('Y_m_d_h_i_s').'.sql';
    }


    public function setDateTimeEnd($dateTimeEnd)
    {
        $this->dateTimeEnd = $dateTimeEnd;
    }

    public function setDateTimeStart($dateTimeStart)
    {
        $this->dateTimeStart = $dateTimeStart;
    }

    private function validate()
    {
        if ($this->table->name) {
            if ($this->dbFrom->connect()->query("SHOW TABLES LIKE '{$this->table->name}'")->rowCount() > 0)
            {
                if ($this->dbTo->connect()->query("SHOW TABLES LIKE '{$this->table->name}'")->rowCount() > 0)
                {
                    $cols1 = $this->getFieldsFromTable('from' );
                    $cols2 = $this->getFieldsFromTable('to' );

                    if (count($diff = array_diff($cols1, $cols2)) == 0)
                    {
                        if (!in_array($this->table->fieldDateTime, $cols1)) throw new \Exception("Erro! Campo de data hora:'{$this->table->fieldDateTime}' não existe no host:'{$this->dbFrom->host}'");
                        if (!in_array($this->table->fieldDateTime, $cols2)) throw new \Exception("Erro! Campo de data hora:'{$this->table->fieldDateTime}' não existe no host:'{$this->dbTo->host}'");
                    }else{
                        throw new \Exception("Erro! As colunas da tabela {$this->table->name} não corresponde entre from e destino! Verifique os seguntes campos: ". implode(',', $diff));
                    }

                }else{
                    throw new \Exception("Erro! A tabela {$this->table->name} Não foi encontrada no banco de destino!");
                }

            }else{
                throw new \Exception("Erro! A tabela {$this->table->name} Não foi encontrada no banco de from!");
            }

        } else {
            throw new \Exception("Erro! Não foi informado nome da tabela para o expurgo!");
        }

    }


    private function getFieldsFromTable($how)
    {

        if ($how == 'from')
        {
            $cols = $this->dbFrom->connect()->query("SHOW COLUMNS FROM {$this->table->name}")->fetchAll();
        }
        if ($how == 'to')
        {
            $cols = $this->dbTo->connect()->query("SHOW COLUMNS FROM {$this->table->name}")->fetchAll();
        }


        if (count($cols) > 0)
        {
            foreach ($cols as $col) {
                $return[] = $col['Field'];
            }
        }

        return isset($return)?$return:[];

    }


}
