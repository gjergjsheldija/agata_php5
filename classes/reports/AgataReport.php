<?php
class AgataReport
{
    /*
     * Set Properties
     */
    function SetProperties($params)
    {
        $AgataDir = $params[1]['general']['AgataDir'];
        $Query    = $params[3];
        
        $this->CurrentQuery     = $Query;
        $this->Query            = $Query->Query;
        $this->MaxLen           = $Query->MaxLen;
        $this->Columns          = $Query->Columns;
        $this->ColumnTypes      = $Query->ColumnTypes;
        $this->FunctionMatrix   = $Query->FunctionMatrix;
        $this->Parameters       = $Query->Parameters;

        $this->agataDB          = $params[0];
        $this->agataConfig      = $params[1];
        $this->FileName         = $params[2];
        
        $this->XmlArray         = $XmlArray = $params[4];
        $this->posAction        = $params[5];
        $this->layout           = $params[6];
        $this->ReportName       = $params[7];
        $this->encoding         = $params[8];
        
        $this->Adjustments      = CoreReport::ExtractAdjustments($XmlArray['Report']['DataSet']);
        $Breaks                 = CoreReport::ExtractBreaks($XmlArray);
        
        if ($Breaks)
        {
            foreach ($Breaks as $key => $formula)
            {
                if ($formula)
                {
                    $this->Breaks[$key] = $formula;
                }
                else
                {
                    $this->Breaks[$key] = true;
                }
            }
        }
        
        $this->ShowBreakColumns = $XmlArray['Report']['DataSet']['Groups']['Config']['ShowGroup'];
        $this->ShowDataColumns  = $XmlArray['Report']['DataSet']['Groups']['Config']['ShowDetail'];
        $this->ShowTotalLabel   = $XmlArray['Report']['DataSet']['Groups']['Config']['ShowLabel'];
        $this->ShowNumber       = $XmlArray['Report']['DataSet']['Groups']['Config']['ShowNumber'];
        $this->ShowIndent       = $XmlArray['Report']['DataSet']['Groups']['Config']['ShowIndent'];
        
        $this->textHeader       = $XmlArray['Report']['Header']['Body'];
        $this->alignHeader      = $XmlArray['Report']['Header']['Align'];
        $this->textFooter       = $XmlArray['Report']['Footer']['Body'];
        $this->alignFooter      = $XmlArray['Report']['Footer']['Align'];
        
        $this->FunctionNames    = CoreReport::GetFunctionNames();
        
        return true;
    }

    /*
     * Returns a Formatted String
     */
    function GetReportName()
    {
        $this->InputBox = new InputBox(_a('Type the Report Name'), 200);
        $this->InputBox->button->connect_object('clicked', array(&$this,'Process'), true);
    }
    
    /*
     * Returns a Formatted String
     */
    function FormatString($Expression, $Lenght, $alignKind = 'left')
    {
        $Brancos  = "                                             ";
        $Brancos .= $Brancos . $Brancos;

        $aligns['center'] = STR_PAD_BOTH;
        $aligns['left']   = STR_PAD_RIGHT;
        $aligns['right']  = STR_PAD_LEFT;

        if (strlen($Expression)>$Lenght)
            return substr($Expression,0,$Lenght);

        return str_pad($Expression, $Lenght, ' ', $aligns[$alignKind]);
    
        if ($alignKind == 'left')
        {
            return $Expression . substr($Brancos,0,$Lenght-strlen($Expression));
        }
        else if ($alignKind == 'center')
        {
            return substr($Brancos,0,($Lenght-strlen($Expression)) /2) .
            $Expression .
            substr($Brancos,0,($Lenght-strlen($Expression)) /2);
        }
        else if ($alignKind == 'right')
        {
            return substr($Brancos,0,$Lenght-strlen($Expression) -5) . $Expression . substr($Brancos,0,5);
        }
    }
    
