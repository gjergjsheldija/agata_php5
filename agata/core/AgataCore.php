<?php

/* * **************************************************************************** */
/* Agata Core - Class that creates the queries and calls
  /* the report classes
  /* by Pablo Dall'Oglio - 2001 - 2006
  /*    Jamiel Spezia - 2006 - 2006
  /****************************************************************************** */

class AgataCore {
    /*     * **************************************************************************** */
    /* Creates a Report
      /****************************************************************************** */

    function CreateReport($type, $params) {
        $class = 'Agata' . strtoupper(substr($type, 0, 1)) . substr($type, 1);
        include_once AGATA_PATH . "/agata/reports/{$class}.php";
        $obj = new $class;
        $obj->SetProperties($params);
        return $obj;
    }

    /*     * **************************************************************************** */
    /* Creates a merged document
      /****************************************************************************** */

    function CreateMergedDocument($params, $kind) {
        include_once AGATA_PATH . '/agata/reports/AgataMerge.php';
        include_once AGATA_PATH . "/agata/reports/AgataMerge{$kind}.php";
        $class = "AgataMerge$kind";
        $obj = new $class;
        $obj->SetProperties($params);
        return $obj;
    }

    /*     * **************************************************************************** */
    /* Parse an OpenOffice Document
      /****************************************************************************** */

    function ParseOpenOffice($params, $kind, $engine=null) {
        include_once AGATA_PATH . '/agata/reports/AgataMerge.php';
        include_once AGATA_PATH . '/agata/reports/AgataOOParser' . $engine . '.php';

        $class = "AgataOOParser" . $engine;
        $obj = new $class;
        $obj->SetProperties($params);
        return $obj;
    }

    /*     * **************************************************************************** */
    /* Creates an address label
      /****************************************************************************** */

    function CreateAddressLabel($params) {
        include_once AGATA_PATH . '/agata/reports/AgataMerge.php';
        include_once AGATA_PATH . '/agata/reports/AgataLabel.php';
        $obj = new AgataLabel;
        $obj->SetProperties($params);
        return $obj;
    }

    /*     * **************************************************************************** */
    /* Creates a graph
      /****************************************************************************** */

    function CreateGraph($params) {
        include_once AGATA_PATH . '/agata/reports/AgataGraph.php';
        $obj = new AgataGraph;
        $obj->SetProperties($params);
        return $obj;
    }

    /*     * ***************************************************************************** */
    /* Creates a query array object
      /******************************************************************************* */

    function CreateQueryArray($array) {
        if (is_null($array)) {
            $data[0][0] = '';
        }

        if (is_array($array)) {
            $x = 0;
            foreach ($array as $line) {
                $y = 1;
                foreach ($line as $col) {
                    $newArray[$x][$y] = $col;
                    $y++;
                }
                $x++;
            }
            $CurrentQuery = new AgataQueryArray($newArray);
            return $CurrentQuery;
        } else {
            return new AgataError(_a('Variable is nor array'));
        }
    }

    /*     * ***************************************************************************** */
    /* Creates a query array object
      /******************************************************************************* */

    function CreateSubQueryArray($array) {
        if (is_null($array)) {
            $array[0][0][0] = '';
        }

        if (is_array($array)) {
            $z = 0;
            foreach ($array as $vector) {
                $x = 0;
                if ($vector) {
                    foreach ($vector as $line) {
                        $y = 1;
                        foreach ($line as $col) {
                            $newArray[$z][$x][$y] = $col;
                            $y++;
                        }
                        $x++;
                    }
                    $z++;
                } else {
                    $newArray[$z][0][0] = $col;
                }
            }
            $CurrentSubQuery = new AgataSubQueryArray($newArray);
            return $CurrentSubQuery;
        } else {
            return new AgataError(_a('Variable is nor array'));
        }
    }

    /*     * ***************************************************************************** */
    /* Creates a query array object
      /******************************************************************************* */

    function CreateSubQueryObject($array) {
        /*        if (is_null($array))
          {
          $array[0][0][0] = '';
          }

          } */

        if (is_array($array) || true) {
            $z = 0;
            foreach ($array as $keyZ => $obj) {
                $x = 0;
                if ($obj->data) {
                    foreach ($obj->data as $line) {
                        //Colunas come�am em 1 obrigatoriamente. Com isso � montado a vari�vel
                        $y = 1;
                        foreach ($line as $col) {
                            $newArray[$keyZ]->data[$x][$y] = $col;
                            $y++;
                        }
                        $x++;
                    }
                    $z++;
                }
            }

            $CurrentSubQuery = new AgataSubQueryObject($newArray);
            return $CurrentSubQuery;
        } else {
            return new AgataError(_a('Variable is nor array'));
        }
    }

