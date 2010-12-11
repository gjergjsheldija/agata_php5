<link href="site.css" rel="stylesheet" type="text/css">
<?
include 'start.php';
$web  = new AgataWEB;
new TMaster;
TMaster::getComponent('TMenu');

$menu = new TMenu('Menu');
$menu->addOption(_a('Add User'),       'index.php?class=Users::UsersForm', 'imagens/ico_users.png');
$menu->addOption(_a('List Users'),     'index.php?class=Users::UsersForm', 'imagens/ico_users.png');
$menu->addOption(_a('Add Right'),      'index.php?class=Users::UsersForm', 'imagens/ico_rights.png');
$menu->addOption(_a('List Rights'),    'index.php?class=Users::UsersForm', 'imagens/ico_rights.png');
$menu->show();
?>