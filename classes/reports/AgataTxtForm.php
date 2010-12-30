<?php
class AgataTxtForm extends AgataReport
{
    var $Query;
    var $Maior;
    var $Columns;
    var $FileName;
    var $ColumnTypes;
    
    function Process()
    {
        if (isGui)
        {
            $InputBox = $this->InputBox;
            $ReportName = $InputBox->InputEntry->get_text();
            $InputBox->Close();
        }
        else
        {
            $ReportName = $this->ReportName;
        }
        
        $FileName = $this->FileName;

        $fd = @fopen($FileName, "w");
        if (!$fd)
        {
            if (isGui)
                new Dialog(_a('File Error'));
            return false;
        }
        Wait::On();

        $this->SetReportLocale();
        
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
            $MarginBreaks = ($CountBreaks * 5);
            if ($this->ShowTotalLabel)
            {
                $MarginBreaks += 10;
            }
            if (!$this->ShowIndent)
            {
                $MarginBreaks = 0;
            }
        }
        else
        {
            $MarginBreks = 0;
        }

        for ($n=1; $n<=count($this->Columns); $n++)
        {
            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$n])) //aquipbreak
            {
                $Cols += $this->Adjustments[$n]['Chars'];
            }
        }

        
        fputs($fd, $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n" );
        fputs($fd, $this->FormatString($ReportName, $MarginBreaks + $Cols, 'center') . "\n");
        fputs($fd, $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n\n" );
        
        # DIFFERENCE
        $maior_col = -1;
        for ($z=0; $z<=count($this->Columns); $z++)
        {
            $maior_col = ($maior_col > strlen(trim($this->Columns[$z]))) ? $maior_col : strlen(trim($this->Columns[$z]));
        }
        /*if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
        {
            for ($z=0; $z<=count($this->Columns); $z++)
            {
                $Column = $this->Columns[$z];
                fputs($fd, $this->FormatString($Column, $this->MaxLen[$z+1] +2));
            }
            fputs($fd, "\n" . $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n" );
        }
        */
        while ($QueryLine = $this->CurrentQuery->FetchNext())
        {
            
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = '';
            
            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------
            
            for ($y=1; $y<=count($QueryLine); $y++)
            {
                $QueryCell = $QueryLine[$y];
                
                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);
                
                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
                {
                    # DIFFERENCE
                    $QueryCell = ereg_replace("\n", '', $QueryCell);
                    $QueryCell = ereg_replace(chr(13), '', $QueryCell);
                    $QueryCell = ereg_replace(chr(10), '', $QueryCell);
                    $splits = my_str_split($QueryCell, 80);
                    $n_splits = 0;
                    foreach($splits as $split)
                    {
                        if ($n_splits==0)
                            $label_split = trim($this->Columns[$y -1]);
                        else
                            $label_split = '';

                        $stringline .= $this->Replicate(' ', $MarginBreaks) .
                                   str_pad($label_split, $maior_col, ' ', STR_PAD_RIGHT) . ' :  ' . trim($split) . "\n";
                                   //str_pad($input, 10, "-=", STR_PAD_LEFT)
                        $n_splits ++;
                    }
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
                        //fputs($fd, $this->Replicate(' ', $MarginBreaks));
                        //fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");
                        
                        foreach ($FinalBreak as $FinalBreakLine)
                        {
                            $w = 0;
                            # DIFFERENCE
                            /*//if ($this->ShowTotalLabel)
                            {
                                fputs($fd, ' (' . substr($this->Summary[$chave]['BeforeLastValue'] ,0, 11) . ')');
                                fputs($fd, $this->Replicate(' ', $MarginBreaks -14));
                            }
                            else
                            {
                                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                            }*/

                            foreach($FinalBreakLine as $content)
                            {
                                $w ++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) //aquipbreak
                                {
                                    if ($content)
                                    {
                                        # DIFFERENCE
                                        fputs($fd, $this->Replicate(' ', $MarginBreaks));
                                        if ($chave == '0')
                                            fputs($fd, '(Grand Total)');
                                        else
                                            fputs($fd, '(' . $this->Summary[$chave]['BeforeLastValue'] . ') :: ');

                                        fputs($fd, '[' . trim($this->Columns[$w-1]) . '] ' . $content);
                                        fputs($fd,  "\n");
                                    }
                                    /*
                                    else
                                    {
                                        fputs($fd, $this->FormatString(' ', $this->MaxLen[$w] +2, 'right'));
                                    }*/
                                }
                            }
                            
                        }
                    }
                }
            }
            
            if (($this->Headers) && ($break != '0'))
            {
                fputs($fd, "\n");
                foreach ($this->Headers as $nCountBreak => $Header)
                {
                    $MarginHeader = $nCountBreak * 5;
                    if (!$this->ShowIndent)
                    {
                        $MarginHeader = 0;
                    }

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

                    fputs($fd, $this->Replicate(' ', $MarginHeader));
                    fputs($fd, "$Header\n");
                    fputs($fd, $this->Replicate(' ', $MarginHeader));
                    fputs($fd, $this->Replicate('=', strlen(trim($Header))) . "\n\n");
                }
                
                # DIFFERENCE
                /*fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");
                
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                for ($z=0; $z<=count($this->Columns); $z++)
                {
                    $Column = $this->Columns[$z];
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
                    {
                        fputs($fd, $this->FormatString($Column, $this->MaxLen[$z+1] +2));
                    }
                }
                fputs($fd, "\n" . $this->Replicate(' ', $MarginBreaks));
                fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n" );*/
            }
            
            if ($this->ShowDataColumns)
            {
                # DIFFERENCE
                //fputs($fd, $this->Replicate(' ', $MarginBreaks) . $stringline);
                fputs($fd, $stringline);
                fputs($fd, "\n" );
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
                    # DIFFERENCE
                    //fputs($fd, $this->Replicate(' ', $MarginBreaks));
                    //fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");
                    //fputs($fd, "\n");
                    
                    foreach ($FinalBreak as $FinalBreakLine)
                    {
                        $w = 0;
                        # DIFFERENCE
                        /*if ($this->ShowTotalLabel)
                        {

                            fputs($fd, ' (' . substr($this->Summary[$chave]['LastValue'] ,0, 11) . ')');
                            fputs($fd, $this->Replicate(' ', $MarginBreaks -14));
                        }
                        else
                        {
                            fputs($fd, $this->Replicate(' ', $MarginBreaks));
                        }*/
                        
                        //fputs($fd, $this->Replicate(' ', $MarginBreaks));
                        foreach($FinalBreakLine as $content)
                        {
                            $w ++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))  //aquipbreak
                            {
                                if ($content)
                                {
                                    # DIFFERENCE
                                    fputs($fd, $this->Replicate(' ', $MarginBreaks));
                                    if ($chave == '0')
                                        fputs($fd, '(Grand Total)');
                                    else
                                        fputs($fd, '(' . $this->Summary[$chave]['LastValue'] . ') :: ');

                                    fputs($fd, '[' . trim($this->Columns[$w-1]) . '] ' . $content);
                                    fputs($fd, "\n");
                                }
                                /*
                                else
                                {
                                    fputs($fd, $this->FormatString(' ', $this->MaxLen[$w] +2, 'right'));
                                }*/
                            }
                        }
                    }
                }
            }
        }
        
        /******************
        END OF LAST PROCESS
        *******************/
        
        
        fclose($fd);
        if ($this->posAction)
        {
            $this->ExecPosAction();
            Project::OpenReport($FileName, $this->agataConfig);
        }

        $this->UnSetReportLocale();        
        Wait::Off();
        
        return true;
    }
}
?>