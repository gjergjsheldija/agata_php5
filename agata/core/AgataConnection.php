<?php
/*******************************************************************************/
/* Agata Connection
/* the connection class
/* by Pablo Dall'Oglio - 2001 - 2006
/*******************************************************************************/
class AgataConnection
{
    var $db;         // the connection identifier
    var $traceback;  // a list of transaction errors
    var $level;      // a counter for the transaction level

    /***********************************************************/
    /* Opens a new connection
    /***********************************************************/
    function Open($agataConfig) {
        $this->agataConfig = $agataConfig;

        $user  = $agataConfig['user'];
        $pass  = $agataConfig['pass'];
        $name  = $agataConfig['name'];
        $host  = $agataConfig['host'];
        $type  = $agataConfig['type'];

        $this->name = $name;
        $tmp = explode('-', $type);
        if (count($tmp) == 2)
        {
            $DriverType = $tmp[0];
            $type = $tmp[1];

            if ($DriverType == 'odbc')
            {
                $type = $DriverType;
            }
        }
        $dsn="$type://$user:$pass@$host/$name";

        switch ($type)
        {
            case 'ibase':
                require_once AGATA_PATH . '/agata/layer/AgataIbase.php';
                $this->db = new AgataIbase;
                break;
            case 'pgsql':
                require_once AGATA_PATH . '/agata/layer/AgataPgsql.php';
                $this->db = new AgataPgsql;
                break;
            case 'sqlite':
                require_once AGATA_PATH . '/agata/layer/AgataSqlite.php';
                $this->db = new AgataSqlite;
                break;
            case 'mysql':
                require_once AGATA_PATH . '/agata/layer/AgataMysql.php';
                $this->db = new AgataMysql;
                break;
            case 'dbase':
                require_once AGATA_PATH . '/agata/layer/AgataDbase.php';
                $this->db = new AgataDbase;
                break;
            case 'oci8':
                require_once AGATA_PATH . '/agata/layer/AgataOracle.php';
                $this->db = new AgataOracle;
                break;
            case 'mssql':
                require_once AGATA_PATH . '/agata/layer/AgataMssql.php';
                $this->db = new AgataMssql;
                break;
            case 'ifx':
                require_once AGATA_PATH . '/agata/layer/AgataIfx.php';
                $this->db = new AgataIfx;
                break;
            case 'sybase':
                require_once AGATA_PATH . '/agata/layer/AgataSybase.php';
                $this->db = new AgataSybase;
                break;
            case 'odbc':
                require_once AGATA_PATH . '/agata/layer/AgataOdbc.php';
                $this->db = new AgataOdbc;
                break;
        }
        if (is_object($this->db)) {
            $ret = $this->db->Connect($host, $name, $user, $pass);

            if (is_agata_error($ret)) {
                Dialog($ret->GetError());
                $this->db = null;
                return false;
            }

            return $this->db;
        }
        return false;
    }

    /***********************************************************/
    /* Executes a short query
    /***********************************************************/
    function ShortQuery()
    {
        $type = $this->GetDbType();

        if ($type=='oci8')
        {
            return 'where rownum=1';
        }
        else
        {
            return 'limit 1';
        }
    }

    /***********************************************************/
    /* Closes the connection
    /***********************************************************/
    function Close()
    {
        if ( $this->db )
        {
            $this->db->Disconnect();
            $this->db = null;
        }
    }

    /***********************************************************/
    /* Creates a new query
    /***********************************************************/
    function CreateQuery($sql="")
    {
        $q = new AgataQuery($this->agataConfig);

        $q->db      = $this->db;
        $q->type    = $this->GetDbType();
        $q->Gateway = $this->GetGateway();

        if ( $sql != "" )
        {
            $q->Open($sql);
        }

        return $q;
    }

    /***********************************************************/
    /* Load field names from a table
    /***********************************************************/
    function LoadFields($table)
    {
        $type           = $this->GetDbType();
        $Gateway        = $this->GetGateway();

        $sql['pgsql']   = "SELECT * FROM $table limit 1";
        //$sql['access']  = "SELECT * FROM $table limit 1";
        $sql['access']  = "SELECT TOP 1 * FROM $table";
        $sql['sqlite']  = "SELECT * FROM $table limit 1";
        $sql['fbsql']   = "SELECT * FROM $table limit 1";
        $sql['dbase']   = "SELECT * FROM $table limit 1";

        $sql['mysql']   = "SHOW COLUMNS FROM $table";
        $sql['pervasive']  = "SELECT TOP 1 * FROM \"$table\"";
        //$sql['oci8']    = "select column_name from user_tab_columns where table_name='$table'";
        $sql['oci8']    = "select distinct column_name from all_tab_columns where table_name=trim(substr('$table', instr('$table', '.', -1)+1, length(trim('$table')) ))";
        $sql['sybase']  = "SELECT c.name FROM sysobjects o INNER JOIN syscolumns c ON o.id=c.id WHERE o.name = '$table' ORDER BY c.name";
        $sql['mssql']   = "SELECT c.name FROM sysobjects o INNER JOIN syscolumns c ON o.id=c.id WHERE o.name = '$table' ORDER BY c.name";
        $sql['ibase']   = "select RDB\$FIELD_NAME as name from RDB\$RELATION_FIELDS " .
                          "where RDB\$RELATION_NAME = '$table' order by RDB\$FIELD_NAME ";
        $sql['ifx']     = "select c.colname from systables t, syscolumns c " .
                          "where t.tabid = c.tabid and t.tabname = '$table' order by colname ";
        $sql['db2']     = "select name from sysibm.syscolumns where tbname='$table'";

        $DB_SQL_Queries = array('ibase', 'mssql', 'sybase', 'ifx', 'oci8', 'db2', 'mysql');

        $sql            = $sql[$type];
        $Results        = $this->db->Query($sql);

        if (in_array($type, $DB_SQL_Queries))
        {
            while ($Row = $this->db->FetchRow($Results))
            {
                $strings[] = trim($Row[0]);
            }
        }
        else
        {
            $Properties = $this->db->Properties($Results);
            if ($Properties)
            {
                foreach ($Properties as $Result)
                {
                    $strings[] = $Result['name'];
                }
            }
        }

        return $strings;
    }

