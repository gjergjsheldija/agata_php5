<?php
/***********************************************************/
/* Header and Footer controls class
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class HeaderFooterArea
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function HeaderFooterArea($glade)
    {
        global $Pixmaps;
        $this->Header = $glade->get_widget('textPageHeader');
        $this->Footer = $glade->get_widget('textPageFooter');

        $this->HeaderFooterNotebook     = $glade->get_widget('notebookHeaderFooter');
        $this->Buttons['DateTime2']     = $glade->get_widget('buttonDateTime2');
        $this->Buttons['Chars2']        = $glade->get_widget('buttonChars2');
        $this->Buttons['InsertImage2']  = $glade->get_widget('buttonInsertImage2');

        $this->radios['rbHeaderLeft']   = $glade->get_widget('rbHeaderLeft');
        $this->radios['rbHeaderCenter'] = $glade->get_widget('rbHeaderCenter');
        $this->radios['rbHeaderRight']  = $glade->get_widget('rbHeaderRight');
        $this->radios['rbFooterLeft']   = $glade->get_widget('rbFooterLeft');
        $this->radios['rbFooterCenter'] = $glade->get_widget('rbFooterCenter');
        $this->radios['rbFooterRight']  = $glade->get_widget('rbFooterRight');

        $labelHeaderLeft   = $this->radios['rbHeaderLeft']->child;
        $labelHeaderCenter = $this->radios['rbHeaderCenter']->child;
        $labelHeaderRight  = $this->radios['rbHeaderRight']->child;
        $labelFooterLeft   = $this->radios['rbFooterLeft']->child;
        $labelFooterCenter = $this->radios['rbFooterCenter']->child;
        $labelFooterRight  = $this->radios['rbFooterRight']->child;
    
        $labelHeaderLeft->set_text(_a('Left'));
        $labelFooterLeft->set_text(_a('Left'));
        $labelHeaderCenter->set_text(_a('Center'));
        $labelFooterCenter->set_text(_a('Center'));
        $labelHeaderRight->set_text(_a('Right'));
        $labelFooterRight->set_text(_a('Right'));

        $img_mimetypes = array('png', 'jpg', 'jpeg');
        $this->param['InsertImage']=array(array(&$this, 'TypeImage'), $img_mimetypes, null,     _a('Open'));
        $this->Buttons['DateTime2']->connect_object('clicked', array(&$this, 'PopDateTime'));
        $this->Buttons['Chars2']->connect_object('clicked',array(&$this, 'PopChars'));
        $this->Buttons['InsertImage2']->connect_object('clicked', 'HandlerFile', $this->param['InsertImage']);

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
    /* Prints any char
    /***********************************************************/
    function TypeChar($text)
    {
        if ($this->HeaderFooterNotebook->get_current_page() == 0)
        {
            $this->InsertHeader($text, true);
        }
        else
        {
            $this->InsertFooter($text, true);
        }
    }

    /***********************************************************/
    /* Clear Header and Footer
    /***********************************************************/
    function Clear()
    {
        $this->Header->delete_text(0, -1);
        $this->Footer->delete_text(0, -1);
    }

    /***********************************************************/
    /* Returns the Header text
    /***********************************************************/
    function GetHeader()
    {
        return $this->Header->get_chars(0, -1);
    }

    /***********************************************************/
    /* Returens the Footer text
    /***********************************************************/
    function GetFooter()
    {
        return $this->Footer->get_chars(0, -1);
    }

    /***********************************************************/
    /* Inserts the Header text
    /***********************************************************/
    function InsertHeader($text, $current = null)
    {
        if ($current)
        {
            $this->Header->insert_text($text, $this->Header->get_position());
        }
        else
        {
            $this->Header->insert(null, null, null, $text);
        }
    }

    /***********************************************************/
    /* Inserts the Footer text
    /***********************************************************/
    function InsertFooter($text, $current = null)
    {
        if ($current)
        {
            $this->Footer->insert_text($text, $this->Footer->get_position());
        }
        else
        {
            $this->Footer->insert(null, null, null, $text);
        }
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
            $this->TypeChar("#image $FileName");
        }
    }

    /***********************************************************/
    /* Returns the Header Align
    /***********************************************************/
    function GetHeaderAlign()
    {
        if ($this->radios['rbHeaderLeft']->get_active())
            return 'left';
        
        if ($this->radios['rbHeaderCenter']->get_active())
            return 'center';
        
        return 'right';
    }

    /***********************************************************/
    /* Returns the Footer Align
    /***********************************************************/
    function GetFooterAlign()
    {
        if ($this->radios['rbFooterLeft']->get_active())
            return 'left';

        if ($this->radios['rbFooterCenter']->get_active())
            return 'center';

        return 'right';
    }

    /***********************************************************/
    /* Set the Header Align
    /***********************************************************/    
    function SetHeaderAlign($align)
    {
        if ($align == 'left')
        {
            $this->radios['rbHeaderLeft']->set_active(true);
        }
        else if ($align == 'right')
        {
            $this->radios['rbHeaderRight']->set_active(true);
        }
        else
        {
            $this->radios['rbHeaderCenter']->set_active(true);
        }
    }

    /***********************************************************/
    /* Set the Footer Align
    /***********************************************************/    
    function SetFooterAlign($align)
    {
        if ($align == 'left')
        {
            $this->radios['rbFooterLeft']->set_active(true);
        }
        else if ($align == 'right')
        {
            $this->radios['rbFooterRight']->set_active(true);
        }
        else
        {
            $this->radios['rbFooterCenter']->set_active(true);
        }
    }
}
?>
