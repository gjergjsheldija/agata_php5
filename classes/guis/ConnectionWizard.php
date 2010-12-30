<?php


/**
* This class administrate project connection wizard.
*
* by Jamiel Spezia 2006 - 2006
* Modified by Eduardo Bonfandini - 26/09/2008 - To accept soap server authentication
*
*/
class ConnectionWizard
{
    var $UseAuthenticateServer;
    var $serverUrl;
    var $serverConn;
    var $isOnline;


    /**
    * Constructor
    */
    function ConnectionWizard($handler)
    {
        global $agataConfig;
        $this->mode = 0;
        if ($agataConfig['general']['AuthenticateServer'])
        {
            $this->UseAuthenticateServer = true;
            $this->mode = 3;
        }
        //0 - no mode
        //1 - open mode
        //2 - new mode
        //3 - server mode
        $this->window = new GtkWindow;
        $this->window->connect_object('delete-event', array(&$this, 'Hide'));
        $this->window->connect_object('key_press_event', array(&$this,'KeyTest'));
        $this->window->set_title(_a('Connection wizard'));
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->set_usize(500,360);
        //$this->window->realize();
        $this->window->set_policy(false,false, false);

        $this->handler = $handler;

        $this->aProjects = Project::ReadProjects();

        //Instance background image
        $this->PixStart = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/connection_wizard.xpm');

        //Instance title style
        $this->TitleStyle = new GtkStyle();
        $this->TitleStyle->fg[GTK_STATE_NORMAL] = new GdkColor('#FFFFFF');
        $this->TitleStyle->font = gdk::font_load ("-*-helvetica-bold-r-*-*-*-180-*-*-*-*-*-*");

        $this->pageStyle = new GtkStyle();
        $this->pageStyle->bg[GTK_STATE_NORMAL] = new GdkColor('#8CB6DB');

        $this->notebook = new GtkNotebook();
        $this->notebook->set_style($this->pageStyle);
        $this->notebook->insert_page($this->generateMain()          , new GtkLabel('0'), 0);
        //New project
        $this->notebook->insert_page($this->generateNewProject()    , new GtkLabel('1'), 1);
        $this->notebook->insert_page($this->generateConfigDb()      , new GtkLabel('2'), 2);
        //Open project
        $this->notebook->insert_page($this->generateOpenProject()   , new GtkLabel('3'), 3);
        //Final wizard
        $this->notebook->insert_page($this->generateCongratulation(), new GtkLabel('4'), 4);
        //offline main
        $this->notebook->insert_page($this->generateMainOffline()          , new GtkLabel('5'), 5);
        $this->notebook->set_show_tabs(false);

        $hbox = new GtkHBox;
        $this->window->add($this->notebook);
        $this->setFields();
        $this->Show();
    }

    /***********************************************************/
    /* Generate the main screen
    /***********************************************************/
    function generateMain()
    {
        if ($this->mode == 3)
        {
            global $agataConfig;

            $fixed = new GtkFixed;
            $fixed->set_style($this->pageStyle);
            $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
            $fixed->put($Start, 0, 0);
            $Title = new GtkLabel(_a('Welcome to the agata wizard!'));
            $Title->set_style($this->TitleStyle);
            $fixed->put($Title, 70, 10);
            $fixed->put(new GtkLabel(_a('Make your online login ...') ),20,75);
            //$fixed->put(new GtkLabel() ),20,250);
            $this       -> serverUrl  = $agataConfig['general']['AuthenticateServer'].'/agata-server.php';
            $this       ->serverConn = new soap_client($this->serverUrl);
            global $serverConn;
            $serverConn = $this->serverConn;
            $result     = $serverConn->call('isOnline') ;
            $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
            $fixed->put($Start, 0, 0);
            $Title = new GtkLabel(_a('Agata User login:'));
            $Title->set_style($this->TitleStyle);
            $fixed->put($Title, 70, 10);

            if ($result)
            {
                $this->isOnline = true;
                global $isOnline;
                $isOnline       = true;
                $fixed  ->put(new GtkLabel(_a('User ')) ,20,100 );
                $fixed  ->put(new GtkLabel(_a('Password')) ,20,134 );
                $fixed  ->put(new GtkLabel(_a('Project')) ,20,164 );
                $this   ->userEntry     = new GtkEntry();
                $this   ->passEntry     = new GtkEntry();
                $passEntry              = $this->passEntry;
                $passEntry              ->set_visibility(false);
                $this   ->project       = new GtkCombo();
                $fixed  ->put($this->userEntry, 100,100);
                $fixed  ->put($this->passEntry, 100,130);
                $fixed  ->put($this->project  , 100,160);
                $project = $this->project;
                $OnProjectList  = unserialize($serverConn->call('listProjects') );
                echo 'listProjects';
                foreach ($OnProjectList  as $line => $info)
                {
                    $options[] = $info[1];
                }
                $project    ->set_popdown_strings($options);
                $fixed->put($this->buttonNext(0, $this->handler, 3), 100, 200);

                $buttonOff = new GtkButton();
                $buttonOff->set_relief(GTK_RELIEF_HALF);
                $buttonOff->set_usize(250, 30);
                $buttonOff->connect_object('clicked', array(&$this, 'next_page'), 5);
                $hboxOff = new GtkHBox();
                $hboxOff->pack_start(new GtkLabel(_a('Or select a offline task ...')));
                $buttonOff->add($hboxOff);

                $fixed->put($buttonOff, 230, 310);

            }
            else
            {
                $fixed->put(new GtkLabel(_a('Agata server not found. Please verify.')) ,80,60);
            }
        }
        else
        {
            $fixed = $this->generateMainOffline();
        }
        return $fixed;
    }


