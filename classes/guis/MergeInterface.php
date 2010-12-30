<?php
/***********************************************************/
/* Merge Interface, all Merge controls are here
/* by Pablo Dall'Oglio 2001-2006
/*    Jamiel Spezia 2006 - 2006
/***********************************************************/
class MergeInterface
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function MergeInterface($glade, $agataConfig)
    {
        global $Pixmaps;
        $this->agataConfig      = $agataConfig;
        $this->MergeNotebook    = $glade->get_widget('MergeNotebook');
        $this->SectionsNotebook = $glade->get_widget('SectionsNotebook');
        $this->Tooltips = new GtkTooltips;
        $this->numberSubReport = 0;

        $img_mimetypes = array('png', 'jpg', 'jpeg');
        $file_mimetypes= array('txt');

        $this->param['InsertImage']=array(array(&$this, 'TypeImage'), $img_mimetypes, null,     _a('Open'));
        $this->param['InsertBgImage']=array(array(&$this, 'TypeBgImage'), $img_mimetypes, null,     _a('Open'));
        $this->param['InsertFile'] = array(array(&$this, 'TypeFile'), $file_mimetypes, null,    _a('Open'));

        $this->entryFonts             = $glade->get_widget( 'entryFonts' );
        $this->entryStyles            = $glade->get_widget( 'entryStyles' );
        $this->entrySizes             = $glade->get_widget( 'entrySizes' );
        $this->entryFields            = $glade->get_widget( 'entryFields' );
        $this->entrySubField          = $glade->get_widget( 'entrySubField' );
        $this->entryTotalField        = $glade->get_widget( 'entryTotalField' );
        $this->entryTotalFinalSummary = $glade->get_widget( 'entryTotalFinalSummary' );

        $this->entries['HSpacing']    = $glade->get_widget('spinHSpacing');
        $this->entries['VSpacing']    = $glade->get_widget('spinVSpacing');
        $this->entries['LabelWidth']  = $glade->get_widget('spinLabelWidth');
        $this->entries['LabelHeight'] = $glade->get_widget('spinLabelHeight');
        $this->entries['LeftMargin']  = $glade->get_widget('spinLeftMargin');
        $this->entries['TopMargin']   = $glade->get_widget('spinTopMargin');
        $this->entries['LabelCols']   = $glade->get_widget('spinLabelCols');
        $this->entries['LabelRows']   = $glade->get_widget('spinLabelRows');
        $this->entries['LabelFormat'] = $glade->get_widget('entryLabelFormat');
        $this->entries['LabelSpacing']= $glade->get_widget('spinLabelSpacing');

        $this->textSections['@LabelText']    = new TextArea;
        $this->textSections['@MergeHeader']  = new TextArea; // header
        $this->textSections['@GroupHeader']  = new TextArea; // groupheader
        $this->textSections['@MergeDetail']  = new TextArea; // detail
        $this->textSections['@GroupFooter']  = new TextArea; // groupfooter
        $this->textSections['@MergeFooter']  = new TextArea; // footer
        $this->textSections['@FinalSummary'] = new TextArea; // final summary
        //$this->textSections['@MergeDetail']->connect_object('painted', array(&$this, 'alert'));
        //modified
        //painted
        //

        $this->Containers['frameSubQuery'] = $glade->get_widget('frameSubQuery');
        $this->Containers['frameSubQuery'] = $glade->get_widget('frameSubQuery');
        $this->Containers['frameSubQuery']->set_label(_a('SubQuery Session'));

        $this->comboFonts             = $glade->get_widget('comboFonts');
        $this->comboFields            = $glade->get_widget('comboFields');
        $this->comboSubFields         = $glade->get_widget('comboSubFields');
        $this->comboTotalFields       = $glade->get_widget('comboTotalFields');
        $this->comboTotalFinalSummary = $glade->get_widget('comboTotalFinalSummary');

        $fontsList  = $this->comboFonts->list;
        $fieldsList = $this->comboFields->list;

        $fontsList->clear_items(0, 2);
        $fieldsList->clear_items(0, 2);

        $Fonts = array('Arial', 'Courier', 'Times');
        foreach ($Fonts as $font)
        {
            $item = new GtkListItem();
            $box = new GtkHBox();
            $arrow = new GtkPixmap($Pixmaps['TrueType'][0], $Pixmaps['TrueType'][1]);
            $label = new GtkLabel($font);
            $box->pack_start($arrow, false, false);
            $box->pack_start($label, false, false, 2);
            $item->add($box);
            $this->comboFonts->set_item_string($item, $font);
            $fontsList->add($item);
            $item->show_all();
        }

        $this->SubSelectList[0] = new SelectList(null);
        $this->SubSelectList[0]->connect_object('changed', array(&$this, 'RefreshSubFields'), 0);
        $this->Containers['frameSubQuery']->add($this->SubSelectList[0]->widget);
        //new ColorButton($Description);


        $this->Labels['labelHeader'] = $glade->get_widget('labelHeader');
        $this->Labels['labelFooter'] = $glade->get_widget('labelFooter');
        $this->Labels['labelFinalSummary'] = $glade->get_widget('labelFinalSummary');
        $this->Labels['labelDetail'] = $glade->get_widget('labelDetail');
        $this->Labels['labelGroupHeader'] = $glade->get_widget('labelGroupHeader');
        $this->Labels['labelGroupFooter'] = $glade->get_widget('labelGroupFooter');
        $this->Labels['labelMerge']   = $glade->get_widget('labelMerge');
        $this->Labels['labelLabel']   = $glade->get_widget('labelLabel');
        //$this->Labels['labelQuantity'] = $glade->get_widget('labelQuantity');
        //$this->Labels['labelPosition'] = $glade->get_widget('labelPosition');
        //$this->Labels['labelLineCount'] = $glade->get_widget('labelLineCount');

        $this->Labels['HSpacing']   = $glade->get_widget('labelHSpacing');
        $this->Labels['VSpacing']   = $glade->get_widget('labelVSpacing');
        $this->Labels['LabelWidth'] = $glade->get_widget('labelLabelWidth');
        $this->Labels['LabelHeight']= $glade->get_widget('labelLabelHeight');
        $this->Labels['LeftMargin'] = $glade->get_widget('labelLeftMargin');
        $this->Labels['TopMargin']  = $glade->get_widget('labelTopMargin');
        $this->Labels['LabelCols']  = $glade->get_widget('labelLabelCols');
        $this->Labels['LabelRows']  = $glade->get_widget('labelLabelRows');
        $this->Labels['OpenLabel']  = $glade->get_widget('labelOpenLabel');
        $this->Labels['LineSpacing']= $glade->get_widget('labelLineSpacing');
        $this->Labels['LabelFormat']= $glade->get_widget('labelLabelFormat');

        $this->Labels['labelHeader']->set_text(_a('Header'));
        $this->Labels['labelFooter']->set_text(_a('Footer'));
        $this->Labels['labelFinalSummary']->set_text(_a('Final Summary'));
        $this->Labels['labelDetail']->set_text(_a('Detail'));
        $this->Labels['labelGroupHeader']->set_text(_a('Group Header'));
        $this->Labels['labelGroupFooter']->set_text(_a('Group Footer'));
        $this->Labels['labelMerge']->set_text(_a('Merge Tool'));
        $this->Labels['labelLabel']->set_text(_a('Label Tool'));
        $this->Labels['LineSpacing']->set_text(_a('Line Spacing'));
        $this->Labels['LabelFormat']->set_text(_a('Page Format'));
        //$this->Labels['labelQuantity']->set_text(_a('Quantity'));
        //$this->Labels['labelPosition']->set_text(_a('Position'));
        //$this->Labels['labelLineCount']->set_text(_a('Lines'));


        $this->Labels['HSpacing']->set_text(_a('Horizontal Spacing'));
        $this->Labels['VSpacing']->set_text(_a('Vertical Spacing'));
        $this->Labels['LabelWidth']->set_text(_a('Width'));
        $this->Labels['LabelHeight']->set_text(_a('Height'));
        $this->Labels['LeftMargin']->set_text(_a('Left Margin'));
        $this->Labels['TopMargin']->set_text(_a('Top Margin'));
        $this->Labels['LabelCols']->set_text(_a('Columns'));
        $this->Labels['LabelRows']->set_text(_a('Lines'));
        $this->Labels['OpenLabel']->set_text(_a('Open ^1', _a('Label Templates')));

        $this->Buttons['MergePdf'] =        $glade->get_widget('buttonMergePdf');
        $this->Buttons['Preview'] =         $glade->get_widget('buttonPreview');
        $this->Buttons['ApplyFont'] =       $glade->get_widget('buttonApplyFont');
        $this->Buttons['VText'] =           $glade->get_widget('buttonVText');
        $this->Buttons['TabWidth'] =        $glade->get_widget('buttonTabWidth');
        $this->Buttons['SetY'] =            $glade->get_widget('buttonSetY');
        $this->Buttons['PageSetup'] =       $glade->get_widget('buttonPageSetup');
        $this->Buttons['FontColor'] =       $glade->get_widget('buttonFontColor');
        $this->Buttons['Rect'] =            $glade->get_widget('buttonRect');
        $this->Buttons['Frame'] =           $glade->get_widget('buttonFrame');
        $this->Buttons['Ellipse'] =         $glade->get_widget('buttonEllipse');
        $this->Buttons['DateTime'] =        $glade->get_widget('buttonDateTime');
        $this->Buttons['Chars'] =           $glade->get_widget('buttonChars');
        $this->Buttons['InsertImage'] =     $glade->get_widget('buttonInsertImage');
        $this->Buttons['InsertBgImage'] =   $glade->get_widget('buttonInsertBgImage');
        $this->Buttons['InsertFile'] =      $glade->get_widget('buttonInsertFile');
        $this->Buttons['PageBreak'] =       $glade->get_widget('buttonPageBreak');
        $this->Buttons['SubQuery'] =        $glade->get_widget('buttonSubQuery');
        $this->Buttons['RoundSquare'] =     $glade->get_widget('buttonRoundSquare');
        $this->Buttons['InsertLine'] =      $glade->get_widget('buttonLine');
        $this->Buttons['TypeField'] =       $glade->get_widget('buttonTypeField');
        $this->Buttons['Functions'] =       $glade->get_widget('buttonFunctions');
        $this->Buttons['FinalSummaryFunctions'] =       $glade->get_widget('buttonFinalSummaryFunctions');
        $this->Buttons['BarCode'] =         $glade->get_widget('buttonBarCode');
        $this->Buttons['Preview'] =         $glade->get_widget('buttonPreview');
        $this->Buttons['TypeSubField'] =    $glade->get_widget('buttonTypeSubField');
        $this->Buttons['OpenLabelTemplates']=$glade->get_widget('buttonOpenLabelTemplates');
        //$this->Buttons['toggleEOL'] =        $glade->get_widget('toggleEOL');
        //$this->Buttons['toggleWrap'] =       $glade->get_widget('toggleWrap');
        //$this->Buttons['toggleCaret'] =      $glade->get_widget('toggleCaret');
        $this->Buttons['breakPage'] =        $glade->get_widget('checkBreak');
        $this->Buttons['AddDetail'] =        $glade->get_widget('buttonAddDetail');
        $this->Buttons['VSpace'] =           $glade->get_widget('buttonVSpace');
        //

        $label = $this->Buttons['breakPage']->child;
        $label->set_text(_a('One record per page'));

        /*$this->caret = false;
        $this->setCaret();
        $this->Buttons['toggleCaret']->set_active(true);

        $this->eol = true;
        $this->setEOL();
        $this->Buttons['toggleEOL']->set_active(false);

        $this->wrap = true;
        $this->setWrap();
        $this->Buttons['toggleWrap']->set_active(false);*/

        $this->Tooltips->set_tip($this->Buttons['BarCode'],         _a('New Bar Code'));
        $this->Tooltips->set_tip($this->Buttons['Ellipse'],         _a('New Ellipse'));
        $this->Tooltips->set_tip($this->Buttons['Rect'],            _a('New Rectangle'));
        $this->Tooltips->set_tip($this->Buttons['RoundSquare'],     _a('New Rounded Rectangle'));
        $this->Tooltips->set_tip($this->Buttons['PageSetup'],       _a('Page Setup'));
        $this->Tooltips->set_tip($this->Buttons['Preview'],         _a('Preview of Report'));
        $this->Tooltips->set_tip($this->Buttons['InsertImage'],     _a('Insert Image'));
        $this->Tooltips->set_tip($this->Buttons['InsertBgImage'],   _a('Insert Background Image'));
        $this->Tooltips->set_tip($this->Buttons['DateTime'],        _a('Insert Date/Time'));
        $this->Tooltips->set_tip($this->Buttons['InsertFile'],      _a('Insert File'));
        $this->Tooltips->set_tip($this->Buttons['PageBreak'],       _a('Page Break'));
        $this->Tooltips->set_tip($this->Buttons['FontColor'],       _a('Font Color'));
        $this->Tooltips->set_tip($this->Buttons['Chars'],           _a('Insert Special Character'));
        $this->Tooltips->set_tip($this->Buttons['MergePdf'],        _a('Export to PDF File'));
        //$this->Tooltips->set_tip($this->Buttons['toggleEOL'],       _a('Show EOL'));
        //$this->Tooltips->set_tip($this->Buttons['toggleWrap'],      _a('Word Wrap'));
        //$this->Tooltips->set_tip($this->Buttons['toggleCaret'],     _a('Caret Lines'));
        $this->Tooltips->set_tip($this->Buttons['InsertLine'],      _a('Insert Line'));
        $this->Tooltips->set_tip($this->Buttons['Frame'],           _a('Insert floating frame'));
        $this->Tooltips->set_tip($this->Buttons['VText'],           _a('Text rotation'));
        $this->Tooltips->set_tip($this->Buttons['TabWidth'],        _a('Horizontal Tab'));
        $this->Tooltips->set_tip($this->Buttons['SetY'],            _a('Vertical Tab'));
        $this->Tooltips->set_tip($this->Buttons['VSpace'],          _a('Line height'));

        $this->Buttons['ApplyFont']->connect_object('clicked', array(&$this, 'TypeFonts'));
        $this->Buttons['TabWidth']->connect_object('clicked', array(&$this, 'Tabulation'));
        $this->Buttons['SetY']->connect_object('clicked', array(&$this, 'SetY'));
        $this->Buttons['PageSetup']->connect_object('clicked', array(&$this, 'PageSetup'));
        $this->Buttons['FontColor']->connect_object('clicked', array(&$this, 'PickColor'), array(&$this, 'FontColor'));
        $this->Buttons['Rect']->connect_object('clicked', array(&$this, 'GetRect'));
        $this->Buttons['VText']->connect_object('clicked', array(&$this, 'GetVText'));
        $this->Buttons['InsertLine']->connect_object('clicked', array(&$this, 'GetLine'));
        $this->Buttons['RoundSquare']->connect_object('clicked', array(&$this, 'GetRound'));
        $this->Buttons['Ellipse']->connect_object('clicked', array(&$this, 'GetEllipse'));
        $this->Buttons['DateTime']->connect_object('clicked', array(&$this, 'PopDateTime'));
        $this->Buttons['Chars']->connect_object('clicked', array(&$this, 'PopChars'));
        $this->Buttons['InsertImage']->connect_object('clicked', 'HandlerFile', $this->param['InsertImage']);
        $this->Buttons['InsertBgImage']->connect_object('clicked', 'HandlerFile', $this->param['InsertBgImage']);
        $this->Buttons['InsertFile']->connect_object('clicked', 'HandlerFile', $this->param['InsertFile']);
        $this->Buttons['PageBreak']->connect_object('clicked', array(&$this, 'TypeChar'), "\n#pagebreak\n");
        $this->Buttons['TypeField']->connect_object('clicked', array(&$this, 'TypeFields'));
        $this->Buttons['Functions']->connect_object('clicked',array(&$this, 'PopFunctions'));
        $this->Buttons['FinalSummaryFunctions']->connect_object('clicked',array(&$this, 'PopFunctions'));
        $this->Buttons['BarCode']->connect_object('clicked', array(&$this, 'GetBarCode'));
        $this->Buttons['TypeSubField']->connect_object('clicked', array(&$this, 'TypeSubField'));
        $this->Buttons['OpenLabelTemplates']->connect_object('clicked', array(&$this, 'OpenLabelTemplates'));
        //$this->Buttons['toggleEOL']->connect_object('clicked', array(&$this, 'setEOL'));
        //$this->Buttons['toggleWrap']->connect_object('clicked', array(&$this, 'setWrap'));
        //$this->Buttons['toggleCaret']->connect_object('clicked', array(&$this, 'setCaret'));
        $this->Buttons['AddDetail']->connect_object('clicked', array(&$this, 'addDetail'), $glade);
        $this->Buttons['VSpace']->connect_object('clicked', array(&$this, 'setVSpace'));

        $this->Buttons['Preview']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['MergePdf']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['Functions']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['FinalSummaryFunctions']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['ApplyFont']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['VText']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['TabWidth']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['SetY']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['PageSetup']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['FontColor']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['Rect']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['Frame']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['Ellipse']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['DateTime']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['InsertImage']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['InsertBgImage']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['InsertFile']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['PageBreak']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['RoundSquare']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['Chars']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['SubQuery']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['TypeField']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['BarCode']->set_relief(GTK_RELIEF_NONE);
        //$this->Buttons['toggleEOL']->set_relief(GTK_RELIEF_NONE);
        //$this->Buttons['toggleWrap']->set_relief(GTK_RELIEF_NONE);
        //$this->Buttons['toggleCaret']->set_relief(GTK_RELIEF_NONE);
        $this->Buttons['InsertLine']->set_relief(GTK_RELIEF_NONE);


        $this->MenuFunctions = new GtkMenu;
        $option1 = new MyNormalMenuItem($Pixmaps['ico_fun'],  _a('Sum'), null);
        $option2 = new MyNormalMenuItem($Pixmaps['ico_fun'],  _a('Average'), null);
        $option3 = new MyNormalMenuItem($Pixmaps['ico_fun'],  _a('Count'), null);
        $option4 = new MyNormalMenuItem($Pixmaps['ico_fun'],  _a('Min'), null);
        $option5 = new MyNormalMenuItem($Pixmaps['ico_fun'],  _a('Max'), null);
        $this->MenuFunctions->append($option1);
        $this->MenuFunctions->append($option2);
        $this->MenuFunctions->append($option3);
        $this->MenuFunctions->append($option4);
        $this->MenuFunctions->append($option5);

        $option1->connect_object('activate', array(&$this, 'TypeSubField'), 'sum');
        $option2->connect_object('activate', array(&$this, 'TypeSubField'), 'avg');
        $option3->connect_object('activate', array(&$this, 'TypeSubField'), 'count');
        $option4->connect_object('activate', array(&$this, 'TypeSubField'), 'min');
        $option5->connect_object('activate', array(&$this, 'TypeSubField'), 'max');


        $this->MenuChars = new GtkMenu;
        $option1  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(185), null);
        $option2  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(178), null);
        $option3  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(179), null);
        $option4  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(186), null);
        $option5  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(170), null);
        $option6  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(188), null);
        $option7  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(189), null);
        $option8  = new MyNormalMenuItem($Pixmaps['ico_chars'],  chr(190), null);
        $option9  = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Registered') . ' ' . chr(174), null);
        $option10 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Copyright')  . ' ' . chr(169), null);
        $option11 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Sterling')   . ' ' . chr(163), null);
        $option12 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Paragraph')  . ' ' . chr(167), null);
        $option13 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Spaniard')   . ' ' . chr(191), null);
        $option14 = new MyNormalMenuItem($Pixmaps['ico_chars'], 'PI ' . chr(182), null);
        $option15 = new MyNormalMenuItem($Pixmaps['ico_chars'], chr(177), null);
        $option16 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Commented Line'), null);
        $option17 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('File Name'), null);
        $option18 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Page Number'), null);
        $option19 = new MyNormalMenuItem($Pixmaps['ico_chars'], _a('Page counting'), null);

        $this->MenuChars->append($option1);
        $this->MenuChars->append($option2);
        $this->MenuChars->append($option3);
        $this->MenuChars->append($option4);
        $this->MenuChars->append($option5);
        $this->MenuChars->append($option6);
        $this->MenuChars->append($option7);
        $this->MenuChars->append($option8);
        $this->MenuChars->append($option9);
        $this->MenuChars->append($option10);
        $this->MenuChars->append($option11);
        $this->MenuChars->append($option12);
        $this->MenuChars->append($option13);
        $this->MenuChars->append($option14);
        $this->MenuChars->append($option15);
        $this->MenuChars->append($option16);
        $this->MenuChars->append($option17);
        $this->MenuChars->append($option18);
        $this->MenuChars->append($option19);

        $option1->connect_object('activate', array(&$this, 'TypeChar'), ' $sup1 ');
        $option2->connect_object('activate', array(&$this, 'TypeChar'), ' $sup2 ');
        $option3->connect_object('activate', array(&$this, 'TypeChar'), ' $sup3 ');
        $option4->connect_object('activate', array(&$this, 'TypeChar'), ' $supo ');
        $option5->connect_object('activate', array(&$this, 'TypeChar'), ' $supa ');
        $option6->connect_object('activate', array(&$this, 'TypeChar'), ' $s14 ');
        $option7->connect_object('activate', array(&$this, 'TypeChar'), ' $s12 ');
        $option8->connect_object('activate', array(&$this, 'TypeChar'), ' $s34 ');
        $option9->connect_object('activate', array(&$this, 'TypeChar'), ' $reco ');
        $option10->connect_object('activate', array(&$this, 'TypeChar'), ' $copy ');
        $option11->connect_object('activate', array(&$this, 'TypeChar'), ' $ster ');
        $option12->connect_object('activate', array(&$this, 'TypeChar'), ' $para ');
        $option13->connect_object('activate', array(&$this, 'TypeChar'), ' $iesp ');
        $option14->connect_object('activate', array(&$this, 'TypeChar'), ' $pi ');
        $option15->connect_object('activate', array(&$this, 'TypeChar'), ' $mame ');
        $option16->connect_object('activate', array(&$this, 'TypeChar'), "\n// ");
        $option17->connect_object('activate', array(&$this, 'TypeChar'), ' $filename ');
        $option18->connect_object('activate', array(&$this, 'TypeChar'), ' $page ');
        $option19->connect_object('activate', array(&$this, 'TypeChar'), ' $pagecounting ');

        $this->MenuDateTime = new GtkMenu;
        $option1 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Year'), null);
        $option2 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Month'), null);
        $option3 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Day'), null);
        $option4 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Month Name'), null);
        $option5 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Week Day'), null);
        $option6 = new MyNormalMenuItem($Pixmaps['ico_cal'],  _a('Time'), null);

        $this->MenuDateTime->append($option1);
        $this->MenuDateTime->append($option2);
        $this->MenuDateTime->append($option3);
        $this->MenuDateTime->append($option4);
        $this->MenuDateTime->append($option5);
        $this->MenuDateTime->append($option6);

        $option1->connect_object('activate', array(&$this, 'TypeChar'), ' $year ');
        $option2->connect_object('activate', array(&$this, 'TypeChar'), ' $month ');
        $option3->connect_object('activate', array(&$this, 'TypeChar'), ' $day ');
        $option4->connect_object('activate', array(&$this, 'TypeChar'), ' $monthname ');
        $option5->connect_object('activate', array(&$this, 'TypeChar'), ' $weekday ');
        $option6->connect_object('activate', array(&$this, 'TypeChar'), ' $time ');

    }

    /***********************************************************/
    /* Toggle Caret Lines
    /***********************************************************/
    function setCaret()
    {
        $this->caret = !$this->caret;
        $this->textSections['@LabelText']->set_caret_line_visible($this->caret);
        $this->textSections['@MergeHeader']->set_caret_line_visible($this->caret);
        $this->textSections['@GroupHeader']->set_caret_line_visible($this->caret);
        $this->textSections['@MergeDetail']->set_caret_line_visible($this->caret);
        $this->textSections['@GroupFooter']->set_caret_line_visible($this->caret);
        $this->textSections['@MergeFooter']->set_caret_line_visible($this->caret);
        $this->textSections['@FinalSummary']->set_caret_line_visible($this->caret);
    }

    /***********************************************************/
    /* Toggle EOL
    /***********************************************************/
    function setEOL()
    {
        $this->eol = !$this->eol;
        $this->textSections['@LabelText']->set_view_eol($this->eol);
        $this->textSections['@MergeHeader']->set_view_eol($this->eol);
        $this->textSections['@GroupHeader']->set_view_eol($this->eol);
        $this->textSections['@MergeDetail']->set_view_eol($this->eol);
        $this->textSections['@GroupFooter']->set_view_eol($this->eol);
        $this->textSections['@MergeFooter']->set_view_eol($this->eol);
        $this->textSections['@FinalSummary']->set_view_eol($this->eol);
    }

    /***********************************************************/
    /* Toggle Word Wrap
    /***********************************************************/
    function setWrap()
    {
        $this->wrap = !$this->wrap;
        $this->textSections['@LabelText']->set_wrap_mode($this->wrap);
        $this->textSections['@MergeHeader']->set_wrap_mode($this->wrap);
        $this->textSections['@GroupHeader']->set_wrap_mode($this->wrap);
        $this->textSections['@MergeDetail']->set_wrap_mode($this->wrap);
        $this->textSections['@GroupFooter']->set_wrap_mode($this->wrap);
        $this->textSections['@MergeFooter']->set_wrap_mode($this->wrap);
        $this->textSections['@FinalSummary']->set_wrap_mode($this->wrap);
    }

    /***********************************************************/
    /* PopUpMenu chars
    /***********************************************************/
    function PopChars()
    {
        $this->MenuChars->popup(null, null, null, 1, 1);
        $this->MenuChars->show_all();
    }

    /***********************************************************/
    /* PopUpMenu datetime
    /***********************************************************/
    function PopDateTime()
    {
        $this->MenuDateTime->popup(null, null, null, 1, 1);
        $this->MenuDateTime->show_all();
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
    /* Color selection dialog
    /***********************************************************/
    function PickColor($callback)
    {
        include_once 'classes/util/ColorChooser.php';
        new ColorChooser($callback);
    }

    /***********************************************************/
    /* Font selection dialog
    /***********************************************************/
    function FontColor($color)
    {
        $this->TypeChar("#setcf$color");
    }

    /***********************************************************/
    /* Background color
    /***********************************************************/
    /*function BgColor($color)
    {
        $this->TypeChar("#setcb$color");
    }*/

    /***********************************************************/
    /* Asks by tabulation
    /***********************************************************/
    function Tabulation()
    {
        $this->TabulationBox = new InputBox(_a('Tab Width'), 100, '', array(&$this, 'GetTabulation'));
    }

    /***********************************************************/
    /* Inserts the tabulation
    /***********************************************************/
    function GetTabulation($entry)
    {
        $this->TabulationBox->Close();
        $width = $entry->get_text();
        $this->TypeChar('#tab' . substr('000', 0, 3-strlen($width)) . $width);
    }

    /***********************************************************/
    /* Asks by tabulation
    /***********************************************************/
    function SetY()
    {
        $this->SetYBox = new InputBox(_a('Tab Width'), 100, '', array(&$this, 'GetSetYBox'));
    }

    /***********************************************************/
    /* Line Space
    /***********************************************************/
    function SetVSpace()
    {
        $this->VSpaceBox = new InputBox(_a('Line height'), 100, '', array(&$this, 'GetVSpace'));
    }

    /***********************************************************/
    /*
    /***********************************************************/
    function GetVSpace($entry)
    {
        $this->VSpaceBox->Close();
        $width = $entry->get_text();
        $this->TypeChar('#setspace' . substr('000', 0, 3-strlen($width)) . $width);
    }

    /***********************************************************/
    /* Inserts the tabulation
    /***********************************************************/
    function GetSetYBox($entry)
    {
        $this->SetYBox->Close();
        $width = $entry->get_text();
        $this->TypeChar('#sety' . substr('000', 0, 3-strlen($width)) . $width);
    }


    /***********************************************************/
    /* Prints the imge tag
    /***********************************************************/
    function TypeImage($fs)
    {
        $FileName = $fs->get_filename();
        if ($FileName)
        {
            $fs->hide();
            # /AGATA/IMAGES/CUSTOM/rodrigo_ass.jpg ao inv�s de C:\AGATA\IMAGES\CUSTOM\rodrigo_ass.jpg
            $this->TypeChar("#image $FileName");
        }
    }

    /***********************************************************/
    /* Prints the background image tag
    /***********************************************************/
    function TypeBgImage($fs)
    {
        $FileName = $fs->get_filename();
        if ($FileName)
        {
            $fs->hide();
            $this->TypeChar("#bgimage $FileName");
        }
    }

    /***********************************************************/
    /* Prints the File tag
    /***********************************************************/
    function TypeFile($fs)
    {
        $FileName = $fs->get_filename();
        if ($FileName)
        {
            $fs->hide();
            $this->TypeChar("#file $FileName");
        }
    }

    function GetCurrentPage()
    {
        $sections[0] = '@MergeHeader';
        $sections[1] = '@GroupHeader';
        $sections[2] = '@MergeDetail';
        $sections[(3+$this->numberSubReport)] = '@GroupFooter';
        $sections[(4+$this->numberSubReport)] = '@MergeFooter';
        $sections[(5+$this->numberSubReport)] = '@FinalSummary';

        return $sections[$this->SectionsNotebook->get_current_page()];
    }

    /***********************************************************/
    /* Prints any char on current notebook
    /***********************************************************/
    function TypeChar($text)
    {
        if ($this->MergeNotebook->get_current_page() == 0)
        {


            $currentSubReport = $this->getCurrentSubReport();
            if ($this->SubSelectList[$currentSubReport] && $currentSubReport != 0)
            {
                $this->SubSql[$currentSubReport]->addDetail($text);
            }
            else
            {
                $page = $this->GetCurrentPage();
                $this->textSections[$page]->insertText($text);
                $this->textSections[$page]->grab_focus();
            }
        }
        else
        {
            $this->textSections['@LabelText']->insertText($text);
            $this->textSections['@LabelText']->grab_focus();
        }
    }

    /***********************************************************/
    /* Prints the Font tag
    /***********************************************************/
    function TypeFonts()
    {
        $font  = strtolower(substr($this->entryFonts->get_text(),0,1));
        $style = strtolower(substr($this->entryStyles->get_text(),0,1));
        $size  = strtolower(substr($this->entrySizes->get_text(),0,2));
        $style = $this->entryStyles->get_text() == 'Bold Italic'            ? 'w'   : $style;
        $style = $this->entryStyles->get_text() == 'Bold Underline'         ? 'x'   : $style;
        $style = $this->entryStyles->get_text() == 'Bold Italic Underline'  ? 'y'   : $style;
        $style = $this->entryStyles->get_text() == 'Italic Underline'       ? 'z'   : $style;
        $this->TypeChar("#setf{$font}{$style}{$size}");
    }

    /***********************************************************/
    /* Prints the Fields tag
    /***********************************************************/
    function TypeFields()
    {
        $entry = $this->entryFields->get_text();
        $text = $this->MergeFields[$entry];
        $this->TypeChar($text);
    }

    /***********************************************************/
    /* Prints the Fields tag
    /***********************************************************/
    function TypeSubField($function = null)
    {
        $currentSubReport = $this->getCurrentSubReport();
        if ($currentSubReport > 0)
        {
            $fieldsList = $this->SubSql[$currentSubReport]->comboSubFields->list;
            $entrySubField = $this->SubSql[$currentSubReport]->comboSubFields->entry;
            $entry = $entrySubField->get_text();
        }
        else
        {
            $fieldsList = $this->comboSubFields->list;
            $entry = $this->entrySubField->get_text();
        }
        if ($function)
        {
            # Type the Field sumarizatio
            if ( $this->GetCurrentPage() == '@GroupFooter' )
            {
                $entry = $this->entryTotalField->get_text();
                $text = $this->MergeSubFields[$currentSubReport][$entry];
            }
            elseif ( $this->GetCurrentPage() == '@FinalSummary')
            {
                $entry = $this->entryTotalFinalSummary->get_text();
                $text = $this->MergeSubFields['@FinalSummary'][$entry];
            }
            $this->TypeChar(' ' . trim($text) . '_' . $function .  ' ');
        }
        else
        {
            # Type the field
            $text = $this->MergeSubFields[$currentSubReport][$entry];
            $this->TypeChar($text);
        }
    }

    /***********************************************************/
    /* Asks by Rectangle parameters
    /***********************************************************/
    function GetRect()
    {
        $ParameterList[] = array(0,          'X',               false, 'spin',   true);
        $ParameterList[] = array(0,          'Y',               false, 'spin',   true);
        $ParameterList[] = array(300,        _a('Width'),       false, 'spin',   true);
        $ParameterList[] = array(100,        _a('Height'),      false, 'spin',   true);
        $ParameterList[] = array(1,          _a('Line height'), false, 'spin',   true);
        $ParameterList[] = array('#FFFFFF',  _a('Fill color'),  false, 'colors', true);
        $ParameterList[] = array('#000000',  _a('Line color'),  false, 'colors', true);

        $this->RectFormEntry = new FormEntry(_a('New Rectangle'), $ParameterList);
        $this->RectFormEntry->SetStatus(_a('The X,Y Coordinates are relative to the current line'));

        $this->RectFormEntry->button->connect_object('clicked', array(&$this,'ReturnRectangle'));
        $this->RectFormEntry->Show();
    }

    /***********************************************************/
    /* Asks by Vertical
    /***********************************************************/
    function GetLine()
    {
        $radio1 = new GtkRadioButton(null,    _a('Horizontal'));
        $radio2 = new GtkRadioButton($radio1, _a('Vertical'));

        $ParameterList[] = array('',  '',                false, $radio1,  null);
        $ParameterList[] = array('',  '',                false, $radio2,  null);
        $ParameterList[] = array(100, _a('Size'),        false, 'spin',   true);

        $this->LineFormEntry = new FormEntry(_a('Insert Line'), $ParameterList, null, true);

        $this->LineFormEntry->button->connect_object('clicked', array(&$this,'ReturnLine'));
        $this->LineFormEntry->Show();
    }

    /***********************************************************/
    /* Prints the Rectangle tag
    /***********************************************************/
    function ReturnLine()
    {
        $entries = $this->LineFormEntry->InputEntries['unique'];
        $direction = $entries[0]->get_active() ? 'H': 'V';
        $size      = str_pad($entries[2]->get_text(), 3, '0', STR_PAD_LEFT);
        $this->TypeChar("#line{$direction}{$size}");
        $this->LineFormEntry->Close();
    }

    /***********************************************************/
    /* Asks by Vertical Text
    /***********************************************************/
    function GetVText()
    {
        $radio1 = new GtkRadioButton(null,    _a('Left'));
        $radio2 = new GtkRadioButton($radio1, _a('Upper'));
        $radio3 = new GtkRadioButton($radio1, _a('Right'));
        $radio4 = new GtkRadioButton($radio1, _a('Down'));
        $radio5 = new GtkRadioButton($radio1, _a('Degrees'));

        $ParameterList[] = array('',  '',                false, $radio1,  null);
        $ParameterList[] = array('',  '',                false, $radio2,  null);
        $ParameterList[] = array('',  '',                false, $radio3,  null);
        $ParameterList[] = array('',  '',                false, $radio4,  null);
        $ParameterList[] = array('',  '',                false, $radio5,  null);
        $ParameterList[] = array('',  '',                false, 'line',   null);
        $ParameterList[] = array(45,  _a('Degrees'),     false, 'spin',   true);
        $ParameterList[] = array('',  _a('Text'),        false, null,     true);

        $this->VTextFormEntry = new FormEntry(_a('Text rotation'), $ParameterList, null, true);

        $this->VTextFormEntry->button->connect_object('clicked', array(&$this,'ReturnVText'));
        $this->VTextFormEntry->Show();
    }

    /***********************************************************/
    /* Prints the Vertical Text
    /***********************************************************/
    function ReturnVText()
    {
        $entries   = $this->VTextFormEntry->InputEntries['unique'];
        $direction = $entries[0]->get_active() ? 'L' : $direction;
        $direction = $entries[1]->get_active() ? 'U' : $direction;
        $direction = $entries[2]->get_active() ? 'R' : $direction;
        $direction = $entries[3]->get_active() ? 'D' : $direction;
        $direction = $entries[4]->get_active() ? 'G' : $direction;
        $size      = str_pad($entries[6]->get_text(), 3, '0', STR_PAD_LEFT);
        $text      = $entries[7]->get_text();

        #rotaLsldkfjasdf
        #rota045sfalssssdf
        if ($direction == 'G') //degrees
        {
            $this->TypeChar("#rota{$size}{$text}");
        }
        else
        {
            $this->TypeChar("#rota{$direction}{$text}");
        }
        $this->VTextFormEntry->Close();
    }

    /***********************************************************/
    /* Prints the Rectangle tag
    /***********************************************************/
    function ReturnRectangle()
    {
        $entries = $this->RectFormEntry->InputEntries['unique'];
        $x      = str_pad($entries[0]->get_text(), 3, '0', STR_PAD_LEFT);
        $y      = str_pad($entries[1]->get_text(), 3, '0', STR_PAD_LEFT);
        $width  = str_pad($entries[2]->get_text(), 3, '0', STR_PAD_LEFT);
        $height = str_pad($entries[3]->get_text(), 3, '0', STR_PAD_LEFT);
        $line   = $entries[4]->get_text();
        $fillc  = $entries[5]->get_text();
        $linec  = $entries[6]->get_text();
        $this->TypeChar("#rect*$x*$y*$width*$height*$line*$fillc*$linec");
        $this->RectFormEntry->Close();
    }


    /***********************************************************/
    /* Asks by Rectangle parameters
    /***********************************************************/
    function GetRound()
    {
        $ParameterList[] = array(0,          'X',               false, 'spin',   true);
        $ParameterList[] = array(0,          'Y',               false, 'spin',   true);
        $ParameterList[] = array(300,        _a('Width'),       false, 'spin',   true);
        $ParameterList[] = array(100,        _a('Height'),      false, 'spin',   true);
        $ParameterList[] = array(1,          _a('Line height'), false, 'spin',   true);
        $ParameterList[] = array('#FFFFFF',  _a('Fill color'),  false, 'colors', true);
        $ParameterList[] = array('#000000',  _a('Line color'),  false, 'colors', true);

        $this->RectFormEntry = new FormEntry(_a('New Rounded Rectangle'), $ParameterList);
        $this->RectFormEntry->SetStatus(_a('The X,Y Coordinates are relative to the current line'));

        $this->RectFormEntry->button->connect_object('clicked', array(&$this,'ReturnRound'));
        $this->RectFormEntry->Show();
    }

    /***********************************************************/
    /* Prints the Rectangle tag
    /***********************************************************/
    function ReturnRound()
    {
        $entries = $this->RectFormEntry->InputEntries['unique'];
        $x      = str_pad($entries[0]->get_text(), 3, '0', STR_PAD_LEFT);
        $y      = str_pad($entries[1]->get_text(), 3, '0', STR_PAD_LEFT);
        $width  = str_pad($entries[2]->get_text(), 3, '0', STR_PAD_LEFT);
        $height = str_pad($entries[3]->get_text(), 3, '0', STR_PAD_LEFT);
        $line   = $entries[4]->get_text();
        $fillc  = $entries[5]->get_text();
        $linec  = $entries[6]->get_text();
        $this->TypeChar("#rectr*$x*$y*$width*$height*$line*$fillc*$linec");
        $this->RectFormEntry->Close();
    }

    /***********************************************************/
    /* Asks by Ellipse parameters
    /***********************************************************/
    function GetEllipse()
    {
        $ParameterList[] = array(200,       'X',               false, 'spin',   true);
        $ParameterList[] = array(0,         'Y',               false, 'spin',   true);
        $ParameterList[] = array(50,        _a('X Ray'),       false, 'spin',   true);
        $ParameterList[] = array(50,        _a('Y Ray'),       false, 'spin',   true);
        $ParameterList[] = array(1,         _a('Line height'), false, 'spin',   true);
        $ParameterList[] = array('#FFFFFF', _a('Fill color'),  false, 'colors', true);
        $ParameterList[] = array('#000000', _a('Line color'),  false, 'colors', true);

        $this->EllipseFormEntry = new FormEntry(_a('New Ellipse'), $ParameterList);
        $this->EllipseFormEntry->SetStatus(_a('The X,Y Coordinates are relative to the current line'));

        $this->EllipseFormEntry->button->connect_object('clicked', array(&$this,'ReturnEllipse'));
        $this->EllipseFormEntry->Show();
    }

    /***********************************************************/
    /* Prints the Ellipse tag
    /***********************************************************/
    function ReturnEllipse()
    {
        $entries = $this->EllipseFormEntry->InputEntries['unique'];
        $x      = str_pad($entries[0]->get_text(), 3, '0', STR_PAD_LEFT);
        $y      = str_pad($entries[1]->get_text(), 3, '0', STR_PAD_LEFT);
        $xray   = str_pad($entries[2]->get_text(), 3, '0', STR_PAD_LEFT);
        $yray   = str_pad($entries[3]->get_text(), 3, '0', STR_PAD_LEFT);
        $line   = $entries[4]->get_text();
        $fillc  = $entries[5]->get_text();
        $linec  = $entries[6]->get_text();
        $this->TypeChar("#elip*$x*$y*$xray*$yray*$line*$fillc*$linec");
        #ellipse*400*400*140*140*1*#f7ec9d*#FFFFFF
        $this->EllipseFormEntry->Close();
    }

    /***********************************************************/
    /* Asks by BarCode parameters
    /***********************************************************/
    function GetBarCode()
    {
        #barcode*code*width*height*printtext
        for ($n=1; $n<=count($this->MergeFields); $n++)
        {
            $code[] = '$var' . $n;
        }

        $ParameterList[] = array('',   _a('Code'),       false, $code,          true);
        $ParameterList[] = array(20,   _a('Char Width'), false, 'spin',         true);
        $ParameterList[] = array(50,   _a('Height'),     false, 'spin',         true);
        $ParameterList[] = array(50,   _a('Print Text'), false, 'CheckButton',  true);

        $this->BarCodeFormEntry = new FormEntry(_a('New Bar Code'), $ParameterList);
        $this->BarCodeFormEntry->SetStatus(_a('Code may be a fixed value or a report column'));

        $this->BarCodeFormEntry->button->connect_object('clicked', array(&$this,'ReturnBarCode'));
        $this->BarCodeFormEntry->Show();
    }

    /***********************************************************/
    /* Prints the BarCode tag
    /***********************************************************/
    function ReturnBarCode()
    {
        $entries   = $this->BarCodeFormEntry->InputEntries['unique'];
        if ($entry     = $entries[0]->entry)
        {
            $code      = $entry->get_text();
        }
        else
        {
            $code      = $entries[0]->get_text();
        }
        $charwidth = $entries[1]->get_text();
        $height    = $entries[2]->get_text();
        $printtext = $entries[3]->get_active();
        $print = $printtext ? '1' : '0';
        $this->TypeChar("#barcode*$code*$charwidth*$height*$print");
        $this->BarCodeFormEntry->Close();
    }

    /***********************************************************/
    /* PopUpMenu datetime
    /***********************************************************/
    function PopFunctions()
    {
        $this->MenuFunctions->popup(null, null, null, 1, 1);
        $this->MenuFunctions->show_all();
    }

    /***********************************************************/
    /* add a sub field on pop-up
    /***********************************************************/
    function addSubField($pixmap, $caption, $value, $currentSubReport=null)
    {
        if (!$currentSubReport)
        {
            $currentSubReport = $this->getCurrentSubReport();
        }
        if ($currentSubReport > 0)
        {
            $fieldsList = $this->SubSql[$currentSubReport]->comboSubFields->list;
        }
        else
        {
            $fieldsList = $this->comboSubFields->list;
        }
        $this->MergeSubFields[$currentSubReport][$caption] = $value;
        # SubField
        $item = new GtkListItem();
        $box = new GtkHBox();
        $arrow = new GtkPixmap($pixmap[0], $pixmap[1]);
        $label = new GtkLabel($caption);
        $box->pack_start($arrow, false, false);
        $box->pack_start($label, false, false, 2);
        $this->comboSubFields->set_item_string($item, $caption);
        $item->add($box);
        $fieldsList->add($item);
        $item->show_all();
    }

    /***********************************************************/
    /* add a field to be summarized
    /***********************************************************/
    function addTotalField($pixmap, $caption, $value)
    {
        $currentSubReport = $this->getCurrentSubReport();
        /*if ($currentSubReport > 0)
        {
            $fieldsList = $this->SubSql[$currentSubReport]->comboSubFields->list;
        }
        else
        {
            $fieldsList = $this->comboSubFields->list;
        }*/
        $fieldsTotalList = $this->comboTotalFields->list;
        $this->MergeSubFields[$currentSubReport][$caption] = $value;
        # SubField Sumarization
        $item = new GtkListItem();
        $box = new GtkHBox();
        $arrow = new GtkPixmap($pixmap[0], $pixmap[1]);
        $label = new GtkLabel($caption);
        $box->pack_start($arrow, false, false);
        $box->pack_start($label, false, false, 2);
        $this->comboTotalFields->set_item_string($item, $caption);
        $item->add($box);
        $fieldsTotalList->add($item);
        $item->show_all();
    }

    /***********************************************************/
    /* add a field to be summarized
    /***********************************************************/
    function addTotalFinalSummary($pixmap, $caption, $value)
    {
        $fieldsTotalFinalSummaryList = $this->comboTotalFinalSummary->list;
        $this->MergeSubFields['@FinalSummary'][$caption] = $value;

        # SubField Sumarization
        $item = new GtkListItem();
        $box = new GtkHBox();
        $arrow = new GtkPixmap($pixmap[0], $pixmap[1]);
        $label = new GtkLabel($caption);
        $box->pack_start($arrow, false, false);
        $box->pack_start($label, false, false, 2);
        $this->comboTotalFinalSummary->set_item_string($item, $caption);
        $item->add($box);
        $fieldsTotalFinalSummaryList->add($item);
        $item->show_all();
    }

    /***********************************************************/
    /* Launched when users change the SELECT statement of
    /* Sub Query
    /***********************************************************/
    function RefreshSubFields($currentSubReport=null)
    {
        global $Pixmaps;
        if (!$currentSubReport)
        {
            $currentSubReport = $this->getCurrentSubReport();
        }
        $Content   = @$this->SubSelectList[$currentSubReport]->Block['Select'][1];
        if ($Content)
        {
            $Elements  = $this->SubSelectList[$currentSubReport]->GetSelectColumns(_a('Column'));
            if ($Elements)
            {
                $this->MergeSubFields[$currentSubReport] = null;
                $i = 1;
                if ($currentSubReport > 0 )
                {
                    $fieldsList = $this->SubSql[$currentSubReport]->comboSubFields->list;
                    $fieldsList->clear_items(0, -1);
                    $subRepot = $currentSubReport;
                }
                else
                {
                    $fieldsList = $this->comboSubFields->list;
                    $fieldsList->clear_items(0, -1);

                    $fieldsTotalFinalSummaryList = $this->comboTotalFinalSummary->list;
                    $fieldsTotalFinalSummaryList->clear_items(0, -1);

                    $fieldsTotalList = $this->comboTotalFields->list;
                    $fieldsTotalList->clear_items(0, -1);
                    $subRepot = null;
                }
                foreach ($Elements as $Element)
                {
                    $this->addSubField(   $Pixmaps['field'], $Element, ' $' . $subRepot . 'subfield' . $i . ' ', $currentSubReport);
                    if (!$subRepot)
                    {
                        $this->addTotalField( $Pixmaps['field'], $Element, ' $' . $subRepot . 'subfield' . $i . ' ', $currentSubReport);
                        $this->addTotalFinalSummary( $Pixmaps['field'], $Element, ' $subfield' . $i . '_summary ', $currentSubReport);
                    }
                    $i ++;
                }
            }
            else
            {
                $this->comboSubFields->set_popdown_strings(array(null));
                $this->comboTotalFields->set_popdown_strings(array(null));
            }
        }
        else
        {
            $this->comboSubFields->set_popdown_strings(array(null));
            $this->comboTotalFields->set_popdown_strings(array(null));
        }
    }

    /***********************************************************/
    /* Load the Main's Query Fields in a ComboBos
    /***********************************************************/
    function LoadFields($Elements, $Parameters)
    {
        global $Pixmaps;
        $this->MergeFields = null;
        $fieldsList = $this->comboFields->list;
        $fieldsList->clear_items(0, -1);
        $i = 1;
        foreach($Elements as $Element)
        {
            $this->MergeFields[$Element] = ' $var' . $i . ' ';
            $item = new GtkListItem();
            $box = new GtkHBox();
            $arrow = new GtkPixmap($Pixmaps['field'][0], $Pixmaps['field'][1]);
            $label = new GtkLabel($Element);
            $box->pack_start($arrow, false, false);
            $box->pack_start($label, false, false, 2);
            $item->add($box);
            $this->comboFields->set_item_string($item, $Element);
            $fieldsList->add($item);
            $item->show_all();
            $i ++;
        }
        if ($Parameters)
        {
            foreach ($Parameters as $Parameter)
            {
                $caption = _a('Parameter') . ' : ' . $Parameter;
                $this->MergeFields[$caption] = $Parameter;
                $item = new GtkListItem();
                $box = new GtkHBox();
                $arrow = new GtkPixmap($Pixmaps['ico_edit'][0], $Pixmaps['ico_edit'][1]);
                $label = new GtkLabel($caption);
                $box->pack_start($arrow, false, false);
                $box->pack_start($label, false, false, 2);
                $item->add($box);
                $this->comboFields->set_item_string($item, $caption);
                $fieldsList->add($item);
                $item->show_all();
            }
        }
    }

    /***********************************************************/
    /* Clear all fields
    /***********************************************************/
    function Clear()
    {
        for ($x=0; $x<=$this->numberSubReport; $x++)
        {
            $this->SubSelectList[$x]->LoadBlocks(null);
        }
        $this->textSections['@MergeHeader']->clear_all();
        $this->textSections['@GroupHeader']->clear_all();
        $this->textSections['@MergeDetail']->clear_all();
        $this->textSections['@GroupFooter']->clear_all();
        $this->textSections['@MergeFooter']->clear_all();
        $this->textSections['@FinalSummary']->clear_all();
        $this->textSections['@LabelText']->clear_all();
        $this->PageValues = null;
    }


    function OpenLabelTemplates()
    {
        global $Pixmaps;
        $LabelDir = $this->agataConfig['general']['AgataDir'] . bar . 'label';
        //$button1 = array('interface/output.xpm', _a('Output'), $OutputDir);
        //new FileDialog(_a('Label Templates'), array('agl'), null, $LabelDir, array(&$this, 'ApplyLabelTemplate'));
        $items = Label::ListLabels();
        $this->LabelList = new IList($items, array(&$this, 'ApplyLabelTemplate'), $Pixmaps['agl'], _a('Label Templates'), _a('Labels'));
        $this->LabelList->window->set_default_size(400, 260);
    }

    function ApplyLabelTemplate()
    {
        $label = $this->LabelList->GetItem();
        if ($label)
        {
            $data = Label::ReadLabel($label);
            $this->entries['HSpacing']->set_text($data['horizontal_spacing']);
            $this->entries['VSpacing']->set_text($data['vertical_spacing']);
            $this->entries['LabelWidth']->set_text($data['label_width']);
            $this->entries['LabelHeight']->set_text($data['label_height']);
            $this->entries['LeftMargin']->set_text($data['left_margin']);
            $this->entries['TopMargin']->set_text($data['top_margin']);
            $this->entries['LabelCols']->set_text($data['label_cols']);
            $this->entries['LabelRows']->set_text($data['label_rows']);
            $this->entries['LabelFormat']->set_text($data['page_format']);
            $this->entries['LabelSpacing']->set_text($data['line_spacing']);
        }
    }

    /***********************************************************/
    /* Add new tab for insert subquery
    /***********************************************************/
    function addDetail($glade)
    {
        $Content   = @$this->SubSelectList[0]->Block['Select'][1];
        if ($Content)
        {
            $box = new GtkHBox;
            $this->numberSubReport++;
            $lBox = new GtkHBox();
            $label = new GtkLabel($this->numberSubReport);

            //Cria bot�o para remover aba
            $this->SubSelectListDelButton[$this->numberSubReport] = new GtkButton('-');
            $this->SubSelectListDelButton[$this->numberSubReport]->connect_object('clicked', array(&$this, 'delDetail'), $this->numberSubReport);
            $this->SubSelectListDelButton[$this->numberSubReport]->show();

            $this->SubSql[$this->numberSubReport] = new SubSql($this->eventAddDetail, array(&$this, 'TypeSubField'));

            $this->SubSelectList[$this->numberSubReport] = &$this->SubSql[$this->numberSubReport]->selectList;
            $this->SubSelectList[$this->numberSubReport]->connect_object('changed', array(&$this, 'RefreshSubFields'), $this->numberSubReport);
            $this->SubAdjustmentsConfig[$this->numberSubReport] = &$this->SubSql[$this->numberSubReport]->SubAdjustmentsConfig;

            $box->pack_start($this->SubSql[$this->numberSubReport]->widget);
            $box->show_all();

            $lBox->pack_start($label);
            $lBox->pack_start($this->SubSelectListDelButton[$this->numberSubReport]);
            //Remove o bot�o da aba anterior
            if ($this->SubSelectListDelButton[$this->numberSubReport-1])
            {
                $this->SubSelectListDelButton[$this->numberSubReport-1]->hide();
            }

            $label->show_all();
            $this->SectionsNotebook->insert_page($box, $lBox, $this->numberSubReport+2);
        }
        else
        {
            new Dialog(_a('You Have to build the Sub SQL Query firstly'));
        }
    }

    /***********************************************************/
    /* Remove tab of subquery
    /***********************************************************/
    function delDetail($numberSubReport)
    {
        $this->numberSubReport--;
        $this->SectionsNotebook->remove_page($numberSubReport+2);
        unset($this->SubSelectList[$numberSubReport],
              $this->SubSql[$numberSubReport],
              $this->SubAdjustmentsConfig[$numberSubReport]);
        //Exibe o bot�o na �ltima aba
        if ($this->SubSelectListDelButton[$this->numberSubReport])
        {
            $this->SubSelectListDelButton[$this->numberSubReport]->show();
        }
    }

    /***********************************************************/
    /* Remove all tab of subquery
    /***********************************************************/
    function delAllDetail()
    {
        $numberSubReport = $this->numberSubReport;
        for ($x=$numberSubReport; $x>=1; $x--)
        {
            $this->delDetail($x);
        }
    }

    /***********************************************************/
    /* Get tab number of current subquery
    /***********************************************************/
    function getCurrentSubReport()
    {
        $currentSubReport = $this->SectionsNotebook->get_current_page()-2;
        if ($currentSubReport < 0 || $currentSubReport > $this->numberSubReport)
        {
            return 0;
        }
        return $currentSubReport;
    }

    /***********************************************************/
    /* Add event in subquery
    /***********************************************************/
    function eventAddDetail($event)
    {
        $this->eventAddDetail = $event;
    }
}
?>
