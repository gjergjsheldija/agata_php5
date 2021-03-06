<?php
/* AgataQuery
 *
 */
class AgataQuery
{
    /***********************************************************/
    /* Constructor method
    /***********************************************************/
    function AgataQuery($agataConfig)
    {
        $this->agataConfig = $agataConfig;
    }

    /***********************************************************/
    /* Open a query
    /***********************************************************/
    function Open($sql)
    {
        $this->result  = $this->db->Query($sql);
        $this->records = 0;
        $this->Properties = null;
        
        if (is_agata_error($this->result))
        {
            $a = new MemoArea($this->result->GetError());
            $this->result =0;
            return false;
        }
        
        return true;
    }

    /***********************************************************/
    /* Get the number of rows
    /***********************************************************/
    function GetRowCount()
    {
        $ret = $this->db->NumRows($this->result);
        return $ret;
    }

    /***********************************************************/
    /* Get the number of columns
    /***********************************************************/
    function GetColumnCount()
    {
        $ret = $this->db->NumCols($this->result);
        return $ret;
    }

    /***********************************************************/
    /* Fetch Results
    /***********************************************************/
    function Fetch()
    {
        $this->records ++;
        
        $row = $this->db->FetchRow($this->result);
        
        if ($this->records > $this->OffSet)
        {
            if (($this->records <= ($this->Limit + $this->OffSet)) or ($this->Limit == 0))
            {
                return $row;
            }
            else
            {
                return null;
            }
        }
        else
        {
            return $this->Fetch();
        }
    }

    /***********************************************************/
    /* Set the OffSet
    /***********************************************************/
    function setOffSet($offset)
    {
        $this->OffSet = $offset;
    }

    /***********************************************************/
    /* Set the Limit
    /***********************************************************/
    function setLimit($limit)
    {
        $this->Limit = $limit;
    }

    /***********************************************************/
    /* Get the names of columns
    /***********************************************************/
    function GetColumnNames()
    {
        if (!$this->Properties)
        {
            $this->Properties = $this->db->Properties($this->result);
        }
        
        $Results = $this->Properties;
        foreach ($Results as $Result)
        {
            if ($Result['name']!= null)
            {
                $strings[] = $Result['name'];
            }
        }
        return $strings;
    }

    /***********************************************************/
    /* Get the types of columns
    /***********************************************************/
    function GetColumnTypes()
    {
        if (!$this->Properties)
        {
            $this->Properties = $this->db->Properties($this->result);
        }
        $Results = $this->Properties;
        foreach ($Results as $Result)
        {
            $strings[] = $Result['type'];
        }
        return $strings;
    }
}
?>