    function generateMainOffline()
    {
        global $agataConfig;

        $fixed = new GtkFixed;
        $fixed->set_style($this->pageStyle);
        $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
        $fixed->put($Start, 0, 0);
        $Title = new GtkLabel(_a('Welcome to the agata wizard!'));
        $Title->set_style($this->TitleStyle);
        $fixed->put($Title, 70, 10);

        $buttonStyle = new GtkStyle();
        $buttonStyle->bg[GTK_STATE_NORMAL]   = new GdkColor('#709DCB');
        $buttonStyle->bg[GTK_STATE_PRELIGHT] = new GdkColor('#8CB6DB');

        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/open.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $buttonOpen = new GtkButton();
        $buttonOpen->set_usize(150, 100);
        $buttonOpen->set_style($buttonStyle);
        $buttonOpen->connect_object('clicked', array(&$this, 'next_page'), '3');
        $hbox = new GtkHBox();
        $hbox->pack_start($image);
        $hbox->pack_start(new GtkLabel(_a('Open project')));
        $buttonOpen->add($hbox);
        $fixed->put($buttonOpen, 100, 180 );

        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/filenew.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $buttonNew = new GtkButton();
        $buttonNew->set_usize(150, 100);
        $buttonNew->set_style($buttonStyle);
        $buttonNew->connect_object('clicked', array(&$this, 'next_page'), '1');
        $hbox = new GtkHBox();
        $hbox->pack_start($image);
        $hbox->pack_start(new GtkLabel(_a('New project')));
        $buttonNew->add($hbox);
        $fixed->put($buttonNew, 300, 180);
        return $fixed;
    }


    /***********************************************************/
    /* Generate the new project screen
    /***********************************************************/
    function generateNewProject()
    {
        $fixed = new GtkFixed;
        $fixed->set_style($this->pageStyle);
        $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
        $fixed->put($Start, 0, 0);
        $Title = new GtkLabel(_a('New project - characteristic of the project'));
        $Title->set_style($this->TitleStyle);
        $fixed->put($Title, 70, 10);

        $line = 70;
        $col  = 10;
        $fixed->put(new GtkLabel(_a('Name'))        , $col, $line);
        $fixed->put(new GtkLabel(_a('Description')) , $col, $line+=30);
        $fixed->put(new GtkLabel(_a('Author'))      , $col, $line+=110);
        $fixed->put(new GtkLabel(_a('Date'))        , $col, $line+=30);

        $line = 70;
        $col  = 100;
        $this->projectName = new GtkEntry();
        $fixed->put($this->projectName, $col, $line);

        $this->description = new GtkText();
        $this->description->set_usize(200,100);
        $this->description->set_editable(true);
        $scroll = new GtkScrolledWindow();
        $scroll->add($this->description);
        $scroll->set_usize(300, 100);
        $scroll->set_policy(GTK_POLICY_NEVER, GTK_POLICY_ALWAYS);
        $fixed->put($scroll  , $col, $line+=30);

        $this->author = new GtkEntry();
        $fixed->put($this->author       , $col, $line+=110);
        $this->date = new GtkEntry();
        $this->date->set_editable(false);
        $fixed->put($this->date         , $col, $line+=30);

        $fixed->put($this->buttonNext(2, array(&$this, 'validateNewProject')), 340, 310);
        $fixed->put($this->buttonPrev(0), 10, 310);

        return $fixed;
    }

