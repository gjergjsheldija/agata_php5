<?php

class AgataXml extends AgataReport {

    var $Query;
    var $Maior;
    var $Columns;
    var $FileName;
    var $ColumnTypes;

    function Multi($Char, $x) {
        for ($n = 1; $n <= $x; $n++) {
            $result .= $Char;
        }
        return $result;
    }

    function SlashTag($tag) {
        if (strpos($tag, ' ')) {
            for ($n = 0; $n <= strlen($tag); $n++) {
                if (substr($tag, $n, 1) == ' ')
                    break;
                $chars .= substr($tag, $n, 1);
            }
            $tag = $chars . '>';
        }

        return substr($tag, 0, 1) . '/' . substr($tag, 1);
    }

    function ChangeQuote($tag) {
        return str_replace('"', '\"', $tag);
    }

    function Process() {

        $ReportName = $this->ReportName;

        $FileName = $this->FileName;

        $fd = @fopen($FileName, "w");
        if (!$fd) {
            new Dialog(_a('File Error'));
            return false;
        }


        $this->SetReportLocale();

        if ($this->Breaks) {
            $CountBreaks = count($this->Breaks);
            if ($this->Breaks['0'])
                $CountBreaks--;

            ksort($this->Breaks);
            reset($this->Breaks);
        }

        if ($CountBreaks > 0) {
            $MarginBreaks = ($CountBreaks * 2) + 2;
        } else {
            $MarginBreaks = 2;
        }

        $config = file('include' . bar . 'output.xml');
        $ReportTag = trim($config[2]);
        $TitleTag = trim($config[3]);
        $HeaderTag = trim($config[5]);
        $HeaderRow = trim($config[6]);
        $HeaderCol = $this->ChangeQuote(trim($config[7]));
        $DataTag = trim($config[10]);
        $DataRow = trim($config[11]);
        $DataCol = $this->ChangeQuote(trim($config[12]));
        $FooterTag = trim($config[14]);
        $TotalRow = trim($config[15]);
        $TotalCol = $this->ChangeQuote(trim($config[16]));
        $GroupTag = $this->ChangeQuote(trim($config[19]));

        $ReportTag_ = $this->SlashTag($ReportTag);
        $TitleTag_ = $this->SlashTag($TitleTag);
        $HeaderTag_ = $this->SlashTag($HeaderTag);
        $HeaderRow_ = $this->SlashTag($HeaderRow);
        $DataTag_ = $this->SlashTag($DataTag);
        $DataRow_ = $this->SlashTag($DataRow);
        $FooterTag_ = $this->SlashTag($FooterTag);
        $TotalRow_ = $this->SlashTag($TotalRow);
        $GroupTag_ = $this->SlashTag($GroupTag);


        fputs($fd, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"no\"?>\n");
        fputs($fd, "$ReportTag\n");

        fputs($fd, "  $TitleTag $ReportName $TitleTag_\n");

        if ((!$this->Breaks) || ((count($this->Breaks) == 1) && ($this->Breaks['0']))) { //aquipbreak
            fputs($fd, "  $HeaderTag\n");
            fputs($fd, "    $HeaderRow\n");
            for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                $colnum = $z + 1;
                $Column = trim($this->Columns[$z]);
                eval("\$var = \"$HeaderCol\";");
                fputs($fd, "      $var\n");
            }
            fputs($fd, "    $HeaderRow_\n");
            fputs($fd, "  $HeaderTag_\n");
            fputs($fd, "  $DataTag\n");
        }


