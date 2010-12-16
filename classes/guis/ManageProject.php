<?php

/**
* Manage Project Dialog, it manage online and offline Projects
* by Pablo Dall'Oglio   2001-2006
*    Jamiel Spezia      2006-2006
*    Eduardo Bonfandini 2008-2008
*/
class ManageProject
{

    /**
    * Constructor
    */
    function ManageProject()
    {
        $this->window = new GtkWindow;
        $this->window->connect_object('delete-event', array(&$this, 'Hide'));
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_title(_a('Manage Project'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->set_default_size(400,400);
        $this->window->realize();
        $hbox = new GtkHBox;
        $this->window->add($hbox);

        $aProjects = Project::ReadProjects();

        $aDict = Dictionary::ListDictionaries();

        $aDBs    = array('native-pgsql'  => 'pg_connect',    'native-mysql'  => 'mysql_connect',
                         'native-oci8'   => 'OCILogon',      'native-sybase' => 'sybase_connect',
                         'native-mssql'  => 'mssql_connect', 'native-fbsql'  => 'fbsql_connect',
                         'native-ibase'  => 'ibase_connect', 'native-ifx'    => 'ifx_connect',
                         'native-sqlite' => 'sqlite_open',   'native-mysqli' => 'mysqli_connect',
                         'native-dbase' => 'dbase_open');

        $aOdbc   = array('odbc-oci8'   => 'odbc_connect', 'odbc-sybase' => 'odbc_connect',
                         'odbc-mssql'  => 'odbc_connect', 'odbc-fbsql'  => 'odbc_connect',
                         'odbc-ibase'  => 'odbc_connect', 'odbc-ifx'    => 'odbc_connect',
                         'odbc-access' => 'odbc_connect', 'odbc-db2'    => 'odbc_connect',
                         'odbc-pgsql'  => 'odbc_connect', 'odbc-mysql'  => 'odbc_connect',
                         'odbc-pervasive'  => 'odbc_connect');

        foreach ($aDBs as $driver => $function)
        {
            if (function_exists($function))
            {
                $aDB[] = $driver;
            }
        }

        foreach ($aOdbc as $driver => $function)
        {
            if (function_exists($function))
            {
                $aDB[] = $driver;
            }
        }

        $trueFalse = array(_a('true'),_a('false'));

        $this->aCheckVars = null;
        $this->DatabaseVars = null;

        //$help1 = "Dabatase Host, for example: 192.168.0.62\nFor Oracle, fill with SID";
        //$help2 = "Dabatase Name, for example: customers\nFor Oracle, fill with SID";
        if ($aProjects)
        {
            foreach ($aProjects as $project => $aProject)
            {
                $dbVars[$project][] = array($aProject['desc'],  _a('Description'),          false, null,    true);
                $dbVars[$project][] = array($aProject['author'],_a('Author'),               false, null,    true);
                $dbVars[$project][] = array($aProject['date'],  _a('Date'),                 false, null,    true);
                $dbVars[$project][] = array($aProject['host'],  _a('Database Host/SID/DSN'),false, null,    true);
                $dbVars[$project][] = array($aProject['name'],  _a('Database Name'),        false, null,    true);
                $dbVars[$project][] = array($aProject['user'],  _a('User'),                 false, null,    true);
                $dbVars[$project][] = array($aProject['pass'],  _a('Password'),             true,  null,    true);
                $dbVars[$project][] = array($aProject['type'],  _a('Database type'),        false, $aDB,    false);
                $dbVars[$project][] = array($aProject['dict'],  _a('Data Dictionary'),      false, $aDict,  false);
            }
        }
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        if ($agataConfig['general']['AuthenticateServer'] && $serverConn && $agataServer->user->isadmin == 't')
        {
            $oProjects = $serverConn->call('listProjects');
            $oProjects = unserialize($oProjects);

            if ($oProjects)
            {
                foreach ($oProjects as $project => $oProject)
                {
                    $project = $oProject['1'];
                    $dbVars[$project][] = array($oProject['3'],  _a('Description'),          false, null,    true);
                    $dbVars[$project][] = array($oProject['4'],  _a('Author'),               false, null,    true);
                    $dbVars[$project][] = array($oProject['5'],  _a('Date'),                 false, null,    true);
                    $dbVars[$project][] = array($oProject['6'],  _a('Database Host/SID/DSN'),false, null,    true);
                    $dbVars[$project][] = array($oProject['2'],  _a('Database Name'),        false, null,    true);
                    $dbVars[$project][] = array($oProject['7'],  _a('User'),                 false, null,    true);
                    $dbVars[$project][] = array($oProject['8'],  _a('Password'),             true,  null,    true);
                    $dbVars[$project][] = array($oProject['9'],  _a('Database type'),        false, $aDB,    false);
                    $dbVars[$project][] = array($oProject['10'],  _a('Data Dictionary'),      false, $aDict,  false);
                    $dbVars[$project][] = array('true',          _a('isOnline'),             false, $trueFalse,  false);
                }
            }
        }

        $Plus[] = array('',             _a('Description'),          false, null,    true);
        $Plus[] = array('',             _a('Author'),               false, null,    true);
        $Plus[] = array(date('d/m/Y'),  _a('Date'),                 false, null,    true);

        $Plus[] = array('',             _a('Database Host or SID'), false, null,    true);
        $Plus[] = array('',             _a('Database Name'),        false, null,    true);
        $Plus[] = array('',             _a('User'),                 false, null,    true);
        $Plus[] = array('',             _a('Password'),             true,  null,    true);
        $Plus[] = array('',             _a('Database type'),        false, $aDB,    false);
        $Plus[] = array('',             _a('Dictionary'),           false, $aDict,  false);
        // only adds online option if user is online and is admin
        if ($agataConfig['general']['AuthenticateServer'] && $serverConn && $agataServer->user->isadmin)
        {
            $Plus[] = array('true',     _a('isOnline'),     false, $trueFalse,    true);
        }

        $slot_del = array(&$this, 'DeleteProject');

        $this->DatabaseVars = new Preferences(&$hbox);
        $this->DatabaseVars->SetTitleImage(images . 'setup.xpm');
        $this->DatabaseVars->SetTitle(_a('Manage Project'));
        $this->DatabaseVars->SetPlus($Plus);
        $this->DatabaseVars->SetOk(array(&$this, 'Save'), _a('Save'));
        $this->DatabaseVars->SetDel($slot_del);
        $this->DatabaseVars->SetStatus(_a('Loaded from ^1 directory', 'projects'));
        $this->DatabaseVars->SetSaveButton(true);
        $this->DatabaseVars->SetPixmap(images . 'ico_db.xpm');
        $this->DatabaseVars->SetListTitle(_a('Project Name'));
        $this->DatabaseVars->BuildForms($dbVars);
        $this->window->show_all();
    }


    /**
    * Delete a project
    */
    function DeleteProject()
    {
        $project                = $this->DatabaseVars->Current();
        $DatabaseVars           = $this->DatabaseVars;
        $return                 = $DatabaseVars->GetVars();
        $key                    = key($return);
        $schema                 = $return[$key];

        $dbVars['projectname']  = $this->DatabaseVars->Current();
        $dbVars['desc']         = $schema[0];
        $dbVars['author']       = $schema[1];
        $dbVars['date']         = $schema[2];
        //Connection
        $dbVars['host']         = $schema[3];
        $dbVars['name']         = $schema[4];
        $dbVars['basename']     = $schema[4];
        $dbVars['user']         = $schema[5];
        $dbVars['pass']         = $schema[6];
        $dbVars['type']         = $schema[7];
        $dbVars['dict']         = $schema[8];
        $dbVars['isOnline']     = $schema[9];

        if ($dbVars['isOnline'] == _a('true') )
        {
            global $serverConn;
            global $agataConfig;
            if ($agataConfig['general']['AuthenticateServer'] && $serverConn)
            {
                $dbVars = serialize( (object) $dbVars );
                $result = $serverConn->call('deleteProject', $dbVars);
                $result = unserialize($result);
                if (!$result)
                {
                    new Dialog(_a('Error on delete online project.') );
                }
            }
        }
        else
        {
            unlink("projects/{$project}.prj");
        }
    }


    /**
    * Save a project
    */
    function Save()
    {
        $DatabaseVars           = $this->DatabaseVars;
        $return                 = $DatabaseVars->GetVars();
        $key                    = key($return);
        $schema                 = $return[$key];

        $dbVars['projectname']  = $this->DatabaseVars->Current();
        $dbVars['desc']         = $schema[0];
        $dbVars['author']       = $schema[1];
        $dbVars['date']         = $schema[2];
        //Connection
        $dbVars['host']         = $schema[3];
        $dbVars['name']         = $schema[4];
        $dbVars['basename']     = $schema[4];
        $dbVars['user']         = $schema[5];
        $dbVars['pass']         = $schema[6];
        $dbVars['type']         = $schema[7];
        $dbVars['dict']         = $schema[8];
        $dbVars['isOnline']     = $schema[9];

        if ($dbVars['isOnline'] == _a('true') )
        {
            global $serverConn;
            global $agataConfig;
            if ($agataConfig['general']['AuthenticateServer'] && $serverConn)
            {
                $dbVars = serialize( (object) $dbVars );
                $result = $serverConn->call('saveProject', $dbVars);
                $result = unserialize($result);
                if (!$result)
                {
                    new Dialog(_a('Error on save online project.') );
                }
            }
        }
        else
        {
            Project::WriteProject($this->DatabaseVars->Current(), $dbVars);
        }
    }


    /**
    * Show the Window
    */
    function Show()
    {
        $this->window->show_all();
        return true;
    }


    /**
    * Hides the Window
    */
    function Hide()
    {
        $this->window->hide();
        return true;
    }


    /**
    * Test the key pressed
    */
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->Hide();
        }
        else if ($p1->keyval == K_ENTER)
        {
            $this->DatabaseVars->onEnter();
        }
    }
}
?>