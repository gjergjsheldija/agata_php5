<?php
/***********************************************************/
/* GtkPopDate, used to allows the user to choose date
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class GtkPopDate
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function GtkPopDate($Mask)
    {
        global $Pixmaps;
        $this->Mask = $Mask;
        $tooltip = new GtkTooltips;
        $newmask = $Mask;
        $newmask = str_replace('yyyy', '9999',  $newmask);
        $newmask = str_replace('mm',   '99',    $newmask);
        $newmask = str_replace('dd',   '99',    $newmask);
        
        $this->entry = new GtkEntryMask($newmask);

        $this->widget = $box = new GtkHBox;
        $this->button = new Button(array($this, 'DateSel'), _a('Date Selection'), $Pixmaps['popdate'], ICONBUTTON, $isdir, true);
        $this->button->set_relief(GTK_RELIEF_NONE);

        $box->pack_start($this->entry);
        $box->pack_start($this->button);
    }
    /***********************************************************/
    /* Changes the color
    /***********************************************************/
    function set_text($data)
    {
        $this->entry->set_text($data);
    }

    function set_editable($bool)
    {
        $this->entry->set_editable($bool);
    }

    /***********************************************************/
    /* Returns the color
    /***********************************************************/
    function get_text()
    {
        return $this->entry->get_text();
    }

    /***********************************************************/
    /* Changes the visibility
    /***********************************************************/    
    function set_visibility($bool)
    {
        $this->entry->set_visibility($bool);
    }

    /***********************************************************/
    /* Changes the position
    /***********************************************************/    
    function set_position($n)
    {
        $this->entry->set_position($n);
    }

    /***********************************************************/
    /* 
    /***********************************************************/    
    function add_events($x)
    {
        $this->entry->add_events($x);
    }

    /***********************************************************/
    /* 
    /***********************************************************/    
    function set_max_length($x)
    {
        $this->entry->set_max_length($x);
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
        $this->entry->set_usize($width, $height);
    }

    /***********************************************************/
    /* Date Selection Dialog
    /***********************************************************/
    function DateSel()
    {
        $this->janela = new GtkWindow;
        $this->janela->connect_object('key_press_event', array(&$this,'KeyTest'), $this->janela);
        $this->janela->set_position(GTK_WIN_POS_CENTER);
        $calendar = new GtkCalendar;
        $calendar->select_month(date('n') -1, date('Y'));
        $calendar->select_day(date('j'));
        $calendar->connect_object('day-selected-double-click', array(&$this, 'GetDate'), &$calendar);
        $this->janela->add($calendar);
        $this->janela->show_all();
    }

    /***********************************************************/
    /* Returns the selected Date
    /***********************************************************/
    function GetDate(&$calendar)
    {
        $date = $calendar->get_date();
        $day = str_pad($date[2], 2, '0', STR_PAD_LEFT);
        $month = str_pad($date[1] +1, 2, '0', STR_PAD_LEFT);
        $year = $date[0];
        $result = $this->Mask;
        $result = str_replace('yyyy', $year,  $result);
        $result = str_replace('mm',   $month, $result);
        $result = str_replace('dd',   $day,   $result);
        $this->janela->hide();
        $this->set_text($result);
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
