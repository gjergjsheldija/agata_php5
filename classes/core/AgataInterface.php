<?php
# +-----------------------------------------------------------------+
# | AGATA Report                                                    |
# | Copyleft (l) 2003  Solis/UNIVATES Lajeado/RS - Brasil           |
# +-----------------------------------------------------------------+
# | Licensed under GPL: www.fsf.org for further details             |
# |                                                                 |
# | Site: http://www.agata.org.br                                   |
# +-----------------------------------------------------------------+
# | Abstract: A Database reporting tool written in PHP-GTK          |
# |                                                                 |
# | Started in  August, 10, 2001                                    |
# | Maintainers: Jamiel Spezia (jamiel@solis.coop.br)               |
# | Author: Pablo Dall'Oglio (pablo@dalloglio.net)                  |
# +-----------------------------------------------------------------+

include_once 'classes/util/Dialog.php';
include_once 'classes/util/AGrid.php';
include_once 'classes/util/InputBox.php';
include_once 'classes/util/InputArea.php';
include_once 'classes/util/Alist.php';
include_once 'classes/util/SimpleTree.php';
include_once 'classes/util/Listing.php';
include_once 'classes/util/CommonForm.php';
include_once 'classes/util/FormEntry.php';
include_once 'classes/util/Preferences.php';
include_once 'classes/util/Wait.php';
include_once 'classes/util/Ask.php';
include_once 'classes/util/Button.php';
include_once 'classes/util/ColorButton.php';
include_once 'classes/util/FontBox.php';
include_once 'classes/util/FileBox.php';
include_once 'classes/util/icone.php';
include_once 'classes/util/FileTree.php';
include_once 'classes/util/FileList.php';
include_once 'classes/util/editor.php';
include_once 'classes/util/TableTree.php';
include_once 'classes/util/TreeHandler.php';
include_once 'classes/util/TextArea.php';
include_once 'classes/util/MemoArea.php';
include_once 'classes/util/XmlArray.php';
include_once 'classes/util/Box.php';
include_once 'classes/util/IList.php';
include_once 'classes/util/FileDialog.php';
include_once 'classes/util/MenuItem.php';
include_once 'classes/util/HintWindow.php';
include_once 'classes/util/GtkEntryMask.php';
include_once 'classes/util/GtkPopDate.php';
include_once 'classes/util/AgataOO.php';
include_once 'classes/util/ProjectList.php';
include_once 'classes/guis/match.php';
include_once 'classes/guis/CrossDB.php';
include_once 'classes/guis/CrossBox.php';
include_once 'classes/guis/FunctionBox.php';
include_once 'classes/guis/CondHighBox.php';
include_once 'classes/guis/MaskBox.php';
include_once 'classes/guis/About.php';
include_once 'classes/guis/LinkMatch.php';
include_once 'classes/guis/InWindow.php';
include_once 'classes/guis/SelectList.php';
include_once 'classes/guis/SubSql.php';
include_once 'classes/guis/LevelsArea.php';
include_once 'classes/guis/LinkedTables.php';
include_once 'classes/guis/MergeInterface.php';
include_once 'classes/guis/OfficeInterface.php';
include_once 'classes/guis/ManageProject.php';
include_once 'classes/guis/ManageUser.php';
include_once 'classes/util/FieldArea.php';
include_once 'classes/guis/ConnectionWizard.php';
include_once 'classes/guis/HeaderFooterArea.php';
include_once 'classes/reports/AgataReport.php';

class AgataInterface
{
    /***********************************************************/
    /* Builds the Agata Interface
    /***********************************************************/
    function AgataInterface($agataConfig, $subQuery = false)
    {
        $this->subQuery = $subQuery;
        $this->agataConfig = $agataConfig;
        $this->glade = new GladeXML('interface' . bar . 'agata.glade');
        $this->window = $this->glade->get_widget('window');

        $this->window->set_border_width(0);
        $this->window->realize();
        $this->connected = false;
        $this->tooltips = new GtkTooltips();

        include ('include/getwidgets.inc');
        include ('include/createwidgets.inc');
        include ('include/setwidgets.inc');

        $gdkwindow = $this->window->window;
        $gdkwindow->set_icon($gdkwindow, $Pixmaps['icon'][0], $Pixmaps['icon'][1]);

        if (!$subQuery)
        {
            $this->Title = 'Agata Report';
            $this->window->set_title($this->Title);
            $this->window->connect_object('delete-event', array(&$this, 'AskFecha'));
            $this->window->connect_object('destroy', array(&$this, 'Quit'));
            $this->SelectList->connect_object('changed',       array(&$this, 'RefreshFields'));
            $this->PackMainMenu();

            /***********************************************************/
            /* Levels Area
            /***********************************************************/
            $this->LevelsArea = new LevelsArea($this->glade);
            $this->LevelsArea->connect_object('onchange', array(&$this, 'onBreakAction'));
            $this->Containers['viewportFixed']->add($this->LevelsArea->widget);
            $this->LevelsArea->CreateBreakList();
            $this->Containers['hboxBreaks']->pack_start($this->LevelsArea->widget2);
            $this->Containers['viewportFixed']->show_all();

            /***********************************************************/
            /* Merge Interface
            /***********************************************************/
            $this->MergeInterface   = new MergeInterface($this->glade, $this->agataConfig);
            $this->HeaderFooterArea = new HeaderFooterArea($this->glade);
            $this->MergeInterface->Buttons['SubQuery']->connect_object('clicked', array(&$this, 'SubQuery'));
            $this->MergeInterface->eventAddDetail(array(&$this, 'SubQuery'));

            $this->MergeInterface->Buttons['Preview']->connect_object('clicked', array(&$this, 'SaveTmpMerge'));
            $this->MergeInterface->Buttons['MergePdf']->connect_object('clicked', array(&$this, 'PreReport'), 'MergePdf');

            $this->Containers['boxLabel']->add($this->MergeInterface->textSections['@LabelText']);
            $this->Containers['boxHeader']->add($this->MergeInterface->textSections['@MergeHeader']);
            $this->Containers['boxGroupHeader']->add($this->MergeInterface->textSections['@GroupHeader']);
            $this->Containers['boxDetail']->add($this->MergeInterface->textSections['@MergeDetail']);
            $this->Containers['boxGroupFooter']->add($this->MergeInterface->textSections['@GroupFooter']);
            $this->Containers['boxFooter']->add($this->MergeInterface->textSections['@MergeFooter']);
            $this->Containers['boxFinalSummary']->add($this->MergeInterface->textSections['@FinalSummary']);

            /***********************************************************/
            /* OpenOffice Interface
            /***********************************************************/
            $this->OfficeInterface   = new OfficeInterface($this->glade, $this->agataConfig);
            $this->OfficeInterface->buttonOpenOfficeParse->connect_object('clicked', array(&$this, 'PreReport'), 'ParseOO');

            $this->PackConnection(true);
        }
        else
        {
            $this->Title = _a('SubReport Wizard');
            $this->window->set_title($this->Title);
            $this->Containers['hboxCloseSubWindow']->pack_start(right(new Button(array(&$this, 'SaveSubReport'), _a('Save SubReport'), $Pixmaps['menu_save'], IMAGEBUTTON)), false, true, 4);
            $this->Containers['hboxCloseSubWindow']->pack_start(right(new Button(array(&$this->window, 'hide'), _a('Quit'), $Pixmaps['menu_quit'], IMAGEBUTTON)), false, true, 4);
            $this->Containers['hboxMainQueryField']->pack_start(new Button(array(&$this, 'PopMainQueryFields'),       _a('Report Fields'), $Pixmaps['column'], IMAGEBUTTON), true, true, 2);
            $this->Containers['hboxMainMenu']->pack_start(darktxt(_a('SubQuery Session')), true, true);
            $this->window->connect_object('destroy', array(&$this->window, 'hide'));

            $this->AgataNotebook->remove_page(1);
            $this->AgataNotebook->remove_page(1);
            $this->AgataNotebook->remove_page(1);
        }

        $this->FileName = null;
        $this->LoadBlocks(true, false);
        $this->window->show_all();
    }

    function PackMainMenu()
    {
        global $Pixmaps;
        $this->accel = $accelgroup = new GtkAccelGroup();
        $this->window->add_accel_group($accelgroup);

        $MenuBar = new GtkMenuBar;

        $Menu = new MyAltMenuItem(_a('File'), '_' . _a('File'), $accelgroup);

        $Subitem0 = new MyControlMenuItem($Pixmaps['menu_connect'], _a('Connect to Database'), '_' .  _a('Connect to Database'),   $accelgroup, array(&$this, 'PackConnection'));
        $SubitemE = new MyControlMenuItem($Pixmaps['menu_connect'], _a('Manage Project'), '_' .  _a('Manage Project'),   $accelgroup, array(&$this, 'ManageProject'));
        $SubitemF = new MyControlMenuItem($Pixmaps['menu_connect'], _a('Manage User'), '_' .  _a('Manage User'),   $accelgroup, array(&$this, 'ManageUser'));

        $Subitem1 = new GtkMenuItem;
        $Subitem2 = new MyControlMenuItem($Pixmaps['menu_new'],     _a('New Report'), '_' .  _a('New'),   $accelgroup, array(&$this, 'ClearQuery'));
        $Subitem3 = new MyControlMenuItem($Pixmaps['menu_open'],    _a('Open Report'),'_O',               $accelgroup, 'HandlerFile', $this->param['OpenRep']);
        $Subitem4 = new MyControlMenuItem($Pixmaps['menu_save'],    _a('Save Report'),'_S' . _a('Save'),  $accelgroup, array(&$this, 'PreSave'), $this->param['SaveRep']);
        $Subitem5 = new MyControlMenuItem($Pixmaps['menu_saveas'],  _a('Save as'),    '',                 $accelgroup, 'HandlerFile', $this->param['SaveRep']);
        $Subitem6 = new MyControlMenuItem($Pixmaps['sql'],          _a('Save SQL'),   '' .   _a('Save'),  $accelgroup, 'HandlerFile', $this->param['SaveSql']);
        $Subitem7 = new MyControlMenuItem($Pixmaps['publish'],      _a('Publish Report'),    '',          $accelgroup, array(&$this, 'PublishReport'));
        $Subitem8= new GtkMenuItem;
        $Subitem9 = new MyControlMenuItem($Pixmaps['menu_prop'],    _a('Report Properties'), '' . _a('xxx'),  $accelgroup, array(&$this, 'Properties'));
        $SubitemA = new MyControlMenuItem($Pixmaps['ico_edit'],     _a('Custom Parameters'), '' . _a('xxx'),  $accelgroup, array(&$this, 'CustomParameters'));
        $SubitemB = new MyControlMenuItem($Pixmaps['menu_page'],    _a('Page Setup'), '' .   _a('Save'),  $accelgroup, array(&$this, 'PageSetup'));
        $SubitemC= new GtkMenuItem;
        $SubitemD= new MyControlMenuItem($Pixmaps['menu_quit'], _a('Quit'),   '_Q',      $accelgroup, array(Gtk, 'main_quit'));

        $SubMenu = new GtkMenu;
        $SubMenu->append($Subitem0);
        $SubMenu->append($SubitemE);
        global $agataConfig;
        if ($agataConfig['general']['AuthenticateServer'])
        {
            $SubMenu->append($SubitemF);
        }
        $SubMenu->append($Subitem1);
        $SubMenu->append($Subitem2);
        $SubMenu->append($Subitem3);
        $SubMenu->append($Subitem4);
        $SubMenu->append($Subitem5);
        $SubMenu->append($Subitem6);
        $SubMenu->append($Subitem7);
        $SubMenu->append($Subitem8);
        $SubMenu->append($Subitem9);
        $SubMenu->append($SubitemA);
        $SubMenu->append($SubitemB);
        $SubMenu->append($SubitemC);
        $SubMenu->append($SubitemD);

        $Menu->set_submenu($SubMenu);
        $MenuBar->append($Menu);

        $Menu = new MyAltMenuItem(_a('Report'), '_' . _a('Report'), $accelgroup);
        $Subitem0 = new MyFunctionMenuItem($Pixmaps['menu_execute'],_a('Preview of Report'),  65471, $accelgroup, array(&$this, 'PreReport'), 'screen');
        $Subitem0a= new GtkMenuItem;
        $Subitem1 = new MyFunctionMenuItem($Pixmaps['menu_txt'],   _a('Export to TXT File'), 65472, $accelgroup, array(&$this, 'PreReport'), 'txt');
        $Subitem3 = new MyFunctionMenuItem($Pixmaps['menu_pdf'],   _a('Export to PDF File'), 65473, $accelgroup, array(&$this, 'PreReport'), 'pdf');
        $Subitem4 = new MyFunctionMenuItem($Pixmaps['menu_html'],  _a('Export to HTML File'),65474, $accelgroup, array(&$this, 'PreReport'), 'html');
        $Subitem5 = new MyFunctionMenuItem($Pixmaps['xml'],        _a('Export to XML File'), 65475, $accelgroup, array(&$this, 'PreReport'), 'xml');
        $Subitem6 = new MyFunctionMenuItem($Pixmaps['csv'],        _a('Export to CSV File'), 65476, $accelgroup, array(&$this, 'PreReport'), 'csv');
        $Subitem7 = new MyFunctionMenuItem($Pixmaps['sxw'],        _a('Export to SXW File'), 65477, $accelgroup, array(&$this, 'PreReport'), 'sxw');
        $Subitem8 = new MyFunctionMenuItem($Pixmaps['sql'],        _a('Export to SQL File'), 65478, $accelgroup, array(&$this, 'PreReport'), 'sql');
        $Subitem9 = new GtkMenuItem;
        $SubitemA = new MyFunctionMenuItem($Pixmaps['menu_txt'],   _a('Export to TXT Form'),  '',   $accelgroup, array(&$this, 'PreReport'), 'TxtForm');
        $SubitemB= new MyFunctionMenuItem($Pixmaps['menu_html'],  _a('Export to HTML Frame'),'',   $accelgroup, array(&$this, 'PreReport'), 'HtmlFrame');
        $SubMenu = new GtkMenu;

        $SubMenu->append($Subitem0);
        $SubMenu->append($Subitem0a);
        $SubMenu->append($Subitem1);
        $SubMenu->append($Subitem3);
        $SubMenu->append($Subitem4);
        $SubMenu->append($Subitem5);
        $SubMenu->append($Subitem6);
        $SubMenu->append($Subitem7);
        $SubMenu->append($Subitem8);
        $SubMenu->append($Subitem9);
        $SubMenu->append($SubitemA);
        $SubMenu->append($SubitemB);

        $Menu->set_submenu($SubMenu);
        $MenuBar->append($Menu);

        $Menu = new MyAltMenuItem(_a('Tools'), '_' . _a('Tools'), $accelgroup);
        $Subitem1 = new MyControlMenuItem($Pixmaps['db'],           _a('Data Dictionary'),          '_' .  _a('Dictionary'),   $accelgroup, array(&$this, 'Dictionary'));
        $Subitem2 = new MyControlMenuItem($Pixmaps['menu_config'],  _a('System Preferences'),       '_P' .  _a('System Preferences'),   $accelgroup, array(&$this, 'ConfigWindow'));
        $Subitem3 = new MyControlMenuItem($Pixmaps['ico_colors'],   _a('Layout Configuration'),     '_L' .  _a('Layout Configuration'),   $accelgroup, array(&$this, 'LayoutConfig'));
        $Subitem4 = new MyControlMenuItem($Pixmaps['agl'],          _a('Label Templates'),          '_T' .  _a('Label Templates'),   $accelgroup, array(&$this, 'LabelConfig'));
        $Subitem5 = new GtkMenuItem;
        $Subitem6 = new MyControlMenuItem($Pixmaps['menu_dia'],       _a('Create Diagram'), '_D', $accelgroup, array(&$this, 'PackDia'));
        $Subitem7 = new MyControlMenuItem($Pixmaps['menu_function'],  _a('Function Repository'),  '_F', $accelgroup, array(&$this, 'PackFunctions'));


        $SubMenu = new GtkMenu;
        $SubMenu->append($Subitem1);
        $SubMenu->append($Subitem2);
        $SubMenu->append($Subitem3);
        $SubMenu->append($Subitem4);
        $SubMenu->append($Subitem5);
        $SubMenu->append($Subitem6);
        $SubMenu->append($Subitem7);

        $Menu->set_submenu($SubMenu);
        $MenuBar->append($Menu);

        $Menu = new MyAltMenuItem('?', '' . '?', $accelgroup);
        $Subitem1 = new MyControlMenuItem($Pixmaps['menu_help'], _a('About'),  '', $accelgroup, array(&$this, 'About'));

        $SubMenu = new GtkMenu;
        $SubMenu->append($Subitem1);

        $Menu->set_submenu($SubMenu);
        $MenuBar->append($Menu);

        $this->Containers['hboxMainMenu']->pack_start($MenuBar, false, false);
    }

