<?php
/***********************************************************/
/* Input Box, Asks the user by a value
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class InputBox
{
    var $dialog;
    var $InputEntry;
    
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function InputBox($mensagem, $lenght, $PreText = '', $action = null)
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
        
        $this->InputEntry  = new GtkEntry();
        $box->pack_start(new EntryBox($this->InputEntry, $lenght, $mensagem, $PreText), false, false, 10);
        $this->InputEntry->connect_object('key_press_event', array(&$this,'KeyTest'), 'entry');
        $this->InputEntry->set_usize($lenght,20);

        $ico_ok = Gdk::pixmap_create_from_xpm($this->dialog->window, null,  images . 'ico_ok.xpm'); 
        $this->button = new VoidButton('OK', $ico_ok, IMAGEBUTTON);
        if ($action)
        {
            $this->button->connect_object('clicked', $action, $this->InputEntry);
        }
        $this->button->connect_object('clicked', array(&$this, 'Close'));
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
