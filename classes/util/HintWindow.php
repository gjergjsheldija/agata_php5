<?php
/***********************************************************/
/* Hint Window, displays information in a popup win
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class HintWindow extends GtkWindow
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function HintWindow($bgcolor = '#FFFFFF')
    {
        GtkWindow::GtkWindow(GTK_WINDOW_POPUP);
        $style = new GtkStyle;
        $style->bg[GTK_STATE_NORMAL] = new GdkColor($bgcolor);
        GtkWindow::set_style($style);

        $this->content = new GtkVBox;

        $styleb = new GtkStyle;
        $styleb->bg[GTK_STATE_NORMAL] = new GdkColor('#000000');
        $sep1 = new GtkVSeparator;
        $sep2 = new GtkVSeparator;
        $sep1->set_style($styleb);
        $sep2->set_style($styleb);

        $vbox = new GtkVBox;
        $hbox = new GtkHBox;
        $hbox->pack_start($sep1, false, false);
        $hbox->pack_start($vbox, true, true);
        $hbox->pack_start($sep2, false, false);
        
        $sep1 = new GtkHSeparator;
        $sep2 = new GtkHSeparator;
        $sep1->set_style($styleb);
        $sep2->set_style($styleb);
        $vbox->pack_start($sep1);
        $vbox->pack_start($this->content);
        $vbox->pack_start($sep2);
        GtkWindow::add($hbox);
        GtkWindow::show_all();
    }

    /***********************************************************/
    /* Add's content
    /***********************************************************/
    function Adiciona($widget, $expand = true, $fill = true, $padding = 0)
    {
        $content = $this->content;
        $content->pack_start($widget, $expand, $fill, $padding);
    }
}
?>