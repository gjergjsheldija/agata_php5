<?
if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
dl('php_gtk.dll');
else
dl('php_gtk.so');

function Fecha()
{
    Gtk::main_quit();
}

$window = &new GtkWindow;
$window->connect('delete-event', 'Fecha');
$window->set_title('!!');
$window->set_border_width(0);
$window->set_default_size(100, 80);
$window->set_position(center);

$window->realize();

$box_ = &new GtkHBox();
$window->add($box_);
$box_->show();
$texto = new GtkLabel('  Wait a moment ... ');
$texto->show();

list($pixmap, $mask) = Gdk::pixmap_create_from_xpm($window->window, null, "wait.xpm");
$pixmapwid = &new GtkPixmap($pixmap, $mask);
$box_->pack_start($pixmapwid);
$box_->pack_start($texto);
$pixmapwid->show();
$window->show();
Gtk::main();
?>
