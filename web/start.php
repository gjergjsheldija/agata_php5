<?
if(isset($_GET) && is_array($_GET))
{
    foreach ($_GET as $key=>$val)
    {
        ${$key}=$val;
    }
}

if(isset($_POST) && is_array($_POST))
{
    foreach ($_POST as $key=>$val)
    {
        ${$key}=$val;
    }
}

$agata_ini = file('agataweb.ini');
$pieces1 = explode('=', $agata_ini[0]);
$pieces2 = explode('=', $agata_ini[1]);

$path = trim($pieces1[1]);
$lang = trim($pieces2[1]);

define ('XMLHEADER', "<?xml version=\"1.0\"?>\n");

# Include AgataAPI class
include_once $path . '/classes/core/AgataAPI.class';
include_once $path . '/classes/core/AgataWEB.class';

# Instantiate new AgataAPI
$api = new AgataAPI;
$api->setLanguage($lang);
#$agataConfig = AgataConfig::FixConfig(AgataConfig::ReadConfig());
?>