    function Quit()
    {
        Gtk::Main_quit();
    }

    /***********************************************************/
    /* Ask user before close the window
    /***********************************************************/
    function AskFecha()
    {
        if ($this->Changed)
        {
            new Ask(_a('Query not saved, Do you really want to continue ?'), array(&$this, 'Quit'), null);
            return true;
        }
        else
        {
            return false;
        }
    }

    /***********************************************************/
    /* Fill the "Require Tables" Page
    /***********************************************************/
    function FillTables()
    {
        global $Pixmaps;
        $this->ctreeTables->Clear();
        $this->ctreeTablesDia->Clear();
        $this->TableNames = null;

        if ($this->connected)
        {
            $conn = new AgataConnection();
            if($conn->Open($this->agataDB))
            {
                $Tables = $conn->LoadTables();
            }
            else
            {
                $conn->close();
                return false;
            }

            if ($Tables)
            {
                foreach ($Tables as $Table)
                {
                    $Table = trim($Table);
                    $nick = null;
                    if ($this->SeekDataDescription("table:$Table"))
                    {
                        $nick = $this->SeekDataDescription("table:$Table");
                    }
                    else
                    {
                        $nick = $Table;
                    }

                    $this->TableNames[$Table] = $nick;
                }
            }
            $conn->close();

            if ($this->agataTbFamilies)
            {
                foreach ($this->agataTbFamilies as $group => $tables)
                {
                    $root1 = $this->ctreeTables->AppendSubTree($group, $Pixmaps['home'], null);
                    $root2 = $this->ctreeTablesDia->AppendSubTree($group, $Pixmaps['home'], null);

                    sort($tables);
                    if ($tables)
                    {
                        $inserts = null;
                        foreach ($tables as $table)
                        {
                            $inserts[] = array($table, $this->TableNames[$table]);
                        }
                        $this->ctreeTables->AppendItems($root1, $inserts, $Pixmaps['table']);
                        $this->ctreeTablesDia->AppendItems($root2, $inserts, $Pixmaps['table']);
                    }
                }
            }

            if ($this->TableNames)
            {
                ksort($this->TableNames);
                $inserts = null;
                foreach ($this->TableNames as $Table => $nick)
                {
                    if (eregi('\.', $Table)) // Schemas Sub-Tree
                    {
                        $insert = null;
                        $pieces = explode('.', $Table);
                        $schema = $pieces[0];
                        if (!$roots[$schema])
                        {
                            $roots[$schema] = $this->ctreeTables->AppendSubTree($schema, $Pixmaps['home'], null);
                        }
                        $insert[] = array($Table, $this->TableNames[$Table]);
                        $this->ctreeTables->AppendItems($roots[$schema], $insert, $Pixmaps['table']);
                    }
                    else
                    {
                        $inserts[] = array($Table, $this->TableNames[$Table]);
                    }
                }
                $root1 = $this->ctreeTables->AppendSubTree(_a('All Tables'), $Pixmaps['home'], null);
                $root2 = $this->ctreeTablesDia->AppendSubTree(_a('All Tables'), $Pixmaps['home'], null);
                $this->ctreeTables->expand($root1);
                $this->ctreeTablesDia->expand($root2);
                $this->ctreeTables->AppendItems($root1, $inserts, $Pixmaps['table']);
                $this->ctreeTablesDia->AppendItems($root2, $inserts, $Pixmaps['table']);
            }
        }
    }

    /***********************************************************/
    /* Returning from the function above
    /***********************************************************/
    function ReturnSelectList($Clause)
    {
        if ($Clause == 'From')
        {
            $this->LoadTablesFrom();
        }
        else
        {
            $this->ReadAdjustments();
            $this->LoadBlocks();
        }
    }

    /***********************************************************/
    /* Clear a specific clause of the main query
    /***********************************************************/
    function DelBlock($Block)
    {
        if ($Block == 'From')
        {
            $this->ctreeTableStructure->ClearTree();
            $this->RequiredTables = null;
        }
        $this->LoadBlocks();
    }

    /***********************************************************/
    /* Apply a function (count, sum, max) to a field
    /***********************************************************/
    function ApplyFunction()
    {
        $entry = $this->comboDBFunctions->entry;
        $index = 'Select';

        $command_tr = $entry->get_text();
        $commands[_a('Sum')]            = 'sum';
        $commands[_a('Count')]          = 'count';
        $commands[_a('Count distinct')] = 'count distinct';
        $commands[_a('Average')]        = 'avg';
        $commands[_a('Minimal')]        = 'min';
        $commands[_a('Maximal')]        = 'max';

        $command = $commands[$command_tr];

        $complem = '';

        $strings = null;
        $selection = $this->ctreeTableStructure->selection;
        $Structure = false;

        foreach ($selection as $node)
        {
            $Info = $this->ctreeTableStructure->node_get_row_data($node);
            $Info = explode(':', $Info);

            $Table = $Info[1];
            $Field = $Info[3];


            if ($Field)
            {
                if (!$Structure)
                {

                    $this->SelectList->Concatenate($index, ($this->SelectList->Block[$index][1]) ? ', ' : '');
                    // count distinct
                    if (strpos($command, ' ') !== false)
                    {
                        $pieces = explode(' ', $command);
                        $command = $pieces[0];
                        $complem = $pieces[1] . ' ';
                    }
                    $this->SelectList->Concatenate($index, "$command($complem");
                }
                else
                {
                    $this->SelectList->Concatenate($index, ',');
                }

                $this->SelectList->Concatenate($index, "$Table.$Field");

                $Structure = true;
            }
        }

        if (!$Structure)
        {
            new Dialog(_a('Select field, please'));
            return null;
        }
        else
        {
            $this->SelectList->Concatenate($index, ")");
            $this->SelectList->SetBlock('Group by', RemoveFunctions($this->SelectList->Block['Select'][1]));
            if ($this->SelectList->Block['Group by'][1])
            {
                new Dialog(_a('This action will create a group'), false);
            }
        }
        $this->LoadBlocks();
    }

    /***********************************************************/
    /* Launched when users change the SELECT statement of
    /* Main Query
    /***********************************************************/
    function RefreshFields()
    {
        # Clear all dependant information
        $this->comboLegend->set_popdown_strings(array(null));
        $this->LevelsArea->comboBFields->set_popdown_strings(array(null));
        $this->LevelsArea->comboBBreaks->set_popdown_strings(array(null));
        $this->PlottedColumns->Clear();
        $this->MergeInterface->comboFields->set_popdown_strings(array(null));
        $this->MergeInterface->Buttons['breakPage']->set_active(true);

        $Content   = @$this->SelectList->Block['Select'][1];
        if ($Content)
        {
            $Elements  = $this->SelectList->GetSelectColumns(_a('Column'));
            if ($Elements)
            {
                # Refresh Breaks Screen
                $this->comboLegend->set_popdown_strings($Elements);
                $this->LevelsArea->comboBFields->set_popdown_strings($Elements);
                $this->LevelsArea->comboBBreaks->set_popdown_strings(array_merge(array(_a('Level') . ' 0 : ' . _a('Grand Total')),
                                                                     $Elements));

                $this->MergeInterface->LoadFields($Elements, @array_keys($this->Parameters));

                foreach($Elements as $Element)
                {
                    $this->PlottedColumns->Append(array($Element));
                }
            }
        }
    }

    /***********************************************************/
    /* Shows the popup menu for SubQueryFields
    /***********************************************************/
    function PopMainQueryFields()
    {
        global $Pixmaps;
        if ($this->MainQueryFields)
        {
            $Menu = new GtkMenu;
            $i = 1;
            foreach ($this->MainQueryFields as $MainQueryField)
            {
                $var = "\$var{$i}";
                $Menuitem = new MyNormalMenuItem($Pixmaps['field'], $MainQueryField,  array(&$this, 'TypeExpression'), $var);
                $Menu->append($Menuitem);
                $i ++;
            }
            if (is_array($this->SubQueryFields))
            {
                foreach ($this->SubQueryFields as $number=>$SubQueryFields)
                {
                    $i = 1;
                    if ($number <= 0)
                    {
                        $number = null;
                    }
                    $subMenu = new MyNormalMenuItem($Pixmaps['field'], _a('Sub Report') . ' ' . $number);
                    $subMenuItem = new GtkMenu;
                    foreach ($SubQueryFields as $SubQueryField)
                    {
                        $var = "\${$number}subfield{$i}";
                        $Menuitem = new MyNormalMenuItem($Pixmaps['field'], $SubQueryField,  array(&$this, 'TypeExpression'), $var);
                        $subMenuItem->append($Menuitem);
                        $i ++;
                    }
                    $subMenu->set_submenu($subMenuItem);
                    $Menu->append($subMenu);
                }
            }
            $Menu->show_all();
            $Menu->popup(null, null, null, 1, 1);
        }
    }

