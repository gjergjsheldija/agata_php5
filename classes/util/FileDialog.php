<?php
/***********************************************************/
/* FileSelection class
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class FileDialog
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function FileDialog($title, $filter = null, $extrabutton = null, $firstdir = null, $Action = null, $Parameter = null, $Networks = null)
    {
        global $Pixmaps;
        
        $window = new GtkWindow;
        $window->set_position(GTK_WIN_POS_CENTER);
        $window->set_usize(556,360);
        $window->set_title($title);
        $window->realize();
        $window->connect_object('key_press_event', array(&$this,'KeyTest'), 'window');
        $this->gdkwindow = $window->window;
        $this->window = &$window;
        
        if (OS == 'WIN')
        {
            $this->root = $_ENV['HOMEDRIVE'] . '\\';
            $this->home = ($_ENV['HOMEDRIVE'].$_ENV['HOMEPATH'])?$_ENV['HOMEDRIVE'].$_ENV['HOMEPATH']:$_ENV['HOME'];
            $this->home = str_replace('\\\\','\\', $this->home);
            $this->home = str_replace('\\\'','\'', $this->home);
            $this->home = "{$this->home}";
            $this->floppy = 'A:\\';
            $this->barra = '\\';
            $this->desktop = $this->home . $this->barra . 'Desktop';
            
            $ico = Gdk::pixmap_create_from_xpm($this->gdkwindow, null, images . 'drive.xpm');
            $this->MenuDrives = new GtkMenu;
            // A:
            $item = new MyNormalMenuItem($ico, " Drive A: ", array(&$this, 'GoLocal'), 'A:');
            $this->MenuDrives->append($item);
            
            for ($n=66; $n<=90; $n++)
            {
                $drive = chr($n) . ':';
                if (file_exists($drive))
                {
                    $item = new MyNormalMenuItem($ico, " Drive $drive ", array(&$this, 'GoLocal'), $drive);
                    $this->MenuDrives->append($item);
                }
            }
            $this->MenuDrives->show_all();
        }
        else
        {
            $this->root = '/';
            $this->home = $_ENV['HOME'];
            $this->floppy = '/mnt/floppy';
            $this->barra = '/';
            $this->desktop = $this->home . $this->barra . 'Desktop';
        }
        
        $HBox = $this->HBox = new GtkHBox;
        $window->add($this->HBox);
        
        $LeftBox = $this->LeftBox  = new GtkVBox;
        $RightBox = $this->RightBox = new GtkVBox;
        
        $framebts = new GtkFrame;
        $framebts->set_shadow_type(GTK_SHADOW_ETCHED_IN);
        $framebts->add($LeftBox);
        
        $tmp = new gtkeventbox;
        $style = $tmp->style;
        $style = $style->copy();
        //$style->bg_pixmap[0] = null;
        $style->bg[GTK_STATE_NORMAL] = new GdkColor(46036, 46036, 46036);
        $tmp->set_style($style);
        $tmp->add($framebts);
        
        $HBox->pack_start($tmp, false, false, 5);
        $HBox->pack_start($RightBox, false, false, 5);
        
        $rootlabel = _a('Root') . ': ' . $this->root;
        $homelabel = _a('Home') . ': ' . $this->home;
        $floppylabel = _a('Floppy') . ': ' . $this->floppy;
        $desktoplabel = _a('Desktop') . ': ' . $this->desktop;
        $networklabel = _a('Drives');
        
        $root    = new Icone($this->gdkwindow, images . 'root.xpm',  ' '._a('Root').' ',    $rootlabel);
        $home    = new Icone($this->gdkwindow, images . 'house.xpm',  ' '._a('Home').' ',    $homelabel);
        $network = new Icone($this->gdkwindow, images . 'webservice.xpm',  ' '._a('Network').' ', _a('Network'));
        
        if ($extrabutton)
        {
            $extrabuttonlabel = _a($extrabutton[1]) . ': ' . $extrabutton[2];
            $extra= new Icone($this->gdkwindow, $extrabutton[0],' '._a($extrabutton[1]).' ',$extrabuttonlabel);
            $extra->connect_object('clicked', array(&$this, 'GoLocal'), $extrabutton[2]);
            $extra->set_relief(GTK_RELIEF_NONE);
            //$extrabutton = array(xpm, text, path, function)
        }
        $desktop= new Icone($this->gdkwindow, images . 'desktop.xpm',' '._a('Desktop').' ', $desktoplabel);
        $filesys= new Icone($this->gdkwindow, images . 'network.xpm',' '._a('Drives').' ', $networklabel);
        //$floppy = new Icone($this->gdkwindow, images . 'floppy.xpm',' '._a('Floppy').' ',  $floppylabel);
        
        $root->connect_object('clicked', array(&$this, 'GoLocal'), $this->root);
        $home->connect_object('clicked', array(&$this, 'GoLocal'), $this->home);
        $desktop->connect_object('clicked', array(&$this, 'GoLocal'), $this->desktop);
        $filesys->connect_object('clicked', array(&$this, 'PopDrives'));
        $network->connect_object('clicked', array(&$this, 'PopNetworks'));
        //$floppy->connect_object('clicked', array(&$this, 'GoLocal'), $this->floppy);
        
        
        $root->set_relief(GTK_RELIEF_NONE);
        $home->set_relief(GTK_RELIEF_NONE);
        $desktop->set_relief(GTK_RELIEF_NONE);
        $filesys->set_relief(GTK_RELIEF_NONE);
        $network->set_relief(GTK_RELIEF_NONE);
        
        $LeftBox->pack_start($root, false, false, 5);
        $LeftBox->pack_start($home, false, false, 5);
        if ($extrabutton)
        {
            $LeftBox->pack_start($extra, false, false, 5);
        }
        
        $LeftBox->pack_start($desktop, false, false, 5);
        
        if ($Networks)
        {
            $iconet = Gdk::pixmap_create_from_xpm($this->gdkwindow, null, images . 'ico_net.xpm');
            $this->LeftBox->pack_start($network, false, false, 5);
            
            $this->MenuNetworks = new GtkMenu;
            foreach ($Networks as $network)
            {
                $item = new MyNormalMenuItem($iconet, $network, array(&$this, 'GoRemote'), $network);
                $this->MenuNetworks->append($item);
            }
            $this->MenuNetworks->show_all();
        }
        
        if ($this->OS == 'WIN')
        {
            $LeftBox->pack_start($filesys, false, false, 5);
        }
        
        $up     = new IconeImg($this->gdkwindow, images . 'up.xpm',     _a('Up'));
        $reload = new IconeImg($this->gdkwindow, images . 'reload.xpm', _a('Reload'));
        $new    = new IconeImg($this->gdkwindow, images . 'new.xpm',    _a('New'));
        
        $reload->connect_object('clicked', array(&$this, 'Reload'));
        $up->connect_object('clicked', array(&$this, 'GoUp'));
        $new->connect_object('clicked', array(&$this, 'NewFolder'));
        
        $up->set_relief(GTK_RELIEF_NONE);
        $reload->set_relief(GTK_RELIEF_NONE);
        $new->set_relief(GTK_RELIEF_NONE);
        
        $RightVBox = new GtkVBox;
        $RightBox->pack_start($RightVBox);
        
        $ControlBox = new GtkHbox;
        $ListingBox = new GtkHBox;
        $ActionBox = new GtkHBox;
        
        $sep = new GtkHSeparator;
        $sep->set_usize(-1, 10);
        
        $RightVBox->pack_start($ControlBox, false, false);
        $RightVBox->pack_start($ListingBox, true, true);
        $RightVBox->pack_start($sep, false, false);
        $RightVBox->pack_start($ActionBox, false, false);
        
        $ControlBox->pack_start($up, false, false);
        $ControlBox->pack_start($reload, false, false);
        $ControlBox->pack_start($new, false, false);
        $ControlBox->pack_start(new GtkHBox, true, true);
        $this->ComboPath = $ComboPath = new GtkCombo;
        $ComboList = $ComboPath->list;
        $ComboEntry = $ComboPath->entry;
        $ComboEntry->connect_object('key_press_event', array(&$this,'KeyTest'), 'combo');
        $ComboList->connect_object('button_press_event', array(&$this, 'ComboChange'));
        $ComboPath->set_usize(280,-1);
        $ControlBox->pack_start($ComboPath, false, false);
        
        $this->EntryPath = $EntryPath = new GtkEntry;
        if (count($filter) ==1)
        {
            if ($filter[0])
            {
                $EntryPath->set_text('*.' . $filter[0]);
            }
        }
        
        $EntryPath->connect_object('key_press_event', array(&$this,'KeyTest'), 'entry');
        $window->set_focus($EntryPath);
        $ActionBox->pack_start($EntryPath, true, true);
        
        $this->DirListing = new DirListing($this->gdkwindow, array(_a('File'), _a('Size'), _a('Date')));
        $dir = &$this->DirListing;
        $dir->barra = $this->barra;
        $dir->Combo = $ComboPath;
        $dir->Entry = $EntryPath;
        $dir->Filter = $filter;
        $dir->janela = $window;
        if ($firstdir)
        {
            $dir->LoadDirectory($firstdir);
        }
        else
        {
            $dir->LoadDirectory($this->root);
        }
        $dir->set_usize(440,400);
        $dir->set_column_width(0, 240);
        $dir->set_column_width(1, 80);
        
        $scroll = new GtkScrolledWindow;
        $scroll->add($this->DirListing);
        $ListingBox->pack_start($scroll, true, true);
        
        $ok     = new Icone($this->gdkwindow, images . 'ok.xpm',    ' '._a('OK').' ',   _a('OK'), true);
        $cancel = new Icone($this->gdkwindow, images . 'cancel.xpm',' '._a('Cancel').' ',   _a('Cancel'), true);
        $this->Action = &$Action;
        $this->Parameter = &$Parameter;
        $ok->connect_object('clicked', array(&$this, 'onOk'));
        $cancel->connect_object('clicked', array($window, 'hide'));
        
        $ok->set_relief(GTK_RELIEF_NONE);
        $cancel->set_relief(GTK_RELIEF_NONE);
        $ActionBox->pack_start($ok, false, false);
        $ActionBox->pack_start($cancel, false, false);
        $window->show_all();
        
        $this->ok_button = $ok;
        $this->cancel_button = $cancel;
    }
    
    /***********************************************************/
    /* PopUp the Drives menu
    /***********************************************************/
    function PopDrives()
    {
        $this->MenuDrives->popup(null, null, null, 1, 1);
    }
    
    
    /***********************************************************/
    /* PopUp the Networks menu
    /***********************************************************/
    function PopNetworks()
    {
        $this->MenuNetworks->popup(null, null, null, 1, 1);
    }

    /***********************************************************/
    /* Set the FileName
    /***********************************************************/
    function set_filename($FileName)
    {
        $EntryPath = $this->EntryPath;
        $EntryPath->set_text($FileName);
    }
    
    /***********************************************************/
    /* Returns the FileName
    /***********************************************************/
    function get_filename()
    {
        $dir = $this->DirListing;
        $EntryPath = $this->EntryPath;
        $file = $EntryPath->get_text();
        
        if (substr($file,0,1)== bar)
        {
            return $file;
        }
        
        if (substr($dir->Directory, -1) != bar)
        $return = $dir->Directory . bar . $file;
        else
        $return = $dir->Directory . $file;
        
        if ($dir->isRemote)
        {
            return "{$dir->Server}://{$return}";
        }
        return $return;
    }
    
    /***********************************************************/
    /* Test the content and runs the callback
    /***********************************************************/
    function onOk()
    {
        $dir = $this->DirListing;
        $EntryPath = $this->EntryPath;
        $file = $EntryPath->get_text();
        
        if (eregi("[*]", $file))
        {
            new Dialog(_a('Contain illegal characters'));
        }
        else
        {
            if ($this->Action)
            {
                if ($this->Parameter)
                {
                    call_user_func($this->Action, &$this, $this->Parameter);
                }
                else
                {
                    call_user_func($this->Action, &$this);
                }
            }
        }
    }
    
    /***********************************************************/
    /* Returns the selected path
    /***********************************************************/
    function get_path()
    {
        $dir = $this->DirListing;
        
        if ($dir)
        {
            return $dir->Directory;
        }
    }
    
    /***********************************************************/
    /* Hide the window
    /***********************************************************/
    function hide()
    {
        $this->window->hide();
    }
    
    /***********************************************************/
    /* Key Test Method
    /***********************************************************/
    function KeyTest($obj, $context)
    {
        if ($context == 'window')
        {
            if ($obj->keyval == K_ESC)
            {
                $this->hide();
            }
        }
        else if ($context == 'combo')
        {
            if ($obj->keyval == K_ENTER)
            {
                $this->ComboChange();
            }
        }
        else if ($context == 'entry')
        {
            if ($obj->keyval == K_ENTER)
            {
                $mypath = $this->get_filename();
                if (is_dir($mypath))
                {
                    $this->GoDir($mypath);
                }
                else // (is_file($mypath))
                {
                    $bt = $this->ok_button;
                    $bt->clicked();
                }
            }
        }
    }
    
    /***********************************************************/
    /* Changes the directory on Combo command
    /***********************************************************/
    function ComboChange()
    {
        $entry = $this->ComboPath->entry;
        $Directory = $entry->get_text();
        if (is_dir($Directory))
        {
            $this->GoDir($Directory);
        }
    }
    
    /***********************************************************/
    /* Goes up
    /***********************************************************/
    function GoUp()
    {
        $dir = $this->DirListing;
        $Directory = $dir->Directory;
        $split = explode(bar,$Directory);
        //$up = $this->root;
        for ($n=0; $n < count($split) -1; $n++)
        {
            if ($n == 0)
            $up .= $split[$n];
            else
            {
                $up .= bar . $split[$n];
            }
        }
        if (!$up)
        {
            $up = $this->root;
        }
        $this->GoDir($up);
    }
    
    /***********************************************************/
    /* Reloads the directory listing
    /***********************************************************/
    function Reload()
    {
        $dir = $this->DirListing;
        $dir->LoadDirectory($dir->Directory);
    }
    
    /***********************************************************/
    /* Ask by a new folder
    /***********************************************************/
    function NewFolder()
    {
        $this->InputBox = new InputBox(_a('New Folder'), 180, '', array(&$this,'MakeFolder'));
    }
    
    /***********************************************************/
    /* Creates a new folder
    /***********************************************************/
    function MakeFolder($entry)
    {
        $dir = $this->DirListing;
        $Directory = $dir->Directory;
        $InputBox = $this->InputBox;
        $Item = $entry->get_chars(0, -1);
        $Caminho =  $Directory . $this->barra . $Item;
        
        if (@mkdir($Caminho))
        {
            $this->Reload();
        }
        else
        {
            new Dialog(_a('Cannot create the directory'));
        }
    }
    
    /***********************************************************/
    /* Changes the directory
    /***********************************************************/
    function GoDir($Directory)
    {
        $dir = &$this->DirListing;
        $dir->LoadDirectory($Directory);
        $entry = $this->EntryPath;
        $entry->set_text('');
        $this->window->set_focus($entry);
    }
    
    function GoLocal($Directory)
    {
        $this->DirListing->isRemote = false;
        $this->GoDir($Directory);
    }
    
    function GoRemote($Server)
    {
        $this->DirListing->client = new soap_client("http://$Server/tulip-server.php");
        $this->DirListing->isRemote = true;
        $this->DirListing->Server = $Server;
        $this->GoDir('.');
    }
}

