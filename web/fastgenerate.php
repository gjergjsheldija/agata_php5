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
#| This file shows how to use AgataAPI to generate simple reports  |
#+-----------------------------------------------------------------+

# Including the necessary classes and definitions.
include 'start.php';

# Defining the SQL file to interpret
$ReportName  = $file;

# Reading the Report
$Report = CoreReport::OpenReport($ReportName);

$mimetype = $Report['Report']['Properties']['Format'];

# Defining the output file that will be generated:
$Output   = temp . '/output.' . $mimetype;

include_once AGATA_PATH . '/classes/core/AgataAPI.class';

if ($Report['Report']['DataSet']['Query']['AgataWeb']['Select'])
{
    $webFields   = MyExplode($Report['Report']['DataSet']['Query']['AgataWeb']['Select']);
    $repFields   = MyExplode($Report['Report']['DataSet']['Query']['Select']);
    $adjustments = $Report['Report']['DataSet']['Fields'];
    
    # Calcula novos ajustes em virtude que campos
    # devem ter sido removidos na exibição.
    $i = 1;
    foreach ($repFields as $key => $field)
    {
        if (in_array($field, $webFields))
        {
            $newAdjustments["Column{$i}"] = $adjustments["Column{$key}"];
	    if ($Report['Report']['DataSet']['Groups']['Formulas'])
	    {
                foreach ($Report['Report']['DataSet']['Groups']['Formulas'] as $group => $Formula)
                {
                    $Report['Report']['DataSet']['Groups']['Formulas'][$group] = str_replace("($key)", "($i)", $Formula);
                    //echo "($key)-($i)<br>";
                }
            }
            $i ++;
        }
    }
    
    $Report['Report']['DataSet']['Fields'] = $newAdjustments;
    $Report['Report']['DataSet']['Query']['Select'] = $Report['Report']['DataSet']['Query']['AgataWeb']['Select'];
}

if ($Report['Report']['DataSet']['Query']['AgataWeb']['Where'])
{
    $Report['Report']['DataSet']['Query']['Where'] .= ' and ' . $Report['Report']['DataSet']['Query']['AgataWeb']['Where'];
}

if ($Report['Report']['DataSet']['Query']['AgataWeb']['OrderBy'])
{
    $Report['Report']['DataSet']['Query']['OrderBy'] = $Report['Report']['DataSet']['Query']['AgataWeb']['OrderBy'];
}

# Instantiate AgataAPI
$api = new AgataAPI;
$api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'
$api->setReport($Report);
$api->setProject($Report['Report']['DataSet']['DataSource']['Name']);
$api->setFormat($mimetype); // 'pdf', 'txt', 'xml', 'html', 'csv', 'sxw'
$api->setOutputPath($Output);
$api->setLayout($Report['Report']['Properties']['Layout']);

$Parameters = $api->getParameters();

# Parameters
if ($Parameters)
{
    foreach ($Parameters as $Parameter)
    {
        $ParameterName = substr($Parameter,1);
        $api->setParameter($Parameter, $Report['Report']['Parameters'][$ParameterName]['value']);
    }
}
$ok = $api->generateReport();
if (!$ok)
{
    echo $api->getError();
    die;
}


//header("Content-type: application/pdf");
//header("Content-Disposition: attachment; filename=\"output.pdf\"");
$download = 'output.' . $mimetype;
//readfile($Output);
//echo 'sdf';
header("Location: download.php?type=$mimetype&download=$download&file=$Output");
//header("Location: $Output");
?>
