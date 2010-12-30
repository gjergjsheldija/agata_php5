<?php
/***********************************************************/
/* Levels Area, GtkFixed responsible by Levels design
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class LevelsArea
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function LevelsArea($glade)
    {
        global $Pixmaps;

        $this->widget    = new GtkFixed;

        $this->MenuLevels = new GtkMenu;
        $option1 = new MyNormalMenuItem($Pixmaps['menu_replace'],  _a('Edit'), null);
        $option2 = new MyNormalMenuItem($Pixmaps['menu_del'],   _a('Delete'), null);
        $option3 = new GtkMenuItem();
        $option4 = new MyNormalMenuItem($Pixmaps['menu_close'], _a('Close'), null);
        $MenuLevels = $this->MenuLevels;
        $MenuLevels->append($option1);
        $MenuLevels->append($option2);
        $MenuLevels->append($option3);
        $MenuLevels->append($option4);
        
        $this->comboBFunctions = $glade->get_widget('comboBFunctions');
        $this->comboBFields = $glade->get_widget('comboBFields');
        $this->comboBBreaks = $glade->get_widget('comboBBreaks');
        
        $b_functions = array(  _a('Sum'), _a('Count'),
                               _a('Average'), _a('Minimal'), _a('Maximal'),
                               _a('Group Average'), _a('Group Maximal'), _a('Group Minimal'));
        
        $this->comboBFunctions->set_popdown_strings($b_functions);
        
        $this->Containers['frameFunction']   = $glade->get_widget('frameFunction');
        $this->Containers['boxLevelMask']  = $glade->get_widget('boxLevelMask');
        $this->Containers['frameTotalLabel'] = $glade->get_widget('frameTotalLabel');
        $this->Containers['frameGroup']      = $glade->get_widget('frameGroup');
        $this->Mask = new MaskBox(_a('Format'), false);
        $this->Containers['boxLevelMask']->add($this->Mask->widget);
        
        $this->Containers['frameFunction']->set_label(_a('Apply Function'));
        $this->Containers['frameGroup']->set_label(_a('Set Group'));
        $this->Containers['frameTotalLabel']->set_label(_a('Label'));
        
        $this->Buttons['GroupIt'] =         $glade->get_widget('buttonGroupIt');
        $this->Buttons['BreakIt'] =         $glade->get_widget('buttonBreakIt');
        $this->Buttons['GroupIt']->connect_object('clicked', array(&$this, 'GroupIt'));
        $this->Buttons['BreakIt']->connect_object('clicked', array(&$this, 'BreakIt'));
        
        $this->entryTotalLabel      = $glade->get_widget( 'entryTotalLabel' );
        
        $this->Labels['labelGroup'] = $glade->get_widget('labelGroup');
        $this->Labels['labelGroup']->set_text(_a('Group') . ' : ');
        
        $option1->connect_object('activate', array(&$this, 'EditBreakList'), null, 2);
        $option2->connect_object('activate', array(&$this, 'DropLevel'));
    }

    function CreateBreakList()
    {
        $this->widget2   = new GtkHBox;
        $this->widget->realize();
        $this->clistBreak = new Alist(&$this->widget2, $this->widget, _a('xxx'),
                                array(_a('Groups'), _a('Formulas')), null, 400, 200,
                                true, array(null),  0, null, ICONBUTTON);
        $this->clistBreak->clist->set_usize(320, 70);
        $this->clistBreak->clist->set_column_width(0, 60);
        $this->clistBreak->clist->set_column_width(1, 800);
        $this->clistBreak->clist->connect_object('select-row', array(&$this, 'EditBreakList'), 1);
    }
    /***********************************************************/
    /* Draws the levels on GtkFixed
    /***********************************************************/
    function DrawLevels($Select)
    {
        global $Pixmaps;

        $this->ClearArea();
        $this->Select = $Select;
        $Content   = $Select;
        $Elements  = MyExplode(trim($Content), _a('Column'), true);
        //$Elements_ = MyExplode(trim($Content), null, true);

        $x = $y = 5;
        $this->clistBreak->clear();
        if ($this->Breaks)
        {
            foreach ($this->Breaks as $Break => $Formula)
            {
                $this->Append(array($Break, $Formula));
                $Formulas = CoreReport::TranslateFormulas($Content, $Formula);

                if ($Break == '0')
                {
                    $level = _a('Level') . ' 0 : ' . _a('Grand Total');
                    $b = new Button(array(&$this, 'PopLevels') , $level,
                         $Pixmaps['level'], IMAGEBUTTON, array('0'), false);
                    $b->connect_object('enter-notify-event', array(&$this, 'GetIn'), $Formulas, $level);
                    $b->connect_object('leave-notify-event', array(&$this, 'GetOut'));
                }
                else
                {
                    $level = $Elements[$Break];
                    $b = new Button(array(&$this, 'PopLevels'), $level,
                         $Pixmaps['level'], IMAGEBUTTON, array($Break), false);
                    $b->connect_object('enter-notify-event', array(&$this, 'GetIn'), $Formulas, $level);
                    $b->connect_object('leave-notify-event', array(&$this, 'GetOut'));
                }
                $b->set_relief(GTK_RELIEF_NONE);
                
                $this->widget->put($b, $x, $y);
                $x += 20;
                $y += 25;
            }
        }
        $this->widget->show_all();
    }
    
    function Append($array)
    {
        $this->clistBreak->append($array);
    }

    /***********************************************************/
    /* Hint Window with formulas
    /***********************************************************/
    function GetIn($event, $Formulas, $Level)
    {
        if ($Formulas[1])
        {
            $this->Hint = new HintWindow;
            $this->Hint->set_title(_a('Formulas'));
            $this->Hint->set_uposition(480,164);
            $this->Hint->set_usize(280,-1);
            $vbox = new GtkVBox;
            $vbox->pack_start(darktxt($Level, -1, 20));
            $vbox->pack_start(new GtkHSeparator);
            foreach($Formulas as $Formula)
            {
                $pos = strpos($Formula, '(');
                $formula = trim(substr($Formula, 0, $pos));
                $express = trim(substr($Formula, $pos));
                $hbox1 = new GtkHBox;
                $hbox2 = new GtkHBox;
                $stub1 = new GtkHBox;
                $stub2 = new GtkHBox;
                $stub1->set_usize(40, -1);
                $stub2->set_usize(40, -1);
                $hbox1->pack_start($stub1, false, false);
                $hbox2->pack_start($stub2, false, false);
                $hbox1->pack_start(darktxt($formula, -1, 20, new GdkColor(52036, 52036, 52036)), true, true);
                $hbox2->pack_start(new GtkLabel($express), true, true);
                $vbox->pack_start($hbox1);
                $vbox->pack_start($hbox2);
            }
            //$this->Hint->add($hbox);
            $this->Hint->Adiciona($vbox);
            $this->Hint->show_all();
        }
    }

    /***********************************************************/
    /* Get Out of button region
    /***********************************************************/
    function GetOut()
    {
        if ($this->Hint)
        {
            $this->Hint->hide();
        }
    }

    /***********************************************************/
    /* PopUpMenu levels
    /***********************************************************/
    function PopLevels($level)
    {
        $this->CurrentLevel = $level;
        $MenuLevels = $this->MenuLevels;
        $MenuLevels->popup(null, null, null, 1, 1);
        $MenuLevels->show_all();
    }

    /***********************************************************/
    /* Drop Level
    /***********************************************************/
    function DropLevel()
    {
        $Break = $this->CurrentLevel[0];
        unset($this->Breaks[$Break]);
        $this->Redraw();
    }

    /***********************************************************/
    /* When user edit the breaks...
    /***********************************************************/
    function EditBreakList($row, $source)
    {
        if ($source == 1) // click at the listing
        {
            $SelectedItem  = $this->clistBreak->GetSelectedItem();
            $Break   = $SelectedItem[0];
            $Formula = $SelectedItem[1];
        }
        else // click at the button
        {
            $Break   = $this->CurrentLevel[0];
            $Formula = $this->Breaks[$Break];
        }
        $Elements = MyExplode(trim($Formula));
        
        $this->BList = $BList = new Wlist(_a('List Edition'), array(_a('Group') . ' ' . $Break), $Elements, 400, 200, false, null);
        $BList->SetCallBack(array(&$this, 'ReturnBreakList'), $Break);
        $BList->Exibe();
    }
    
    /***********************************************************/
    /* Returning from the function above
    /***********************************************************/
    function ReturnBreakList($Break)
    {
        $BList = $this->BList;
        $clist = $BList->clist;
        $strings = '';
        
        $init = false;
        $separator = ', ';
        
        $n =0;
        while ($text = @$clist->get_text($n,0))
        {
            if (($init) && ($text))
                $strings .= $separator;
            
            if ($text)
                $strings .= $text;
            
            $init = true;
            $n ++;
        }

        $this->SetBreak($Break, $strings);
        if (!trim($strings))
        {
            //unset($this->Breaks[$Break]);
            $this->SetBreak($Break, null);
        }

        $this->Redraw();
        $BList->CloseList();
    }


    /***********************************************************/
    /* Clear Area
    /***********************************************************/
    function ClearArea()
    {
        $children = $this->widget->children;
        foreach ($children as $child)
        {
            $this->widget->remove($child->widget);
        }
    }

    /***********************************************************/
    /* Draws again
    /***********************************************************/
    function Redraw()
    {
        $this->DrawLevels($this->Select);
    }

    /***********************************************************/
    /* Clear Breaks
    /***********************************************************/
    function ClearBreaks()
    {
        $this->Breaks = null;
    }

    /***********************************************************/
    /* Return Breaks
    /***********************************************************/
    function GetBreaks()
    {
        return $this->Breaks;
    }

    /***********************************************************/
    /* Concatenate new string
    /***********************************************************/
    function ConcatBreak($break, $content)
    {
        $this->Breaks[$break] .= $content;
    }

    /***********************************************************/
    /* Set Breaks content
    /***********************************************************/
    function SetBreak($break, $content)
    {
        $this->Breaks[$break] = $content;
    }

    /***********************************************************/
    /* Launched when users make a data group, with function
    /***********************************************************/
    function BreakIt()
    {
        $Field_    = $this->comboBFields->entry;
        $Break_    = $this->comboBBreaks->entry;
        $Function_ = $this->comboBFunctions->entry;
        $Field     = $Field_->get_text();
        $Break     = $Break_->get_text();
        $Function_tr= $Function_->get_text();
        
        $commands[_a('Sum')] = 'sum';
        $commands[_a('Count')] = 'count';
        $commands[_a('Count distinct')] = 'count distinct';
        $commands[_a('Average')] = 'avg';
        $commands[_a('Minimal')] = 'min';
        $commands[_a('Maximal')] = 'max';
        $commands[_a('Group Average')]  = 'gavg';
        $commands[_a('Group Maximal')]  = 'gmax';
        $commands[_a('Group Minimal')]  = 'gmin';
        
        $Function = $commands[$Function_tr];
        
        if (($Field) && ($Break))
        {
            $fields = explode (':', $Field);
            $fields = explode (' ', $fields[0]);
            $field  = trim($fields[1]);
            
            $breaks = explode (':', $Break);
            $breaks = explode (' ', $breaks[0]);
            $break = trim($breaks[1]);
            $totalLabel = $this->entryTotalLabel->get_text();
            if ($totalLabel)
            {
                $add = " as '$totalLabel'";
            }
            $mask = $this->Mask->get_text();
            if ($mask)
            {
                $add .= " mask '$mask'";
            }
            $this->ConcatBreak($break, ($this->Breaks[$break]) ? ",$Function($field){$add}" : "$Function($field){$add}");
            $a = array('0');
            $putinorder = array_diff(array_keys($this->GetBreaks()), $a);
            sort($putinorder);
            $ordering = implode(",", $putinorder);
            $this->entryTotalLabel->set_text('');
            $this->ReDraw();
            if ($this->callbacks['onchange'])
            {
                call_user_func_array($this->callbacks['onchange'], $ordering);
            }
            $this->Mask->set_text('');
        }
    }

    /***********************************************************/
    /* Launched when users make a data group
    /***********************************************************/
    function GroupIt()
    {
        $Break_    = $this->comboBBreaks->entry;
        $Break     = $Break_->get_text();
        
        if ($Break)
        {
            $breaks = explode (':', $Break);
            $breaks = explode (' ', $breaks[0]);
            $break = trim($breaks[1]);
            
            if (!$this->Breaks[$break])
            {
                $this->SetBreak($break, '');
                $a = array('0');
                $putinorder = array_diff(array_keys($this->GetBreaks()), $a);
                sort($putinorder);
                $ordering = implode(",", $putinorder);
                $this->entryTotalLabel->set_text('');
                $this->ReDraw();
                if ($this->callbacks['onchange'])
                {
                    call_user_func_array($this->callbacks['onchange'], $ordering);
                }
                $this->Mask->set_text('');
            }
        }
    }

    /***********************************************************/
    /* connect_object
    /***********************************************************/
    function connect_object($signal, $callback)
    {
        $this->callbacks[$signal] = $callback;
    }
}
?>