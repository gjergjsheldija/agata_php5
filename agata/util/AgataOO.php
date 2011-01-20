<?php
/* class AgataOO
 * Jamiel Spezia 2005 - 2005
 */

class AgataOO
{
    function GetConfig($source)
    {
        require_once 'vednor/pclzip/pclzip.lib.php';
        include_once 'agata/include/util.inc';

        if (!file_exists($source))
        {
            return;
        }

	$this->prefix = temp . bar . 'agata' . rand();
        $zip      = new PclZip($source);

        if (($list = $zip->listContent()) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }

        recursive_remove_directory($this->prefix);
        if ($zip->extract(PCLZIP_OPT_PATH, $this->prefix) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }

        $content= file_get_contents($this->prefix . '/meta.xml');

        $array_content = preg_split ('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', trim ($content), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        for ($x=0; $x < count($array_content); $x++)
        {
            $line = $array_content[$x];
            if ($line == '<dc:description>')
            {
                while ($line != '</dc:description>')
                {
                    $x++;
                    $agataDescription .= $line = $array_content[$x];
                }
                $tags = preg_split ('/(\{AGATA::[^=]*=[^\}]*\})/', trim ($agataDescription), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                foreach ($tags as $t)
                {
                    $feature = '{AGATA::';
                    if (substr($t, 0, strlen($feature)) == $feature)
                    {
                        $preg = '/({AGATA::([^=]*)=([^\}]*)})/';

                        $array = '[\'' . implode('\'][\'', explode('::', strtolower(preg_replace($preg, '\2', $t)))) . '\']';
                        $result = preg_replace($preg, '\3', $t);
                        eval("\$config$array = \$result;");
                    }
                }
            }
        }
        recursive_remove_directory($this->prefix);
        return $config;
    }

    function encode($string)
    {
        $from = array( '<'=>'&lt;',
                       '>'=>'&gt;',
                       '\''=>'&apos;',
                       '&'=>'&amp;'
                     );

        $x = 0;
        foreach ($from as $caracter=>$f)
        {
            $scape['\\' . $caracter] = 'AGATASCAPE' . $x;
            $scapeReverse['AGATASCAPE' . $x] = $caracter;
            $x++;
        }
        $textScape   = strtr($string, $scape);
        $text        = strtr($textScape, $from);
        $textReverse = strtr($text, $scapeReverse);

        return utf8_encode($textReverse);
    }



    function drawReplace($line, $imagePath, $tmpPath)
    {
        #extrai informa��es necess�rias para identifica��o da imagem
        ereg ("<draw:image .* xlink:href=\"#([^\"]*)\" .*", $line, $link);

        return copy($imagePath, $tmpPath . '/' . $link[1]);
    }

    function drawName($line)
    {
        ereg ("<draw:image .* draw:name=\"([^\"]*)\" .*", $line, $name);
        return $name[1];
    }

    function isDrawLine($line)
    {
        $draw = '<draw:image ';
        return (substr($line, 0, strlen($draw)) == $draw);
    }
}
