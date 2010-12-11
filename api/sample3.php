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
#| This file shows how to use AgataAPI to generate labels          |
#+-----------------------------------------------------------------+

# Include AgataAPI class
include_once '/agata/classes/core/AgataAPI.class';

# Instantiate AgataAPI
$api = new AgataAPI;
$api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'
$api->setReportPath('/agata/reports/samples/customers.agt');
$api->setProject('Samples');
$api->setOutputPath('/tmp/test.pdf');
#How to set parameters, if they exist
#$api->setParameter('$personCode', 4);
#$api->setParameter('$personName', "'mary'");

$ok = $api->generateLabel();
if (!$ok)
{
    echo $api->getError();
}
else
{
    // opens file dialog
    $api->fileDialog();
}
?>
