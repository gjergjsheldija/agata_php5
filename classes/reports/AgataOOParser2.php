<?php
/* class AgataOOParser2
 * Jamiel Spezia 2006-2006
 */
include_once 'classes/util/AgataOO.php';
class AgataOOParser2 extends AgataMerge
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
        $this->config = AgataOO::GetConfig($source);

        $this->fixedDetails     = $this->XmlArray['Report']['OpenOffice']['Config']['FixedDetails'];
        $this->expandDetails    = $this->XmlArray['Report']['OpenOffice']['Config']['ExpandDetails'];
        $this->printEmptyDetail = $this->XmlArray['Report']['OpenOffice']['Config']['printEmptyDetail'];
        $this->sumByTotal       = $this->XmlArray['Report']['OpenOffice']['Config']['SumByTotal'];
        $this->repeatHeader     = $this->XmlArray['Report']['OpenOffice']['Config']['RepeatHeader'];
        $this->repeatFooter     = $this->XmlArray['Report']['OpenOffice']['Config']['RepeatFooter'];

        require_once 'vednor/pclzip/pclzip.lib.php';
        include_once 'include/util.inc';
        $this->buffer = array();
        $this->break_style = '<style:style style:name="AgataPageBreak" style:family="paragraph" style:parent-style-name="Standard">' .
                             '<style:properties fo:break-before="page"/>' .
                             '</style:style>' .
                             '<style:style style:name="AgataPageBreakO" style:family="paragraph" style:parent-style-name="Standard">' .
                             '<style:properties fo:break-before="page" text:display="true"/>' .
                             '</style:style>';


        //Usado para definir a quebra de p�gina
        $this->page_break = '<text:p text:style-name="AgataPageBreakO"/>';
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
                $line = $this->break_style . $this->user_style . $line;
            }

            if (AgataOO::isDrawLine($line))
            {
                $drawName = AgataOO::drawName($line);
                if ($this->imageReplace[$drawName])
                {
                    AgataOO::drawReplace($line, $this->imageReplace[$drawName], $this->prefix);
                }
            }
            $this->buffer[strtolower($section)][] = $line;
        }

        $output = implode('', $this->buffer['start']);


        //Substitui no cabe�alho
        if ($this->Parameters)
        {
            $headerContent = file_get_contents($this->prefix . '/styles.xml');

            $array_contentHeader = preg_split ('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', trim ($headerContent), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($array_contentHeader as $lineHeader)
            {
                foreach ($this->Parameters as $var=>$content)
                {
                    $lineHeader = str_replace($var, AgataOO::encode($content), $lineHeader);
                }
                $outputHeader .= $lineHeader;
            }
            $fd = fopen($this->prefix . '/styles.xml', 'w');
            fwrite($fd, $outputHeader);
            fclose($fd);
        }


        //Imprime cabe�alho do relat�rio uma vez no in�cio da p�gina
        $this->SubTotals = array();
        $output .= $this->printSection('reportHeader',  $line);
        # Percore os registros da consulta principal
        $break = false;
        $lineFatherQuery = 0;
        while ($line = $this->CurrentQuery->FetchNext())
        {
            # Ajusta as vari�veis globais para este registro
            for ($y=1; $y<=count($line); $y++)
            {
                $QueryCell = trim($line[$y]);
                $globalVars['$var' . $y] = AgataOO::encode(FormatMask($this->Adjustments[$y]['Mask'], $QueryCell));
            }

            //Imprime o cabe�alho da p�gina
            $this->SubTotals = array();
            $output .= $this->printSection('pageHeader',  $line, $break);

            //Imprime os dados
            $this->SubTotals = array();
            $output .= $this->printSection('data',  $line, $globalVars, $lineFatherQuery);

            //Imprime o cabe�alho da p�gina
            $this->SubTotals = array();
            $output .= $this->printSection('pageFooter',  $line);
            $break = true;
            $lineFatherQuery++;
        }
        $this->SubTotals = array();
        $output .= $this->printSection('reportFooter',  $line);

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
    function printSection($section, $data, $plus = false, $lineFatherQuery = 0)
    {
        $output = '';
        $section = strtolower($section);

        # Percore o array buffer para fazer as substitui��es
        for ($bufferLine = 0; $bufferLine < count($this->buffer[$section]); $bufferLine++)
        {
            $text_line = $this->buffer[$section][$bufferLine];

            # Quebra a p�gina, colocando o estilo de PageBreak
            # Na primeira linha de cada 'pageheader'
            if (($bufferLine == 0) and ($section == 'pageheader') and ($plus == true))
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

            if ( $section == 'data' )
            {
                $text_line = $this->parseRepeatTable($this->buffer[$section], $bufferLine, 0, $plus, $lineFatherQuery);
                if (!$text_line)
                {
                    $text_line = $this->buffer[$section][$bufferLine];
                }
                else
                {
                    $bufferLine--;
                }
            }
            # Ajusta as vari�veis globais para este registro
            for ($i=sizeof($data); $i>0; $i--)
            {
                $cell = $data[$i];
                $text_line = str_replace('$var' . $i, AgataOO::encode($cell), $text_line);
                $text_line = str_replace('[' . $this->CurrentQuery->ColumnNames[$i -1] . ']', utf8_encode($cell), $text_line);
            }

            # Search by barCode
            # Formato = {barcode_EAN128(var1)5x4}
            if (preg_match('/{barcode_[A-z0-9]*\([A-z0-9]*\)[0-9]x[0-9]}/', $text_line, $matches))
            {
                $text_line = str_replace($matches[0], $this->barCode($matches[0]), $text_line);
            }

            $text_line = $this->replaceTotal($text_line, 'subtotal', 0);
            $text_line = $this->replaceParameters($text_line);

            $output .= $text_line;
        }

        return $output;
    }

    /*
     * mothod parseRepeatTable
     *
     */
    function parseRepeatTable($buffer, &$bufferLine, $level, $globalVars, $lineFatherQuery, $fatherName=null)
    {
        $repeatTable = '<table:table table:name="RepeatTable_';
        $text_line = $buffer[$bufferLine];
        if (substr($text_line, 0, strlen($repeatTable)) == $repeatTable)
        {
            if ($level > 0)
            {
                $this->SubTotals[$level] = array();
            }
            # cria o modelo para repetir as linhas
            $begin = 0;
            while ($text_line != '</table:table>' && $bufferLine < count($buffer))
            {
                $text_line = $buffer[$bufferLine];
                $repeatCell = '<table:table-cell';
                if (substr($text_line, 0, strlen($repeatCell)) == $repeatCell)
                {
                    $lineCell = '';
                    $countCell = 0;
                    while ($text_line != '</table:table-cell>' || $countCell != 0)
                    {
                        $lineCell[] = $text_line = $buffer[$bufferLine];

                        # faz o controle para copiar no modelo as outras tabelas
                        $newCell = '<table:table-cell';
                        if (substr($text_line, 0, strlen($newCell)) == $newCell)
                        {
                            $countCell++;
                        }
                        if ($text_line == '</table:table-cell>')
                        {
                            $countCell--;
                        }
                        $bufferLine++;
                    }
                    $modelCell[] = $lineCell;
                    $begin = 1;
                }
                else
                {
                    # coloca em um buffer o que ser� repetido mais tarde
                    $modelTable[$begin] .= $buffer[$bufferLine];
                    $bufferLine++;
                }
            }

            #Verifica se est� utilizando o modo data ou query
            if ( $this->agataDB )
            {
                # Substitui��o de Par�metros
                $where = $this->XmlArray['Report']['Merge']['Details']['Detail1']['DataSet'.($level+1)]['Query']['Where'];
                krsort($globalVars);
                foreach ($globalVars as $var => $content)
                {
                    $where = str_replace($var, $content, $where);
                }
                $DataSet = $this->XmlArray['Report']['Merge']['Details']['Detail1']['DataSet'.($level+1)];
                $DataSet['Query']['Where'] = $where;

                # Cria Query
                $subQuery = AgataCore::CreateQuery($this->agataDB, $DataSet, $this->Parameters);
            }
            else
            {
                if ($this->subDataArray->subData)
                {

                    $subQuery = AgataCore::CreateSubQueryObject($this->subDataArray->subData[$level]);
                    $tmpFather = $fatherName . $lineFatherQuery;
                    $subQuery->SetFatherLineQuery($tmpFather);
                    $subDataName = $this->subDataArray->subData[$level][$tmpFather]->name;
                }
                else
                {
                    $subQuery = AgataCore::CreateSubQueryArray($this->subDataArray[$level]);
                    $subQuery->SetFatherLineQuery($lineFatherQuery);
                }
            }

            if (is_agata_error($subQuery))
            {
                new Dialog($subQuery->GetError());

                Wait::Off();
                return false;
            }
            else
            {
                $localVars = array();
                $levelTmp = null;
                if ($level > 0)
                {
                    $levelTmp = $level;
                }
                $x=0;
                while ($sub_row = $subQuery->FetchNext())
                {
                    $data[] = $sub_row;
                    foreach ($sub_row as $key=>$sR)
                    {
                        $localVars[$x]['$' . $levelTmp . 'subfield' . $key] = $sR;
                    }
                    $x++;
                }


                $text = $modelTable[0];
                $z=0;
                for ($x=0; $x<count($modelCell); $x++)
                {
                    $textDetail = $this->parseDetailTable($modelCell[$x], $data, $z, $level+1, $globalVars, $localVars, $bufferBreak, $subDataName);
                    if (!$textDetail)
                    {
                        $text .= implode('',$modelCell[$x]);
                    }
                    else
                    {
                        $text .= $textDetail;
                    }
                    //Verifica se terminou o modelo, se tem mais dados e se � para quebrar p�gina. Se tiver mais dados, terminou o modelo e n�o h� outra p�gina, ent�o, abre uma nova linha;
                    if ($x+1 == count($modelCell) && $data[$z+$back])
                    {
                        if (!$bufferBreak['content'])
                        {
                            $text .= '</table:table-row><table:table-row>';
                        }
                        //Tira um do X para repetir os dados
                        $x=-1;
                    }
                    //Se h� quebra de p�gina
                    if ($bufferBreak['content'])
                    {
                        $text .= $modelTable[1] . $bufferBreak['content'] . $modelTable[0];
                        //Conta as p�gina para a compara��o da quantidade de tabelas
                        $bufferBreak['page']++;
                        unset($bufferBreak['content']);
                        $x=-1;
                    }
                }
                $text .= $modelTable[1];
            }
            return $text;
        }
        return false;
    }

    /*
     * mothod parseDetailTable
     *
     */
    function parseDetailTable($content, $data, &$dataPosition, $level, $globalVars, $localVars, &$bufferBreak, $subDataName=null)
    {
        $existDetailTable = false;
        for ($x=0; $x<count($content); $x++)
        {
            $text_line = $content[$x];
            $detailTable = '<table:table table:name="DetailTable_';
            if (substr($text_line, 0, strlen($detailTable)) == $detailTable)
            {
                if ($existDetailTable)
                {
                    $dataPosition++;
                }

                //Extrai o nome da tabela
                $detailTableName = preg_replace('/' . $detailTable . '([^_]*)?.*/', '\1', $text_line);
                //Verifica de h� configura��o
                if ($this->config['break']['detailtable'][$detailTableName])
                {
                    if (!$bufferBreak['page'])
                    {
                        //Inicia o n�mero de p�ginas
                        $bufferBreak['page'] = 1;
                    }

                    //Se ultrapassou o n�mero definido de tabelas gera o conteudo
                    if (($this->config['break']['detailtable'][$detailTableName] * $bufferBreak['page']) == ($dataPosition+1) && $data[$dataPosition+1])
                    {
                        // Adiciona rodap�
                        $bufferBreak['content'] = $this->printSection('pageFooter',  $line);
                        // Adicona cabe�alho
                        $bufferBreak['content'] .= $this->printSection('pageHeader',  $line, true);
                    }
                }

                $globalVars_ = @array_merge($globalVars, $localVars[$dataPosition]);
                $existDetailTable = true;
                $controlOperation = array();
                # percore toda a tabela de detalhes para substituir os campos
                $countTable = 0;
                while ( $text_line != '</table:table>' || $countTable != 0)
                {
                    if ($data[$dataPosition])
                    {
                        $text_line = $this->parseRepeatTable($content, $x, $level, $globalVars_, $dataPosition, $subDataName);
                    }
                    else
                    {
                        $text_line = false;
                    }
                    if (!$text_line)
                    {
                        $text_line = $this->replaceDetails($content[$x], $data[$dataPosition], $level, $controlOperation);
                        $newTable = '<table:table table:name=';
                        if (substr($text_line, 0, strlen($newTable)) == $newTable)
                        {
                            $countTable++;
                        }
                        if ($text_line == '</table:table>')
                        {
                            $countTable--;
                        }
                    }
                    else
                    {
                        $x--;
                    }
                    if ($level > 0)
                    {
                        $text_line = $this->replaceTotal($text_line, 'subtotal', $level);
                    }
                    $x++;
                    # s� exibe a tabela se ela cont�m dados
                    if ($data[$dataPosition])
                    {
                        $text .= $text_line;
                    }
                }
                $x--;
            }
            else
            {
                $text .= $text_line;
            }
        }
        if ($existDetailTable)
        {
            $dataPosition++;
            return $text;
        }
        else
        {
            return false;
        }
    }

    /*
     * mothod parseDetailTable
     *
     */
    function replaceDetails($text_line, $data, $level, &$controlOperation)
    {
        if ($data)
        {
            $level--;
            $levelTmp = null;
            if ($level > 0)
            {
                $levelTmp = $level;
            }
            for ($x=sizeof($data); $x>0; $x--)
            {
                $this_text = '$' . $levelTmp . 'subfield' . $x;
                $subMyVar = $this_text;
                if (strpos($text_line, $subMyVar) !== false)
                {
                    $that_text = AgataOO::encode(FormatMask($this->SubAdjustments[$level][$x]['Mask'], $data[$x]));
                    # renomeia para n�o substituir a vari�vel opera��o
                    $text_line = str_replace($this_text . '_', 'AgataOperation_', $text_line);
                    $text_line = str_replace($this_text, $that_text, $text_line);
                    $text_line = str_replace('AgataOperation_', $this_text . '_', $text_line);

                    if (!$controlOperation[$subMyVar])
                    {
                        # Conta o n�mero de registros para cada vari�vel
                        $this->SubTotals[$level][$subMyVar]['count'] ++;
                        $this->GrandTotal[$level][$subMyVar]['count'] ++;
                        if (is_numeric($data[$x]))
                        {
                            $this->SubTotals[$level][$subMyVar]['sum'] += $data[$x];
                            $this->SubTotals[$level][$subMyVar]['min'] = ((!$this->SubTotals[$level][$subMyVar]['min']) or ($data[$x] < $this->SubTotals[$level][$subMyVar]['min'])) ? $data[$x] : $this->SubTotals[$level][$subMyVar]['min'];
                            $this->SubTotals[$level][$subMyVar]['max'] = ($data[$x] > $this->SubTotals[$level][$subMyVar]['max']) ? $data[$x] : $this->SubTotals[$level][$subMyVar]['max'];
                            $this->SubTotals[$level][$subMyVar]['avg'] = $this->SubTotals[$level][$subMyVar]['sum'] / $this->SubTotals[$level][$subMyVar]['count'];
    
                            $this->GrandTotal[$level][$subMyVar]['sum'] += $data[$x];
                            $this->GrandTotal[$level][$subMyVar]['min'] = ((!$this->GrandTotal[$level][$subMyVar]['min']) or ($data[$x] < $this->GrandTotal[$level][$subMyVar]['min'])) ? $data[$x] : $this->GrandTotal[$level][$subMyVar]['min'];
                            $this->GrandTotal[$level][$subMyVar]['max'] = ($data[$x] > $this->GrandTotal[$level][$subMyVar]['max']) ? $data[$x] : $this->GrandTotal[$level][$subMyVar]['max'];
                            $this->GrandTotal[$level][$subMyVar]['avg'] = $this->GrandTotal[$level][$subMyVar]['sum'] / $this->GrandTotal[$level][$subMyVar]['count'];
                        }
                        $controlOperation[$subMyVar] = true;
                    }
                }
            }
        }

        return $text_line;
    }

    /*
     * mothod replaceParameters
     *
     */
    function replaceParameters($line)
    {
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $var=>$content)
            {
                $line = str_replace($var, AgataOO::encode($content), $line);
            }
        }
        return $line;
    }

    /*
     * mothod replaceTotal
     *
     */
    function replaceTotal($text_line, $kind, $level)
    {
        # Substituir os SubTotais
        
        if ($kind == 'subtotal')
        {
            $matrix = $this->SubTotals[$level];
        }
        else if ($kind == 'grandtotal')
        {
            $matrix = $this->GrandTotal[$level];
        }

        if ($matrix)
        {
            foreach ($matrix as $var => $formulas)
            {
                foreach ($formulas as $formula => $content)
                {
                    $col = substr($var, 9); //pega o numero do campo
                    $content = FormatMask($this->SubAdjustments[$level][$col]['Mask'], $content);
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

    function SetUserStyle($style)
    {
        $this->user_style = $style;
    }

    function SetImageReplace($imageName, $imagePath)
    {
        $this->imageReplace[$imageName] = $imagePath;
    }
}
?>
