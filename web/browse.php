<?
#+-----------------------------------------------------------------+
#| AGATA Report  (http://www.agata.org.br)                         |
#| Copyleft (l) 2004  Solis - Lajeado - RS - Brasil                |
#| Licensed under GPL: http://www.fsf.org for further details      |
#+-----------------------------------------------------------------+
#| Started in  2001, August, 10                                    |
#| Author: Pablo Dall'Oglio (pablo@dalloglio.net)                  |
#+-----------------------------------------------------------------+
#| Agata Report: A Database reporting tool written in PHP-GTK      |
#| This file shows how to use AgataWeb interface                   |
#+-----------------------------------------------------------------+
# Including the necessary classes and definitions.
//phpinfo();
include 'start.php';
if (get_magic_quotes_gpc())
{
    new Dialog('Error, magic_quotes_gpc should be OFF !! <br>');
}


if (!$goal)
{
    $goal=1;
}

if ($goal == 1)
{
    if (!$BrowseDir)
    {
        if ($agataConfig['general']['RptDir'])
        {
           $BrowseDir = $agataConfig['general']['RptDir'];
        }
        else
        {
           $BrowseDir = AGATA_PATH . '/reports';
        }
    }

    # Define the action of the links
    $action  = 'browse.php?goal=1';

    # Define the listed extensions
    $filter = array('agt');
}
else
{
    if (!$BrowseDir)
    {
        if ($agataConfig['general']['RptDir'])
        {
           $BrowseDir = $agataConfig['general']['OutputDir'];
        }
        else
        {
           $BrowseDir = AGATA_PATH . '/output';
        }
    }

    # Define the action of the links
    $action  = 'browse.php?goal=2';
    
    # Define the listed extensions
    $filter = array('txt', 'csv', 'html', 'ps', 'pdf', 'xml', 'sxw', 'dia');
}

AgataWEB::DirList($BrowseDir, $filter, $action, $agataConfig);

?>
