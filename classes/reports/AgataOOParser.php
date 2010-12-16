<?php
/* class AgataOOParser
 * Jamiel Spezia 2005 - 2005
 */
include_once 'classes/util/AgataOO.php';
class AgataOOParser extends AgataMerge
{
    var $buffer;
    var $prefix;
    var $page_break;
    var $break_style;

    /* Constructor Method
     *
     */
    function Generate($source, $target)
    {
        $this->fixedDetails     = $this->XmlArray['Report']['OpenOffice']['Config']['FixedDetails'];
        $this->expandDetails    = $this->XmlArray['Report']['OpenOffice']['Config']['ExpandDetails'];
        $this->printEmptyDetail = $this->XmlArray['Report']['OpenOffice']['Config']['printEmptyDetail'];
        $this->sumByTotal       = $this->XmlArray['Report']['OpenOffice']['Config']['SumByTotal'];
        $this->repeatHeader     = $this->XmlArray['Report']['OpenOffice']['Config']['RepeatHeader'];
        $this->repeatFooter     = $this->XmlArray['Report']['OpenOffice']['Config']['RepeatFooter'];
        
        require_once 'classes/pclzip/pclzip.lib.php';
        include_once 'include/util.inc';
        $this->buffer = array();
        $this->break_style = '<style:style style:name="AgataPageBreak" style:family="paragraph" style:parent-style-name="Standard">' .
                             '<style:properties fo:break-before="page"/>' .
                             '</style:style>';
        
        $this->page_break = '<text:p text:style-name="AgataPageBreak"/>';
        $this->complement = array();
        if (!file_exists($source))
        {
            return;
        }
        
	$this->prefix = temp . bar . 'agata' . rand();
        $zip      = new PclZip($source);
        
        if (($list = $zip->listContent()) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }
        
        recursive_remove_directory($this->prefix);
        if ($zip->extract(PCLZIP_OPT_PATH, $this->prefix) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }
        
        $content= file_get_contents($this->prefix . '/content.xml');
        
        # break xml tags
        $array_content = preg_split ('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', trim ($content), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $section = 'start';
        foreach ($array_content as $line)
        {
            // <text:section text:style-name="Sect1" text:name="header">
            if (substr(trim($line), 0, 13) == '<text:section')
            {
                $pieces = explode('text:name="', $line);
                $section = substr($pieces[1], 0, -2);
            }
            else if (substr(trim($line), 0, 14) == '</office:body>')
            {
                $section = 'end';
            }
            
            if ($line == '</office:automatic-styles>')
            {
                $line = $this->break_style . $line;
            }
            
            $this->buffer[$section][] = $line;
        }
        
        $output = implode('', $this->buffer['start']);
        
        $break  = false;
        Wait::On();
        //Percore os registros da consulta pricipal
        while ($line = $this->CurrentQuery->FetchNext())
        {
            for ($y=1; $y<=count($line); $y++)
            {
                $QueryCell = trim($line[$y]);
                $vars['$var' . $y] = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);
            }
            
            # Substitui��o de Par�metros
            krsort($vars);
            $where = $this->XmlArray['Report']['Merge']['Details']['Detail1']['DataSet1']['Query']['Where'];
            foreach ($vars as $var => $content)
            {
                $where = str_replace($var, $content, $where);
            }
            $DataSet = $this->XmlArray['Report']['Merge']['Details']['Detail1']['DataSet1'];
            $DataSet['Query']['Where'] = $where;
            
            # Cria Query
            $subQuery = AgataCore::CreateQuery($this->agataDB, $DataSet, $this->Parameters);
            
            if (is_agata_error($subQuery))
            {
                new Dialog($subQuery->GetError());
                
                Wait::Off();
                return false;
            }
            else
            {
                $sub = array();
                $y=0;
                while ($sub_row = $subQuery->FetchNext())
                {
                    $sub[] = $sub_row;
                    /*$y++;
                    $QueryCell = trim($sub_row[$y]);
                    $vars['$subvar' . $y] = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);*/
                }
            }
            
            $this->GrandTotal = array();
            $this->firstPage  = true;
            $output .= $this->printSection('header',  $line, $break);
            if ($this->printEmptyDetail or $sub)
            {
                $output .= $this->printSection('details', $line, $sub);
            }
            $output .= $this->printSection('footer',  $line);
            
            $break = true;
            while ($this->rest)
            {
                $this->firstPage  = false;
                if ($this->repeatHeader)
                {
                    $output .= $this->printSection('header',  $line, $break);
                }
                else
                {
                    $output .= $this->page_break;
                }
                $output .= $this->printSection('details', $line, $this->rest);
                if ($this->repeatFooter)
                {
                    $output .= $this->printSection('footer',  $line);
                }
            }
        }
        
        $output .= implode('', $this->buffer['end']);
        $fd = fopen($this->prefix . '/content.xml', 'w');
        fwrite($fd, $output);
        fclose($fd);
        
        @unlink ($target);
        $zip2 = new PclZip($target);
        foreach ($list as $file)
        {
            $zip2->add($this->prefix . '/' . $file['filename'], PCLZIP_OPT_REMOVE_PATH, $this->prefix);
        }
        
        foreach ($this->complement as $file)
        {
            $zip2->add($this->prefix . '/' . $file['filename'], PCLZIP_OPT_REMOVE_PATH, $this->prefix);
        }
        $this->complement = null;
        Wait::Off();
        Project::OpenReport($target, $this->agataConfig);
        recursive_remove_directory($this->prefix);
    }