    /******************************
    Returns a Replicatted Character
    *******************************/
    function Replicate($Expression, $Lenght)
    {
        for ($n=1; $n<=$Lenght; $n++)
        {
            $Return .= $Expression;
        }
        
        return $Return;
    }


    function ProcessBreaks($QueryLine)
    {
        if ($this->Breaks)
        {
            $CountBreak = 0;
            if ($this->Breaks['0'])
            {
                $CountBreak = -1;
            }
            foreach ($this->Breaks as $break => $Formulafull)
            {
                $Formulas = MyExplode($Formulafull, null);
                
                // if ($break == $y)
                {
                    $ClearColumns = null;
                    # Change in the algorithm: Instead of analize the last break change,
                    # analize all break changes.
                    $this->Summary[$break]['ActualValue'] = $QueryLine[$break];
                    
                    # Hey !! QueryCell = QueryLine[$y]
                    
                    if (($this->Summary[$break]['ActualValue'] != $this->Summary[$break]['LastValue']) or ($this->lastchanged < $break))
                    {
                        $this->Summary[$break]['Count'] ++;
                        $this->lastchanged = $break;
                        
                        foreach ($Formulas as $Formula)
                        {
                            //$this->HasFormula[$break] = ($formula) ? true : false;
                            
                            #PARTE NOVA
                            $this->HasFormula[$break] = true;
                            
                            if ($this->Summary[$break]['LastValue'])
                            {
                                list($result, $column, $formula, $label, $columns) = $this->ProcessFormula($break, $Formula);
                                
                                $cellBreakContent = _a($this->FunctionNames[$formula]) . ": $result";
                                
                                # Custom Total Label
                                if (is_string ($label))
                                {
                                    if ($label) 
                                    {
                                        $cellBreakContent =  $label . ": $result";
                                    }
                                    else
                                    {
                                        $cellBreakContent =  $result;
                                    }
                                }
                                
                                $this->BreakMatrix[$break][$column][] = $cellBreakContent;
                                $ClearColumns = @array_merge($ClearColumns, $columns);
                            }
                            $FormattedCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryLine[$break]);
                            $this->Headers[$CountBreak] = trim($this->Columns[$y -1]) . " : " . trim($FormattedCell);
                            $this->Association[$break] = $CountBreak;
                        }
                        $this->Summary[$break + 1]['Count'] = 0;  // reinicializa contador de sub-quebras
                    }
                    
                    if ($ClearColumns)
                    {
                        # Quando tem uma quebra, limpa tudo, mas totaliza os gmax e gmin
                        foreach ($ClearColumns as $ClearColumn)
                        {
                            $this->Summary[$break][$ClearColumn]['gmax'] += $this->Summary[$break][$ClearColumn]['max'];
                            $this->Summary[$break][$ClearColumn]['gmin'] += $this->Summary[$break][$ClearColumn]['min'];
                            $gmax = $this->Summary[$break][$ClearColumn]['gmax'];
                            $gmin = $this->Summary[$break][$ClearColumn]['gmin'];
                            $this->Summary[$break][$ClearColumn] = null;
                            $this->Summary[$break][$ClearColumn]['gmax'] = $gmax;
                            $this->Summary[$break][$ClearColumn]['gmin'] = $gmin;
                        }
                    }
                    
                    $this->Summary[$break]['BeforeLastValue'] = $this->Summary[$break]['LastValue'];
                    $this->Summary[$break]['LastValue'] = $QueryLine[$break];
                }
                
                $count = 1;
                foreach ($QueryLine as $Cell)
                {
                    if (strstr($Formulafull, "($count)"))
                    {
                        $this->Summary[$break][$count]['sum'] += $Cell;
                        $this->Summary[$break][$count]['count'] ++;
                        $this->Summary[$break][$count]['max'] = ($Cell > $this->Summary[$break][$count]['max']) ? $Cell : $this->Summary[$break][$count]['max'];
                        
                        if (!$this->Summary[$break][$count]['min'])
                        {
                            $this->Summary[$break][$count]['min'] = $Cell;
                        }
                        
                        $this->Summary[$break][$count]['min'] = ($Cell < $this->Summary[$break][$count]['min']) ? $Cell : $this->Summary[$break][$count]['min'];
                    }
                    $count ++;
                }
                $CountBreak ++;
            }
        } // end if Breaks
        
