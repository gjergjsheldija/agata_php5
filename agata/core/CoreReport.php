<?php

/* * ******************************************************** */
/* Class to deal with Report Files
  /* by Pablo Dall'Oglio 2001-2006
  /********************************************************** */

class CoreReport {
    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function OpenReport($FileName) {
        $Report['Report'] = Xml2Array($FileName);
        return $Report;
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function OpenSql($FileName) {
        $Report = Xml2Array($FileName);
        return $Report;
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function SaveReport($FileName, $XmlArray) {
        $fd = @fopen($FileName, "w");
        if (!$fd) {
            new Dialog(_a('Permission Denied'), true, false, _a('File') . ': ' . $FileName);
            return false;
        }
        fwrite($fd, trim(XMLHEADER . Array2Xml($XmlArray)));
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function BlockToSql($Block, $distinct = false, $break = false) {
        $sql = '';
        $ln = $break ? "\n" : '';
        foreach ($Block as $Clause) {
            if ($Clause[1]) {
                if ($Clause[0] == 'Select') {
                    if ($distinct) {
                        $sql .= $Clause[0] . ' distinct ' . $Clause[1] . ' ';
                    } else {
                        $sql .= $Clause[0] . ' ' . $Clause[1] . ' ';
                    }
                } else {
                    $sql .= $ln . $Clause[0] . ' ' . $Clause[1] . ' ';
                }
            }
        }

        return $sql;
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function SqlToBlock($sql) {
        $sqlteste = strtoupper($sql);

        if (strpos($sqlteste, ' ORDER BY ') !== false) {
            $pieces = WordExplode(' ORDER BY ', $sql);
            $orderby = $pieces[1];
            $sql = $pieces[0];
        }


        if (strpos($sqlteste, ' GROUP BY ') !== false) {
            $pieces = WordExplode(' GROUP BY ', $sql);
            $groupby = $pieces[1];
            $sql = $pieces[0];
        }

        if (strpos($sqlteste, ' WHERE ') !== false) {
            $pieces = WordExplode(' WHERE ', $sql);
            $where = $pieces[1];
            $sql = $pieces[0];
        }

        if (strpos($sqlteste, ' FROM ') !== false) {
            $pieces = WordExplode(' FROM ', $sql);
            $from = $pieces[1];
            $sql = $pieces[0];
        }

        if (strpos($sqlteste, 'SELECT ') !== false) {
            $pieces = WordExplode('SELECT ', $sql);
            $select = $pieces[1];
            $sql = $pieces[0];
        }


        $Block['Select'] = array('Select', $select);
        $Block['From'] = array('From', $from);
        $Block['Where'] = array('Where', $where);
        $Block['Group by'] = array('Group by', $groupby);
        $Block['Order by'] = array('Order by', $orderby);
        return $Block;
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function SqlFromReport($DataSet) {
        return CoreReport::BlockToSql(CoreReport::ExtractBlock($DataSet), $DataSet['Query']['Config']['Distinct'], false);
    }

    /*     * ******************************************************** */
    /*
      /********************************************************** */

    function ExtractBlock($DataSet) {
        $Block['Select'] = array('Select', $DataSet['Query']['Select']);
        $Block['From'] = array('From', $DataSet['Query']['From']);
        $Block['Where'] = array('Where', $DataSet['Query']['Where']);
        $Block['Group by'] = array('Group by', $DataSet['Query']['GroupBy']);
        $Block['Order by'] = array('Order by', $DataSet['Query']['OrderBy']);

        return $Block;
    }

    function ExtractAdjustments($DataSet) {
        $adjustments = $DataSet['Fields'];

        if ($adjustments) {
            foreach ($adjustments as $index => $content) {
                foreach ($content as $key => $value) {
                    $index_ = substr($index, 6);
                    $Adjustments[$index_][$key] = $value;
                }
            }
        }
        return $Adjustments;
    }

    function ExtractBreaks($Report) {
        if ($groups = $Report['Report']['DataSet']['Groups']['Formulas']) {
            foreach ($groups as $key => $formula) {
                $break = substr($key, 5);
                $Breaks[$break] = trim($formula);
            }
        }
        return $Breaks;
    }

    function GetFunctionNames() {
        return array('count' => 'Count', 'sum' => 'Sum',
            'gavg' => 'Group Average', 'avg' => 'Average',
            'min' => 'Minimal', 'max' => 'Maximal',);
    }

    function TranslateFormulas($Select, $Formula) {
        $Elements_ = MyExplode(trim($Select), null, true);
        $Formulas = MyExplode($Formula);
        $Functions = CoreReport::GetFunctionNames();

        for ($n = 0; $n <= count($Formulas) - 1; $n++) {
            foreach ($Functions as $function => $name) {
                $Formulas[$n] = str_replace($function, _a($name), $Formulas[$n]);
            }
            $function = $Formulas[$n];
            $pieces1 = explode('(', $function);
            $pieces2 = explode(')', $pieces1[1]);
            $index = $pieces2[0];
            $Formulas[$n] = str_replace($index, $Elements_[$index], $function);
        }
        return $Formulas;
    }

}

?>