<?php
/***********************************************************/
/* Comom Form, implements Form Fields
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class CommonForm
{
    /***********************************************************/
    /* Builds the Form Entries array
    /***********************************************************/
    function BuildFormEntries($Config, $CheckVars)
    {
        $i = 0;
        $count = count($CheckVars);
        $this->CheckVars[$Config] = $CheckVars;
        foreach ($CheckVars as $CheckVar)
        {
            $Content     = $CheckVar[0];
            $Description = $CheckVar[1];
            $IsPassword  = $CheckVar[2];
            $Kind        = $CheckVar[3];
            $Editable    = $CheckVar[4];
            $Tooltip     = $CheckVar[5];
            $Mask        = $CheckVar[6];

            $box = new GtkHBox(false, 3);
            if (($i < ($count/2)) or ($count <= 2) or ($this->SimpleColumn))
            {
                $this->vbox->pack_start($box, false, false);
            }
            else
            {
                $this->vbox2->pack_start($box, false, false);
            }
            
            if (is_array($Kind))
            {
                $this->InputEntries[$Config][$i] = new GtkCombo();
                $box->pack_start(new Box($this->InputEntries[$Config][$i], $Description), false, false, 10);
                $this->InputEntries[$Config][$i]->set_popdown_strings($Kind);
                $entry =$this->InputEntries[$Config][$i]->entry;
                $entry->set_text($Content);
                $entry->set_editable($Editable);
                $entry->set_usize(126,22);
                $this->InputEntries[$Config][$i]->set_usize(126,22);
            }
            else if (get_class($Kind) == 'GtkRadioButton')
            {
                $this->InputEntries[$Config][$i] = $Kind;
                $this->InputEntries[$Config][$i]->set_usize(126, 22);

                $box->pack_start($this->InputEntries[$Config][$i], false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                $Kind = 'RadioButton';
            }
            else if (is_object($Kind))
            {
                $this->InputEntries[$Config][$i] = $Kind;
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                
                $this->InputEntries[$Config][$i]->set_text($Content);

                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
            }
            else if ($Kind=='spin')
            {
                $this->InputEntries[$Config][$i] = new GtkSpinButton(new GtkAdjustment((double)$Content, 0, 999, 1, 10, 10));
                $this->InputEntries[$Config][$i]->set_position(0);
                $this->InputEntries[$Config][$i]->set_usize(88, 22);
                $box->pack_start(new Box($this->InputEntries[$Config][$i], $Description), false, false, 10);
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
                $this->InputEntries[$Config][$i]->show();
            }
            else if ($Kind=='line')
            {
                $this->InputEntries[$Config][$i] = new GtkHSeparator;
                $this->InputEntries[$Config][$i]->set_usize(88, 2);
                $box->pack_start(new Box($this->InputEntries[$Config][$i], $Description), false, false, 2);
                $this->InputEntries[$Config][$i]->show();
            }
            else if ($Kind=='colors')
            {                
                $this->InputEntries[$Config][$i] = new ColorButton($Description);
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_text($Content);

                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
            }
            else if ($Kind=='gtkfont')
            {
                $this->InputEntries[$Config][$i] = new GtkFontBox($Description, $Content);
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                $this->InputEntries[$Config][$i]->set_text($Content);

                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
            }
            else if (($Kind=='fonts') or ($Kind=='fonts2'))
            {
                $this->InputEntries[$Config][$i] = new FontBox($Description, $Kind=='fonts2');
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                $this->InputEntries[$Config][$i]->set_text($Content);
                
                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
            }
            else if (($Kind=='files') or ($Kind=='files2'))
            {
                $this->InputEntries[$Config][$i] = new FileBox($Description, $Kind=='files2');
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                $this->InputEntries[$Config][$i]->set_text($Content);

                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
            }
            else if ($Kind=='CheckButton')
            {
                
                $this->InputEntries[$Config][$i] = new GtkCheckButton(_a('Yes'));
                $this->InputEntries[$Config][$i]->set_active($Content);
                $box->pack_start(new Box($this->InputEntries[$Config][$i], $Description), false, false, 10);
                $this->InputEntries[$Config][$i]->show();
            }
            else if ($Kind=='TextArea')
            {
                $this->InputEntries[$Config][$i] = new GtkText();
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                $scroll = new GtkScrolledWindow;
                $scroll->set_usize(200,100);
                $scroll->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);
                $scroll->add($this->InputEntries[$Config][$i]);
                $box->pack_start(new Box($scroll, $Description), false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                $this->InputEntries[$Config][$i]->insert(null, null, null, $Content);
            }
            else if (class_exists($Kind))
            {
                $this->InputEntries[$Config][$i] = new $Kind;
                $this->InputEntries[$Config][$i]->set_usize(126, 22);
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                
                $this->InputEntries[$Config][$i]->set_text($Content);
                
                $box->pack_start($this->InputEntries[$Config][$i]->widget, false, false, 10);
                $this->InputEntries[$Config][$i]->show();
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
            }
            else
            {
                if ($Mask)
                {
                    if (strstr($Mask, 'dd') and strstr($Mask, 'mm') and strstr($Mask, 'yyyy')) // � data
                    {
                        $this->InputEntries[$Config][$i] = new GtkPopDate($Mask);
                        $this->InputEntries[$Config][$i]->set_text($Content);
                        $box->pack_start(new Box($this->InputEntries[$Config][$i]->widget, $Description), false, false, 10);
                    }
                    else
                    {
                        $this->InputEntries[$Config][$i] = new GtkEntryMask($Mask);
                        $box->pack_start(new EntryBox($this->InputEntries[$Config][$i], 100, $Description, $Content), false, false, 10);
                    }
                }
                else
                {
                    $this->InputEntries[$Config][$i] = new GtkEntry();
                    $box->pack_start(new EntryBox($this->InputEntries[$Config][$i], 100, $Description, $Content), false, false, 10);
                }
                
                $this->InputEntries[$Config][$i]->set_editable($Editable);
                $this->InputEntries[$Config][$i]->set_position(0);
                
                if ($IsPassword)
                {
                    $this->InputEntries[$Config][$i]->set_visibility(false);
                }
                $this->InputEntries[$Config][$i]->show();
                $this->InputEntries[$Config][$i]->set_usize(140,22);
            }
            
            if ($Kind == null)
            {
                $Kind = 'Entry';
            }

            if (is_array($Kind))
                $Kind = 'Combo';

            if ($Tooltip)
            {
                $this->tooltips->set_tip($this->InputEntries[$Config][$i], $Tooltip);
            }
            
            $this->InputKinds[$Config][$i] = $Kind;
            $i ++;
        }
        return $this->InputEntries;
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

    /***********************************************************/
    /* Returns the Form Values from a specific sheet
    /***********************************************************/
    function GetFormValues($sheet)
    {
        if ($this->CheckVars[$sheet])
        {
            $count = count($this->CheckVars[$sheet]);
        }
        else
        {
            $count = count($this->Plus);
        }
        
        for ($n=0; $n< $count; $n++)
        {
            if ($this->InputKinds[$sheet][$n] == 'Combo')
            {
                $entry = $this->InputEntries[$sheet][$n]->entry;
                $vetor[$n] = $entry->get_text();
            }
            else if ($this->InputKinds[$sheet][$n] == 'spin')
            {
                $vetor[$n] = $this->InputEntries[$sheet][$n]->get_value_as_int();
            }
            else if (($this->InputKinds[$sheet][$n] == 'CheckButton') or ($this->InputKinds[$sheet][$n] == 'RadioButton'))
            {
                $vetor[$n] = $this->InputEntries[$sheet][$n]->get_active();
            }
            else if (($this->InputKinds[$sheet][$n] == 'TextArea'))
            {
                $vetor[$n] = $this->InputEntries[$sheet][$n]->get_chars(0,-1);
            }
            else
            {
                $vetor[$n] = $this->InputEntries[$sheet][$n]->get_text();
            }
        }
        return $vetor;
    }

    /***********************************************************/
    /* Returns the Current Values
    /***********************************************************/
    function GetVars()
    {
        $current = $this->Current();
        $vetor[$current] = $this->GetFormValues($current);
        return $vetor;
    }

    /***********************************************************/
    /* Returns all the Values
    /***********************************************************/
    function GetAllVars()
    {
        if ($this->CheckVars)
        {
            foreach ($this->CheckVars as $CheckVar => $content)
            {
                if ($content)
                {
                    $count = count($CheckVar);
                    $vetor[$CheckVar] = $this->GetFormValues($CheckVar);
                }
            }
        }
        return $vetor;
    }
}
?>