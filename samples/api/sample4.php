<?php
#+-----------------------------------------------------------------+
#| AGATA Report  (http://www.agata.org.br)                         |
#| Copyleft (l) 2004  Solis - Lajeado - RS - Brasil                |
#| Licensed under GPL: http://www.fsf.org for further details      |
#+-----------------------------------------------------------------+
#| Started in  2001, August, 10                                    |
#| Author: Pablo Dall'Oglio (pablo@dalloglio.net)                  |
#+-----------------------------------------------------------------+
#| Agata Report: A Database reporting tool written in PHP-GTK      |
#| This file shows how to use AgataAPI to generate merged docs     |
#+-----------------------------------------------------------------+

# Include AgataAPI class
include_once '/agata/agata/core/AgataAPI.php';

# Instantiate AgataAPI
$api = new AgataAPI;
$api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'
$api->setReportPath('/agata/reports/samples/customers.agt');
$api->setProject('sqlite');
$api->setOutputPath('/tmp/teste2.sxw');
#How to set parameters, if they exist
$api->setParameter('$city', 1);
#$api->setParameter('$personCode', 4);
#$api->setParameter('$personName', "'mary'");

$ok = $api->parseOpenOffice('/agata/resources/teste.sxw');
if (!$ok)
{
    echo $api->getError();
}
else
{
    // opens file dialog
    #$api->fileDialog();
}
?>