    /**
    * Load Tables from a database
    * This params are only used in only mode.
    * @param $option can (load only table that user has access), all (load all tables), diff (the tables that user don't has acces
    * @param $user the user to verify
    */
    function LoadTables($option = 'CAN', $user=NULL)
    {
        $type       = $this->GetDbType();
        $result     = null;

        if ($type == 'pgsql')
        {
            $fields = $this->LoadFields('pg_tables');
            if (in_array('schemaname',$fields))
            {
                $TableNamesQuery['pgsql']  = "select schemaname || '.' || tablename from pg_tables where " .
                                             "tablename not like 'pg%' and schemaname<>'information_schema' union " .
                                             "select viewname from pg_views where viewname not like 'pg%' and ".
                                             "schemaname<>'information_schema' order by 1";
            }
            else
            {
                $TableNamesQuery['pgsql']  = "select tablename from pg_tables where " .
                                             "tablename not like 'pg%' union " .
                                             "select viewname from pg_views where viewname not like 'pg%'".
                                             "order by 1";
            }
        }

        $TableNamesQuery['mysql']  = "SHOW TABLES";
        //$TableNamesQuery['oci8']   = "SELECT table_name FROM user_tables union select view_name from user_views";
        $TableNamesQuery['pervasive'] = "SELECT Xf\$Name FROM X\$File";

        $TableNamesQuery['oci8']   = "SELECT owner || '.' || table_name table_name FROM all_tables union select owner || '.' || view_name view_name from all_views";
        $TableNamesQuery['sybase'] = "select name from sysobjects where type = 'U' order by name";
        $TableNamesQuery['mssql']  = "select name from sysobjects where (type = 'U' or type='V') order by name";
        $TableNamesQuery['fbsql']  = "select \"table_name\" from information_schema.tables";
        $TableNamesQuery['ibase']  = "select RDB\$RELATION_NAME from RDB\$RELATIONS where RDB\$SYSTEM_FLAG=0 order by RDB\$RELATION_NAME";
        $TableNamesQuery['ifx']    = "select tabname from systables where tabid > 99 order by tabname";
        $TableNamesQuery['db2']    = "select name from sysibm.systables where name not like 'SYS%' and type='T'";
        $TableNamesQuery['sqlite'] = "SELECT name FROM sqlite_master WHERE (type = 'table')";
        $TableNamesQuery['access'] = "SELECT Name FROM MSysObjects WHERE Type = 1";

        $sql = $TableNamesQuery[$type];
        if($type == 'dbase')
        {
            $filename = $file = GetFileName($this->name);
            if (eregi('\.', $filename))
            {
                $pieces = explode('.', $filename, 2);
                $file = $pieces[0];
            }
            $result = array($file);
        }

        if ($sql)
        {
            $Result = $this->db->Query($sql);
            while ($Row = $this->db->FetchRow($Result))
            {
                if ($type == 'pgsql') // public schema don't need the prefix.
                {
                    $Row[0] = str_replace('public.', '', $Row[0]);
                }
                $strings[] = trim($Row[0]);
            }
            $result = $strings;

        }
        else
        {
            $result =  null;
        }

        global          $agataServer;
        global          $agataConfig;
        global          $serverConn;


        //if is online
        if ($agataConfig['general']['AuthenticateServer'] && $serverConn && $agataServer)
        {

            $data->user         = $user;
            if (!$user)
            {
                $data->user         = $agataServer->user->login;
            }
            $data->projectName  = $agataServer->project->projectname;

            $return = unserialize($serverConn->call('listAccess', serialize($data) ) );

            if ($return)
            {
                foreach ($return as $line => $info)
                {
                    $tables[] = $info[0];
                }
            }

            if ($option =='ALL')
            {
                return $result;
            }
            else if ($option =='CAN')
            {
                if (!$tables)
                {
                    return array();
                }
                else
                {
                    return $tables;
                }
            }
            else if ($option == 'DIFF')
            {
                if (!$tables)
                {
                    $tables = array();
                }
                $diffTables     = array_diff($result, $tables);
                return $diffTables;
            }
        }
        else
        {
            return $result;
        }

    }

    /***********************************************************/
    /* Get database type
    /***********************************************************/
    function GetDbType()
    {
        // native-pgsql
        // native-mssql
        // odbc-oci8
        // odbc-mssql

        $agataConfig = $this->agataConfig;
        $type = $agataConfig['type'];

        $tmp = explode('-', $type);
        if (count($tmp) == 2)
        {
            $type = $tmp[1];
        }

        return $type;
    }

    /***********************************************************/
    /* Get Gateway
    /***********************************************************/
    function GetGateway()
    {
        // native-pgsql
        // native-mssql
        // odbc-oci8
        // odbc-mssql

        $agataConfig = $this->agataConfig;
        $type = $agataConfig['type'];

        $tmp = explode('-', $type);
        if (count($tmp) == 2)
        {
            $Gateway = $tmp[0];
        }

        return $Gateway;

    }
}
?>