    function validateNewProject()
    {
        $project = $this->projectName->get_text();
        if (strlen($project) <= 0)
        {
            $error = _a('Please, enter with project name');
        }
        elseif ( Project::ExistProject($this->projectName->get_text()) )
        {
            $error = _a('The project already exist');
        }
        if ($error)
        {
            new Dialog($error);
            $this->next_page(1);
        }
    }

    /***********************************************************/
    /* Generate the config data base screen
    /***********************************************************/
    function generateConfigDb()
    {
        $fixed = new GtkFixed;
        $fixed->set_style($this->pageStyle);
        $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
        $fixed->put($Start, 0, 0);
        $Title = new GtkLabel(_a('New project - configure the database'));
        $Title->set_style($this->TitleStyle);
        $fixed->put($Title, 70, 10);

        $line = 70;
        $col  = 10;
        $fixed->put(new GtkLabel(_a('Database Host/SID/DSN'))   , $col, $line);
        $fixed->put(new GtkLabel(_a('Database type'))           , $col, $line+=30);
        $fixed->put(new GtkLabel(_a('Database name'))           , $col, $line+=30);
        $fixed->put(new GtkLabel(_a('User'))                    , $col, $line+=30);
        $fixed->put(new GtkLabel(_a('Password'))                , $col, $line+=30);
        $fixed->put(new GtkLabel(_a('Data dictionary'))         , $col, $line+=30);

        $line = 70;
        $col  = 150;

        $this->host = new GtkCombo();
        $fixed->put($this->host  , $col, $line);

        $this->type = new GtkCombo();
        $eType = $this->type->entry;
        $eType->set_editable(false);
        //$eType->set_editable(true);
        $fixed->put($this->type  , $col, $line+=30);

        $this->name = new FileBox(null, false);
        $this->name->set_usize(126, 22);
        $this->name->set_text($Content);
        $fixed->put($this->name->box  , $col, $line+=30);

        $this->user = new GtkEntry();
        $fixed->put($this->user  , $col, $line+=30);

        $this->password = new GtkEntry();
        $this->password->set_visibility(false);
        $fixed->put($this->password  , $col, $line+=30);

        $this->dataDictionary = new GtkCombo();
        $eDataDictionary = $this->dataDictionary->entry;
        $eDataDictionary->set_editable(false);
        $fixed->put($this->dataDictionary  , $col, $line+=30);

        $fixed->put($this->buttonNext(2, $this->handler, 2), 340, 10);
        $fixed->put($this->buttonPrev(1), 10, 310);

        return $fixed;
    }

    function dataHost()
    {
        if ( $this->aProjects )
        {
            foreach ($this->aProjects as $project)
            {
                if ($project['host'])
                {
                    $dataHost[$project['host']] = $project['host'];
                }
            }
            $dataHost[0] = '';
            sort($dataHost);
        }

        return $dataHost;
    }

