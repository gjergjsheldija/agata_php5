<?php

class ManageUser extends GtkWindow
{
    var $tablesData;
    var $tablesDataGtk;


    /**
    * default constructor..
    */
    function ManageUser()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        if ($agataConfig['general']['AuthenticateServer'] && $serverConn && ($agataServer->user->isadmin=='t' or $agataServer->user->isadmin=='1'))
        {
            parent::GtkWindow();
            $this->connect_object('delete-event', array(&$this, 'hide'));
            $this->connect_object('key_press_event', array(&$this,'KeyTest'));
            $this->set_title(_a('Manage User'));
            $this->set_position(GTK_WIN_POS_CENTER);
            $this->set_usize(320,280);
            $this->realize();
            $fixed = new GtkFixed;
            $this->set_policy(false, false, false);

            global $userCombo;
            global $nameText;
            global $userPassword;
            global $userEmail;
            global $isadminComboEntry;

            $this->listUser();
            $usernames = $this->userNames;
            $userCombo = new GtkCombo();
            if ($usernames)
            {
                $userCombo ->set_popdown_strings($usernames);
            }
            $userComboEntry = $userCombo->entry;
            $userComboEntry->connect_object('changed', array(&$this,'userComboChange'));
            $fixed->put(new GtkLabel(_a('User ') ) ,10,10);
            $fixed->put($userCombo, 100, 10);

            $nameText= new GtkEntry();
            $fixed->put(new GtkLabel(_a('Name') ) ,10,40);
            $fixed->put($nameText, 100, 40);

            $userPassword = new GtkEntry();
            $userPassword ->set_visibility(false);
            $fixed->put(new GtkLabel(_a('Password') ) ,10,70);
            $fixed->put($userPassword, 100, 70);

            $userEmail= new GtkEntry();
            $fixed->put(new GtkLabel(_a('Email') ) ,10,100);
            $fixed->put($userEmail, 100, 100);

            $isadminCombo = new GtkCombo();
            $isadminCombo ->set_popdown_strings( array(_a('True'), _a('False') ) );
            $isadminComboEntry = $isadminCombo->entry;
            $isadminComboEntry->set_editable(false);
            $fixed->put(new GtkLabel(_a('Is Admin') ) ,10,130);
            $fixed->put($isadminCombo, 100, 130);

            $pix        = Gdk::pixmap_create_from_xpm($this->window, null, 'interface/ico_next.xpm');
            $image      = new GtkPixmap($pix[0], $pix[1]);
            $buttonSave = new GtkButton();
            $buttonSave ->set_usize(80, 30);
            $buttonSave ->connect_object('clicked', array(&$this, 'saveUser'));
            $hbox       = new GtkHBox();
            $hbox       ->pack_start(new GtkLabel(_a('Save')));
            $hbox       ->pack_start($image);
            $buttonSave->add($hbox);
            $fixed      ->put($buttonSave, 100, 160);

            $pix2       = Gdk::pixmap_create_from_xpm($this->window, null, 'interface/del.xpm');
            $image2     = new GtkPixmap($pix2[0], $pix2[1]);
            $buttonDel  = new GtkButton();
            $buttonDel  ->set_usize(80, 30);
            $buttonDel  ->connect_object('clicked', array(&$this, 'deleteUser'));
            $hbox2      = new GtkHBox();
            $hbox2      ->pack_start(new GtkLabel(_a('Delete')));
            $hbox2      ->pack_start($image2);
            $buttonDel  ->add($hbox2);
            $fixed      ->put($buttonDel, 180, 160);

            /* down */

            $tablesCombo = new GtkCombo();
            $tablesComboEntry = $tablesCombo->entry;

            $fixed->put(new GtkLabel(_a('Table') ) ,10,200);
            $fixed->put($tablesCombo, 100, 200);
            $this->tablesComboEntry = $tablesComboEntry;
            $this->tablesCombo = $tablesCombo;
            $this->listTables();

            $tableList  = new GtkCombo;

            $this->tableList = $tableList;
            $this->listAccess();

            $fixed      ->put(new GtkLabel( _a('Access') ), 10,230);
            $fixed      ->put($tableList, 100,230);

            $pix3       = Gdk::pixmap_create_from_xpm($this->window, null, 'interface/ico_add.xpm');
            $image3     = new GtkPixmap($pix3[0], $pix3[1]);
            $buttonAdd  = new GtkButton();
            $buttonAdd  ->set_usize(30, 22);
            $buttonAdd  ->connect_object('clicked', array(&$this, 'addToTable'));
            $hbox3      = new GtkHBox();
            $hbox3      ->pack_start($image3);
            $buttonAdd  ->add($hbox3);
            $fixed      ->put($buttonAdd, 280, 200);

            $pix4       = Gdk::pixmap_create_from_xpm($this->window, null, 'interface/del.xpm');
            $image4     = new GtkPixmap($pix4[0], $pix4[1]);
            $buttonDel2 = new GtkButton();
            $buttonDel2 ->set_usize(30, 22);
            $buttonDel2 ->connect_object('clicked', array(&$this, 'deleteAccess'));
            $hbox4      = new GtkHBox();
            $hbox4      ->pack_start($image4);
            $buttonDel2 ->add($hbox4);
            $fixed      ->put($buttonDel2, 280, 230);

            $this->fixed = $fixed;
            $this->add($fixed);
            $this->userComboChange();
            $this->show_all();
        }
        else
        {
            new Dialog (_a('You dont have rights to manage users, or is working offline') );
        }
    }

    /**
    * List tables that CAN be inserted in access list
    */
    function listTables()
    {
        global $dbVars;
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        global $userCombo;

        $userComboText      = $userCombo->entry;
        $user               = $userComboText->get_text();

        $conn = new AgataConnection();
        if($conn->Open($dbVars))
        {
            $allTables = $conn->LoadTables('DIFF', $user);
        }
        else
        {
            $conn->close();
            return false;
        }


        $tablesCombo = $this->tablesCombo;
        if ($allTables)
        {
            $tablesCombo->set_popdown_strings($allTables);
        }
    }


    /**
    * List tables that actual user has access
    */
    function listAccess()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        global $userCombo;

        $projectComboEntry  = $this->projectComboEntry;
        $tablesComboEntry   = $this->tablesComboEntry;
        $userComboText      = $userCombo->entry;
        //$data->projectName  = $projectComboEntry->get_text();
        $data->projectName  = $agataServer->project->projectname;
        $data->access       = $tablesComboEntry->get_text();
        $data->user         = $userComboText->get_text();

        $result             = $serverConn->call('listAccess', serialize($data));
        $result             = unserialize($result);
        if ($result)
        {
            foreach ($result as $line => $info)
            {
                $access[] = $info[0];
            }
        }
        $tableList = $this->tableList;
        if ($access)
        {
            $tableList->set_popdown_strings($access);
        }
        else
        {
            $tableListEntry = $tableList->entry;
            $tableListEntry->set_text('');
        }
        $this->listTables();
    }

    function addToTable($insert=true)
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;

        global $userCombo;
        $projectComboEntry  = $this->projectComboEntry;
        $tablesComboEntry   = $this->tablesComboEntry;
        $userComboText      = $userCombo->entry;
        $data->projectName  = $agataServer->project->projectname;
        $data->access       = $tablesComboEntry->get_text();
        $data->user         = $userComboText->get_text();
        if ($insert)
        {
            $result         = $serverConn->call('insertAccess', serialize($data));
            $result         = unserialize($result);
        }
        $this->listAccess();
        $this->listTables();
    }





    function deleteAccess()
    {
        //new Dialog('uhu');
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        global $userCombo;

        $projectComboEntry  = $this->projectComboEntry;
        $tablesComboEntry   = $this->tablesComboEntry;
        $userComboText      = $userCombo->entry;
        $data->projectName  = $agataServer->project->projectname;
        //$data->access       = $tablesComboEntry->get_text();
        $data->user         = $userComboText->get_text();
        $tableList          = $this->tableList;
        $access             = $tableList->entry;
        $data->access       = $access->get_text();
        $result             = $serverConn->call('deleteAccess', serialize($data));
        $result             = unserialize($result);
        $this->listAccess();
    }

    function userComboChange()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;

        global $userCombo;
        $userComboText = $userCombo->entry;
        global $nameText;
        global $userPassword;
        global $userEmail;
        global $isadminComboEntry;
        $selectUser = $userComboText->get_text();
        $userList = $this->userList;
        foreach ($userList as $line => $info)
        {
            if ($info[1] == $selectUser )
            {
                $userData = $info;
            }
        }
        $nameText->set_text($userData[2]);
        $userPassword->set_text('');
        $userEmail->set_text($userData[4]);
        if ($userData[5] == '1' || $userData[5] =='t')
        {
            $userData[5] = _a('True');
        }
        else
        {
            $userData[5] = _a('False');
        }
        $isadminComboEntry->set_text($userData[5]);
        $this->listTables();
        $this->listAccess();
    }


    function hide()
    {
        parent::hide();
    }

    function listUser()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        $userList  = $serverConn->call('listUser');
        $userList  = unserialize($userList);
        $this->userList = $userList;
        foreach ($userList as $line => $info)
        {
            $userNames[] = $info[1];
        }
        $this->userNames = $userNames;
        $projectList  = $serverConn->call('listProjects');
        $projectList  = unserialize($projectList);
        foreach ($projectList as $line => $info)
        {
            $projectNames[] = $info[1];
        }
        $this->projectNames = $projectNames;
    }


    function saveUser()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        global $userCombo;
        $userComboText = $userCombo->entry;
        global $nameText;
        global $userPassword;
        global $userEmail;
        global $isadminComboEntry;
        $data->login            = $userComboText->get_text();
        $data->name             = $nameText ->get_text();
        $password               = $userPassword -> get_text();
        if ($password) {$password=md5($password);}
        $data->password         = $password;
        $data->email            = $userEmail ->get_text();
        $data->isadmin          = $isadminComboEntry->get_text();
        $result = $serverConn ->call('saveUser', serialize($data) );
        $result = unserialize($result);
        $this->listUser();
        $usernames = $this->userNames;
        if ($usernames)
        {
            $userCombo ->set_popdown_strings($usernames);
        }
        $userComboText->set_text($data->login);
    }

    function deleteUser()
    {
        global $serverConn;
        global $agataConfig;
        global $agataServer;
        global $userCombo;
        $userComboText = $userCombo->entry;
        global $nameText;
        global $userPassword;
        global $userEmail;
        global $isadminComboEntry;
        $data->login            = $userComboText->get_text();
        $result = $serverConn ->call('deleteUser', serialize($data) );
        $result = unserialize($result);
        $this->listUser();
        $usernames = $this->userNames;
        if ($usernames)
        {
            $userCombo ->set_popdown_strings($usernames);
        }
    }

    /**
    * Test the key pressed
    */
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->hide();
        }
    }
}
?>