    /***********************************************************/
    /* Shows the popup menu for Parameters
    /***********************************************************/
    function PopParameters()
    {
        global $Pixmaps;
        if ($this->Parameters)
        {
            $Menu = new GtkMenu;
            $Parameters = array_keys($this->Parameters);
            foreach ($Parameters as $parameter)
            {
                $Menuitem = new MyNormalMenuItem($Pixmaps['ico_edit'], $parameter,  array(&$this, 'TypeExpression'), $parameter);
                $Menu->append($Menuitem);
            }
            $Menu->show_all();
            $Menu->popup(null, null, null, 1, 1);
        }
        else
        {
            if (!$this->subQuery)
            {
                $this->CustomParameters();
            }
        }
    }

    function TypeExpression($text)
    {
        $size = $this->textExpression->get_length();
        if ($size > 0)
            $this->textExpression->insert_text(" $text", $size);
        else
            $this->textExpression->insert_text("$text ", 0);
    }

    /***********************************************************/
    /* Shows the popup menu at Table Structure Tree...
    /***********************************************************/
    function PopTableStructure()
    {
        $text = $this->ctreeTableStructure->GetInfo();
        if (count(explode(':', $text)) == 2)
        {
            $Menu = new GtkMenu;
            $names = $this->names;
            $open_img  = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'menu_open.xpm');
            $del_img   = Gdk::pixmap_create_from_xpm($this->window->window, null,  images . 'menu_del.xpm');

            $Menuitem1 = new MyNormalMenuItem($open_img, _a('Open'),   array(&$this->ctreeTableStructure, 'ExpandCurrent'));
            $Menuitem2 = new MyNormalMenuItem($del_img,  _a('Delete'), array(&$this, 'RemoveTable'));
            $Menu->append($Menuitem1);
            $Menu->append($Menuitem2);

            $Menu->show_all();
            $Menu->popup(null, null, null, 1, 1);
        }
        else
        {
            $selection = $this->ctreeTableStructure->selection;
            if (count($selection) == 1)
            {
                $this->entryDataDescription->set_editable(true);
                $nick = $this->SeekDataDescription($text);
                $this->entryDataDescription->set_text($nick);
            }
            else
            {
                $this->entryDataDescription->set_text('');
                $this->entryDataDescription->set_editable(false);
            }
        }
    }

    function RemoveTable()
    {
        $text = $this->ctreeTableStructure->GetInfo();
        $pieces = explode(':', $text);
        $table = $pieces[1];
        $this->ctreeTableStructure->RemoveCurrent();

        $new = remove_table_from($this->SelectList->Block['From'][1], $table);

        if ($new)
        {
            $this->SelectList->SetBlock('From', $new);
            $this->LoadTablesFrom();
            $this->removelink();
        }
        else
        {
            $this->SelectList->SetBlock('From', '');
            $this->RequiredTables = null;
            $this->LoadBlocks();
            $this->removelink();
        }
        $this->checklink();
    }

    /***********************************************************/
    /* Launched when users change the SQL Page
    /***********************************************************/
    function ChangePageSQL()
    {
        if ($this->QueryNotebook->get_current_page() == 0)
        {
            $this->QueryNotebook->set_page(1);
        }
        else if ($this->QueryNotebook->get_current_page() == 2)
        {
            $selection = $this->ctreeTableStructure->selection;
            $node = $selection[0];
            if ($node)
            {
                $Info = $this->ctreeTableStructure->node_get_row_data($node);
                $Info = explode(':', $Info);

                $Table = $Info[1];
                $Field = $Info[3];

                if ($Field)
                {
                    $this->TypeExpression("$Table.$Field");
                }
            }
        }
    }

    /***********************************************************/
    /* Launched when users change the Graph Page
    /***********************************************************/
    function ChangePageGraph($page)
    {
        $this->GraphNotebook->set_page($page);
    }

    /***********************************************************/
    /* Refresh the screen
    /***********************************************************/
    function LoadBlocks($init = false, $Changed = true)
    {
        if ($init)
        {
            $this->SelectList->LoadBlocks(null);

            $this->textAreas['@ReportDescription']->delete_text(0, -1);
            $this->textAreas['@ManualQuery']->delete_text(0, -1);
            $this->textAreas['@GraphIntroduction']->delete_text(0, -1);

            if (!$this->subQuery)
            {
                $this->LevelsArea->ClearBreaks();
                $this->HeaderFooterArea->Clear();
                $this->MergeInterface->Clear();
                $this->entryTitle->set_text('');
                $this->entryTitlex->set_text('');
                $this->entryTitley->set_text('');
                $this->entrySizeX->set_text('480');
                $this->entrySizeY->set_text('268');
                $this->radios['checkShowGroup']->set_active(false);
                $this->radios['checkShowDetail']->set_active(true);
                $this->radios['checkShowTotal']->set_active(false);
                $this->radios['checkShowNumber']->set_active(true);
                $this->radios['checkShowIndent']->set_active(true);
                $this->Properties = null;
                $this->SelectList->setDistinct(false);
                $this->SelectList->setOffSet(0);
                $this->SelectList->setLimit(0);
                $this->MergeInterface->SubSelectList[0]->setOffSet(0);
                $this->MergeInterface->SubSelectList[0]->setLimit(0);
                $this->MergeInterface->SubSelectList[0]->setDistinct(false);
                $this->LevelsArea->entryTotalLabel->set_text('');
                $this->OfficeInterface->entryOpenOffice->set_text('');
            }

            $this->textExpression->delete_text(0, -1);
            $this->Adjustments = null;
            $this->SubAdjustments = null;
            //$this->ParametersContent = null;
            $this->Parameters = null;
        }

        $this->SelectList->Reload();
        $this->DrawAdjustments(0);
        if (!$this->subQuery)
        {
            $this->LevelsArea->DrawLevels($this->SelectList->Block['Select'][1]);
            for ($x=0; $x<=$this->MergeInterface->numberSubReport; $x++)
            {
                $this->MergeInterface->SubSelectList[$x]->Reload();
                $this->DrawAdjustments(1, $x);
            }
        }

        $this->Changed = $Changed;
        $char = $Changed ? ' *' : '';

        $this->window->set_title($this->Title . $this->FileName . $char);
    }

    /***********************************************************/
    /* Draws breaks structure.
    /***********************************************************/
    function DrawAdjustments($sub, $currentSubReport = null)
    {
        if ($sub)
        {
            if (!$currentSubReport)
            {
                $currentSubReport = $this->MergeInterface->GetCurrentSubReport();
            }
            $Elements = $this->MergeInterface->SubSelectList[$currentSubReport]->GetSelectColumns(_a('Column'));
            if ($currentSubReport == 0)
            {
                $Adjustments = $this->SubAdjustments;
            }
            else
            {
                $Adjustments = $this->MergeInterface->SubAdjustments[$currentSubReport];
            }
        }
        else
        {
            $Elements = $this->SelectList->GetSelectColumns(_a('Column'));
            $Adjustments = $this->Adjustments;
        }

        if ($Elements)
        {
            $i = 1;
            foreach ($Elements as $Element)
            {
                $oFunction    = new FunctionBox(_a('Functions'));
                $oCondHigh    = new CondHighBox(_a('Conditional highlight'));
                $oCross       = new CrossBox(_a('Fields'));
                $oCross->info = $Element;
                $oMask        = new MaskBox(_a('Format'));

                $chars      = $Adjustments[$i]['Chars']       ? $Adjustments[$i]['Chars']   : 30;
                $points     = $Adjustments[$i]['Points']      ? $Adjustments[$i]['Points']  : 60;
                $align      = $Adjustments[$i]['Align']       ? $Adjustments[$i]['Align']   : 'left';
                $mask       = $Adjustments[$i]['Mask']        ? $Adjustments[$i]['Mask']    : '';
                $cross      = $Adjustments[$i]['Cross']       ? $Adjustments[$i]['Cross']   : '';
                $function   = $Adjustments[$i]['Function']    ? $Adjustments[$i]['Function']: '';
                $conditional= $Adjustments[$i]['Conditional'] ? $Adjustments[$i]['Conditional']: '';

                $Vars[$Element][] = array($chars,       _a('Characters'), false, 'spin',                           true);
                $Vars[$Element][] = array($points,      _a('Points'),     false, 'spin',                           true);
                $Vars[$Element][] = array($align,       _a('Align'),      false, array('left', 'center', 'right'), false);
                $Vars[$Element][] = array($mask,        _a('Mask'),       false, $oMask,                           false);
                $Vars[$Element][] = array($function,    _a('Functions'),  false, $oFunction,                       true);
                $Vars[$Element][] = array($cross,       _a('Cross'),      false, $oCross,                           true);
                $Vars[$Element][] = array($conditional, _a('Conditional highlight'),false, $oCondHigh,                       true);

                $i ++;
            }
        }

        if ($sub)
        {
            if ($currentSubReport == 0)
            {
                if ($this->SubAdjustmentsConfig)
                {
                    $this->SubAdjustmentsConfig->Clear();
                    $this->SubAdjustmentsConfig->FillForms($Vars);
                }
            }
            else
            {
                if ($this->MergeInterface->SubAdjustmentsConfig[$currentSubReport])
                {
                    $this->MergeInterface->SubAdjustmentsConfig[$currentSubReport]->Clear();
                    $this->MergeInterface->SubAdjustmentsConfig[$currentSubReport]->FillForms($Vars);
                }
            }
        }
        else
        {
            if ($this->AdjustmentsConfig)
            {
                $this->AdjustmentsConfig->Clear();
                $this->AdjustmentsConfig->FillForms($Vars);
            }
        }
    }

    /***********************************************************/
    /* onBreakAction
    /***********************************************************/
    function onBreakAction($ordering)
    {
        $this->SelectList->Reload();
        $this->SelectList->SetBlock('Order by', $ordering);
    }

    /***********************************************************/
    /* Clear the expression (constraints) field
    /***********************************************************/
    function ClearExpr()
    {
        $this->textExpression->delete_text(0, -1);
    }

    /***********************************************************/
    /* Transfer a expression to Where clause
    /***********************************************************/
    function WriteItDown()
    {
        $text = $this->textExpression->get_chars(0, -1);
        if ($text)
        {
            $this->SelectList->Concatenate('Where', ($this->SelectList->Block['Where'][1]) ? ' and ' : '');
            $this->SelectList->Concatenate('Where', "$text");
            $this->LoadBlocks();
            $this->ClearExpr();
        }
    }


    /***********************************************************/
    /* Paste Agata'SQL as a Free Hand SQL
    /***********************************************************/
    function DownSql()
    {
        $sql = $this->ReturnSql();
        $this->textAreas['@ManualQuery']->insert(null, null, null, $sql);
    }

    /***********************************************************/
    /* Converts Free Hand SQL into Agata'SQL
    /***********************************************************/
    function UpSql()
    {
        $sql = ereg_replace("\n", ' ', $this->textAreas['@ManualQuery']->get_chars(0, -1));
        $this->ClearQuery(true, false);
        $this->SelectList->LoadBlocks(CoreReport::SqlToBlock($sql));
        $this->LoadTablesFrom();
    }

    /***********************************************************/
    /* Get the Required Tables and put them on the screen
    /***********************************************************/
    function RequireTables()
    {
        $this->ctreeTables->freeze();
        $selection = $this->ctreeTables->selection;
        $strings = null;

        if (!$this->connected)
        {
            new Dialog(_a('Cannot connect to Database'));
            return false;
        }

        foreach ($selection as $SelectionLine)
        {
            $text = $this->ctreeTables->node_get_row_data($SelectionLine);
            {
                $strings[] = $text;
            }
        }

        if ($strings)
        {
            $this->SuggestTables($strings);
        }

        $require = new LinkedTables(array(&$this, 'SuggestTables'), $this->TableNames, $this->agataTbLinks, $this->agataDataDescription, $this->agataDB);
        $HierarquicalLinks = $require->CallLinks($strings, $this->RequiredTables, true);

        if ($HierarquicalLinks)
        {
            $require->BuildScreen($HierarquicalLinks);
        }

        $this->ctreeTables->thaw();
    }

    /***********************************************************/
    /* Get the Required Tables and put them on the screen
    /***********************************************************/
    function JoinTables()
    {
        $this->ctreeTables->freeze();
        $selection = $this->ctreeTables->selection;
        $strings = null;

        if (!$this->connected)
        {
            new Dialog(_a('Cannot connect to Database'));
            return false;
        }

        foreach ($selection as $SelectionLine)
        {
            $text = $this->ctreeTables->node_get_row_data($SelectionLine);
            {
                $strings[] = $text;
                $last = $text;
            }
        }

        if ($strings)
        {
            $this->SuggestTables($strings);
        }

        $require = new LinkedTables(array(&$this, 'JoinTheseTables'), $this->TableNames, $this->agataTbLinks, $this->agataDataDescription, $this->agataDB);
        $HierarquicalLinks = $require->CallLinks(array($last), $this->RequiredTables, false);

        //if ($HierarquicalLinks)
        {
            $require->BuildScreen($HierarquicalLinks, true, $last, $this->TableNames);
        }
        //else
        {
            //new Dialog(_a('No Links'), true, false, _a('Further Information: ^1', _a('Data Dictionary')));
        }

        //$this->autolink();
        $this->ctreeTables->thaw();
    }

    function LoadTables($tables, $clear = false)
    {
        if ($clear)
        {
            $this->ctreeTableStructure->ClearTree();
        }
        $this->ctreeTableStructure->LoadTheseTables($this->agataDB, $tables, $this->agataDataDescription);
    }

    function LoadTablesFrom()
    {
        $From = $this->SelectList->Block['From'][1];
        $aFrom = get_tables_from($From);
        $this->RequiredTables = $aFrom;

        if ($aFrom)
        {
            if (!$this->connected)
            {
                new Dialog(_a('Cannot connect to Database'));
                $this->LoadBlocks(null, false);
                return false;
            }
            $this->LoadTables($aFrom, true);
        }
        $this->LoadBlocks(null, false);
    }

    function SuggestTables($tables)
    {
        foreach ($tables as $table)
        {
            if ($this->RequiredTables)
            {
                if (!in_array($table, $this->RequiredTables) and ($table))
                {
                    $loadtables[] = $table;
                }
            }
            else
            {
                $loadtables[] = $table;
            }
        }

        if ($loadtables)
        {
            $this->LoadTables($loadtables, false);
            $MergeTables = array_merge($this->RequiredTables, $loadtables);
            $this->RequiredTables = $MergeTables;

            $list = implode(',', $loadtables);
            if ($this->SelectList->Block['From'][1])
            {
                $this->SelectList->Concatenate('From',",$list");
            }
            else
            {
                $this->SelectList->Concatenate('From', "$list");
            }

            $this->autolink($tables);
            $this->LoadBlocks();
        }
    }

    function JoinTheseTables($table1, $table2, $inner, $condition)
    {
        $loadtables = array($table1, $table2);
        $this->LoadTables($loadtables, false);

        $newpiece = "$table1 $inner $table2 on ($condition) ";
        if (strpos($this->SelectList->Block['From'][1], "$table1,") !== false)
        {
            $this->SelectList->SetBlock('From', str_replace("$table1,", "$newpiece,", $this->SelectList->Block['From'][1]));
        }
        else if (strpos($this->SelectList->Block['From'][1], "$table1 ") !== false)
        {
            $this->SelectList->SetBlock('From', str_replace("$table1 ", "$newpiece ", $this->SelectList->Block['From'][1]));
        }
        else if (strpos($this->SelectList->Block['From'][1], ", $table1") !== false)
        {
            $this->SelectList->SetBlock('From', str_replace(", $table1", ", $newpiece", $this->SelectList->Block['From'][1]));
        }
        else if (strpos($this->SelectList->Block['From'][1], ",$table1") !== false)
        {
            $this->SelectList->SetBlock('From', str_replace(",$table1", ",$newpiece", $this->SelectList->Block['From'][1]));
        }
        else if ($this->SelectList->Block['From'][1] == $table1)
        {
            $this->SelectList->SetBlock('From', str_replace($table1, $newpiece, $this->SelectList->Block['From'][1]));
        }
        else
        {
            $this->SelectList->Concatenate('From', ', ' . $newpiece);
        }
        $this->LoadBlocks();
    }

    /***********************************************************/
    /* Clear Fields of the Screen
    /***********************************************************/
    function ClearTables($delAllDetail = false)
    {
        $this->ctreeTableStructure->ClearTree();
        $this->RequiredTables = null;
        $this->SelectList->SetBlock('From', '');
        $this->LoadBlocks(null);

        if (isset($this->MergeInterface) && $delAllDetail )
        {
            $this->MergeInterface->delAllDetail();
        }
    }

    /***********************************************************/
    /* Insert a selected field to a specific clause
    /***********************************************************/
    function InsertOnBlock($index, $more=null)
    {
        $more = ($more) ? trim($more) : $more;
        $strings = null;
        $selection = $this->ctreeTableStructure->selection;
        foreach ($selection as $node)
        {
            $Info = $this->ctreeTableStructure->node_get_row_data($node);
            $Info = explode(':', $Info);

            $Table = $Info[1];
            $Field = $Info[3];
            $Structure = false;

            if ($Field)
            {
                $Structure = true;
                if (($index == 'Select') or ($index == 'Group by'))
                {
                    $user_nick = $this->entryDataDescription->get_text();
                    $nodeindex = "table:$Table:field:$Field";
                    $sys_nick  = $this->SeekDataDescription($nodeindex);
                    $nick      = $user_nick ? $user_nick : $sys_nick;

                    $this->SelectList->Concatenate($index, ($this->SelectList->Block[$index][1]) ? ', ' : '');
                    $nodetext = ($nick) ? ' as "' . $nick .'"': '';
                    $this->SelectList->Concatenate($index, ("$Table.$Field" . $nodetext));
                }
                else if ($index == 'Order by')
                {
                    $direction = ($this->radios['AscendantOrder']->get_active()) ? 'asc' : 'desc';

                    $this->SelectList->Concatenate($index, ($this->SelectList->Block[$index][1]) ? ', ' : '');
                    $this->SelectList->Concatenate($index, "$Table.$Field $direction");
                }
                else
                {
                    $this->TypeExpression("$more");
                }
            }
        }

        if (!$Structure)
        {
            new Dialog(_a('Select field, please'));
            return null;
        }
        $this->LoadBlocks();
        $this->window->set_focus($this->textExpression);
    }

    /*
    * Suggests the user to aprove the link among tables
    */
    function autolink($tables)
    {
        $From  = get_tables_from($this->SelectList->Block['From'][1]);
        $Where = $this->SelectList->Block['Where'][1];

        // Verify if required tables are foreign of From tables
        if ($tables)
        {
            foreach($tables as $table)
            {
                $table = trim($table);
                $links = $this->agataTbLinks[$table];
                if ($links)
                {
                    foreach ($links as $field => $fk_reg)
                    {
                        if (in_array($fk_reg[0], $From))
                        {
                            $text = $table . '.'. $field . ' = '.$fk_reg[0]. '.'.$fk_reg[1];
                            if ((strpos($Where, $text) === false) and ($table != $fk_reg[0])) // evitar c�clico
                            {
                                $linking[] = array(1, $table, $field, '=', $fk_reg[0], $fk_reg[1]);
                                $Where .= ' ' . $text;
                            }
                        }
                    }
                }
            }
        }

        // Verify if From tables are foreign of required tables
        if ($From)
        {
            foreach($From as $table)
            {
                $table = trim($table);
                $links = $this->agataTbLinks[$table];
                if ($links)
                {
                    foreach ($links as $field => $fk_reg)
                    {
                        if (in_array($fk_reg[0], $tables))
                        {
                            $text = $table . '.'. $field . ' = '.$fk_reg[0]. '.'.$fk_reg[1];
                            if ((strpos($Where, $text) === false) and ($table != $fk_reg[0]))// evitar c�clico
                            {
                                $linking[] = array(1, $table, $field, '=', $fk_reg[0], $fk_reg[1]);
                                $Where .= ' ' . $text;
                            }
                        }
                    }
                }
            }
        }
        if ($linking)
        {
            new LinkMatch(array(&$this, 'ApplyLink'), null, null, null, $linking, $this->agataDataDescription, $this->agataDB);
        }
    }

    function ApplyLink($table1, $table2, $joinkind, $condition)
    {
        if ($condition)
        {
            $this->SelectList->Concatenate('Where', ($this->SelectList->Block['Where'][1]) ? ' and ' : '');
            $this->SelectList->Concatenate('Where', "$condition");
            $this->LoadBlocks();
        }
    }


    /***********************************************************/
    /* Get the linking settings among the tables (From clause)
    /* record these linking settings in the where clause
    /***********************************************************/
    function removelink()
    {
        $From  = $this->SelectList->Block['From'][1];
        $Where = $this->SelectList->Block['Where'][1];
        $WhereElements = explode(' and ', $Where);

        $aFrom_ = explode(',', $From);
        foreach ($aFrom_ as $From)
        {
            $aFrom[] = trim($From);
        }

        if ($Where)
        {
            foreach ($WhereElements as $element)
            {
                if (ereg('^(.*)\.(.*)=(.*)\.(.*)$', $element))
                {
                    $elements = explode('=', $element);
                    if ($aFrom)
                    {
                        $Aside = $Bside = false;
                        foreach ($aFrom as $FromTable)
                        {
                            if (strpos($elements[0], $FromTable . '.') !== false)
                            {
                                $Aside = true;
                            }
                            if (strpos($elements[1], $FromTable . '.') !== false)
                            {
                                $Bside = true;
                            }
                        }
                        if ($Aside and $Bside)
                        {
                            $Constraints[] = trim($element);
                        }
                    }
                }
                else
                {
                    $Constraints[] = trim($element);
                }
            }
        }

        if ($aFrom)
        {
            $this->SelectList->SetBlock('Where', '');
            if ($Constraints)
            {
                foreach ($Constraints as $key)
                {
                    $this->SelectList->Concatenate('Where', ($this->SelectList->Block['Where'][1]) ? ' and ' : '');
                    $this->SelectList->Concatenate('Where', "$key");
                }
            }
            $this->LoadBlocks();
        }
    }


    /***********************************************************/
    /* Verify if exists links among all the FROM tables
    /***********************************************************/
    function checklink()
    {
        $From  = $this->SelectList->Block['From'][1];
        $Where = $this->SelectList->Block['Where'][1];
        $WhereElements = explode(' and ', $Where);

        $aFrom_ = explode(',', $From);
        foreach ($aFrom_ as $From)
        {
            $aFrom[] = trim($From);
        }

        $LinkedTables = array();
        if ($Where)
        {
            foreach ($WhereElements as $element)
            {
                if (ereg('^(.*)\.(.*)=(.*)\.(.*)$', $element))
                {
                    $elements = explode('=', $element);
                    if ($aFrom)
                    {
                        $Aside = $Bside = false;
                        foreach ($aFrom as $FromTable)
                        {
                            if (strpos($elements[0], $FromTable . '.') !== false)
                            {
                                $Aside = true;
                                $ATable = $FromTable;
                            }
                            if (strpos($elements[1], $FromTable . '.') !== false)
                            {
                                $Bside = true;
                                $BTable = $FromTable;
                            }
                        }
                        if ($Aside and $Bside)
                        {
                            $LinkedTables[] = $ATable;
                            $LinkedTables[] = $BTable;
                        }
                    }
                }
            }
        }
        # Find all items that are in aFrom and not in LinkedTables
        $NotLinked = array_diff($aFrom, $LinkedTables);
    }


    function SeekDataDescription($index)
    {
        if ($this->agataDataDescription[$index])
        {
            return $this->agataDataDescription[$index];
        }
        else
        {
            $pieces = explode(':', $index);
            if (count($pieces) == 2)
            {
                $upper = $this->agataDataDescription[$pieces[0] . ':' . strtoupper($pieces[1])];
                $lower = $this->agataDataDescription[$pieces[0] . ':' . strtolower($pieces[1])];

                return ($upper ? $upper : $lower);
            }
            else
            {
                $upper = $this->agataDataDescription[$pieces[0] . ':' . strtoupper($pieces[1]) . ':' . $pieces[2] . ':' . strtoupper($pieces[3])];
                $lower = $this->agataDataDescription[$pieces[0] . ':' . strtolower($pieces[1]) . ':' . $pieces[2] . ':' . strtolower($pieces[3])];

                return ($upper ? $upper : $lower);
            }
        }
    }

    /***********************************************************/
    /* Get the Query as the SQL instruction
    /***********************************************************/
    function ReturnSQL($kind = 1)
    {
        if ($kind == 1)
        {
            $sql = CoreReport::BlockToSql($this->SelectList->Block, $this->SelectList->getDistinct(), true);
        }
        else
        {
            $sql = CoreReport::BlockToSql($this->MergeInterface->SubSelectList[0]->Block, true);
        }
        return $sql;
    }

    /***********************************************************/
    /* Prepare a report to be generated
    /* asks the user for the parameters
    /***********************************************************/
    function PreReport($type)
    {
        global $Pixmaps;

        if (!$this->connected)
        {
            new Dialog(_a('Cannot connect to Database'));
            return false;
        }

        $sql  = $this->ReturnSQL(1);

        /* Bloqueio de pelo servidor online*/
        global $agataServer;
        global $agataConfig;
        global $serverConn;
        if ($agataConfig['general']['AuthenticateServer'] && $serverConn && $agataServer)
        {
            if (!$result = $this->verifySql($sql, $tables) )
            {
                $notAccess = implode(', ', $tables);
                new Dialog (_a("You don't has permission to access this tables:") . ' ' .$notAccess);
                return false;
            }
            else
            {
                //echo 'Voc� tem permiss�o.';
            }
        }

        /**/
        $sql2 = $this->ReturnSQL(2);

        if (!$sql)
        {
            new Dialog(_a('You Have to build the SQL Query firstly'));
            return false;
        }

        if (($type == 'lines') or ($type == 'bars'))
        {
            if (!$this->TestB4Graph())
            {
                return false;
            }
        }
        $Parameters = $this->Parameters;
        if ($Parameters)
        {
            $n = 0;
            foreach ($Parameters as $Parameter => $Properties)
            {
                $ParameterList[] = array($Properties['value'], $Parameter,  false, null, true, '', $Properties['mask']);
                $this->FormIndex[$n] = $Parameter;
                $n ++;
            }

            $this->ParametersFormEntry = new FormEntry(_a('Parameters'), $ParameterList);
            $this->ParametersFormEntry->SetStatus(_a('Type the query parameters above'));

            $this->button_handler = $this->ParametersFormEntry->button->connect_object('clicked', array(&$this,'ReturnParameters'), $type);
            $this->ParametersFormEntry->Show();
        }
        else
        {
            if (in_array($type, array('screen', 'txt', 'sql', 'xml', 'csv', 'lines', 'bars', 'MergePdf', 'ParseOO', 'MergeSxw', 'MergePdfTmp', 'TxtForm')))
            {
                $this->DialogOutput($type, null);
            }
            else
            {
                $items = Layout::ListLayouts();
                $this->LayoutList = new IList($items, array(&$this, 'ReturnLayout'), $Pixmaps['ico_colors'], _a('Choose the Layout'), _a('Layout Name'), $type);
            }
        }
    }

    /**
    * Verify if user has permission to acces this sql
    *
    * @param $sql the sql to test
    * @param $tables the array of tables that user has permission
    * @return boolean false if user dont has permission
    */
    function verifySql($sql, &$tables)
    {
        $sql = strtolower($sql);
        $ok = true;
        if ($this->connected)
        {
            $conn = new AgataConnection();
            if($conn->Open($this->agataDB))
            {
                $diffTables = $conn->LoadTables('DIFF');
            }
            else
            {
                $conn->close();
                return false;
            }
        }
        if ($diffTables)
        {
            foreach ($diffTables as $line => $info)
            {
                $pos = strpos(strtolower($sql), strtolower($info));
                if ($pos > 0)
                {
		    $tables[] = $info;
                    $ok = false;
                }
            }
        }
        return $ok;
    }

    /***********************************************************/
    /* Replace the Parameters by the content
    /***********************************************************/
    function ReturnParameters($type)
    {
        global $Pixmaps;
        $aInputEntries = $this->ParametersFormEntry->InputEntries['unique'];
        $this->ParametersFormEntry->Close(null);
        //$this->ParametersContent = null;
        $this->clearParametersContent();
        foreach ($aInputEntries as $key => $InputEntry)
        {
            //$this->ParametersContent[$this->Parameters[$key]] = $InputEntry->get_text();
            $this->Parameters[$this->FormIndex[$key]]['value'] = $InputEntry->get_text();
            //echo $key . $this->FormIndex[$key] . "\n" ;
            //;
        }

        if (in_array($type, array('screen', 'txt', 'sql', 'xml', 'csv', 'lines', 'bars', 'MergePdf', 'ParseOO', 'MergeSxw', 'MergePdfTmp', 'TxtForm')))
        {
            $this->DialogOutput($type, null);
        }
        else
        {
            $items = Layout::ListLayouts();
            $this->LayoutList = new IList($items, array(&$this, 'ReturnLayout'), $Pixmaps['ico_colors'], _a('Choose the Layout'), _a('Layout Name'), $type);
        }
    }

    function ReturnLayout($type)
    {
        $layout = $this->LayoutList->GetItem();
        if ($layout)
        {
            $this->DialogOutput($type, $layout);
        }
    }


    /************************************************************
                        PreReport  ==============\
                            ||                  ||
                            \/                  ||
                        ReturnParameters        ||
                            ||                  ||
                            \/                  ||
                        ChooseLayout   <========||
                            ||                  ||
                            \/                  ||
                        ReturnLayout            ||
                            ||                  ||
                            \/                  ||
                        DialogOutput  <===============/
                            ||
                            \/
                        CreateReport
    ************************************************************/


    function DialogOutput($type, $layout)
    {
        if ($type == 'screen')
        {
            $this->CreateReport(null, array($type, $layout));
        }
        else if ($type == 'MergePdfTmp')
        {
            $type = 'MergePdf';
            $FileName = $this->agataConfig['general']['TmpDir'] . bar . md5(rand(1, 9999)) . '.pdf';
            $this->CreateReport($FileName, array($type, null));
        }
        else
        {
            $OutputDir = $this->agataConfig['general']['OutputDir'];
            $button1 = array('interface/output.xpm', _a('Output'), $OutputDir);
            $mask = $type;
            if ($type == 'TxtForm')   $mask = 'txt';
            if ($type == 'HtmlFrame') $mask = 'html';
            if ($type == 'MergePdf')  $mask = 'pdf';
            if ($type == 'MergeSxw')  $mask = 'sxw';
            if ($type == 'ParseOO')   $mask = 'sxw';
            if ($type == 'lines')     $mask = ($this->radios['radioHTML']->get_active()) ? 'html' : 'sxw';
            if ($type == 'bars')      $mask = ($this->radios['radioHTML']->get_active()) ? 'html' : 'sxw';
            $messg = _a('Export to ' . strtoupper($mask) . ' File');
            $dialog = new FileDialog($messg, array($mask), $button1, $OutputDir, array(&$this, 'CreateReport'), array($type, $layout));
            if ($this->FileName)
            {
                $dialog->set_filename(RemoveExtension(GetFileName($this->FileName)) . ".{$mask}");
            }
        }
    }

    /***********************************************************/
    /* Launch the correct report classes (html, ps)...
    /***********************************************************/
    function CreateReport($FileName, $params)
    {
        if (is_object($FileName))
        {
            $FileName->hide();
            $FileName = $FileName->get_filename();
        }

        $type   = $params[0];
        $layout = $params[1];

        $XmlArray = $this->GetXmlArray();

        $DataSet = $XmlArray['Report']['DataSet'];
        //$CurrentQuery = AgataCore::CreateQuery($this->agataDB, $DataSet, $this->ParametersContent);
        $CurrentQuery = AgataCore::CreateQuery($this->agataDB, $DataSet, $this->getParametersContent());


        $posAction = array(&$this, 'RefreshRepository');

        if (!is_agata_error($CurrentQuery))
        {
            $params[0] = $this->agataDB;
            $params[1] = $this->agataConfig;
            $params[2] = $FileName;
            $params[3] = $CurrentQuery;
            $params[4] = $XmlArray;
            $params[5] = $posAction;

            if ($type=='screen')
            {
                $params[2] = '';

                $obj = AgataCore::CreateReport($type, $params);
                $obj->Process();
            }
            else if (in_array($type, array('pdf', 'txt', 'xml', 'html', 'csv', 'sxw', 'TxtForm', 'HtmlFrame', 'sql')))
            {
                $params[6] = $layout;

                $obj = AgataCore::CreateReport($type, $params);
                $obj->GetReportName();
            }
            else if ($type=='lines')
            {
                $selection = $this->PlottedColumns->clist->selection;
                foreach ($selection as $line)
                {
                    $PlottedColumns[] = $this->PlottedColumns->GetItem($line,0);
                }

                $params[6]= $PlottedColumns;
                $entry = $this->comboLegend->entry;
                $legend = $entry->get_text();

                $obj = AgataCore::CreateGraph($params);
                $obj->Lines($legend);
            }
            else if ($type=='bars')
            {
                $selection = $this->PlottedColumns->clist->selection;
                foreach ($selection as $line)
                {
                    $PlottedColumns[] = $this->PlottedColumns->GetItem($line,0);
                }

                $params[6]= $PlottedColumns;
                $entry = $this->comboLegend->entry;
                $legend = $entry->get_text();

                $obj = AgataCore::CreateGraph($params);
                $obj->Bars($legend);
            }
            else if ($type=='MergePdf')
            {
                //$params[6] = $this->ParametersContent;
                $params[6] = $this->getParametersContent();

                if ($this->MergeInterface->MergeNotebook->get_current_page() == 0)
                {
                    $params[7] = $this->MergeInterface->Buttons['breakPage']->get_active();
                    $obj = AgataCore::CreateMergedDocument($params, 'Pdf');
                    $obj->Generate();
                }
                else
                {
                    $obj = AgataCore::CreateAddressLabel($params);
                    $obj->Generate();
                }
            }
            else if ($type=='ParseOO')
            {
                $params[6] = $this->getParametersContent();

                $Source = $XmlArray['Report']['OpenOffice']['Source'];
                $config = AgataOO::GetConfig($Source);
                $obj = AgataCore::ParseOpenOffice($params, 'Sxw', $config['engine']);
                $obj->Generate($Source, $FileName);
            }
            else if ($type=='MergeSxw')
            {
                //$params[6] = $this->ParametersContent;
                $params[6] = $this->getParametersContent();

                if ($this->MergeInterface->MergeNotebook->get_current_page() == 0)
                {
                    $obj = AgataCore::CreateMergedDocument($params, 'Sxw');
                    $obj->Generate();
                }
                else
                {
                    $obj = AgataCore::CreateAddressLabel($params);
                    $obj->Generate();
                }
            }
        }
    }

    /***********************************************************/
    /* Refresh the Repository tree
    /***********************************************************/
    function RefreshRepository()
    {
        $this->ctreeRepository->ReadAgain();
    }

    /***********************************************************/
    /* Refresh the Reports tree
    /***********************************************************/
    function RefreshReports()
    {
        $this->ctreeReports->ReadAgain();
    }

    /***********************************************************/
    /* Open a Report from Tree
    /***********************************************************/
    function OpenReportFromTree($flag = false)
    {
        $FileTree = $this->ctreeReports;
        $node = $FileTree->selection[0];
        $Info = $this->ctreeReports->node_get_row_data($node);

        if (!$Info)
        return true;

        if (($this->Changed) && (!$flag))
        {
            $b = new Ask(_a('Query not saved, Do you really want to continue ?'), array(&$this,'OpenReportFromTree'), null, array(true));
            return true;
        }
        else
        {
            if ($Info)
            {
                $FileName = $Info;
                $this->LoadReport( $FileName );
            }
            //$this->ChangePageAgata();
        }
        return true;
    }

    /***********************************************************/
    /* Open a SQL from Tree
    /***********************************************************/
    function OpenSQLFromTree($flag = false)
    {
        $FileTree = $this->ctreeTemplates;
        $node = $FileTree->selection[0];
        $Info = $this->ctreeTemplates->node_get_row_data($node);

        if (!$Info)
        {
            return true;
        }

        if ($Info)
        {
            $FileName = $Info;
            if (!file_exists($FileName))
            {
                return;
            }
            $XmlArray = CoreReport::OpenSql($FileName);
            if ($XmlArray)
            {
                $this->SelectList->LoadBlocks(CoreReport::ExtractBlock($XmlArray));
            }
            $this->LoadTablesFrom();
        }

        return true;
    }

    /***********************************************************/
    /* Open a Repository item
    /***********************************************************/
    function OpenRepository($flag = false)
    {
        $FileTree = $this->ctreeRepository;
        $node = $FileTree->selection[0];
        $Info = $this->ctreeRepository->node_get_row_data($node);
        if (!$Info)
        {
            return true;
        }

        if (!$flag)
        {
            $b = new Ask(_a('Do you want to open this report ?'), array(&$this,'OpenRepository'), null, array(true));
            return true;
        }
        else
        {
            if (($Info) && ($Info))
            {
                $FileName = $Info;
                Project::OpenReport($FileName, $this->agataConfig);
            }
        }
        return true;
    }

    /***********************************************************/
    /* Open a Report
    /***********************************************************/
    function OpenReport( $fs )
    {
        $FileName = $fs->get_filename();
        if (file_exists($FileName))
        {
            $fs->hide();

            $this->LoadReport($FileName);
            //$this->ChangePageAgata();
        }
    }

    /***********************************************************/
    /* Load a SQL File
    /***********************************************************/
    function LoadReport($FileName)
    {
        if (!file_exists($FileName))
        {
            return;
        }
        $this->ClearQuery(true);
        $this->FileName = $FileName;

        $XmlArray = CoreReport::OpenReport($FileName);
        if ($XmlArray)
        {
            # Reading Parameters
            $Parameters = $XmlArray['Report']['Parameters'];
            if ($Parameters)
            {
                foreach ($Parameters as $paramKey => $paramValue)
                {
                    $this->Parameters['$' . $paramKey] = $paramValue;
                }
            }

            $this->SelectList->LoadBlocks(CoreReport::ExtractBlock($XmlArray['Report']['DataSet']));
            $this->Adjustments       = CoreReport::ExtractAdjustments($XmlArray['Report']['DataSet']);
            $this->SubAdjustments    = CoreReport::ExtractAdjustments($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet1']);

            for ($x=1; $x<=$XmlArray['Report']['Merge']['Details']['Detail1']['NumberSubSql']; $x++)
            {
                $this->MergeInterface->SubAdjustments[$x] = CoreReport::ExtractAdjustments($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet'.($x+1)]);;
            }

            $this->SelectList->setDistinct($XmlArray['Report']['DataSet']['Query']['Config']['Distinct']);
            $this->SelectList->setOffSet($XmlArray['Report']['DataSet']['Query']['Config']['OffSet']);
            $this->SelectList->setLimit($XmlArray['Report']['DataSet']['Query']['Config']['Limit']);

            # Reading Breaks
            $Breaks = CoreReport::ExtractBreaks($XmlArray);
            if ($Breaks)
            {
                foreach ($Breaks as $break=>$formula)
                {
                    $this->LevelsArea->SetBreak($break, $formula);
                }
            }

            # Read Just when it's not a sub-report screen
            if (!$this->subQuery)
            {
                $this->radios['checkShowGroup']->set_active( $XmlArray['Report']['DataSet']['Groups']['Config']['ShowGroup']);
                $this->radios['checkShowDetail']->set_active($XmlArray['Report']['DataSet']['Groups']['Config']['ShowDetail']);
                $this->radios['checkShowTotal']->set_active( $XmlArray['Report']['DataSet']['Groups']['Config']['ShowLabel']);
                $this->radios['checkShowNumber']->set_active($XmlArray['Report']['DataSet']['Groups']['Config']['ShowNumber']);
                $this->radios['checkShowIndent']->set_active($XmlArray['Report']['DataSet']['Groups']['Config']['ShowIndent']);

                $this->entryTitle->set_text($XmlArray['Report']['Graph']['Title']);
                $this->entryTitlex->set_text($XmlArray['Report']['Graph']['TitleX']);
                $this->entryTitley->set_text($XmlArray['Report']['Graph']['TitleY']);
                $this->entrySizeX->set_text($XmlArray['Report']['Graph']['Width'] ? $XmlArray['Report']['Graph']['Width'] : 480);
                $this->entrySizeY->set_text($XmlArray['Report']['Graph']['Height'] ? $XmlArray['Report']['Graph']['Height'] : 268);
                $this->radios['checkData']->set_active($XmlArray['Report']['Graph']['ShowData']);
                $this->radios['showValues']->set_active($XmlArray['Report']['Graph']['ShowValues']);
                $this->radios['radioColumns']->set_active(($XmlArray['Report']['Graph']['Orientation'] == 'columns'));
                $this->radios['radioLines']->set_active(($XmlArray['Report']['Graph']['Orientation'] == 'lines'));

                $this->MergeInterface->textSections['@LabelText']->add_text($XmlArray['Report']['Label']['Body']);

                $this->MergeInterface->entries['HSpacing']->set_text($XmlArray['Report']['Label']['Config']['HorizontalSpacing']);
                $this->MergeInterface->entries['VSpacing']->set_text($XmlArray['Report']['Label']['Config']['VerticalSpacing']);
                $this->MergeInterface->entries['LabelWidth']->set_text($XmlArray['Report']['Label']['Config']['LabelWidth']);
                $this->MergeInterface->entries['LabelHeight']->set_text($XmlArray['Report']['Label']['Config']['LabelHeight']);
                $this->MergeInterface->entries['LeftMargin']->set_text($XmlArray['Report']['Label']['Config']['LeftMargin']);
                $this->MergeInterface->entries['TopMargin']->set_text($XmlArray['Report']['Label']['Config']['TopMargin']);
                $this->MergeInterface->entries['LabelCols']->set_text($XmlArray['Report']['Label']['Config']['Columns']);
                $this->MergeInterface->entries['LabelRows']->set_text($XmlArray['Report']['Label']['Config']['Rows']);
                $this->MergeInterface->entries['LabelFormat']->set_text($XmlArray['Report']['Label']['Config']['PageFormat']);
                $this->MergeInterface->entries['LabelSpacing']->set_text($XmlArray['Report']['Label']['Config']['LineSpacing']);

                $this->MergeInterface->PageValues = $XmlArray['Report']['Merge']['PageSetup'];
                $this->PageValues                 = $XmlArray['Report']['PageSetup'];
                $this->Properties                 = $XmlArray['Report']['Properties'];

                $this->textAreas['@GraphIntroduction']->insert(null, null, null, $XmlArray['Report']['Graph']['Description']);
                $this->HeaderFooterArea->InsertHeader($XmlArray['Report']['Header']['Body']);
                $this->HeaderFooterArea->InsertFooter($XmlArray['Report']['Footer']['Body']);
                $this->HeaderFooterArea->SetHeaderAlign($XmlArray['Report']['Header']['Align']);
                $this->HeaderFooterArea->SetFooterAlign($XmlArray['Report']['Footer']['Body']);

                # resolver
                for ($x=0; $x<=$XmlArray['Report']['Merge']['Details']['Detail1']['NumberSubSql']; $x++)
                {
                    $numSubSql = $x+1;
                    if ($x > 0)
                    {
                        $this->MergeInterface->addDetail(&$this->glade);
                        $this->MergeInterface->SubSql[$x]->setDetail($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['GroupHeader'], 0);
                        $this->MergeInterface->SubSql[$x]->setDetail($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Body'], 1);
                    }
                    $this->MergeInterface->SubSelectList[$x]->LoadBlocks(CoreReport::ExtractBlock($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]));
                    $this->MergeInterface->SubSelectList[$x]->setOffSet($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['OffSet']);
                    $this->MergeInterface->SubSelectList[$x]->setLimit($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['Limit']);
                    $this->MergeInterface->SubSelectList[$x]->setDistinct($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['Distinct']);
                }

                $this->MergeInterface->textSections['@MergeHeader']->add_text($XmlArray['Report']['Merge']['ReportHeader']);
                $this->MergeInterface->textSections['@GroupHeader']->add_text($XmlArray['Report']['Merge']['Details']['Detail1']['GroupHeader']);
                $this->MergeInterface->textSections['@MergeDetail']->add_text($XmlArray['Report']['Merge']['Details']['Detail1']['DataSet1']['Body']);
                $this->MergeInterface->textSections['@GroupFooter']->add_text($XmlArray['Report']['Merge']['Details']['Detail1']['GroupFooter']);
                $this->MergeInterface->textSections['@MergeFooter']->add_text($XmlArray['Report']['Merge']['ReportFooter']);
                $this->MergeInterface->textSections['@FinalSummary']->add_text($XmlArray['Report']['Merge']['FinalSummary']);

                $this->OfficeInterface->entryOpenOffice->set_text($XmlArray['Report']['OpenOffice']['Source']);

                $this->OfficeInterface->radioFixedDetails->set_active($XmlArray['Report']['OpenOffice']['Config']['FixedDetails']);
                $this->OfficeInterface->radioExpandDetails->set_active($XmlArray['Report']['OpenOffice']['Config']['ExpandDetails']);
                $this->OfficeInterface->checkPrintEmpty->set_active($XmlArray['Report']['OpenOffice']['Config']['printEmptyDetail']);
                $this->OfficeInterface->checkSumTotal->set_active($XmlArray['Report']['OpenOffice']['Config']['SumByTotal']);
                $this->OfficeInterface->checkRepeatHeader->set_active($XmlArray['Report']['OpenOffice']['Config']['RepeatHeader']);
                $this->OfficeInterface->checkRepeatFooter->set_active($XmlArray['Report']['OpenOffice']['Config']['RepeatFooter']);
            }

            $this->textAreas['@ReportDescription']->insert(null, null, null, $XmlArray['Report']['Properties']['Description']);

            $this->LoadTablesFrom();
        }
        else
        {
            new Dialog(_a('File Error'));
        }
    }

    /***********************************************************/
    /* Check if the file to be saved has already a name
    /***********************************************************/
    function PreSave($param)
    {
        if ($this->FileName)
        {
            $this->SaveReport( null, $this->FileName );
        }
        else
        {
            HandlerFile($param);
        }
    }

    /***********************************************************/
    /*
    /***********************************************************/
    function GetXmlArray()
    {
        $XmlArray['Report']['Version']                          = AGATA_VERSION;
        $XmlArray['Report']['Properties']['Description']        = $this->textAreas['@ReportDescription']->get_chars(0, -1);
        $XmlArray['Report']['Properties']['Title']              = $this->Properties['Title'];
        $XmlArray['Report']['Properties']['Author']             = $this->Properties['Author'];
        $XmlArray['Report']['Properties']['Keywords']           = $this->Properties['Keywords'];
        $XmlArray['Report']['Properties']['Date']               = $this->Properties['Date'];
        $XmlArray['Report']['Properties']['FrameSize']          = $this->Properties['FrameSize'];

        $XmlArray['Report']['Header']['Body']                   = $this->HeaderFooterArea->GetHeader();
        $XmlArray['Report']['Header']['Align']                  = $this->HeaderFooterArea->GetHeaderAlign();
        $XmlArray['Report']['Footer']['Body']                   = $this->HeaderFooterArea->GetFooter();
        $XmlArray['Report']['Footer']['Align']                  = $this->HeaderFooterArea->GetFooterAlign();

        # Storing Parameters
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $parameter => $Properties) //as $paramKey => $paramValue)
            {
                $XmlArray['Report']['Parameters'][substr($parameter,1)] = $Properties;
            }
        }

        $XmlArray['Report']['DataSet']['DataSource']['Name']              = $this->project;

        # Storing queries
        foreach ($this->SelectList->Block as $key =>$Clause)
        {
            $key = alltrim(ucwords($key));
            $XmlArray['Report']['DataSet']['Query'][$key] = $Clause[1];
        }
        $XmlArray['Report']['DataSet']['Query']['Config']['Distinct']     = $this->SelectList->getDistinct();
        $XmlArray['Report']['DataSet']['Query']['Config']['OffSet']       = $this->SelectList->getOffSet();
        $XmlArray['Report']['DataSet']['Query']['Config']['Limit']        = $this->SelectList->getLimit();

        $XmlArray['Report']['DataSet']['Groups']['Config']['ShowGroup']   = $this->radios['checkShowGroup']->get_active();
        $XmlArray['Report']['DataSet']['Groups']['Config']['ShowDetail']  = $this->radios['checkShowDetail']->get_active();
        $XmlArray['Report']['DataSet']['Groups']['Config']['ShowLabel']   = $this->radios['checkShowTotal']->get_active();
        $XmlArray['Report']['DataSet']['Groups']['Config']['ShowNumber']  = $this->radios['checkShowNumber']->get_active();
        $XmlArray['Report']['DataSet']['Groups']['Config']['ShowIndent']  = $this->radios['checkShowIndent']->get_active();
        $XmlArray['Report']['PageSetup']                                  = $this->PageValues;


        # Storing Breaks
        if ($this->LevelsArea->GetBreaks())
        {
            foreach ($this->LevelsArea->Breaks as $Break => $Formula)
            {
                //$this->LevelsArea->Append(array($Break, $Formula));
                $Formula = $Formula ? $Formula : ' ';
                $XmlArray['Report']['DataSet']['Groups']['Formulas']["Group$Break"] = $Formula;
            }
        }

        $XmlArray['Report']['DataSet']['Fields'] = $this->ReadAdjustments();

        $XmlArray['Report']['Graph']['Title']       = $this->entryTitle->get_text();
        $XmlArray['Report']['Graph']['TitleX']      = $this->entryTitlex->get_text();
        $XmlArray['Report']['Graph']['TitleY']      = $this->entryTitley->get_text();
        $XmlArray['Report']['Graph']['Width']       = $this->entrySizeX->get_text();
        $XmlArray['Report']['Graph']['Height']      = $this->entrySizeY->get_text();
        $XmlArray['Report']['Graph']['Description'] = $this->textAreas['@GraphIntroduction']->get_chars(0, -1);
        $XmlArray['Report']['Graph']['ShowData']    = $this->radios['checkData']->get_active();
        $XmlArray['Report']['Graph']['ShowValues']  = $this->radios['showValues']->get_active();
        $XmlArray['Report']['Graph']['Orientation'] = $this->radios['radioColumns']->get_active() ? 'columns' : 'lines';

        $XmlArray['Report']['Merge']['ReportHeader']                            = $this->MergeInterface->textSections['@MergeHeader']->get_text();
        $XmlArray['Report']['Merge']['Details']['Detail1']['GroupHeader']       = $this->MergeInterface->textSections['@GroupHeader']->get_text();
        $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet1']['Body']              = $this->MergeInterface->textSections['@MergeDetail']->get_text();
        # Storing Sub-queries

        $XmlArray['Report']['Merge']['Details']['Detail1']['NumberSubSql'] = $this->MergeInterface->numberSubReport;
        for ($x=0; $x<=$this->MergeInterface->numberSubReport; $x++)
        {
            $numSubSql = $x+1;
            foreach ($this->MergeInterface->SubSelectList[$x]->Block as $key =>$Clause)
            {
                $key = alltrim(ucwords($key));
                $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query'][$key] = $Clause[1];
            }

            $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['Distinct']  = $this->MergeInterface->SubSelectList[$x]->getDistinct();
            $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['OffSet']    = $this->MergeInterface->SubSelectList[$x]->getOffSet();
            $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Query']['Config']['Limit']     = $this->MergeInterface->SubSelectList[$x]->getLimit();
            if ($x > 0)
            {
                $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['GroupHeader'] = $this->MergeInterface->SubSql[$x]->getDetail(0);
                $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Body'] = $this->MergeInterface->SubSql[$x]->getDetail(1);
            }
            $XmlArray['Report']['Merge']['Details']['Detail1']['DataSet' . $numSubSql]['Fields'] = $this->ReadAdjustments(1, $x);
        }

        $XmlArray['Report']['Merge']['Details']['Detail1']['GroupFooter']       = $this->MergeInterface->textSections['@GroupFooter']->get_text();
        $XmlArray['Report']['Merge']['ReportFooter']                            = $this->MergeInterface->textSections['@MergeFooter']->get_text();
        $XmlArray['Report']['Merge']['FinalSummary']                            = $this->MergeInterface->textSections['@FinalSummary']->get_text();

        $XmlArray['Report']['Merge']['PageSetup']                               = $this->MergeInterface->PageValues;

        $XmlArray['Report']['Label']['Body']                       = $this->MergeInterface->textSections['@LabelText']->get_text();
        $XmlArray['Report']['Label']['Config']['HorizontalSpacing']= $this->MergeInterface->entries['HSpacing']->get_text();
        $XmlArray['Report']['Label']['Config']['VerticalSpacing']  = $this->MergeInterface->entries['VSpacing']->get_text();
        $XmlArray['Report']['Label']['Config']['LabelWidth']       = $this->MergeInterface->entries['LabelWidth']->get_text();
        $XmlArray['Report']['Label']['Config']['LabelHeight']      = $this->MergeInterface->entries['LabelHeight']->get_text();
        $XmlArray['Report']['Label']['Config']['LeftMargin']       = $this->MergeInterface->entries['LeftMargin']->get_text();
        $XmlArray['Report']['Label']['Config']['TopMargin']        = $this->MergeInterface->entries['TopMargin']->get_text();
        $XmlArray['Report']['Label']['Config']['Columns']          = $this->MergeInterface->entries['LabelCols']->get_text();
        $XmlArray['Report']['Label']['Config']['Rows']             = $this->MergeInterface->entries['LabelRows']->get_text();
        $XmlArray['Report']['Label']['Config']['PageFormat']       = $this->MergeInterface->entries['LabelFormat']->get_text();
        $XmlArray['Report']['Label']['Config']['LineSpacing']      = $this->MergeInterface->entries['LabelSpacing']->get_text();

        $XmlArray['Report']['OpenOffice']['Source']                  = $this->OfficeInterface->entryOpenOffice->get_text();
        $XmlArray['Report']['OpenOffice']['Config']['FixedDetails']  = $this->OfficeInterface->radioFixedDetails->get_active();
        $XmlArray['Report']['OpenOffice']['Config']['ExpandDetails'] = $this->OfficeInterface->radioExpandDetails->get_active();
        $XmlArray['Report']['OpenOffice']['Config']['printEmptyDetail']     = $this->OfficeInterface->checkPrintEmpty->get_active();
        $XmlArray['Report']['OpenOffice']['Config']['SumByTotal']    = $this->OfficeInterface->checkSumTotal->get_active();
        $XmlArray['Report']['OpenOffice']['Config']['RepeatHeader']  = $this->OfficeInterface->checkRepeatHeader->get_active();
        $XmlArray['Report']['OpenOffice']['Config']['RepeatFooter']  = $this->OfficeInterface->checkRepeatFooter->get_active();

        return $XmlArray;
    }

    /***********************************************************/
    /* Save a Report
    /***********************************************************/
    function SaveReport( $fs, $FileName = null )
    {
        if ($fs)
        {
            $this->FileName = $FileName = $fs->get_filename();
        }
        else
        {
            $this->FileName = $FileName;
        }

        $XmlArray = $this->GetXmlArray();
        CoreReport::SaveReport($FileName, $XmlArray);

        if ($fs)
        {
            $fs->hide();
        }
        $this->LoadBlocks(null, false);
        $this->ctreeReports->ReadAgain();
    }

    /***********************************************************/
    /* Save a Query Template
    /***********************************************************/
    function SaveSQL( $fs )
    {
        if ($fs)
        {
            $this->FileName = $FileName = $fs->get_filename();
        }
        else
        {
            return false;
        }

        # Storing queries
        foreach ($this->SelectList->Block as $key =>$Clause)
        {
            $key = alltrim(strtolower($key));
            $XmlArray['query'][$key] = $Clause[1];
        }

        $fd = fopen ($FileName, "w");
        if (!$fd)
        {
            new Dialog(_a('Permission Denied'));
            return;
        }
        fwrite($fd, trim( XMLHEADER . Array2Xml($XmlArray)));

        if ($fs)
        {
            $fs->hide();
        }
        $this->ctreeTemplates->ReadAgain();
    }

    function PublishReport()
    {
        //include_once 'classes/util/nusoap.php';

        if ($this->Changed)
        {
            new Dialog(_a('Save the Report first'));
            return;
        }
        # Cria objeto cliente SOAP
        $Server = $this->agataConfig['general']['Server'];
        $client = new soap_client("http://$Server/agata-server.php");

        $len = strlen($this->agataConfig['general']['RptDir']);
        $len2 = strlen($this->agataConfig['general']['AgataDir'] . '/resources/');

        $XmlArray = $this->GetXmlArray();
        $OpenOffice = $XmlArray['Report']['OpenOffice']['Source'];

        $texto = base64_encode(file_get_contents($this->FileName));
        $param = array('File' => substr($this->FileName, $len), 'text' => $texto, 'folder' => 'reports');
        $ret = $client->call('save_file', $param);

        $texto2 = base64_encode(file_get_contents($OpenOffice));
        $param2 = array('File' => substr($OpenOffice, $len2), 'text' => $texto2, 'folder' => 'resources');
        $ret2 = $client->call('save_file', $param2);

        if (!$ret or !$ret2)
        {
            new Dialog(_a('Permission denied'));
        }
        else
        {
            new Dialog(_a('Report Published'));
        }
    }

    function ReadAdjustments($sub = false, $numSubSql = null)
    {
        if ($sub)
        {
            if (!$numSubSql)
            {
                $Values = $this->SubAdjustmentsConfig->GetAllVars();
            }
            else
            {
                $Values = $this->MergeInterface->SubAdjustmentsConfig[$numSubSql]->GetAllVars();
            }
        }
        else
        {
            $Values = $this->AdjustmentsConfig->GetAllVars();
        }

        $i = 1;
        if ($Values)
        {
            foreach ($Values as $column => $AdjustmentsValues)
            {
                $Adjustments[$i]['Chars']       = $AdjustmentsValues[0];
                $Adjustments[$i]['Points']      = $AdjustmentsValues[1];
                $Adjustments[$i]['Align']       = $AdjustmentsValues[2];
                $Adjustments[$i]['Mask']        = $AdjustmentsValues[3];
                $Adjustments[$i]['Function']    = $AdjustmentsValues[4];
                $Adjustments[$i]['Cross']       = $AdjustmentsValues[5];
                $Adjustments[$i]['Conditional'] = $AdjustmentsValues[6];

                $Array["Column$i"]['Chars']      = $AdjustmentsValues[0];
                $Array["Column$i"]['Points']     = $AdjustmentsValues[1];
                $Array["Column$i"]['Align']      = $AdjustmentsValues[2];
                $Array["Column$i"]['Mask']       = $AdjustmentsValues[3];
                $Array["Column$i"]['Function']   = $AdjustmentsValues[4];
                $Array["Column$i"]['Cross']      = $AdjustmentsValues[5];
                $Array["Column$i"]['Conditional']= $AdjustmentsValues[6];

                $i ++;
            }
        }

        if ($sub)
        {
            if (!$numSubSql)
            {
                $this->SubAdjustments = $Adjustments;
            }
            else
            {
                $this->MergeInterface->SubAdjustments[$numSubSql] = $Adjustments;
            }
        }
        else
        {
            $this->Adjustments = $Adjustments;
        }

        return $Array;
    }
    /***********************************************************/
    /* Tests before generating a graph
    /***********************************************************/
    function TestB4Graph()
    {
        if (!extension_loaded('gd'))
        {
            new Dialog(_a('Extension not loaded') . ': gd');
            return false;
        }

        $x = $this->entrySizeX->get_text();
        $y = $this->entrySizeY->get_text();

        if (($x<100) || ($y<100))
        {
            new Dialog(_a('Dimensions too small'));
            return false;
        }

        if (!$this->PlottedColumns->clist->selection)
        {
            new Dialog(_a('Select columns to plot'));
            return false;
        }

        if ($this->radios['radioLines']->get_active())
        {
            $entry = $this->comboLegend->entry;
            $legend = $entry->get_text();
            if (!$entry)
            {
                new Dialog(_a('Select a Legend, please'));
                return false;
            }
        }

        return true;
    }

    /***********************************************************/
    /* Merge Document into a PDF File
    /***********************************************************/
    function SaveTmpMerge()
    {
        $this->PreReport('MergePdfTmp');
    }

    /***********************************************************/
    /* Clear query
    /***********************************************************/
    function ClearQuery($flag = false, $clearAll = true)
    {
        if (($this->Changed) && (!$flag))
        {
            $a = new Ask(_a('Query not saved, Do you really want to continue ?'), array(&$this,'ClearQuery'), null, array(true));
            return false;
        }
        else
        {
            $this->FileName = null;
            $this->ClearTables($clearAll);
            if ($clearAll)
            {
                $this->LoadBlocks(true, false);
            }
            else
            {
                $this->textAreas['@ManualQuery']->delete_text(0, -1);
            }
        }
        return true;
    }

    function PackFunctions()
    {
        if (class_exists('GtkScintilla'))
        {
            include_once 'classes/guis/TulipEditor.php';
            if ($this->TulipEditor)
            {
                $this->TulipEditor->Show();
            }
            else
            {
                $this->TulipEditor= new TulipEditor('functions');
            }
        }
        else
        {
            new Dialog(_a('Scintilla support is not enabled'));
        }
    }

    /***********************************************************/
    /* Pack the Configuration Screen
    /***********************************************************/
    function Dictionary()
    {
        include_once 'classes/guis/DictionaryWindow.php';
        if ($this->DictionaryWindow)
        {
            $this->DictionaryWindow->Show();
            $this->DictionaryWindow->connected = $this->connected;
            if ($this->DictionaryWindow->agataDB != $this->agataDB)
            {
                $this->DictionaryWindow->agataDB = $this->agataDB;
                $this->DictionaryWindow->Set(@array_keys($this->TableNames), $this->agataTbGroups, $this->PlainTbFamilies,
                                        $this->PlainTbLinks, $this->PlainDataDescription, $this->agataDataDescription);
            }
        }
        else
        {
            $this->DictionaryWindow = new DictionaryWindow();
            $this->DictionaryWindow->connected = $this->connected;
            $this->DictionaryWindow->agataDB = $this->agataDB;
            $this->DictionaryWindow->Set(@array_keys($this->TableNames), $this->agataTbGroups, $this->PlainTbFamilies,
                                    $this->PlainTbLinks, $this->PlainDataDescription, $this->agataDataDescription);
            $this->DictionaryWindow->Show();
        }
    }


    function CustomParameters()
    {
        include_once 'classes/guis/CustomParameterWindow.php';
        if ($this->CustomParameterWindow)
        {
            $this->CustomParameterWindow->SetParameters($this->Parameters);
            $this->CustomParameterWindow->Show();
        }
        else
        {
            $this->CustomParameterWindow = new CustomParameterWindow();
            $this->CustomParameterWindow->SetParameters($this->Parameters);
            $this->CustomParameterWindow->connect_object('return-parameters', array(&$this, 'ReturnCustomParameters'));
            $this->CustomParameterWindow->Show();
        }
    }

    function ReturnCustomParameters($items)
    {
        $this->Parameters = $items;
        $this->RefreshFields();
    }

    function ConfigWindow()
    {
        include_once 'classes/guis/ConfigWindow.php';
        $this->ConfigWindow = new ConfigWindow(array(&$this,'ReturnVars'), $this->agataConfig);
        $this->ConfigWindow->Show();
    }

    function Properties()
    {
        include_once 'classes/guis/PropertiesWindow.php';
        $this->PropertiesWindow = new PropertiesWindow(array(&$this,'ReturnPrefs'), $this->Properties);
        $this->PropertiesWindow->Show();
    }

    /***********************************************************/
    /* Pack the Dia configuration Screen
    /***********************************************************/
    function PackDia()
    {
        include_once 'classes/guis/DiaWindow.php';
        if ($this->DiaWindow)
        {
            $this->DiaWindow->connected    = $this->connected;
            $this->DiaWindow->agataDB = $this->agataDB;
            $this->DiaWindow->agataConfig  = $this->agataConfig;
            $this->DiaWindow->agataTbLinks = $this->agataTbLinks;
            $this->DiaWindow->project      = $this->project;
            $this->DiaWindow->DiaTables->clear();
            $this->DiaWindow->Show();
        }
        else
        {
            $this->DiaWindow = new DiaWindow($this->agataDB, $this->agataConfig,
                               $this->ctreeTablesDia, $this->agataTbLinks, $this->project);

            $this->DiaWindow->connected = $this->connected;
        }
    }

    function ImportSql()
    {
        $inWindow = new InWindow;
        $inWindow->connect_object('select-file', array(&$this, 'ReturnInSql'));
    }

    function ReturnInSql($Info)
    {
        if ($Info)
        {
            $FileName = $Info;
            if (!file_exists($FileName))
            {
                return;
            }
            $XmlArray = CoreReport::OpenReport($FileName);
            if ($XmlArray)
            {
                $this->TypeExpression(' (' . CoreReport::SqlFromReport($XmlArray['Report']['DataSet']) . ') ');
            }
        }
    }

    function About()
    {
        new About;
    }

    /***********************************************************/
    /* Show detail only if indent is on
    /***********************************************************/
    function ToggleDetail()
    {
        if ($this->radios['checkShowTotal']->get_active())
        {
            $this->radios['checkShowIndent']->set_active(true);
        }
    }


    /***********************************************************/
    /* Return Vars from Configuration screen
    /***********************************************************/
    function ReturnVars($agataConfig)
    {
        $this->agataConfig = $agataConfig;

        $this->ctreeRepository->MyClear();
        $this->ctreeReports->MyClear();

        $this->ctreeRepository->AbreDiretorio($this->agataConfig['general']['OutputDir'], null);
        $this->ctreeReports->AbreDiretorio($this->agataConfig['general']['RptDir'], null);

        AgataConfig::WriteConfig($this->agataConfig);
        new Dialog(_a('Saved'), false);
    }

    /***********************************************************/
    /* Return Vars from Properties screen
    /***********************************************************/
    function ReturnPrefs($Properties)
    {
        $this->Properties = $Properties;
    }

    /***********************************************************/
    /* Shows Manage Project
    /***********************************************************/
    function ManageProject()
    {
        $manageProject = new ManageProject();
    }

    /**
    * Call the window to manage users
    */
    function ManageUser()
    {
        $manageUser = new ManageUser();
    }

    /***********************************************************/
    /* Shows Connection Window
    /***********************************************************/
    function PackConnection($initial = false)
    {
        if (!$this->agataConfig['general']['StartupConnect'] or !$initial)
        {
            if ($this->ConnectionWizard)
            {
                $this->ConnectionWizard->next_page(0);
                $this->ConnectionWizard->setFields();
                $this->ConnectionWizard->Show();
            }
            else
            {
                $this->ConnectionWizard = new ConnectionWizard(array(&$this,'ReturnProjectVars'));
                //$this->ConnectionWizard->connectButtonNewReport(array(&$this, 'NewReport'));
            }
        }
        else
        {
            $this->ReturnProjectVars();
        }
    }

    /***********************************************************/
    /* Shows Layout Configuration Window
    /***********************************************************/
    function LayoutConfig()
    {
        include_once 'classes/guis/LayoutConfig.php';
        if ($this->LayoutConfig)
        {
            $this->LayoutConfig->Show();
        }
        else
        {
            $this->LayoutConfig = new LayoutConfig;
        }
    }

    /***********************************************************/
    /* Shows Label Configuration Window
    /***********************************************************/
    function LabelConfig()
    {
        include_once 'classes/guis/LabelConfig.php';
        if ($this->LabelConfig)
        {
            $this->LabelConfig->Show();
        }
        else
        {
            $this->LabelConfig = new LabelConfig;
        }
    }


    /***********************************************************/
    /* Page Setup Option
    /***********************************************************/
    function PageSetup()
    {
        include_once 'classes/guis/PageSetup.php';
        if ($this->PageSetup)
        {
            $this->PageSetup->Show();
        }
        else
        {
            $this->PageSetup = new PageSetup(array(&$this, 'GetPageSetup'));
        }
        $this->PageSetup->SetValues($this->PageValues);
    }

    /***********************************************************/
    /* Return Page Setup
    /***********************************************************/
    function GetPageSetup($return)
    {
        $this->PageValues = $return;
    }

    /***********************************************************/
    /* Connect to a Database
    /***********************************************************/
    function ReturnProjectVars()
    {
        if ($this->agataConfig['general']['StartupConnect'] and !$this->ConnectionWizard)
        {
            $project = Project::getLast();
            $dbVars = Project::ReadProject($project);
        }
        else
        {
            $DatabaseVars = $this->ConnectionWizard->DatabaseVars();
            $project = $DatabaseVars->projectName;

            global $dbVars;

            //Details
            $dbVars['desc']   = $DatabaseVars->desc;
            $dbVars['author'] = $DatabaseVars->author;
            $dbVars['date']   = $DatabaseVars->date;
            //Connection
            $dbVars['host']   = $DatabaseVars->host;
            $dbVars['name']   = $DatabaseVars->name;
            $dbVars['user']   = $DatabaseVars->user;
            $dbVars['pass']   = $DatabaseVars->pass;
            $dbVars['type']   = $DatabaseVars->type;
            $dbVars['dict']   = $DatabaseVars->dict;

        }

        if ($this->Connect($project, $dbVars))
        {
            //New project mode
            if ( $this->ConnectionWizard )
            {
                if ($this->ConnectionWizard->mode == 2)
                {
                    Project::WriteProject($project, $dbVars);
                }
            }
            $this->ConnectionWizard->next_page(4);
        }
        else
        {
            new Dialog(_a('Cannot connect to Database'));
        }
    }

    /***********************************************************/
    /* Select Sub Query
    /***********************************************************/
    function SubQuery()
    {
        if (!$this->connected)
        {
            new Dialog(_a('Cannot connect to Database'));
        }
        else
        {
            if ($this->connected)
            {
                $Content   = @$this->SelectList->Block['Select'][1];
                if ($Content)
                {
                    $this->ReadAdjustments(1);
                    $Elements  = $this->SelectList->GetSelectColumns(_a('Column'));
                    $currentSubReport = $this->MergeInterface->GetCurrentSubReport();
                    $this->Wizard = new AgataInterface($this->agataConfig, true);
                    $this->Wizard->Connect($this->project, $this->agataDB);
                    $this->Wizard->SelectList->LoadBlocks($this->MergeInterface->SubSelectList[$currentSubReport]->Block);
                    $this->Wizard->SelectList->setDistinct($this->MergeInterface->SubSelectList[$currentSubReport]->getDistinct());
                    $this->Wizard->SelectList->setOffSet($this->MergeInterface->SubSelectList[$currentSubReport]->getOffSet());
                    $this->Wizard->SelectList->setLimit($this->MergeInterface->SubSelectList[$currentSubReport]->getLimit());
                    $this->Wizard->Parameters = $this->Parameters;

                    if ($tables = get_tables_from($this->MergeInterface->SubSelectList[$currentSubReport]->Block['From'][1]))
                    {
                        $this->Wizard->LoadTables($tables, true);
                    }
                    $this->Wizard->Adjustments = $this->SubAdjustments;
                    $this->Wizard->LoadBlocks(null, false);
                    $this->Wizard->callback = array(&$this, 'GrabSubReport');
                    $this->Wizard->MainQueryFields = $Elements;
                    if ($this->MergeInterface->getCurrentSubReport() > 0)
                    {
                        for ($x=0; $x<$this->MergeInterface->getCurrentSubReport(); $x++)
                        {
                            $this->Wizard->SubQueryFields[$x] = $this->MergeInterface->SubSelectList[$x]->GetSelectColumns(_a('Column'));
                        }
                    }
                }
                else
                {
                    new Dialog(_a('You Have to build the SQL Query firstly'));
                }
            }
        }
    }

    /***********************************************************/
    /* Called on SubReport window
    /***********************************************************/
    function SaveSubReport()
    {
        $this->window->hide();
        $this->ReadAdjustments();
        call_user_func($this->callback);
    }

    /***********************************************************/
    /* Called on Main window
    /***********************************************************/
    function GrabSubReport()
    {
        $currentSubReport = $this->MergeInterface->GetCurrentSubReport();
        $this->MergeInterface->SubSelectList[$currentSubReport]->LoadBlocks($this->Wizard->SelectList->Block);
        $this->MergeInterface->SubSelectList[$currentSubReport]->setDistinct($this->Wizard->SelectList->getDistinct());
        $this->MergeInterface->SubSelectList[$currentSubReport]->setLimit($this->Wizard->SelectList->getLimit());
        $this->MergeInterface->SubSelectList[$currentSubReport]->setOffSet($this->Wizard->SelectList->getOffSet());
        $this->SubAdjustments = $this->Wizard->Adjustments;
        $this->MergeInterface->RefreshSubFields();
        $this->LoadBlocks();
    }

    /***********************************************************/
    /* Connect Project
    /***********************************************************/
    function Connect($project, $dbVars)
    {
        Wait::On();

        $this->ProjectDefs[$project] = Dictionary::ReadDictionary($dbVars['dict']);
        $this->agataDB          = $dbVars;
        $this->agataTbFamilies       = $this->ProjectDefs[$project][0];
        $this->agataTbGroups         = $this->ProjectDefs[$project][1];
        $this->agataTbLinks          = $this->ProjectDefs[$project][2];
        $this->agataDataDescription  = $this->ProjectDefs[$project][3];

        $PlanVars = Dictionary::Planification($this->agataTbFamilies, $this->agataTbLinks, $this->agataDataDescription);

        $this->PlainTbFamilies = $PlanVars[0];
        $this->PlainTbLinks =  $PlanVars[1];
        $this->PlainDataDescription = $PlanVars[2];

        $this->project = $project;
        if (!$this->subQuery)
            $this->Title = "Agata Report [{$project}] ";
        else
            $this->Title = _a('SubReport Wizard') . " [{$project}] ";

        $this->window->set_title($this->Title);

        // Check DataBase
        $conn = new AgataConnection;
        $Pass = $conn->Open($this->agataDB);
        $conn->Close();

        if ($Pass)
        {
            $this->connected = true;
            $this->ClearQuery(true);
            $this->FillTables();
            Wait::Off();
            return true;
        }
        else
        {
            $this->connected = false;
            Wait::Off();
            return false;
        }
    }

    /***********************************************************/
    /* get parameters content
    /***********************************************************/
    function getParametersContent()
    {
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $Parameter => $Properties)
            {
                $return[$Parameter] = $Properties['value'];
            }
        }
        return $return;
    }

    /***********************************************************/
    /* Clear parameters content
    /***********************************************************/
    function clearParametersContent()
    {
        foreach ($this->Parameters as $Parameter => $Properties)
        {
            $Properties['value'] = null;
            $return[$Parameter] = $Properties;
        }
        $this->Parameters = $return;
    }
}
?>