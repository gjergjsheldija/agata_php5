<?php

/**
* This class read, write and administrates ini files.
* <p> It is used to read agata.ini in agata folder.
*
*/

class AgataConfig
{

    /**
    * Read Config File
    * @param $path the path of configuration file
    * @return $agataConfig the array with all content in inifile.
    */
    function ReadConfig($path = '/agata.ini')
    {
        if (file_exists(AGATA_PATH . $path))
        {
            $config = @file(AGATA_PATH . $path);
            if (!$config)
            {
                new Dialog(_a('Permission Denied'), true, true, _a('File') . ': ' . AGATA_PATH . bar . $path);
                return false;
            }

            foreach ($config as $line)
            {
                $line = trim($line);
                if (substr($line,0,1)=='[')
                {
                    $key = substr($line,1,-1);
                }
                else
                {
                    if (strlen($line) > 3)
                    {
                        $pieces = explode('=', $line, 2);
                        $agataConfig[$key][trim($pieces[0])] = trim($pieces[1]);
                    }
                }
            }
            return $agataConfig;
        }
        else
        {
            echo _a('ERROR: File ' . $path .' not found. Please reinstall your software.'."\n");
            die;
        }
    }


    /**
    * Write Config File
    * @param $agataConfig the array to be writed in ini file.
    */
    function WriteConfig($agataConfig, $path='/agata.ini')
    {
        $fd = @fopen (AGATA_PATH . $path, "w");
        if (!$fd)
        {
            new Dialog(_a('Permission Denied'), true, true, _a('File') . ': ' . AGATA_PATH . bar . $path);
            return false;
        }

        if ($fd)
        {
            foreach($agataConfig as $key => $Content)
            {
                if ($Content)
                {
                    fwrite($fd, "\n[$key]\n");

                    foreach ($Content as $Config => $Value)
                    {
                        fwrite($fd, str_pad($Config,20, ' ', STR_PAD_RIGHT) .  "= $Value\n");
                    }
                }
            }
            fclose($fd);
        }
        return true;
    }


    /**
    * Write Setup File
    */
    function WriteSetup($Theme, $Language)
    {
        $fd = @fopen(AGATA_PATH . '/agata/include/setup.inc', "w");
        if (!$fd)
        {
            new Dialog(_a('Permission Denied'), true, true, _a('File') . ': ' . AGATA_PATH . bar . 'include' . bar . 'setup.inc');
            return false;
        }

        if ($fd)
        {
            fwrite ($fd, "<?\n");
            fwrite ($fd, "  \$Theme='$Theme';\n");
            fwrite ($fd, "  \$Language='$Language';\n");
            fwrite ($fd, "?>\n");
        }
        fclose($fd);
        return true;
    }
}
?>