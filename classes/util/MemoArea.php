<?php
/***********************************************************/
/* Dialog class, shows messages to the users
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class MemoArea
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function MemoArea($mensagem)
    {
        if (isGui)
        {
            $this->window = new GtkWindow;
            $this->window->set_title(_a('Message'));
            $this->window->set_default_size(400, 280);
            $this->window->connect_object('key_press_event', array(&$this, 'KeyTest'));
            $this->window->set_position(GTK_WIN_POS_CENTER);
            $this->window->realize();
            
            $vbox = new GtkVBox;
            
            $this->window->add($vbox);
            $col1 = new GdkColor(56000, 0, 0);
            
            $HelpText = new GtkText();
            $HelpText->set_word_wrap(true);
            $vbox->pack_start($HelpText, true, true);
            $font = gdk::font_load ("-bitstream-helvetica-medium-r-normal-*-*-140-*-*-m-*-iso8859-9");
            $HelpText->insert($font, $col1, null, "$mensagem\n");
            $HelpText->set_usize(364, -1);
            
            $close = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'ico_close.xpm');
            $this->button = new VoidButton(_a('Close'), $close, IMAGEBUTTON);
            $this->button->connect_object('clicked', array($this, 'Close'));
            $vbox->pack_start(right($this->button), false, false);
            
            $this->window->show_all();
        }
        else
        {
            if (is_array($labels))
            {
                echo '<b>' . _a('Message') . ': </b>' . implode(' ', $labels) . '<br>';
            }
            else
            {
                echo '<b>' . _a('Message') . ': </b>' . $labels . '<br>';
            }
        }
    }
    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($p1)
    {
        if (($p1->keyval == K_ENTER) or ($p1->keyval == K_ESC) or ($p1->keyval == K_SPACE))
        {
            $this->window->hide();
        }
    }
    
    function Close()
    {
        $this->window->hide();
    }
}
?>