    /*     * **************************************************************************** */
    /* Creates a query object
      /****************************************************************************** */

    function CreateQuery($agataDB, $DataSet, $ParametersContent) {
        $sql = CoreReport::SqlFromReport($DataSet);
        $Adjustments = CoreReport::ExtractAdjustments($DataSet);

        if ($ParametersContent) {
            // sort to $teste2 comes before that $teste in replace
            krsort($ParametersContent);
            foreach ($ParametersContent as $Parameter => $Content) {
                # Fill the Paramenter content with '' around.
                if ((substr($Content, 0, 1) != "'") and (substr($Content, 0, -1) != "'")) {
                    $Content = "'$Content'";
                }
                $sql = str_replace($Parameter, $Content, $sql);
                $NewParametersContent[$Parameter] = $Content;
            }
        }
        if ($sql) {
            $conn = new AgataConnection;
            if ($conn->Open($agataDB)) {
                $query = $conn->CreateQuery($sql);
                $query->SetOffSet($DataSet['Query']['Config']['OffSet']);
                $query->SetLimit($DataSet['Query']['Config']['Limit']);

                $result = $query->result;
                if (!$result) {
                    $conn->Close();
                    //Wait::Off();
                    return new AgataError(_a('Query Error') . ': ' . $sql);
                }

                $ColumnCount = $query->GetColumnCount();
                $ColumnNames = $query->GetColumnNames();
                $ColumnTypes = $query->GetColumnTypes();

                $i = 1;
                foreach ($Adjustments as $column => $Adjustment) {
                    foreach ($Adjustment as $key => $content) {

                        if (($key == 'Function') and $content) {
                            $function_name = AgataCore::requireFunction($content);
                            $function_matrix[$i] = $function_name;
                        } elseif (($key == 'Fross') and $content) {
                            $cut_hash = explode('#', $content);
                            $database = substr($cut_hash[0], 1);

                            $expr1 = explode('(', $cut_hash[1]);
                            $get_stuffs = explode('.', $expr1[0]);
                            $get_table = $get_stuffs[0];
                            $get_field = $get_stuffs[1];

                            $equ_expr = substr($expr1[1], 0, -1);
                            $connections[] = $database;

                            $definitions = Project::ReadProjectDefinitions($database);
                            $index = "table:$get_table:field:$get_field";
                            $nickname = $definitions[3][$index];
                            if ($nickname) {
                                $ColumnNames[$i - 1] = $nickname;
                            }
                            $cross_matrix[$i] = array($database, $get_table, $get_field, $equ_expr);
                        }
                    }
                    $i++;
                }
                if ($cross_matrix) {
                    $projects = Project::ReadProjects();
                    $connections = array_unique($connections);
                    foreach ($connections as $connection) {
                        $ext_conn[$connection] = new AgataConnection;
                        if (!$ext_conn[$connection]->Open($projects[$connection], false)) {
                            $ext_conn[$connection] = null;
                        }
                    }
                }

                $CurrentQuery = new AgataDataSet;
                $CurrentQuery->FunctionMatrix = $function_matrix;
                $CurrentQuery->CrossMatrix = $cross_matrix;
                $CurrentQuery->ColumnCount = $ColumnCount;
                $CurrentQuery->ColumnNames = $ColumnNames;
                $CurrentQuery->ColumnTypes = $ColumnTypes;
                $CurrentQuery->Columns = $ColumnNames;
                $CurrentQuery->ext_conn = $ext_conn;
                $CurrentQuery->result = $query;
                $CurrentQuery->Parameters = $NewParametersContent;
                //$this->CurrentQuery           = $CurrentQuery;
                //$conn->Close();
                //@Wait::Off();
                return $CurrentQuery;
            } else {
                return new AgataError(_a('Cannot connect to Database'));
            }
        } else {
            return new AgataError(_a('Query Error') . BREAKLN . $sql);
        }
    }

    /*     * **************************************************************************** */
    /* Requires an Agata Function
      /****************************************************************************** */

    function requireFunction($function) {
        $function_dir = AGATA_PATH . 'assets/functions';
        $function_path = $function_dir . str_replace('/', bar, $function);
        $function_divide = explode('/', $function);
        $function_file = $function_divide[count($function_divide) - 1];
        $function_dots = explode('.', $function_file);
        $function_name = $function_dots[0];

        if (!function_exists($function_name)) {
            include_once($function_path);
        }

        return $function_name;
    }

}

?>
