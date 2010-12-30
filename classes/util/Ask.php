<?php
/***********************************************************/
/* Questions class, ask the user for yes or no
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Ask
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function Ask($question, $array_yes, $array_no = null, $extra_yes = null)
    {
        $this->window = new GtkWindow(GTK_WINDOW_DIALOG);
        $this->window->set_title(_a('Question'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->set_border_width(10);
        $this->window->set_default_size(300, 80);
        $this->window->set_policy(false, true, false);
        $this->window->set_modal(true);
        $this->window->realize();
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'), null);
        
        $vbox = new GtkVBox(false,10);
        $this->window->add($vbox);
        
        $hbox1 = new GtkHbox(false, 10);
        $img   = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'ico_ask.xpm');
        $pixmapwid = new GtkPixmap($img[0], $img[1]);
        $hbox1->pack_start($pixmapwid, false, false);
        $texto = new GtkLabel($question);
        
        $hbox1->pack_start($texto, false, false);
        $hbox2 = new GtkHbox(false, 10);

        $yes    = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'ico_ok.xpm');
        $no     = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'ico_cancel.xpm');
        $cancel = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'ico_close.xpm');

        $this->bt1 = $bt1 = new Button(array($this->window, 'hide'), _a('Yes'), $yes, IMAGEBUTTON);

        if ($extra_yes)
        {
            if (count($extra_yes) == 1)
                $bt1->connect_object('clicked', $array_yes, $extra_yes[0]);
            else if (count($extra_yes) == 2)
                $bt1->connect_object('clicked', $array_yes, $extra_yes[0], $extra_yes[1]);
            else if (count($extra_yes) == 3)
                $bt1->connect_object('clicked', $array_yes, $extra_yes[0], $extra_yes[1], $extra_yes[2]);
        }
        else
        {
            $bt1->connect_object('clicked', $array_yes);
        }

        $bt2 = new Button(array($this->window, 'hide'), _a('No'), $no, IMAGEBUTTON);
        if ($array_no)
        {
            $bt2->connect_object('clicked', $array_no);
        }

        $bt3 = new Button(array($this->window, 'hide'), _a('Cancel'), $cancel, IMAGEBUTTON);


        $hbox2->pack_start($bt1);
        $hbox2->pack_start($bt2);
        $hbox2->pack_start($bt3);
        
        $vbox->pack_start($hbox1);
        $vbox->pack_start(right($hbox2));
        $this->window->show_all();
        
    }

    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($p1, $p2)
    {
        if ($p1->keyval == K_ENTER)
        {
            $this->bt1->clicked();
        }
    }
}
?>