<?php
/***********************************************************/
/* Office Interface, all Office controls are here
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class OfficeInterface
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function OfficeInterface($glade, $agataConfig)
    {
        global $Pixmaps;
        $this->agataConfig      = $agataConfig;
        $this->entryOpenOffice  = $glade->get_widget('entryOpenOffice');
        $this->buttonOpenOfficeOpen  = $glade->get_widget('buttonOpenOfficeOpen');
        $this->buttonOpenOfficeParse = $glade->get_widget('buttonOpenOfficeParse');
        $this->radioFixedDetails     = $glade->get_widget('radioFixedDetails');
        $this->radioExpandDetails    = $glade->get_widget('radioExpandDetails');
        $this->checkPrintEmpty       = $glade->get_widget('checkPrintEmpty');
        $this->checkSumTotal         = $glade->get_widget('checkSumTotal');
        $this->checkRepeatHeader     = $glade->get_widget('checkRepeatHeader');
        $this->checkRepeatFooter     = $glade->get_widget('checkRepeatFooter');
        
        $label1 = $this->radioFixedDetails->child;
        $label2 = $this->radioExpandDetails->child;
        $label3 = $this->checkPrintEmpty->child;
        $label4 = $this->checkSumTotal->child;
        $label5 = $this->checkRepeatHeader->child;
        $label6 = $this->checkRepeatFooter->child;
        
        $label1->set_text(_a('Fixed Rows in Details'));
        $label2->set_text(_a('Expand Rows in Details'));
        $label3->set_text(_a('Print Empty Detail'));
        $label4->set_text(_a('Print Grand Total'));
        $label5->set_text(_a('Repeat Header'));
        $label6->set_text(_a('Repeat Footer'));
        
        $this->buttonOpenOfficeOpen->connect_object('clicked', array(&$this, 'onOpenFile'));
    }

    /***********************************************************/
    /* onOpenFile
    /***********************************************************/
    function onOpenFile()
    {
        $params[0] = array(&$this, 'onReceiveFile');
        $params[1] = array('sxw', 'odt');
        $params[2] = AGATA_PATH;
        $params[3] = _a('Open');
        HandlerFile($params);
    }

    /***********************************************************/
    /* onReceiveFile
    /***********************************************************/
    function onReceiveFile($fs)
    {
        if ($fs)
        {
            $FileName = $fs->get_filename();
            $this->entryOpenOffice->set_text($FileName);
            $fs->hide();
        }
    }
}
?>