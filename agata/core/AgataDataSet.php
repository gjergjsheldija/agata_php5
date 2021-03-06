<?php
/*******************************************************************************/
/* AgataDataSet - Class that means the query itself and
/* its funcionalities
/* by Pablo Dall'Oglio - 2001 - 2006
/*******************************************************************************/
class AgataDataSet
{
    var $result;
    var $ColumnCount;
    var $FunctionMatrix;
    var $CrossMatrix;
    var $rownum = 0;
    var $lastrow = null;

    /*******************************************************************************/
    /* Fetch next result
    /*******************************************************************************/
    function FetchNext()
    {
        $this->rownum ++;
        if ($row=$this->result->Fetch())
        {
            for ($col=1; $col<=$this->ColumnCount; $col++)
            {
                $Conteudo = $row[$col-1] == null ? '' : trim($row[$col-1]);
                
                if ($this->CrossMatrix[$col])
                {
                    $Conteudo =  $this->GetExternalField($this->ext_conn, $this->CrossMatrix[$col], $Conteudo);
                }
                
                if ($this->FunctionMatrix[$col])
                {
                    $function = $this->FunctionMatrix[$col];
                    if (function_exists($function))
                    {
                        $Conteudo = $function($Conteudo, $row, $this->lastrow, $this->rownum, $col);
                    }
                    else
                    {
                        $Conteudo = $Content;
                    }
                }
                
                $QueryLine[$col] = $Conteudo;
            }
            $this->lastrow = $QueryLine;
        }
        return $QueryLine;
    }

    /*******************************************************************************/
    /* Connects to the third Database and get the resultant field.
    /*******************************************************************************/
    function GetExternalField($ext_conn, $matrix, $var)
    {
        $database  = $matrix[0];
        $get_table = $matrix[1];
        $get_field = $matrix[2];
        $equ_expr  = $matrix[3];
        
        $conn = $ext_conn[$database];
        if ($conn)
        {
            $result_data = $this->ExternalData[$get_table][$get_field][$equ_expr][$var];
            if ($result_data)
            {
                return $result_data;
            }
            else
            {
                $sql = "select $get_field from $get_table where $equ_expr='$var'";
                $query = $conn->CreateQuery($sql);
                $result = $query->result;
                if ($result)
                {
                    if ($row = $query->Fetch())
                    {
                        $content = trim($row[0]);
                        $this->ExternalData[$get_table][$get_field][$equ_expr][$var] = $content;
                        return $content;
                    }
                }
            }
        }
        return '';
    }
}

/*******************************************************************************/
/* AgataQueryArray - A Query stored in one big array
/* by Pablo Dall'Oglio - 2001 - 2006
/*******************************************************************************/
class AgataQueryArray
{
    var $QueryArray;
    var $i;

    /*******************************************************************************/
    /* Constructor
    /*******************************************************************************/
    function AgataQueryArray($QueryArray)
    {
        $this->QueryArray = $QueryArray;
        $this->i = 0;
    }

    /*******************************************************************************/
    /* Fetch next result
    /*******************************************************************************/
    function FetchNext()
    {
        $i = $this->i;
        $this->i ++;
        return $this->QueryArray[$i];
    }
}

/*******************************************************************************/
/* AgataSubQueryArray - A Sub Query stored in one big array
/* by Jamiel Spezia - 2006 - 2006
/*******************************************************************************/
class AgataSubQueryArray
{
    var $SubQueryArray;
    var $query;
    var $line;

    /*******************************************************************************/
    /* Constructor
    /*******************************************************************************/
    function AgataSubQueryArray($SubQueryArray)
    {
        $this->SubQueryArray = $SubQueryArray;
        $this->line = 0;
        $this->query = 0;
    }

    /*******************************************************************************/
    /* Fetch next result
    /*******************************************************************************/
    function FetchNext()
    {
        $line = $this->line;
        $this->line ++;
        return $this->SubQueryArray[$this->query][$line];
    }

    /*******************************************************************************/
    /* Fetch next query
    /*******************************************************************************/
    function SetFatherLineQuery($query)
    {
        $this->query = $query;
    }
}

/*******************************************************************************/
/* AgataSubQueryObject - A Sub Query stored in one big object
/* by Jamiel Spezia - 2009 - 2009
/*******************************************************************************/
class AgataSubQueryObject
{
    var $SubQueryObject;
    var $query;
    var $line;

    /*******************************************************************************/
    /* Constructor
    /*******************************************************************************/
    function AgataSubQueryObject($SubQueryObject)
    {
        $this->SubQueryObject = $SubQueryObject;
        $this->line = 0;
        $this->query = 0;
    }

    /*******************************************************************************/
    /* Fetch next result
    /*******************************************************************************/
    function FetchNext()
    {
        $line = $this->line;
        $this->line ++;
        return $this->SubQueryObject[$this->query]->data[$line];
    }

    /*******************************************************************************/
    /* Fetch next query
    /*******************************************************************************/
    function SetFatherLineQuery($query)
    {
        $this->query = $query;
    }
}


?>
