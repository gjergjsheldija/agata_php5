<?php
/***********************************************************/
/* Configuration Dialog
/* by Pablo Dall'Oglio 2001-2006
/*    Jamiel Spezia 2006-2006
/***********************************************************/
class PropertiesWindow
{
    /***********************************************************/
    /* Constructor
    /***********************************************************/
    function PropertiesWindow($slot, $Properties)
    {
        $this->window = new GtkWindow;
        $this->window->connect_object('delete-event', array(&$this, 'Hide'));
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_title(_a('Properties'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->set_default_size(400,400);
        $this->Properties = $Properties;
        $this->slot       = $slot;

        $vbox = new GtkVBox;
        $this->window->add($vbox);

        $Vars = null;
        $Properties['Date'] = $Properties['Date'] ? $Properties['Date'] : date("Y-m-d");
        $Properties['FrameSize'] = $Properties['FrameSize'] ? $Properties['FrameSize'] : '30%';

        $Vars[_a('Properties')][] = array($Properties['Title'],      _a('Title'), false, false, true);
        $Vars[_a('Properties')][] = array($Properties['Author'],     _a('Author'), false, false, true);
        $Vars[_a('Properties')][] = array($Properties['Keywords'],   _a('Keywords'), false, false, true);
        $Vars[_a('Properties')][] = array($Properties['Date'],       _a('Date'), false, false, true);

        $Vars[_a('Miscellaneous')][] = array($Properties['FrameSize'],_a('Frame Size'), false, false, true);

        $this->ConfigList = new Preferences(&$vbox);
        $this->ConfigList->SetTitleImage(images . 'properties.xpm');
        $this->ConfigList->SetTitle(_a('Properties'));
        $this->ConfigList->SetOptions(false);
        $this->ConfigList->SetSaveButton(true);
        $this->ConfigList->SetPlus($Plus);
        $this->ConfigList->SetOk(array(&$this, 'Save'), _a('Save'));
        //$this->ConfigList->SetStatus(_a('Loaded from ^1 directory', 'layout'));
        $this->ConfigList->SetPixmap(images . 'menu_config.xpm');
        $this->ConfigList->SetListTitle(_a('Configuration'));

        $this->ConfigList->BuildForms($Vars);
    }

    /***********************************************************/
    /* Save Project
    /***********************************************************/
    function Save()
    {
        $ConfigList = $this->ConfigList;
        $return = $ConfigList->GetVars();
        $key = key($return);
        $schema = $return[$key];

        if ($key == _a('Properties'))
        {
            $this->Properties['Title']    = $schema[0];
            $this->Properties['Author']   = $schema[1];
            $this->Properties['Keywords'] = $schema[2];
            $this->Properties['Date']     = $schema[3];
        }
        else if ($key == _a('Miscellaneous'))
        {
            $this->Properties['FrameSize']    = $schema[0];
        }
        $this->window->hide();
        call_user_func($this->slot, $this->Properties);
    }

    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->Hide();
        }
    }

    /***********************************************************/
    /* Show the Window
    /***********************************************************/
    function Show()
    {
        $this->window->show_all();
        return true;
    }

    /***********************************************************/
    /* Hide the Window
    /***********************************************************/
    function Hide()
    {
        $this->window->hide();
        return true;
    }
}
?>