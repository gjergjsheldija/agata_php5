<?php
class AgataScreen extends AgataReport
{
    var $Query;
    var $Maior;
    var $Columns;
    var $ColumnTypes;
    
    function Process()
    {
        global $Pixmaps;
        

        if ($this->Breaks)
        {
            $CountBreaks=count($this->Breaks);
            if ($this->Breaks['0'])
            {
                $CountBreaks --;
            }
            ksort($this->Breaks);
            reset($this->Breaks);
        }
        
        if ($CountBreaks > 0)
        {
            $MarginBreaks = ($CountBreaks * 5) +10;
        }
        else
        {
            $MarginBreks = 0;
        }

        for ($z=0; $z<=count($this->Columns) -1; $z++)
        {
            $Column = trim($this->Columns[$z]);
            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
            {
                $TreeColumns[] = $Column;
                $EmptyColumns[] = '';
            }
        }
        
        
        $this->window = new GtkWindow;
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_title(_a('Preview of Report'));
        $this->window->set_uposition(20,20);
        $this->window->set_default_size(740,540);
        $this->window->realize();
        $Scroll = new GtkScrolledWindow;
        $vbox = new GtkVBox;
        if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
        {
            // usar clist
            for ($z=0; $z<=count($this->Columns); $z++)
            {
                $Column = $this->Columns[$z];
            }
            $TreeListColumns = array_merge(array(''),$TreeColumns);
            $TreeView = false;
            
            $this->TreeList = new Listing($TreeListColumns);
        }
        else
        {
            $TreeListColumns = array_merge(_a('Node'),$TreeColumns);
            $TreeView = true;
            $TdCols += $CountBreaks;
            
            $this->TreeList = new SimpleTree($TreeListColumns);
        }

        $this->TreeList->set_column_justification(0, GTK_JUSTIFY_LEFT);
        $this->TreeList->set_column_width(0, 200);
        $Scroll->add($this->TreeList);
        $this->window->add($vbox);
        
        /*for ($y=0; $y<count($this->Columns); $y++)
        {
            $width = $this->Adjustments[$y][1];
            $this->TreeList->set_column_width($y - (($this->ShowBreakColumns) ? 0 : $CountBreaks), $width);
        }*/

        $local_pix['edit']   = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/edit.xpm');
        $local_pix['pdf']    = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/pdf.xpm');
        $local_pix['html']   = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/html.xpm');
        $local_pix['xml']    = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/xml.xpm');
        $local_pix['csv']    = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/csv.xpm');
        $local_pix['sxw']    = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/sxw.xpm');

        $hbox = new GtkHBox;
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to TXT File'), $local_pix['edit'], ICONBUTTON, 'txt'), false, false);
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to PDF File'), $local_pix['pdf'],  ICONBUTTON, 'pdf'), false, false);
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to HTML File'),$local_pix['html'], ICONBUTTON, 'html'),false, false);
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to XML File'), $local_pix['xml'],  ICONBUTTON, 'xml'), false, false);
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to CSV File'), $local_pix['csv'],  ICONBUTTON, 'csv'), false, false);
        $hbox->pack_start(new Button(array(&$this, 'PreGenerate'), _a('Export to SXW File'), $local_pix['sxw'],  ICONBUTTON, 'sxw'), false, false);
        $hbox->pack_start(new GtkHBox);
        
        $hbox2 = new GtkHBox;
        $hbox2->pack_start(new GtkHBox);
        $hbox2->pack_start(new Button(array(&$this, 'Close'),   _a('Close'), $Pixmaps['ok'],  IMAGEBUTTON), false, false);
        
        $vbox->pack_start($hbox, false, false);
        $vbox->pack_start($a = new GtkHSeparator, false, false);
        $a ->set_usize(-1, 20);
        $vbox->pack_start($Scroll);
        $vbox->pack_start($b = new GtkHSeparator, false, false);
        $b ->set_usize(-1, 20);
        $vbox->pack_start($hbox2, false, false);

        while ($QueryLine = $this->CurrentQuery->FetchNext())
        {
            $this->QueryArray[] = $QueryLine;
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = null;
            $stringline[] = '';
            
            
            list($break) = $this->ProcessBreaks($QueryLine);
            
            for ($y=1; $y<=count($QueryLine); $y++)
            {
                $aligns['left']   = GTK_JUSTIFY_LEFT;
                $aligns['center'] = GTK_JUSTIFY_CENTER;
                $aligns['right']  = GTK_JUSTIFY_RIGHT;
                $align = $aligns[$this->Adjustments[$y]['Align']];

                $QueryCell = $QueryLine[$y];
                if ($align)
                {
                    $this->TreeList->set_column_justification($y - (($this->ShowBreakColumns) ? 0 : $CountBreaks), $align);
                }
                
                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);
                
                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
                {
                    $stringline[] = $QueryCell;
                }
            }
            
            if (($this->BreakMatrix) && ($break != '0'))
            {
                $chaves = array_reverse(array_keys($this->BreakMatrix));
                
                foreach ($chaves as $chave)
                {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------
                    
                    if ($this->HasFormula[$chave])
                    {
                        foreach ($FinalBreak as $FinalBreakLine)
                        {
                            $w = 0;
                            $totalline = null;
                            $withcontent = false;
                            
                            if ($this->ShowTotalLabel)
                            {
                                if ($chave == '0')
                                $totalline[] = ' (Grand Total)';
                                else
                                $totalline[] = ' (' . $this->Summary[$chave]['BeforeLastValue']  . ')';
                            }
                            else
                            {
                                $totalline[] = '';
                            }
                            
                            foreach($FinalBreakLine as $content)
                            {
                                $w ++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) //aquipbreak
                                {
                                    if ($content)
                                    {
                                        $totalline[] = trim($content);
                                        $withcontent = true;
                                    }
                                    else
                                    {
                                        $totalline[] = null;
                                    }
                                }
                            }
                            if ($withcontent)
                            {
                                $bg = new GdkColor(54000, 54000, 54000);
                                $style = null;
                                $style = new GtkStyle;
                                $style->base[GTK_STATE_NORMAL] = $bg;
                                $node = $this->TreeList->AppendLineItems($this->Nodes[$this->Association[$chave]], $totalline, null, $style);
                                $fg = new GdkColor(51400, 0, 0);
                                $style->fg[GTK_STATE_NORMAL] = $fg;
                            }
                        }
                    }
                }
            }
            
