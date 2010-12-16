<?php
/***********************************************************/
/* Wait class, shows the "Wait a moment" window
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Wait
{
    /***********************************************************/
    /* Raises the window
    /***********************************************************/
    
    
    function On()
    {

        if (isGui)
        {
            if (OS != 'WIN')
            {
                exec("php wait.php >/dev/null &");
            }
            else
            {
                if (PHP_OS != 'WINNT')
                {
                    exec("wait >NULL &");
                }
            }
        }
    }

    /***********************************************************/
    /* Kills the window
    /***********************************************************/
    function Off()
    {
        if (isGui)
        {
            if (OS != 'WIN')
            {
                exec("for i in `ps ax|grep wait.php |grep -v \"grep\"|awk -F\" \" '{printf  $1\"\\n\"   }'`; do kill -9 \$i; done");
            }
            else
            {
                if (PHP_OS != 'WINNT')
                {
                    exec("pv > processes.pid");
                    $pid = -1;
                    $fd = fopen ('processes.pid', "r");
                    while (!feof ($fd))
                    {
                        $buffer = trim(fgets($fd, 500));
                        if ($buffer!='')
                        {
                            $Linha = explode(".EXE", trim($buffer));
                            if (trim($Linha[0]) == 'PHP')
                            {
                                if ((trim($Linha[0]) > $pid) || ($pid == -1))
                                $pid = trim($Linha[1]);
                            }
                        }
                    }
                    fclose($fd);
                    exec("kill $pid");
                }
            }
        }
    }
}
?>
