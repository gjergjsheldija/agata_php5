<?php
/***********************************************************/
/* CondHigh, Conditional Highlight
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class CondHighBox
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function CondHighBox($Description)
    {
        global $Pixmaps;
        $tooltip = new GtkTooltips;
        $this->entry = new GtkEntry;

        $box = new GtkHBox;
        $this->widget = new Box($box, $Description);
        $this->button = new Button(array($this, 'HighlightSel'), _a('Conditional highlight'), $Pixmaps['ico_colors'], ICONBUTTON, $isdir, true);
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
    /* Apply a Conditional Highlight
    /***********************************************************/
    function HighlightSel()
    {
        $this->window = new GtkWindow;
        $this->window->connect_object('delete-event', array(&$this, 'Hide'));
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_title(_a('DataBase Connection Setup'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->set_default_size(400,340);
        $this->window->realize();
        $hbox = new GtkHBox;
        $this->window->add($hbox);
        $text = $this->get_text();
        
        $cond_high['function']  = $cond_high['function']  ? $cond_high['function']  : '';
        $cond_high['fontface']  = $cond_high['fontface']  ? $cond_high['fontface']  : 'Arial-B-4';
        $cond_high['fontcolor'] = $cond_high['fontcolor'] ? $cond_high['fontcolor'] : '#ffffff';
        $cond_high['bgcolor']   = $cond_high['bgcolor']   ? $cond_high['bgcolor']   : '#ff0000';
        
        $cond_pieces = explode(';', $text);
        $i = 1;
        foreach ($cond_pieces as $piece)
        {
            if ($piece)
            {
                $cond_high  = splitCondHigh($piece);
                $oFunction    = new FunctionBox(_a('Functions'));
                $Vars[$i][] = array($cond_high['function'],   _a('Functions'),     false, $oFunction, true);
                $Vars[$i][] = array($cond_high['result'],     _a('Result'),        false, null, true);
                $Vars[$i][] = array($cond_high['fontface'],   _a('Data Font'),     false, 'fonts', true);
                $Vars[$i][] = array($cond_high['fontcolor'],  _a('Data Color'),    false, 'colors', true);
                $Vars[$i][] = array($cond_high['bgcolor'],    _a('Data Background Color'), false, 'colors', false);
                $i ++;
            }
        }
        
        $oFunction    = new FunctionBox(_a('Functions'));
        $Plus[] = array('',   _a('Functions'),     false, 'FunctionBox', true);
        $Plus[] = array('',   _a('Result'),        false, null, true);
        $Plus[] = array('',   _a('Data Font'),     false, 'fonts', true);
        $Plus[] = array('#000000',  _a('Data Color'),    false, 'colors', true);
        $Plus[] = array('#FFFFFF',  _a('Data Background Color'), false, 'colors', false);
        
        $slot_ok = array(&$this, 'onLayoutConfig');
        $slot_del = array(&$this, 'DeleteLayout');
        
        //$this->Conditional = new FormEntry(_a('Conditional highlight'), $Vars);
        //$this->button_handler = $this->Conditional->button->connect_object('clicked', array(&$this,'GetConditional'), $type);
        //$this->Conditional->Show();

        $slot_ok = array(&$this, 'GetConditional');
        $this->ConditionalVars = new Preferences(&$hbox);
        $this->ConditionalVars->SetTitleImage(images . 'conditional.xpm');
        $this->ConditionalVars->setAutomaticIncrement(true);
        $this->ConditionalVars->SetTitle(_a('Conditional highlight'));
        $this->ConditionalVars->SetPlus($Plus);
        $this->ConditionalVars->SetOk($slot_ok, _a('Apply'));
        //$this->DatabaseVars->SetDel($slot_del);
        //$this->DatabaseVars->SetStatus(_a('Loaded from ^1 directory', 'projects'));
        $this->ConditionalVars->SetSaveButton(true);
        $this->ConditionalVars->SetPixmap(images . 'ico_db.xpm');
        $this->ConditionalVars->SetListTitle(_a('Rule'));
        $this->ConditionalVars->BuildForms($Vars);
        $this->window->show_all();
    }
    
    function GetConditional()
    {
        $formulas = $this->ConditionalVars->GetAllVars();
        foreach ($formulas as $tab => $content)
        {
            $function = $content[0];
            $result   = $content[1];
            $fontface = $content[2];
            $fontcolor= $content[3];
            $fontbgcol= $content[4];
            $expression .= "if \"$function\"  = \"$result\" then fontface=\"$fontface\", fontcolor=\"$fontcolor\", bgcolor=\"$fontbgcol\";";
            
        }
        $this->set_text($expression);
        $this->window->hide();
    }

    /***********************************************************/
    /* KeyTest Method
    /***********************************************************/
    function KeyTest($obj)
    {
        if ($obj->keyval == K_ESC)
        {
            $this->window->hide();
        }
    }
}
?>