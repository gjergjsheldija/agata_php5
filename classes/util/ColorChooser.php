<?php
/***********************************************************/
/* Dialog for color selection
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class ColorChooser
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function ColorChooser($callback)
    {
        $this->window = $window = new GtkWindow();
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $window->show();
        $window->set_title(_a('Color Selection'));
        
        $box = new GtkVBox();
        
        $this->cs = $cs = new GtkColorSelection();
        $box->pack_start($cs, false, false);
        
        $bt = new GtkButton('ok');
        $bt->connect_object('clicked', array(&$this,'GetColor'), $callback);
        $bt->set_usize(-1,20);
        $box->pack_start($bt, false, false);
        
        $window->add($box);
        $window->show_all();
    }

    /***********************************************************/
    /* Returns the select color
    /***********************************************************/
    function GetColor(&$callback)
    {
        $ac = ($this->cs->get_color());
        $red = (int)($ac[0]*255);
        $green = (int)($ac[1]*255);
        $blue = (int)($ac[2]*255);

        $redc   = str_pad(dechex($red),2,'0', STR_PAD_LEFT);
        $greenc = str_pad(dechex($green),2,'0', STR_PAD_LEFT);
        $bluec  = str_pad(dechex($blue),2,'0', STR_PAD_LEFT);

        $this->window->Hide();
        call_user_func($callback, "$redc$greenc$bluec");
    }

    /***********************************************************/
    /* KeyTest Method
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->window->hide();        
        }
    }
}
?>