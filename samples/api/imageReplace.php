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
include_once '/agata/classes/core/AgataAPI.php';

# Instantiate AgataAPI
$api = new AgataAPI;
$api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'
$api->setOutputPath('/tmp/imageReplace.sxw');

# Set main data
$data = array( array('Jamiel Spezia', 'Engenharia da Computação'),
                array('William Prigol Lopes', 'Análise de Sistemas'),
                array('Rafael Luis Spengler', 'Análise de Sistemas'),
                array('Daniel Afonso Heisler', 'Engenharia da Computação')
                );

$api->setDataArray($data);
$api->setImageReplace('figura1', '/agata/images/agata.jpg');
$api->setImageReplace('figura2', '/agata/images/background.png');
$api->setImageReplace('assinatura', '/agata/images/assinatura_fulano.png');
$ok = $api->parseOpenOffice('/agata/resources/imageReplace.sxw');
if (!$ok)
{
    echo $api->getError();
}
else
{
    // opens file dialog
    #$api->fileDialog();
    #$api->removeOutputFile();
}
?>