        while ($QueryLine = $this->CurrentQuery->FetchNext()) {
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = '';

            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------

            for ($y = 1; $y <= count($QueryLine); $y++) {
                $QueryCell = htmlspecialchars($QueryLine[$y]);

                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);

                //var_dump($this->Headers);
                if ($this->Headers)
                    $ReverseHeaders = array_reverse($this->Headers, true);

                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) { //aquipbreak
                    $colnum = $y;
                    $align = $this->Adjustments[$y]['Align'];

                    $stringline .= $this->Replicate(' ', $MarginBreaks);

                    eval("\$var = \"$DataCol\";");
                    $stringline .= "    $var\n";
                }
            }

            if (($this->BreakMatrix) && ($break != '0')) {
                $chaves = array_reverse(array_keys($this->BreakMatrix));
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "$DataTag_\n");
                foreach ($chaves as $chave) {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------
                    if ($this->HasFormula[$chave]) {
                        fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                        fputs($fd, "$FooterTag\n");
                        foreach ($FinalBreak as $FinalBreakLine) {
                            $w = 0;
                            fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                            fputs($fd, "  $TotalRow\n");

                            foreach ($FinalBreakLine as $content) {
                                $w++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                    $content = trim($content);
                                    if ($content) {
                                        $tmp = explode(':', $content);
                                        $formula = trim($tmp[0]);
                                        $Value = trim($tmp[1]);
                                        fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                                        $colnum = $w;
                                        //fputs($fd, "    <col type=\"total\" formula=\"$formula\" colnum=\"$colnum\"> $Value </col>\n");
                                        eval("\$var = \"$TotalCol\";");
                                        fputs($fd, "    $var\n");
                                    }
                                }
                            }
                            fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                            fputs($fd, "  $TotalRow_\n");
                        }
                        fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                        fputs($fd, "$FooterTag_\n");
                    }

                    // headers index is every (0, 1, 2, ...)
                    // chave may be any value (0, 2, ...)
                    if ($OpenHeaders) {
                        if ($ReverseHeaders) {
                            $key = $this->Association[$chave];
                            if ($chave != '0') {
                                $ReverseHeader = $ReverseHeaders[$key];
                                $ReverseHeaders[$key] = null;

                                $MarginHeader = ($key * 2) + 2;
                                fputs($fd, $this->Replicate(' ', $MarginHeader));
                                fputs($fd, "$GroupTag_\n");
                                $MarginHeader = (($key - 1) * 2) + 2;
                            }
                        }
                    }
                }
            }

            // if break has changed.
            if (($this->Headers) && ($break != '0')) {
                if ($OpenHeaders) {
                    foreach ($ReverseHeaders as $key => $ReverseHeader) {
                        if ($ReverseHeader) {
                            if ((!$this->Breaks['0']) || ($key != '0')) {
                                $MarginHeader = ($key * 2) + 2;
                                fputs($fd, $this->Replicate(' ', $MarginHeader));
                                fputs($fd, "$GroupTag_\n");
                            }
                        }
                    }
                }

                foreach ($this->Headers as $nCountBreak => $Header) {
                    $MarginHeader = ($nCountBreak * 2) + 2;
                    $OpenHeaders = true;
                    $MarginHeader = ($nCountBreak * 5) + 1;
                    $this->Index[$nCountBreak + 1]++;
                    $this->Index[$nCountBreak + 2] = 0;

                    $index = '';
                    for ($n = 1; $n <= $nCountBreak + 1; $n++) {
                        $index .= $this->Index[$n] . '.';
                    }
                    if ($this->ShowNumber) {
                        $Header = "{$index} {$Header}";
                    }

                    fputs($fd, $this->Replicate(' ', $MarginHeader));
                    $GroupLabel = trim($Header);
                    eval("\$var = \"$GroupTag\";");
                    fputs($fd, "$var\n");
                }

                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "$HeaderTag\n");
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "  $HeaderRow\n");

                for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                    $Column = trim($this->Columns[$z]);
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z + 1)])) { //aquipbreak
                        fputs($fd, $this->Replicate(' ', $MarginBreaks));
                        $colnum = $z + 1;
                        //fputs($fd, "    <col type=\"header\" colnum=\"$colnum\"> $Column </col>\n");
                        eval("\$var = \"$HeaderCol\";");
                        fputs($fd, "    $var\n");
                    }
                }
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "  $HeaderRow_\n");
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "$HeaderTag_\n");
                fputs($fd, $this->Replicate(' ', $MarginBreaks));
                fputs($fd, "$DataTag\n");
            }
            if ($this->ShowDataColumns) {
                if (trim($stringline)) {
                    fputs($fd, $this->Replicate(' ', $MarginBreaks));
                    fputs($fd, "  $DataRow\n");
                    fputs($fd, $stringline);
                    fputs($fd, $this->Replicate(' ', $MarginBreaks));
                    fputs($fd, "  $DataRow_\n");
                }
            }
        }


        /*         * ************************
          PROCESS TOTALS OF LAST LINE
         * ************************* */

        //------------------------
        $this->ProcessLastBreak();
        //------------------------

        if ($this->BreakMatrix) {
            $chaves = array_reverse(array_keys($this->BreakMatrix));
            fputs($fd, $this->Replicate(' ', $MarginBreaks));
            fputs($fd, "$DataTag_\n");

            foreach ($chaves as $chave) {
                //-----------------------------------------
                $FinalBreak = $this->EqualizeBreak($chave);
                //-----------------------------------------
                //fputs($fd, $this->Replicate(' ', $MarginBreaks));
                if (($this->HasFormula[$chave]) || ($chave == '0')) {
                    fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                    fputs($fd, "$FooterTag\n");

                    foreach ($FinalBreak as $FinalBreakLine) {
                        $w = 0;

                        fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                        fputs($fd, "  $TotalRow\n");

                        foreach ($FinalBreakLine as $content) {
                            $w++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                $content = trim($content);
                                if ($content) {
                                    $tmp = explode(':', $content);
                                    $formula = trim($tmp[0]);
                                    $Value = trim($tmp[1]);
                                    fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                                    $colnum = $w;
                                    //fputs($fd, "    <col type=\"total\" formula=\"$formula\" colnum=\"$colnum\"> $Value </col>\n");
                                    eval("\$var = \"$TotalCol\";");
                                    fputs($fd, "    $var\n");
                                }
                            }
                        }
                        fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                        fputs($fd, "  $TotalRow_\n");
                    }
                    fputs($fd, $this->Replicate(' ', $MarginHeader + 2));
                    fputs($fd, "$FooterTag_\n");
                }

                if ($OpenHeaders) {
                    if ($ReverseHeaders) {
                        $key = $this->Association[$chave];
                        if ($chave != '0') {
                            $ReverseHeader = $ReverseHeaders[$key];
                            $ReverseHeaders[$key] = null;

                            $MarginHeader = ($key * 2) + 2;
                            fputs($fd, $this->Replicate(' ', $MarginHeader));
                            fputs($fd, "$GroupTag_\n");
                            $MarginHeader = (($key - 1) * 2) + 2;
                        }
                    }
                }
            }
        }

        // if break has changed.
        if ($this->Headers) {
            if ($OpenHeaders) {
                foreach ($ReverseHeaders as $key => $ReverseHeader) {
                    if ($ReverseHeader) {
                        if ((!$this->Breaks['0']) || ($key != '0')) {
                            $MarginHeader = ($key * 2) + 2;
                            fputs($fd, $this->Replicate(' ', $MarginHeader));
                            fputs($fd, "$GroupTag_\n");
                        }
                    }
                }
            }
        }

        /*         * ****************
          END OF LAST PROCESS
         * ***************** */

        if (!$this->BreakMatrix) {
            fputs($fd, "  $DataTag_\n");
        }
        fputs($fd, "$ReportTag_\n");
        fclose($fd);
        if ($this->posAction) {
            $this->ExecPosAction();
            Project::OpenReport($FileName, $this->agataConfig);
        }

        $this->UnSetReportLocale();

        Wait::Off();

        return true;
    }

}

?>