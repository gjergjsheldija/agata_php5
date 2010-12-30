<?php
/***********************************************************/
/* ColorButton, used to allows the user to choose colors
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class ColorButton
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function ColorButton($Description)
    {
        global $Pixmaps;
        $tooltip = new GtkTooltips;
        $this->button = new GtkButton('');

        $box = new GtkHBox;
        $this->widget = new Box($box, $Description);
        $tooltip->set_tip($this->button, _a('Color Selection'), '');

        $pixmap = new GtkPixmap($Pixmaps['cor'][0], $Pixmaps['cor'][1]);
        
        $box->pack_start($this->button);
        $box->pack_start($pixmap);

        $this->style  = new GtkStyle;
        $this->connect_object('clicked', array($this, 'SelColor'));
    }
    /***********************************************************/
    /* Changes the color
    /***********************************************************/
    function set_text($data)
    {
        $this->entry_data = $data;
        $style = new GtkStyle;
        $style->bg[GTK_STATE_NORMAL] = new GdkColor($data);
        $this->button->set_style($style);

        $style = new GtkStyle;
        $style->fg[GTK_STATE_NORMAL] = new GdkColor(invcolor($data));
        $style->font = gdk::font_load ("-adobe-helvetica-bold-r-*-*-*-140-*-*-*-*-*-*");
        $label = $this->button->child;
        $label->set_style($style);
        $label->set_text($data);
    }

    /***********************************************************/
    /* Defines the callback
    /***********************************************************/
    function connect_object($signal, $slot, $param = null)
    {
        $this->button->connect_object($signal, $slot, $param);
    }

    /***********************************************************/
    /* Returns the color
    /***********************************************************/
    function get_text()
    {
        $label = $this->button->child;
        return $label->get();
    }

    /***********************************************************/
    /* Show the button
    /***********************************************************/
    function show()
    {
        $this->widget->show_all();
    }

    /***********************************************************/
    /* Define the size
    /***********************************************************/
    function set_usize($width, $height)
    {
        $this->button->set_usize($width, $height);
    }

    /***********************************************************/
    /* Color Selection Dialog
    /***********************************************************/
    function SelColor()
    {
        $this->cswindow = $cswindow = new GtkWindow();
        $this->cswindow->connect_object('key_press_event', array(&$this,'KeyTest'), $this->cswindow);
        $this->cswindow->set_position(GTK_WIN_POS_CENTER);
        $cswindow->show();
        $cswindow->set_title(_a('Color Selection'));
        
        $box = new GtkVBox();
        
        $this->cs = $cs = new GtkColorSelection();
        $rgb = $this->get_text();
        $int = rgb2int($rgb);
        $this->cs->set_color($int[0], $int[1], $int[2]);
        $this->cs->connect_object('key_press_event', array(&$this,'KeyTest'), $cs);
        $box->pack_start($cs, false, false);
        
        $bt = new GtkButton('ok');
        $bt->connect_object('clicked', array(&$this,'GetColor'));
        $bt->set_usize(-1,20);
        $box->pack_start($bt, false, false);
        
        $cswindow->add($box);
        $cswindow->show_all();
    }

    /***********************************************************/
    /* Returns the selected color
    /***********************************************************/
    function GetColor()
    {
        $ac = ($this->cs->get_color());
        $red = (int)($ac[0]*255);
        $green = (int)($ac[1]*255);
        $blue = (int)($ac[2]*255);
        
        $redc   = str_pad(dechex($red),2,'0', STR_PAD_LEFT);
        $greenc = str_pad(dechex($green),2,'0', STR_PAD_LEFT);
        $bluec  = str_pad(dechex($blue),2,'0', STR_PAD_LEFT);
        
        $html_color = "#".dechex($redc).dechex($green).dechex($blue);
        $this->set_text("#$redc$greenc$bluec");
        $this->cswindow->Hide();
    }

    /***********************************************************/
    /* KeyTest Method
    /***********************************************************/
    function KeyTest($obj, $window)
    {
        if ($obj->keyval == K_ESC)
        {
            $window->hide();
        }
    }
}
?>