    function databaseVars()
    {
        //Open mode
        if ($this->mode == 1)
        {
            $databaseVars = $this->dataOpenProject;
        }
        //New mode
        elseif ($this->mode == 2)
        {
            $eHost = $this->host->entry;
            $eType = $this->type->entry;
            $eDataDictionary = $this->dataDictionary->entry;
            //Project data
            $databaseVars->projectName  = $this->projectName->get_text();
            $databaseVars->desc         = $this->description->get_chars(0,-1);
            $databaseVars->author       = $this->author->get_text();
            $databaseVars->date         = $this->date->get_text();
            //Connection data
            $databaseVars->host         = $eHost->get_text();
            $databaseVars->type         = $eType->get_text();
            $databaseVars->name         = $this->name->get_text();
            $databaseVars->user         = $this->user->get_text();
            $databaseVars->pass         = $this->password->get_text();
            $databaseVars->dict         = $eDataDictionary->get_text();
        }
        //server mode
        elseif ($this->mode == 3)
        {
            $userEntry          = $this->userEntry;
            $passEntry          = $this->passEntry;
            $data->login        = $userEntry->get_text();
            $data->password     = $passEntry->get_text();
            $projectEntry       = $this->project->entry;
            $data->project      = $projectEntry->get_text();
            if ($this->isOnline)
            {
                $serverConn = $this->serverConn;
                $result = $serverConn->call('agataLogin',serialize($data)) ;
                global $agataServer;
                $agataServer = unserialize($result);
                unset($databaseVars);
                if ($agataServer->user->userid)
                {
                    $databaseVars->projectName  = $agataServer->project->projectname;
                    $databaseVars->desc         = $agataServer->project->description;
                    $databaseVars->author       = $agataServer->project->author;
                    $databaseVars->date         = $agataServer->project->date;
                    $databaseVars->host         = $agataServer->project->host;
                    $databaseVars->type         = $agataServer->project->type;
                    $databaseVars->name         = $agataServer->project->basename;
                    $databaseVars->user         = $agataServer->project->baseuser;
                    $databaseVars->pass         = $agataServer->project->pass;
                    $databaseVars->dict         = $agataServer->project->dict;
                }
            }
        }
        return $databaseVars;
    }

    /***********************************************************/
    /* Generate the open project screen
    /***********************************************************/
    function generateOpenProject()
    {
        $fixed = new GtkFixed;
        $fixed->set_style($this->pageStyle);
        $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
        $fixed->put($Start, 0, 0);
        $Title = new GtkLabel(_a('Open project'));
        $Title->set_style($this->TitleStyle);
        $fixed->put($Title, 70, 10);

        $line = 70;
        $col  = 10;
        $fixed->put(new GtkLabel(_a('Project name'))                , $col, $line);
        $fixed->put(new GtkLabel(_a('Details of the selected project'))      , $col+=180, $line);

        $line = 90;
        $col  = 10;
        $this->projectList = new ProjectList();
        $this->projectList->connect_object('select-row', array(&$this, 'selectProject'));
        $scroll = new GtkScrolledWindow;
        $scroll->set_policy(GTK_POLICY_ALWAYS, GTK_POLICY_ALWAYS);
        $scroll->add($this->projectList);
        $scroll->set_usize(140, 200);
        $fixed->put($scroll, $col, $line);

        $this->listing = new Listing(array(_a('Field'), _a('Content')));
        $this->listing->set_column_width(0, 100);
        //$this->listing->set_usize(200, 200);
        $scroll = new GtkScrolledWindow;
        //$scroll->set_policy(GTK_POLICY_ALWAYS, GTK_POLICY_ALWAYS);
        $scroll->add($this->listing);
        $scroll->set_usize(300, 200);
        $fixed->put($scroll  , $col+=180, $line);

        $fixed->put($this->buttonNext(3, $this->handler, 1), 340, 310);
        $fixed->put($this->buttonPrev(0), 10, 310);

        return $fixed;
    }

    function generateCongratulation()
    {
        $fixed = new GtkFixed;
        $fixed->set_style($this->pageStyle);
        $Start = new GtkPixmap($this->PixStart[0], $this->PixStart[1]);
        $fixed->put($Start, 0, 0);
        $Title = new GtkLabel(_a('Congratulations!'));
        $Title->set_style($this->TitleStyle);
        $fixed->put($Title, 70, 10);
        $Text = new GtkLabel (_a('You are connected to project'));
        $fixed->put($Text, 70, 50);

        $line = 70;
        $col  = 10;
        $fixed->put($this->buttonClose(), 340, 310);
        return $fixed;
    }

    /***********************************************************/
    /*
    /***********************************************************/
    function selectProject()
    {
        $projectName = $this->projectList->getSelection();
        $project = $this->aProjects[$projectName];

        $this->dataOpenProject->projectName = $projectName;
        foreach ($project as $key=>$p)
        {
            $this->dataOpenProject->$key = $p;
            if ( $key = Project::FieldName($key) )
            {
                $data[] = array($key, $p);
            }
        }
        $this->listing->clear();
        $this->listing->AppendItems($data);
    }

    function connectButtonNewReport($handler)
    {
        $this->buttonReport->connect_object('clicked', $handler);
    }