            if (($this->Headers) && ($break != '0'))
            {
                foreach ($this->Headers as $nCountBreak => $Header)
                {
                    $MarginHeader = ($nCountBreak * 5) +1;
                    $this->Index[$nCountBreak +1] ++;
                    $this->Index[$nCountBreak +2] = 0;

                    $index = '';
                    for ($n=1; $n<=$nCountBreak +1; $n ++)
                    {
                        $index .= $this->Index[$n]. '.';
                    }
                    if ($this->ShowNumber)
                    {
                        $Header = "{$index} {$Header}";
                    }

                    $this->Nodes[$nCountBreak] = $this->TreeList->AppendSubTree(array_merge(array($Header), $EmptyColumns), $Pixmaps['folder1'], $this->Nodes[$nCountBreak -1]);
                    $nodes[] = $this->Nodes[$nCountBreak];
                    $lastnode = $this->Nodes[$nCountBreak];
                }
            }
            
            if ($this->ShowDataColumns)
            {
//		var_dump($stringline);
                if ($TreeView)
                {
                    $this->TreeList->AppendLineItems($lastnode, $stringline, null);
                }
                else
                {
                    $this->TreeList->AppendLineItems($stringline);
                }
            }
        }
        
        /**************************
        PROCESS TOTALS OF LAST LINE
        ***************************/
        
        //------------------------
        $this->ProcessLastBreak();
        //------------------------
        
        if ($this->BreakMatrix)
        {
            $chaves = array_reverse(array_keys($this->BreakMatrix));
            foreach ($chaves as $chave)
            {
                //-----------------------------------------
                $FinalBreak = $this->EqualizeBreak($chave);
                //-----------------------------------------
                if (($this->HasFormula[$chave]) || ($chave =='0'))
                {
                    foreach ($FinalBreak as $FinalBreakLine)
                    {
                        $w = 0;
                        $totalline = null;
                        $withcontent = false;
                        
                        if ($this->ShowTotalLabel)
                        {
                            if ($chave == '0')
                            $totalline[] = ' (Grand Total)';
                            else
                            $totalline[] = ' (' . $this->Summary[$chave]['BeforeLastValue']  . ')';
                        }
                        else
                        {
                            $totalline[] = null;
                        }
                        
                        foreach($FinalBreakLine as $content)
                        {
                            $w ++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))  //aquipbreak
                            {
                                if ($content)
                                {
                                    $totalline[] = trim($content);
                                    $withcontent = true;
                                }
                                else
                                {
                                    $totalline[] = null;
                                }
                            }
                        }
                        if ($withcontent)
                        {
                            $bg = new GdkColor(54000, 54000, 54000);
                            $style = null;
                            $style = new GtkStyle;
                            $style->base[GTK_STATE_NORMAL] = $bg;
                            if ($TreeView)
                            {
                                $node = $this->TreeList->AppendLineItems($this->Nodes[$this->Association[$chave]], $totalline, null, $style);
                            }
                            else
                            {
                                $node = $this->TreeList->AppendLineItems($totalline, $style);
                            }
                            $fg = new GdkColor(51400, 0, 0);
                            $style->fg[GTK_STATE_NORMAL] = $fg;
                        }
                    }
                }
            }
        }
        
        /******************
        END OF LAST PROCESS
        *******************/
        
        
        
        if ($nodes)
        {
            foreach ($nodes as $node)
            {
                $this->TreeList->expand($node);
            }
        }
        $this->TreeList->columns_autosize();
        Wait::Off();
        $this->window->show_all();

        return true;
    }

    function PreGenerate($type)
    {
        global $Pixmaps;
        if (!in_array($type, array('txt', 'xml', 'csv')))
        {
            $items  = Layout::ListLayouts();
            $this->LayoutList = new IList($items, array(&$this, 'ReturnLayout'), $Pixmaps['ico_colors'], _a('Choose the Layout'), _a('Layout Name'), $type);
        }
        else
        {
            $this->ReturnLayout($type);
        }
    }

    function ReturnLayout($type)
    {
        $OutputDir = $this->agataConfig['general']['OutputDir'];
        $button = array('interface/output.xpm', _a('Output'), $OutputDir);
        $message = _a('Export to ' . strtoupper($type) . ' File');
        new FileDialog($message, array($type), $button, $OutputDir, array(&$this, 'Generate'), $type);
    }

    function Generate($fileselection, $type)
    {
        if (!in_array($type, array('txt', 'xml', 'csv')))
        {
            $layout = $this->LayoutList->GetItem();
        }
        $FileName = $fileselection->get_filename();
        if ($FileName)
        {
            $fileselection->hide();
            $objQueryData = new AgataQueryArray($this->QueryArray);
            $objQueryData->Columns = $this->CurrentQuery->Columns;
            $objQueryData->ColumnCount = $this->CurrentQuery->ColumnCount;

            $params[0] = $this->agataDB;
            $params[1] = $this->agataConfig;
            $params[2] = $FileName;
            $params[3] = $objQueryData;
            $params[4] = $this->XmlArray;
            $params[5] = $this->posAction;
            $params[6] = $layout;

            $obj = AgataCore::CreateReport($type, $params);
            $obj->GetReportName();
        }
    }
    
    function Close()
    {
        $this->window->Hide();
    }
    
    function KeyTest($p1)
    {
        if ($p1->keyval == 65307)
        {
            $this->window->hide();
        }
    }
}
?>
