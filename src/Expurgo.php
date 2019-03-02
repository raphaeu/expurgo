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
    private $deadline;
    private $fileDump;
    private $onlyDump = false;


    function __construct(Table $table, Database $dbFrom, Database $dbTo)
    {
        $this->table = $table;
        $this->dbFrom =$dbFrom;
        $this->dbTo =$dbTo;
        $this->validate();
    }

    public function setOnlyDump($onlyDump)
    {
        $this->onlyDump = $onlyDump;
    }

    public function go()
    {
        echo(Colorize::white(1).Colorize::bold().Colorize::black());
        echo(str_pad("{$this->table->name}", 100, ' ', STR_PAD_BOTH));
        echo(Colorize::clear().PHP_EOL.PHP_EOL);

        Timer::start();

        //verifica id do ultimo registro
        Timer::start();
        echo('[    ] Verificando ultimo ID');
        $sql="
            SELECT
              tmp.max as first_id
              ,tmp.min as last_id
              ,tmp.min  - tmp.max as total
            FROM (
              SELECT
                (SELECT {$this->table->id}
                 FROM {$this->table->name}
                 WHERE {$this->table->fieldDateTime} <= '{$this->deadline} 23:59:59'
                 ORDER BY {$this->table->fieldDateTime} DESC
                 LIMIT 1) AS min,
                (SELECT {$this->table->id}
                 FROM {$this->table->name}
                 WHERE {$this->table->fieldDateTime} <= '{$this->deadline} 23:59:59'
                 ORDER BY {$this->table->fieldDateTime} ASC
                 LIMIT 1) AS max
            ) as tmp
        ";


        $registersFrom = $this->dbFrom->connect()->query($sql)->fetchObject();

        $first_id = $registersFrom->first_id;
        $last_id = $registersFrom->last_id;
        $total  = $registersFrom->total;

        if ($total > 0)
        {
            echo("(".Colorize::bold().$first_id.Colorize::clear()."/".Colorize::bold().$last_id.Colorize::clear().") | Total register: ".Colorize::bold().$total.Colorize::clear()." (".Timer::secondsToTimeString(Timer::stop()) .")  \r[ ".Colorize::green()."OK".Colorize::clear()." ] ". PHP_EOL);

            Timer::start();
            echo('[    ] Gerando dump da tabela');
            $this->makeDump($first_id, $last_id);
            echo (" (".Timer::secondsToTimeString(Timer::stop()) .")\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);

            Timer::start();
            echo('[    ] Importando dump da tabela');
            $this->importDump();
            echo (" (".Timer::secondsToTimeString(Timer::stop()) .") \r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);

            Timer::start();
            echo('[    ] Verificando registros expurgados ');
            if ($this->checkDumpImported($first_id, $last_id))
            {
                echo ("\r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);
                echo('[    ] Excluindo registros ');
                $this->deleteDump($first_id,$last_id);
                echo (" (".Timer::secondsToTimeString(Timer::stop()) .") \r[ ".Colorize::green()."OK".Colorize::clear()." ]". PHP_EOL);
            }else{
                echo (" (".Timer::secondsToTimeString(Timer::stop()) .") \r[ ".Colorize::red()."Err".Colorize::clear()."]". PHP_EOL);
            }

        }else{
            echo("\r[ ".Colorize::green()."OK".Colorize::clear().' ]');
            echo PHP_EOL;
            echo(Colorize::yellow());
            echo(str_pad("Info: Não existe itens a serem expurgado", 100, ' ', STR_PAD_BOTH));
            echo(Colorize::clear());
            echo PHP_EOL;
        }


        echo(PHP_EOL.PHP_EOL.Colorize::black(1).Colorize::white());

        echo(str_pad(Timer::secondsToTimeString(Timer::stop()) , 100, ' ', STR_PAD_BOTH));

        echo(Colorize::clear().PHP_EOL.PHP_EOL);

    }

    private function importDump()
    {
        shell_exec('export MYSQL_PWD='.$this->dbTo->password.' && mysql -h '.$this->dbTo->host.' -u '.$this->dbTo->user.' --force -s -D '.$this->dbTo->db.'  <  '.$this->getFileDump());
    }


    private  function checkDumpImported($first_id, $last_id)
    {
        $result = $this->dbTo->connect()->query("SELECT if (count({$this->table->id})=2,1,0) AS checkDumpImported FROM {$this->table->name} WHERE {$this->table->id} IN({$first_id} ,{$last_id})")->fetchObject();
        return $result->checkDumpImported;
    }


    private function deleteDump($first_id,$last_id)
    {
            if ($last_id > $first_id)
            {
                $this->dbFrom->connect()->exec("set foreign_key_checks=0");
                $this->dbFrom->connect()->exec("delete from  {$this->table->name} where {$this->table->id} >= {$first_id} and {$this->table->id} <= {$last_id}");
                $this->dbFrom->connect()->exec("set foreign_key_checks=1");
            }
    }


    private function makeDump($first_id, $last_id)
    {
        shell_exec('export MYSQL_PWD='.$this->dbFrom->password.' && mysqldump -h '.$this->dbFrom->host.' -u '.$this->dbFrom->user.' '.$this->dbFrom->db.' '.$this->table->name.' --quick  --no-create-info --single-transaction --where="'.$this->table->id.' >= '.$first_id.' and '.$this->table->id.' <= '.$last_id.'"  >  '.$this->getFileDump());
    }


    public function setFileDump($fileDump)
    {
        $this->fileDump = $fileDump;
    }

    private function getFileDump()
    {
        return $this->fileDump = $this->fileDump?$this->fileDump:md5(time())."_{$this->table->name}_".date('Y_m_d_h_i_s').'.sql';
    }


    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
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
