<?php
/***********************************************************/
/* ArrayGrid, Display an array as a Grid
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class AGrid
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function AGrid($Title, $Titles, $width, $height, $label='ok')
    {
        $this->window = new GtkWindow;
        $this->window->connect_object('delete-event', array(&$this,'FechaGrid'));
        $this->window->set_title($Title);
        $this->window->set_border_width(0);
        $this->window->set_default_size($width, $height);
        $this->window->set_uposition(80, 80);
        
        $this->window->add_events(GDK_KEY_PRESS_MASK);
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $vbox = new GtkVBox();
        $this->window->add($vbox);
        $vbox->show();
        
        $scrolled_win = new GtkScrolledWindow();
        $scrolled_win->set_border_width(5);
        $scrolled_win->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);
        
        $this->clist = new GtkCList(count($Titles), $Titles);
        $scrolled_win->add($this->clist);
        $this->clist->show();
        
        $vbox->pack_start($scrolled_win);
        
        $hbox = new GtkHBox();
        $vbox->pack_start($hbox, false, false);
        $hbox->show();
        
        $this->button = new GtkButton($label);
        $this->button->connect_object('clicked', array(&$this, 'FechaGrid'));
        $hbox->pack_start($this->button);
        $this->button->show();
        
        $scrolled_win->show();
    }

    /***********************************************************/
    /* Shows the Grid
    /***********************************************************/
    function Exibe()
    {
        $this->window->show();
    }

    /***********************************************************/
    /* Append Lines to the Grid
    /***********************************************************/
    function AppendLineItems($Contents)
    {
        for ($n=1; $n<=count($Contents[0]); $n++)
        {
            if (($Contents[0][$n]>=0) && (strpos($Contents[0][$n],'.')>0))
            $this->clist->set_column_justification($n-1, GTK_JUSTIFY_RIGHT);
        }
        
        if($Contents)
        foreach ($Contents as $Content)
        $this->clist->append($Content);
        $this->clist->columns_autosize();
    }

    /***********************************************************/
    /* Closes the Grid
    /***********************************************************/
    function FechaGrid()
    {
        $this->window->hide();
    }


    /***********************************************************/
    /* KeyTest Method
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->FechaGrid();
        }
        
        if ($p1->keyval == K_ENTER)
        {
            $this->button->clicked();
        }
    }
}

?>