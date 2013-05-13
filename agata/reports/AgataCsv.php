<?php

class AgataCsv extends AgataReport {

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

    function Process() {
        $SpreadSoft = $this->agataConfig['general']['SpreadSoft'];
        $Delimiter = $this->agataConfig['general']['Delimiter'];
        $TxtDelimiter = $this->agataConfig['general']['TxtDelimiter'];

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

        $MarginBreaks = $CountBreaks * 5;

        fputs($fd, $TxtDelimiter . $ReportName . $TxtDelimiter . "\n");


        if ((!$this->Breaks) || ((count($this->Breaks) == 1) && ($this->Breaks['0']))) { //aquipbreak
            for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                $Column = $this->Columns[$z];
                fputs($fd, $TxtDelimiter . trim($Column) . $TxtDelimiter . $Delimiter);
            }
            fputs($fd, "\n");
        }

        while ($QueryLine = $this->CurrentQuery->FetchNext()) {
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = '';

            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------

            for ($y = 1; $y <= count($QueryLine); $y++) {
                $QueryCell = $QueryLine[$y];

                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);

                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) {
                    $stringline .= $TxtDelimiter . $QueryCell . $TxtDelimiter . $Delimiter;
                }
            }

            if (($this->BreakMatrix) && ($break != '0')) {
                $chaves = array_reverse(array_keys($this->BreakMatrix));

                foreach ($chaves as $chave) {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------

                    foreach ($FinalBreak as $FinalBreakLine) {
                        $w = 0;

                        fputs($fd, "\n");
                        if ($this->ShowTotalLabel) {
                            if ($chave == '0') {
                                fputs($fd, $TxtDelimiter . "(Grand Total)" . $TxtDelimiter . $Delimiter);
                            } else {
                                fputs($fd, $TxtDelimiter . "(" . $this->Summary[$chave]['BeforeLastValue'] . ")" . $TxtDelimiter . $Delimiter);
                            }

                            if ($this->ShowIndent) {
                                fputs($fd, $this->Multi($Delimiter, $CountBreaks - 1));
                            }
                        } else {
                            if ($this->ShowIndent) {
                                fputs($fd, $this->Multi($Delimiter, $CountBreaks));
                            }
                        }

                        foreach ($FinalBreakLine as $content) {
                            $w++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                if ($content) {
                                    fputs($fd, $TxtDelimiter . $content . $TxtDelimiter . $Delimiter);
                                } else {
                                    fputs($fd, "$Delimiter");
                                }
                            }
                        }
                        //fputs($fd, "\n");
                    }
                }
            }

            if (($this->Headers) && ($break != '0')) {
                foreach ($this->Headers as $nCountBreak => $Header) {
                    $MarginHeader = $nCountBreak * 5;

                    $this->Index[$nCountBreak + 1]++;
                    $this->Index[$nCountBreak + 2] = 0;

                    $index = '';
                    for ($n = 1; $n <= $nCountBreak + 1; $n++) {
                        $index .= $this->Index[$n] . '.';
                    }
                    if ($this->ShowNumber) {
                        $Header = "{$index} {$Header}";
                    }

                    fputs($fd, "\n");
                    if ($this->ShowIndent) {
                        fputs($fd, $this->Multi($Delimiter, $nCountBreak));
                    }
                    $Header = trim($Header);
                    fputs($fd, $TxtDelimiter . $Header . $TxtDelimiter . $Delimiter);
                }

                fputs($fd, "\n");
                if ($this->ShowIndent) {
                    fputs($fd, $this->Multi($Delimiter, $CountBreaks));
                }

                for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                    $Column = $this->Columns[$z];
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z + 1)])) { //aquipbreak
                        fputs($fd, $TxtDelimiter . $Column . $TxtDelimiter . $Delimiter);
                    }
                }
                //fputs($fd, "\n");
            }
            if ($this->ShowDataColumns) {
                if (trim($stringline)) {
                    fputs($fd, "\n");
                    if ($this->ShowIndent) {
                        fputs($fd, $this->Multi($Delimiter, $CountBreaks));
                    }
                    fputs($fd, $stringline);
                    //fputs($fd, "\n");
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

            foreach ($chaves as $chave) {
                //-----------------------------------------
                $FinalBreak = $this->EqualizeBreak($chave);
                //-----------------------------------------

                foreach ($FinalBreak as $FinalBreakLine) {
                    $w = 0;
                    fputs($fd, "\n");
                    if ($this->ShowTotalLabel) {
                        if ($chave == '0')
                            fputs($fd, $TxtDelimiter . "(Grand Total)" . $TxtDelimiter . $Delimiter);
                        else
                            fputs($fd, $TxtDelimiter . "(" . $this->Summary[$chave]['BeforeLastValue'] . ")" . $TxtDelimiter . $Delimiter);
                        if ($this->ShowIndent) {
                            fputs($fd, $this->Multi($Delimiter, $CountBreaks - 1));
                        }
                    } else {
                        if ($this->ShowIndent) {
                            fputs($fd, $this->Multi($Delimiter, $CountBreaks));
                        }
                    }

                    foreach ($FinalBreakLine as $content) {
                        $w++;
                        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                            if ($content) {
                                fputs($fd, $TxtDelimiter . $content . $TxtDelimiter . $Delimiter);
                            } else {
                                fputs($fd, "$Delimiter");
                            }
                        }
                    }
                    //fputs($fd, "\n");
                }
            }
        }


        /*         * ****************
          END OF LAST PROCESS
         * ***************** */


        fclose($fd);
        if ($this->posAction) {
            $this->ExecPosAction();
            Project::OpenReport($FileName, $this->agataConfig);
        }

        $this->UnsetReportLocale();


        return true;
    }

}

?>