    /*
     * method printSection
     * 
     */
    function printSection($section, $data, $plus = false)
    {
        $output = '';
        if ($section == 'details')
        {
            $this->rest = null;
            $sub_data = $plus;
            $row = 1;
            $this->SubTotals = array();
        }
        
        $line    = 0;
        $sub_row = -1;
        $process = false;
        $totalFound = false;
        $details_stage = 0;
        $total_stage = 0;
        foreach ($this->buffer[$section] as $text_line)
        {
            for ($i=sizeof($data); $i>0; $i--)
            {
                $cell = $data[$i];
                $text_line = str_replace('$var' . $i, utf8_encode($cell), $text_line);
                $text_line = str_replace('[' . $this->CurrentQuery->ColumnNames[$i -1] . ']', utf8_encode($cell), $text_line);
            }
            
            # Search by barCode
            # Formato = {barcode_EAN128(var1)5x4}
            if (preg_match('/{barcode_[A-z0-9]*\([A-z0-9]*\)[0-9]x[0-9]}/', $text_line, $matches))
            {
                $text_line = str_replace($matches[0], $this->barCode($matches[0]), $text_line);
            }
            
            # Quebra a p�gina, colocando o estilo de PageBreak
            # Na primeira linha de cada 'header'
            if (($line == 1) and ($section == 'header') and ($plus == true))
            {
                //<text:p text:style-name="Standard">
                $begin = '<text:p text:style-name=';
                if (substr($text_line, 0, strlen($begin)) == $begin)
                {
                    # Faz o split para separar a expressao.
                    $pattern   = '/(<text:p text:style-name=".*")/';
                    $pieces    = preg_split($pattern, trim ($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                    $text_line = '<text:p text:style-name="AgataPageBreak">' . $pieces[1];
                }
                else
                {
                    $text_line .= $this->page_break;
                }
            }
            
            if ($section == 'details')
            {
                # Fim do cabe�alho, come�a a contar as linhas dos detalhes
                if (substr($text_line, 0, 26) == '</table:table-header-rows>')
                {
                    $process = true;
                }
                
                if ((substr($text_line, 0, 16) == '<table:table-row') and ($total_stage == 0))
                {
                    $total_stage = 1;
                    $totalContent = '';
                }
                
                if ($total_stage == 1)
                {
                    if ((strpos($text_line, '_sum') !== false) or (strpos($text_line, '_count') !== false) or
                        (strpos($text_line, '_min') !== false) or (strpos($text_line, '_max') !== false) or
                        (strpos($text_line, '_avg') !== false))
                    {
                        $totalFound = true;
                    }
                    $totalContent .= $text_line;
                }
                
                # substitui a linha template por todos registros
                if ((substr($text_line, 0, 18) == '</table:table-row>') and ($total_stage == 1))
                {
                    if ($totalFound)
                    {
                        $total_stage = 2;
                    }
                    else
                    {
                        $total_stage = 0;
                    }
                }
                
                if ($process)
                {
                    if ($this->fixedDetails)
                    {
                        if (substr($text_line, 0, 14) == '</table:table>')
                        {
                            $process = false;
                            for ($j = $sub_row; $j <= count($sub_data); $j ++)
                            {
                                $this->rest[] = $sub_data[$j];
                            }
                            
                            if (!$this->rest and $sub_data and !$this->firstPage and $this->sumByTotal)
                            {
                                $text_line = $totalContent . $text_line;
                                $text_line = $this->replaceTotal($text_line, 'grandtotal');
                            }
                        }
                        $text_line = $this->replaceTotal($text_line, 'subtotal');
                        if (substr($text_line, 0, 16) == '<table:table-row')
                        {
                            $sub_row ++;
                        }
                        
                        # Substituir as vari�veis
                        if ($sub_data[$sub_row])
                        {
                            $text_line = $this->replaceDetails($text_line, $sub_data[$sub_row]);
                        }
                        else
                        {
                            # Limpar ultimas linhas, quando j� passou do eof
                            for ($i = 20; $i >= 1; $i --)
                            {
                                $text_line = str_replace('$subfield' . $i, '', $text_line);
                            }
                        }
                    }
                    else
                    {
                        if ((substr($text_line, 0, 16) == '<table:table-row') and ($details_stage == 0))
                        {
                            $details_stage = 1;
                        }
                        
                        if ($details_stage == 1)
                        {
                            $detailsContent .= $text_line;
                            
                            if ($sub_data)
                            {
                                $text_line = $this->replaceDetails($text_line, $sub_data[0]);
                            }
                            else
                            {
                                # Limpar ultimas linhas, quando j� passou do eof
                                for ($i = 20; $i >= 1; $i --)
                                {
                                    $text_line = str_replace('$subfield' . $i, '', $text_line);
                                }
                            }
                        }
                        
                        # substitui a linha template por todos registros
                        if ((substr($text_line, 0, 18) == '</table:table-row>') and ($details_stage == 1))
                        {
                            $details_stage = 2;
                            if ($sub_data)
                            {
                                # Substituir os detalhes
                                $flexibleBuffer = '';
                                foreach ($sub_data as $key => $sub_line)
                                {
                                    if ($key>0)
                                    {
                                        
                                        $line_buffer = $detailsContent;
                                        $line_buffer = $this->replaceDetails($line_buffer, $sub_line);
                                        $flexibleBuffer .= $line_buffer;
                                    }
                                }
                                $text_line .= $flexibleBuffer;
                            }
                        }
                        
                        
                        if ($details_stage == 2)
                        {
                            $text_line = $this->replaceTotal($text_line, 'subtotal');
                            for ($i = 20; $i >= 1; $i --)
                            {
                                $text_line = str_replace('$subfield' . $i, '', $text_line);
                            }
                        }
                    }
                }
            }
            $output .= $text_line;
            $line ++;
        }
        //var_dump($detailsContent);
        return $output;
    }
    
    function replaceDetails($text_line, $line)
    {
        if ($line)
        {
            $col = 1;
            foreach ($line as $cell)
            {
                $this_text = '$subfield' . $col;
                $SubMyVar  = $this_text;
                
                if (strpos($text_line, $SubMyVar) !== false)
                {
                    $that_text = FormatMask($this->SubAdjustments[$col]['Mask'], $cell);
                    $text_line = AgataOO::encode(str_replace($this_text, $that_text, $text_line));

                    $this->SubTotals[$SubMyVar]['count'] ++;
                    $this->GrandTotal[$SubMyVar]['count'] ++;
                    if (is_numeric($cell))
                    {
                        $this->SubTotals[$SubMyVar]['sum'] += $cell;
                        $this->SubTotals[$SubMyVar]['min'] = ((!$this->SubTotals[$SubMyVar]['min']) or ($cell < $this->SubTotals[$SubMyVar]['min'])) ? $cell : $this->SubTotals[$SubMyVar]['min'];
                        $this->SubTotals[$SubMyVar]['max'] = ($cell > $this->SubTotals[$SubMyVar]['max']) ? $cell : $this->SubTotals[$SubMyVar]['max'];
                        $this->SubTotals[$SubMyVar]['avg'] = $this->SubTotals[$SubMyVar]['sum'] / $this->SubTotals[$SubMyVar]['count'];
                        
                        $this->GrandTotal[$SubMyVar]['sum'] += $cell;
                        $this->GrandTotal[$SubMyVar]['min'] = ((!$this->GrandTotal[$SubMyVar]['min']) or ($cell < $this->GrandTotal[$SubMyVar]['min'])) ? $cell : $this->GrandTotal[$SubMyVar]['min'];
                        $this->GrandTotal[$SubMyVar]['max'] = ($cell > $this->GrandTotal[$SubMyVar]['max']) ? $cell : $this->GrandTotal[$SubMyVar]['max'];
                        $this->GrandTotal[$SubMyVar]['avg'] = $this->GrandTotal[$SubMyVar]['sum'] / $this->GrandTotal[$SubMyVar]['count'];
                    }
                }
                $col ++;
            }
            $row ++;
        }
        return $text_line;
    }
    
    function replaceTotal($text_line, $kind)
    {
        # Substituir os SubTotais
        
        if ($kind == 'subtotal')
        {
            $matrix = $this->SubTotals;
        }
        else if ($kind == 'grandtotal')
        {
            $matrix = $this->GrandTotal;
        }
        
        if ($matrix)
        {
            foreach ($matrix as $var => $formulas)
            {
                foreach ($formulas as $formula => $content)
                {
                    $col = substr($var, 9); //pega o numero do campo
                    $content = FormatMask($this->SubAdjustments[$col]['Mask'], $content);
                    $text_line = str_replace("{$var}_{$formula}", $content, $text_line);
                }
            }
        }
        else
        {
            # Limpar os SubTotais
            for ($i = 20; $i >= 1; $i --)
            {
                $text_line = str_replace("\$subfield{$i}_sum",   '', $text_line);
                $text_line = str_replace("\$subfield{$i}_min",   '', $text_line);
                $text_line = str_replace("\$subfield{$i}_max",   '', $text_line);
                $text_line = str_replace("\$subfield{$i}_avg",   '', $text_line);
                $text_line = str_replace("\$subfield{$i}_count", '', $text_line);
            }
        }
        
        return $text_line;
    }
    
    
    /*
     * method barCode
     * 
     */
    function barCode($string)
    {
        include_once 'AgataBarCode.php';
        
        preg_match('/\([A-z0-9]*\)/', $string, $match1);  // "(var1)"
        preg_match('/[0-9]x[0-9]/',   $string, $match2);   // "5x4"
        preg_match('/_[A-z0-9]*\(/',  $string, $match3);// "_EAN128("
        
        list($width, $height) = explode('x', $match2[0]);
        $code   = substr($match1[0], 1, -1);
        $format = substr($match3[0], 1, -1);
        $x      = '0.14';
        $y      = '0.14';
        
        # begin BarCode #
        $p_xDim = 2;
        $p_w2n = 2;
        $p_charGap = $p_xDim;
        $p_invert = "N";
        $p_charHeight = 50;
        $rand = rand();
        $fileName = $this->prefix . '/Pictures/barcode' . $rand;
        
        $file['filename']         = "Pictures/barcode{$rand}.png";
        //$file['stored_filename']  = "/Pictures/barcode{$rand}.png";
        $this->complement[]       = $file;
        //$this->complement[]['size']             = "Pictures/{$fileName}";
        
        $standards['CODE39']    = BC_TYPE_CODE39;
        $standards['INTER25']   = BC_TYPE_INTER25;
        $standards['STD25']     = BC_TYPE_STD25;
        $standards['CODE93']    = BC_TYPE_CODE93;
        $standards['ROYMAIL4']  = BC_TYPE_ROYMAIL4;
        $standards['POSTNET']   = BC_TYPE_POSTNET;
        if (!$standards[$format])
        {
            return;
        }
        AgataBarCode::barCode(
            $standards[$format], // barcode type
            $code,               // text
            $p_xDim,
            $p_w2n,
            $p_charGap,
            false,               // inverted
            $p_charHeight,
            $p_type,
            true,                // show text label
            0,                   // rotate angle (disabled)
            true,                // check digit ?
            true,                // too file
            $fileName);
        
        # end BarCode #
        
        return "<draw:image draw:style-name=\"fr2\" draw:name=\"Graphic1\" text:anchor-type=\"paragraph\" svg:x=\"{$x}cm\" svg:y=\"{$y}cm\" svg:width=\"{$width}cm\" svg:height=\"{$height}cm\" draw:z-index=\"0\" xlink:href=\"#Pictures/barcode{$rand}.png\" xlink:type=\"simple\" xlink:show=\"embed\" xlink:actuate=\"onLoad\"/>";
    }
}
?>
