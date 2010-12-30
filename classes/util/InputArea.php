<?php
/***********************************************************/
/* Input area Class, a input text area
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class InputArea
{
    var $dialog;
    var $InputEntry;
    
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function InputArea($mensagem, $lenght, $PreText = '')
    {
        $this->dialog = new GtkWindow;
        $this->dialog->set_title(_a('Question'));
        $this->dialog->set_border_width(0);
        $this->dialog->set_position(GTK_WIN_POS_CENTER);
        $this->dialog->connect_object('key_press_event', array(&$this,'KeyTest'), 'window');
        $this->dialog->realize();
        $this->dialog->set_modal(true);
 
        $Vbox = new GtkHBox(false, 3);
        $Vbox->show();
        $this->dialog->add($Vbox);
        
        $box = new GtkVBox(false, 3);
        $box->show();
        $Vbox->pack_start($box);
        
        $this->InputEntry  = new GtkText();
        $this->InputEntry->set_editable(true);
        $this->InputEntry->insert(null, null, null, $PreText);
        
        $box->pack_start($this->InputEntry);
        $this->InputEntry->set_usize(364, 80);
        $this->InputEntry->connect_object('key_press_event', array(&$this,'KeyTest'), 'entry');

        $ico_ok = Gdk::pixmap_create_from_xpm($this->dialog->window, null,  images . 'ico_ok.xpm');
        $this->button = new VoidButton('OK', $ico_ok, IMAGEBUTTON);
        $this->button->show();
        $this->button->set_relief(GTK_RELIEF_NONE);

        $hbox = new GtkHBox;
        $hbox->pack_start(new GtkHBox, true, true);
        $hbox->pack_start($this->button, false, false);
        $box->pack_start($hbox, false, true, 0);

        $this->dialog->set_focus($this->InputEntry);
        $this->dialog->realize();
        $this->dialog->show_all();
    }

    /***********************************************************/
    /* Returns the content
    /***********************************************************/
    function GetText()
    {
        $length = $this->InputEntry->get_length();
        $chars = $this->InputEntry->get_chars(0, $length);
    }

    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($p1, $context)
    {
        if ($p1->keyval == K_ENTER && $context=='entry')
        {
            $this->button->clicked();
        }
        else if ($p1->keyval == K_ESC && $context=='window')
        {
            $this->dialog->hide();
        }
    }
    
    /***********************************************************/
    /* Closes the window
    /***********************************************************/
    function Close()
    {
        $this->dialog->hide();
    }
}
?>