/***********************************************************/
/* DirListing class a GtkCList for directory listing
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class DirListing extends GtkCList
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function DirListing($gdkwindow, $array)
    {
        GtkCList::GtkCList(count($array), $array);
        GtkCList::connect_object('select-row', array(&$this, 'GoDir'));
    }
    
    /***********************************************************/
    /* Changes the directory
    /***********************************************************/
    function GoDir()
    {
        $selection = $this->selection;
        $line = $selection[0];
        $dir = $this->get_pixtext($line,0);
        if (substr($this->Directory, -1) != $this->barra)
        $Directory = $this->Directory . $this->barra . $dir[0];
        else
        $Directory = $this->Directory . $dir[0];
        
        if ($this->isRemote)
        {
            $client = $this->client;
            $isDir= $client->call('dir_list',$Directory);
        }
        else
        {
            $isDir = is_dir($Directory);
        }
        
        if ($isDir)
        {
            $this->LoadDirectory($Directory);
            $entry = $this->Entry;
            $entry->set_text('');
            $window = $this->janela;
            $window->set_focus($entry);
        }
        else
        {
            $entry = $this->Entry;
            $entry->set_text($dir[0]);
            $window = $this->janela;
            $window->set_focus($entry);
        }
    }
    
    /***********************************************************/
    /* Loads a directory in the List
    /***********************************************************/
    function LoadDirectory($Directory)
    {
        global $Pixmaps;
        
        
        $this->Directory = $Directory;
        
        # Put the dirs on ComboBox
        $Directory_ = substr($Directory, strlen($this->root));
        
        $split = explode($this->barra, $Directory_);
        $dirs = null;
        foreach($split as $part)
        {
            if ($part)
            {
                $join .= $this->barra . $part;
                $dirs[] = $join;
            }
        }
        if ($dirs)
        {
            $combo = $this->Combo;
            $entry = $combo->entry;
            $combo->set_popdown_strings($dirs);
            $entry->set_text($Directory);
        }
        else
        {
            $combo = $this->Combo;
            $combo->set_popdown_strings(array(null));
        }
        
        
        GtkCList::Clear();
        
        if (!$this->isRemote)
        {
            $dir = @opendir($Directory);
            if (!$dir)
            {
                return;
            }
            # Read the entries
            while (($arquivo = readdir($dir)) !== false)
            {
                $Caminho = $Directory . $this->barra . $arquivo;
                if (OS == 'WIN')
                {
                    $arquivo = strtolower($arquivo);
                }
                
                if (is_dir($Caminho))
                {
                    if (OS == 'WIN')
                    {
                        $arquivo = strtoupper($arquivo);
                    }
                    $arquivo = ' ' . $arquivo;
                }
                
                if (substr(trim($arquivo),0,1) != '.')
                $filelist[] = $arquivo;
            }
        }
        else
        {
            # Define um array de parāmetros
            $param = array('Directory'=>$Directory);
            # Cria objeto cliente SOAP
            $client = $this->client;
            $filelist= $client->call('dir_list',$param);
            if ($filelist == 'denied')
            {
                new Dialog(_a('Permission denied'));
                return false;
            }
        }
        if ($filelist)
        {
            sort($filelist);
            foreach ($filelist as $arquivo)
            {
                $old = $arquivo;
                $arquivo = trim($arquivo);
                $Caminho = $Directory . bar . $arquivo;
                if ($arquivo == '.' || $arquivo == '..')
                {
                    continue;
                }
                
                if (substr($old,0,1) == ' ') // directory
                {
                    $ico = $Pixmaps['folder1'];
                    $nodo = GtkCList::append(array($arquivo, $this->FileSize($Caminho), $this->FileDate($Caminho)));
                    GtkCList::set_pixtext($nodo, 0, $arquivo, 2, $ico[0], $ico[1]);
                }
                else
                {
                    if (count($this->Filter) == 1)
                    {
                        $ok = ($this->Filter) ? (in_array(substr($Caminho,-strlen($this->Filter[0])), $this->Filter)) : true;
                    }
                    else
                    {
                        $ok1 = ($this->Filter) ? (in_array(substr($Caminho,-3), $this->Filter)) : true;
                        $ok2 = ($this->Filter) ? (in_array(substr($Caminho,-4), $this->Filter)) : true;
                        $ok = ($ok1 or $ok2);
                    }
                    if ($ok)
                    {
                        $posicao = strpos($arquivo, '.');
                        $ext = substr($arquivo, $posicao+1);
                        $ico = $Pixmaps[$ext];
                        if (!$ico)
                        $ico = $Pixmaps['generic'];
                        $nodo = GtkCList::append(array($arquivo, $this->FileSize($Caminho), $this->FileDate($Caminho)));
                        GtkCList::set_pixtext($nodo, 0, $arquivo, 2, $ico[0], $ico[1]);
                    }
                }
            }
        }
    }
    
    /***********************************************************/
    /* Returns the FileSize
    /***********************************************************/
    function FileSize($file)
    {
        $sizelb = array('b','kb','Mb','Gb');
        if($size=@filesize($file))
        {
            foreach($sizelb as $lb){
                if($size<1000){
                    $size = round($size,1)." $lb";
                    break;
                }
                $size = $size/1024;
            }
        }
        return $size;
    }
    
    /***********************************************************/
    /* Returns FileDate
    /***********************************************************/
    function FileDate($file)
    {
        return date("Y-m-d H:i",@fileatime($file));
    }
}
?>
