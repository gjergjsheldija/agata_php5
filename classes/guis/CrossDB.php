<?php
/***********************************************************/
/* Link diferent databases
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class CrossDB extends AgataCore
{
    var $window;
    var $clist;
    var $button;

    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function CrossDB($info)
    {
        global $Pixmaps;
        $this->window = new GtkWindow;
        $this->window->set_title('Cross DB');
        $this->window->set_default_size(600, 360);
        $this->window->set_position(GTK_WIN_POS_CENTER);
        $this->window->connect_object('key_press_event', array(&$this, 'KeyTest'));
        $this->window->realize();
        $this->projects = Project::ReadProjects();
        $this->ProjectDefs = $ProjectDefs;

        $vbox = new GtkVBox;
        $vbox->pack_start(darktxt(_a('Database Link')), false, false);
        
        $hbox = new GtkHBox;
        $vbox->pack_start($hbox, true, true, 10);
        
        $listbox = new GtkVBox;
        $connect = new Button(array(&$this, 'Connect'), _a('Connect'), $Pixmaps['ok'], IMAGEBUTTON);
        $this->list = new GtkCList(1, array(_a('Project Name')));
        $this->list->set_column_width(0, 160);
        
        $listbox->pack_start($this->list, true, true, 2);
        $listbox->pack_start($connect, false, false, 2);
        
        $scroll1 = new GtkScrolledWindow;
        $scroll2 = new GtkScrolledWindow;
        $scroll1->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_ALWAYS);
        $scroll2->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_ALWAYS);
        
        $this->tree1 = new TableTree(_a('Tables Structure'), true);
        $this->tree2 = new TableTree(_a('Tables Structure'), true);
        $this->tree1->connect_object('tree-select-row', array(&$this, 'LoadTables2'));
        
        $scroll1->add($this->tree1);
        $scroll2->add($this->tree2);
        
        $chaves = array_keys($this->projects);
        foreach ($chaves as $project)
        {
            $node = $this->list->append(array($project));
            $this->list->set_pixtext($node, 0, $project, 5, $Pixmaps['db'][0], $Pixmaps['db'][1]);
        }
        
        $vbox1 = new GtkVBox();
        $vbox2 = new GtkVBox();
        $this->info = $info;
        
        $pieces = explode(':', $this->info, 2);
        $phrase = _a('Where') . "\n" . $pieces[0] . ' =';
        $label1 = new GtkLabel(_a('Select this column') . ':');
        $label2 = new GtkLabel($phrase);
        $this->buttonok = new Button(array(&$this->window, 'hide'), _a('Return'), $Pixmaps['require'], IMAGEBUTTON);
        //$this->buttonok->connect_object('clicked', );
        $label1->set_usize(-1, 30);
        $label2->set_usize(-1, 60);
        $vbox1->pack_start($label1, false, false, 2);
        $vbox2->pack_start($label2, false, false, 2);
        $vbox1->pack_start($scroll1);
        $vbox2->pack_start($scroll2);
        $vbox2->pack_start($this->buttonok, false, false, 2);
        
        $hbox->pack_start($listbox, false, false, 5);
        $hbox->pack_start($vbox1, true, true, 5);
        $hbox->pack_start($vbox2, true, true, 5);

        $this->window->add($vbox);
        $this->window->show_all();
    }

    /***********************************************************/
    /* Load tables - 2nd step
    /***********************************************************/
    function LoadTables2()
    {
        $info = $this->tree1->GetInfo();
        $tmp = explode(':', $info);
        //if ($info && count($tmp) == 2)
        if ($info)
        {
            $table = $tmp[1];
            $this->tree2->ClearTree();
            $this->tree2->LoadTheseTables($this->DbAttributes, array($table), $this->DataDescription);
        }
    }

    /***********************************************************/
    /* Connects the Database
    /***********************************************************/
    function Connect()
    {
        $selection = $this->list->selection;
        if ($selection)
        {
            $data = $this->list->get_pixtext($selection[0], 0);
            $this->project = $project = $data[0];
            $DbAttributes = $this->projects[$project];
            $this->DbAttributes = $DbAttributes;
            $this->ProjectDefs[$project] = Project::ReadProjectDefinitions($project);
            $this->DataDescription = $this->ProjectDefs[$project][3];
            
            $this->tree1->LoadTables($DbAttributes, $this->DataDescription);
            //$this->tree2->LoadTables($DbAttributes, $this->ProjectDefs[$project][3]);
        }
    }

    /***********************************************************/
    /* Closes the Window
    /***********************************************************/
    function Close()
    {
        $this->window->hide();
    }

    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == 65307)
        $this->Close();
    }
}
?>