        return array($break);
    }
    
    
    /******************************************
    Makes the Totalization after the last line
    *******************************************/
    function ProcessLastBreak()
    {
        $this->Headers = null;
        $this->BreakMatrix = null;
        if ($this->Breaks)
        {
            $CountBreak = 0;
            foreach ($this->Breaks as $Break => $Formulafull)
            {
                $break = $Break;
                $Formulas = MyExplode($Formulafull, null);
                
                $ClearColumns = null;
                foreach ($Formulas as $Formula)
                {
                    list($result, $column, $formula, $label, $columns) = $this->ProcessFormula($break, $Formula);
                    
                    $cellBreakContent = _a($this->FunctionNames[$formula]) . ": $result";
                    
                    # Custom Total Label
                    if (is_string ($label))
                    {
                        if ($label) 
                        {
                            $cellBreakContent =  $label . ": $result";
                        }
                        else
                        {
                            $cellBreakContent =  $result;
                        }
                    }
                    
                    $this->BreakMatrix[$break][$column][] = $cellBreakContent;
                    
                    $ClearColumns = @array_merge($ClearColumns, $columns);
                    
                    $this->Headers[$CountBreak] = trim($this->Columns[$y -1]) . " : " . trim($QueryCell);
                }
                
                if ($ClearColumns)
                {
                    foreach ($ClearColumns as $ClearColumn)
                    {
                        $this->Summary[$break][$ClearColumn]['gmax'] += $this->Summary[$break][$ClearColumn]['max'];
                        $this->Summary[$break][$ClearColumn]['gmin'] += $this->Summary[$break][$ClearColumn]['min'];
                        $gmax = $this->Summary[$break][$ClearColumn]['gmax'];
                        $gmin = $this->Summary[$break][$ClearColumn]['gmin'];
                        $this->Summary[$break][$ClearColumn] = null;
                        $this->Summary[$break][$ClearColumn]['gmax'] = $gmax;
                        $this->Summary[$break][$ClearColumn]['gmin'] = $gmin;
                    }
                }
                
                $CountBreak ++;
            }
        } // end if Breaks
    }

    /*
     * Process Formula
     */
    function ProcessFormula($break, $Formula)
    {
        /*
            "sum(5) as 'soma'"
            "count(4)"
            "(sum(2) / max(7)* 10 + (200/2) as 'pre�o total'"
        */
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $parameter => $value)
            {
                $Formula = str_replace($parameter, $value, $Formula);
            }
        }
        
        if (strstr($Formula, ' mask '))
        {
            $mask_pos = strpos($Formula, ' mask ');
            $mask     = substr($Formula, $mask_pos +7, -1);
            $Formula  = substr($Formula, 0, $mask_pos);
        }
        if (strstr($Formula, ' as '))
        {
            # Custom Total Label
            $as_pos = strpos($Formula, ' as ');
            if ($as_pos !== false)
            {
                $label = substr($Formula, $as_pos+5, -1);
            }
            else
            {
                $label = false;
            }
        }
        $pieces = explode(' as ', $Formula);
        $Formula = $pieces[0];
        $pieces = extractFunctions($Formula);
        if ($pieces)
        {
            foreach ($pieces as $Expression)
            {
                $formula= trim($Expression[0]);
                $column = trim($Expression[1]);
                $firstcol = !$firstcol ? $column : $firstcol;
                $Formula = str_replace("$formula($column)", $this->Summarize($break, $column, $formula), $Formula);
                $columns[] = $column;
            }
            
            eval("\$result = $Formula;");
            
            if ($mask)
            {
                $result = FormatMask($mask, $result);
            }
            else if ($formula != 'count') // count nao vai mascara
            {
                $result = FormatMask($this->Adjustments[$firstcol]['Mask'], $result);
            }
        }
        return array($result, $firstcol, $formula, $label, $columns);
    }

    /*
     * Summarize
     */
    function Summarize($break, $column, $formula)
    {
        if ($formula == 'avg')
        {
            # Normal average
            $result =  $this->Summary[$break][$column]['sum'] /
                       $this->Summary[$break][$column]['count'];
        }
        else if ($formula == 'gavg')
        {
            for ($n=$break + 1; $n<=count($this->Columns); $n++)
            {
                if ($this->Summary[$n]['Count']) // se h� um sub-n�vel
                {
                    $result =  $this->Summary[$break][$column]['sum'] /
                               $this->Summary[$n]['Count'];
                    break;
                }
            }
            
            if (!$result)
            {
                # Normal average
                $result =  $this->Summary[$break][$column]['sum'] /
                           $this->Summary[$break][$column]['count'];
            }
        }
        else if ($formula == 'gmax')  // M�dia dos m�ximos de cada quebra
        {
            for ($n=$break + 1; $n<=count($this->Columns); $n++)
            {
                if ($this->Summary[$n]['Count']) // se h� um sub-n�vel
                {
                    $result =  $this->Summary[$n][$column]['gmax'];
                    break;
                }
            }
            
            if (!$result)
            {
                # Normal Max
                $result =  $this->Summary[$break][$column]['max'];
            }
        }
        else if ($formula == 'gmin') // M�dia dos m�nimos de cada quebra
        {
            for ($n=$break + 1; $n<=count($this->Columns); $n++)
            {
                if ($this->Summary[$n]['Count']) // se h� um sub-n�vel
                {
                    $result =  $this->Summary[$n][$column]['gmin'];
                    break;
                }
            }
            
            if (!$result)
            {
                # Normal Min
                $result =  $this->Summary[$break][$column]['min'];
            }
        }
        else
        {
            $result = $this->Summary[$break][$column][$formula];
        }
        return $result;
    }
    
    function ExecPosAction()
    {
        $obj = &$this->posAction[0];
        $att = &$this->posAction[1];
        $obj->{$att}();
    }
    
    /***********************************************************
     * Conditional Formatting
     ***********************************************************/
    function EvalConditional($expression, $QueryCell, $QueryLine)
    {
        $cond_pieces = explode(';', $expression);
        $i = 1;
        foreach ($cond_pieces as $piece)
        {
            if (trim($piece))
            {
                $cond_high  = splitCondHigh($piece);
                if ($cond_high)
                {
                    $function_name     = AgataCore::requireFunction($cond_high['function']);
                    $conditionalResult = ($function_name($QueryCell, $QueryLine) == $cond_high['result']);
                    if ($conditionalResult)
                    {
                        return $cond_high;
                    }
                }
            }
        }
        return false;
    }
    
    
    /**********************************************************
    This Function Equilize the GroupResults
    ***********************************************************/
    function EqualizeBreak($chave)
    {
        $Biggest = 0;
        $FinalBreak = null;
        $linebreak = $this->BreakMatrix[$chave];
        
        foreach ($linebreak as $tmp)
        {
            $Len = count($tmp);
            if ($Len > $Biggest)
            $Biggest = $Len;
        }
        
        for ($w=1; $w<=count($this->Columns); $w++)
        {
            $contents = $linebreak[$w];
            if (!$contents)
            $contents = array('');
            
            $contents = array_pad ($contents, $Biggest, '');
            $wline = 0;
            foreach ($contents as $content)
            {
                $FinalBreak[$wline][] = $content;
                $wline ++;
            }
        }
        return $FinalBreak;
    }

    function SetReportLocale()
    {
        setlocale(LC_ALL, 'POSIX');
    }

    function UnsetReportLocale()
    {
        if (OS == 'WIN')
        {
            setlocale(LC_ALL, 'english');
        }
        else
        {
            setlocale(LC_ALL, 'pt_BR');
        }
    }
}
?>