    function buttonNewReport($handler=null)
    {
        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/ico_new.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $this->buttonReport = new GtkButton();
        $this->buttonReport->set_relief(GTK_RELIEF_HALF);
        $this->buttonReport->set_usize(150, 30);
        $this->buttonReport->connect_object('clicked', array(&$this, 'Hide'), $page);
        $hbox = new GtkHBox();
        $hbox->pack_start($image);
        $hbox->pack_start(new GtkLabel(_a('New report')));
        $this->buttonReport->add($hbox);

        return $this->buttonReport;
    }

    function buttonClose($handler=null)
    {
        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/ico_close.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $buttonClose = new GtkButton();
        $buttonClose->set_relief(GTK_RELIEF_HALF);
        $buttonClose->set_usize(100, 30);
        $buttonClose->connect_object('clicked', array(&$this, 'Hide'), $page);
        $hbox = new GtkHBox();
        $hbox->pack_start($image);
        $hbox->pack_start(new GtkLabel(_a('Close')));
        $buttonClose->add($hbox);

        if ($handler)
        {
            $buttonClose->connect_object('clicked', $handler);
        }

        return $buttonClose;
    }

    function buttonPrev($page, $handler=null)
    {
        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/ico_prev.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $buttonPrev = new GtkButton();
        $buttonPrev->set_relief(GTK_RELIEF_HALF);
        $buttonPrev->set_usize(150, 30);
        $buttonPrev->connect_object('clicked', array(&$this, 'next_page'), $page);
        $hbox = new GtkHBox();
        $hbox->pack_start($image);
        $hbox->pack_start(new GtkLabel(_a('Prev')));
        $buttonPrev->add($hbox);

        if ($handler)
        {
            $buttonPrev->connect_object('clicked', $handler);
        }

        return $buttonPrev;
    }

    function buttonNext($page, $handler=null, $mode=0)
    {
        $pix = Gdk::pixmap_create_from_xpm($this->window->window, null, 'interface/ico_next.xpm');
        $image = new GtkPixmap($pix[0], $pix[1]);
        $buttonNext = new GtkButton();
        $buttonNext->set_usize(150, 30);
        $buttonNext->connect_object('clicked', array(&$this, 'next_page'), $page, $mode);
        $hbox = new GtkHBox();
        $hbox->pack_start(new GtkLabel(_a('Next')));
        $hbox->pack_start($image);
        $buttonNext->add($hbox);

        if ($handler)
        {
            $buttonNext->connect_object('clicked', $handler);
        }

        return $buttonNext;
    }

    /***********************************************************/
    /* Alter screen
    /***********************************************************/
    function next_page($page, $mode=0)
    {
        $this->mode = $mode;
        $this->notebook->set_page($page);
    }

    function setFields()
    {
        include_once 'include/util.inc';

        $this->projectName->set_text('');
        $this->description->delete_text(0,-1);
        $this->author->set_text('');
        $this->date->set_text('');
        $this->date->set_text(date('Y-m-d'));
        $host = $this->host->entry;
        $host->set_text('');
        $this->name->set_text('');
        $this->user->set_text('');
        $this->password->set_text('');
        $dataDictionary = $this->dataDictionary->entry;
        $dataDictionary->set_text('');

        $this->aProjects = Project::ReadProjects();
        $aDict = Dictionary::ListDictionaries();
        $aDB = suported_databases_type();

        $this->host->set_popdown_strings($this->dataHost());
        $this->dataDictionary->set_popdown_strings($aDict);
        $this->type->set_popdown_strings($aDB);

        $this->projectList->ClearProjects();
        $this->projectList->SetProjects($this->aProjects);
    }

    /***********************************************************/
    /* Show the Window
    /***********************************************************/
    function Show()
    {
        global $serverConn;
        //echo 'uhu';
        $project = $this->project;
        if ($project)
        {
            $OnProjectList  = unserialize($serverConn->call('listProjects') );
            foreach ($OnProjectList  as $line => $info)
            {
                $options[] = $info[1];
            }
            $project    ->set_popdown_strings($options);
        }
        $this->window->show_all();
        return true;
    }

    /***********************************************************/
    /* Hides the Window
    /***********************************************************/
    function Hide()
    {
        $this->window->hide();
        return true;
    }

    /***********************************************************/
    /* Test the key pressed
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->Hide();
        }
        /*else if ($p1->keyval == K_ENTER)
        {
            $this->DatabaseVars->onEnter();
        }*/
    }

}