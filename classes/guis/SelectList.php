<?php
/***********************************************************/
/* Class that controls the Select statements
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class SelectList
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function SelectList($Block)
    {
        global $Pixmaps;
        $this->widget = new GtkVBox;
        $this->widget->set_border_width(4);

        $this->clist= new GtkCList(2, array(_a('Clause'), _a('Content')));
        $this->clist->connect_object('select-row', array(&$this, 'EditSelect'));
        $this->clist->set_selection_mode(GTK_SELECTION_EXTENDED);
        $this->clist->set_row_height(18);
        $this->clist->set_column_width(0, 80);
        $this->clist->set_column_width(1, 10248);
        $this->Block = $Block;
        $this->LoadBlocks($this->Block);
  
        $this->hbox = new GtkHBox;
        $this->widget->pack_start($this->hbox, true, true);
        
        $part1 = new GtkVBox;
        $part2 = new GtkHBox;
        $this->hbox->pack_start($part1, false, fase, 2);
        $this->hbox->pack_start($part2, true,  true, 2);

        $DelBox = new GtkVBox(false, 0);
        $a = new GtkLabel('');  $a->set_usize(18,25);
    
        $DelBox->pack_start($a, false, false);
        $blocks = array('Select', 'From', 'Where', 'Group by', 'Order by');
        foreach ($blocks as $block)
        {
          $DelBox->pack_start($a = new Button(array(&$this, 'Del'),    _a('Clear'), $Pixmaps['del'], ICONBUTTON, $block), false, false);
          $a->set_usize(18,19); $a->set_relief(GTK_RELIEF_NONE);
        }
        $part1->pack_start($DelBox, false, false, 0);
        
        $hbox = new GtkHBox;
        $this->OffSet   = new GtkSpinButton(new GtkAdjustment(0,0,999999, 1, 10, 0));
        $this->Limit    = new GtkSpinButton(new GtkAdjustment(0,0,999999, 1, 10, 0));
        $this->Distinct = new GtkCheckButton;
        
        $stub = new GtkHBox;
        $stub->set_usize(26,-1);
        $hbox->pack_start($stub, false, false);
        $hbox->pack_start(new GtkLabel(_a('OffSet') . ':    '), false, false);
        $hbox->pack_start($this->OffSet, false, false);
        $hbox->pack_start(new GtkLabel(_a('Limit') . ':    '), false, false);
        $hbox->pack_start($this->Limit, false, false);
        $hbox->pack_start(new GtkLabel('    ' . _a('Distinct Values') . '   '), false, false);
        $hbox->pack_start($this->Distinct, false, false);

        
        $this->widget->pack_start($hbox, false, false);
        
        $scroll = new GtkScrolledWindow;
        $scroll->add($this->clist);
        $part2->pack_start($scroll, true, true, 2);
    }

    /***********************************************************/
    /* Get Distinct
    /***********************************************************/
    function getDistinct()
    {
        return $this->Distinct->get_active();
    }
    
    /***********************************************************/
    /* Set Distinct
    /***********************************************************/
    function setDistinct($bool)
    {
        $this->Distinct->set_active($bool);
    }
    
    /***********************************************************/
    /* Set OffSet
    /***********************************************************/
    function setOffSet($text)
    {
        if (!$text)
        {
            $this->OffSet->set_text(0);
        }
        else
        {
            $this->OffSet->set_text($text);
        }
    }
    
    /***********************************************************/
    /* Get OffSet
    /***********************************************************/
    function getOffSet()
    {
        return $this->OffSet->get_text();
    }

    /***********************************************************/
    /* Set Limit
    /***********************************************************/
    function setLimit($text)
    {
        if (!$text)
        {
            $this->Limit->set_text(0);
        }
        else
        {
            $this->Limit->set_text($text);
        }
    }
    
    /***********************************************************/
    /* Get Limit
    /***********************************************************/
    function getLimit()
    {
        return $this->Limit->get_text();
    }

    /***********************************************************/
    /* Connects the signals
    /***********************************************************/
    function connect_object($signal, $callback, $param=null)
    {
        $this->callbacks[$signal] = $callback;
        $this->param = $param;
    }

    /***********************************************************/
    /* Load the SQL statements
    /***********************************************************/
    function LoadBlocks($Block)
    {
        if (!$Block)
        {
            $Block['Select']   = array('Select', '');
            $Block['From']     = array('From', '');
            $Block['Where']    = array('Where', '');
            $Block['Group by'] = array('Group by', '');
            $Block['Order by'] = array('Order by', '');
        }
        $this->Block = $Block;
        $this->Reload();
        if ($this->callbacks['changed'])
        {
            call_user_func($this->callbacks['changed'], $this->param);
        }
    }

    /***********************************************************/
    /* Reload the statements
    /***********************************************************/
    function Reload()
    {
        $translate['Select'] =      _a('Fields');
        $translate['From'] =        _a('Tables');
        $translate['Where'] =       _a('Constraints');
        $translate['Group by'] =    _a('Groups');
        $translate['Order by'] =    _a('Ordering');
        
        
        $this->clist->clear();
        foreach ($this->Block as $Clause)
        {
            $Clause[0] = $translate[$Clause[0]];
            $row = $this->clist->append($Clause);
            if (($row % 2) == 0)
                $this->clist->set_background($row, new GdkColor('#DDDDDD'));
        }
    }

    /***********************************************************/
    /* Returns the statements
    /***********************************************************/
    function GetBlocks()
    {
        return $this->Block;
    }

    /***********************************************************/
    /* Opens the edit list dialog
    /***********************************************************/
    function EditSelect()
    {
        $this->clist->freeze();
        $selection = $this->clist->selection;
        $this->clist->thaw();
        
        $translate[_a('Fields')] =      'Select';
        $translate[_a('Tables')] =      'From';
        $translate[_a('Constraints')] = 'Where';
        $translate[_a('Groups')] =      'Group by';
        $translate[_a('Ordering')] =    'Order by';

        $Clause  = $this->clist->get_text($selection[0],0);
        $Content = $this->clist->get_text($selection[0],1);
        
        $Clause = $translate[$Clause];
        
        if ($Clause=='Where')
            $Elements = WordExplode(' and ', trim($Content));
        else
            $Elements = MyExplode(trim($Content));
        
        # keep just 1 instance
        if ($this->SelectList)
        {
            $this->SelectList->Close();
            $this->SelectList = null;
        }
        
        $this->SelectList = new Wlist(_a('List Edition'), array($Clause), $Elements, 400, 200, false, null);
        $this->SelectList->SetCallBack(array(&$this, 'ReturnSelectList'), $Clause);
        $this->SelectList->Exibe();
    }

    /***********************************************************/
    /* Clear some clause
    /***********************************************************/
    function Del($Block)
    {
        $this->Block[$Block][1] = '';
        $this->LoadBlocks($this->Block);
        if ($this->callbacks['del-block'])
        {
            call_user_func($this->callbacks['del-block'], $Block);
        }
        if ($this->callbacks['changed'])
        {
            call_user_func($this->callbacks['changed']);
        }
    }

    /***********************************************************/
    /* Returning from the function above
    /***********************************************************/
    function ReturnSelectList($Clause)
    {
        $SelectList = $this->SelectList;
        $strings = '';
        
        if ($Clause=='Where')
            $separator  = ' and ';
        else
            $separator  = ', ';
        
        $items = $SelectList->GetItems();
        if ($items)
            $strings = implode($separator, $items);
        
        $this->Block[$Clause][1] = $strings;
        $this->LoadBlocks($this->Block);
        $SelectList->CloseList();
        if ($this->callbacks['return-select'])
        {
            call_user_func($this->callbacks['return-select'], $Clause);
        }
        if ($this->callbacks['changed'])
        {
            call_user_func($this->callbacks['changed']);
        }
    }
    
    /***********************************************************/
    /* Returns the Selected Columns
    /***********************************************************/
    function GetSelectColumns($separator, $posas = true)
    {
        $Content   = $this->Block['Select'][1];
        return MyExplode(trim($Content), $separator, $posas);
    }
    
    function SetBlock($index, $content)
    {
        $this->Block[$index][1] = $content;
        $this->Reload();
        if ($this->callbacks['changed'])
        {
            call_user_func($this->callbacks['changed']);
        }
    }
    
    function Concatenate($index, $content)
    {
        $this->Block[$index][1] .= $content;
        $this->Reload();
        if ($this->callbacks['changed'])
        {
            call_user_func($this->callbacks['changed']);
        }
    }
}
?>