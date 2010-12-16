<?php
/***********************************************************/
/* Generic box
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Box extends GtkFrame
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function Box(&$field, $title)
    {
        GtkFrame::GtkFrame($title);
        GtkFrame::set_shadow_type(GTK_SHADOW_NONE);
        $bbox = new GtkHButtonBox();

        $bbox->set_border_width(4);
        $bbox->set_layout(GTK_BUTTONBOX_SPREAD);
        $bbox->set_spacing(2);
        GtkFrame::add($bbox);
        
        if ($title)
        {
            $bbox->pack_start($field, false, false);
        }
        GtkFrame::show_all();
    }
}

/***********************************************************/
/* Generic box for entry data
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class EntryBox extends GtkFrame
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function EntryBox(&$field, $max_lenght, $title, $PreText)
    {
        GtkFrame::GtkFrame($title);
        GtkFrame::set_shadow_type(GTK_SHADOW_NONE);
        $bbox = new GtkHButtonBox();
        
        $bbox->set_border_width(4);
        $bbox->set_layout(GTK_BUTTONBOX_SPREAD);
        $bbox->set_spacing(2);
        $bbox->set_child_size(15, 20);
        GtkFrame::add($bbox);
        
        $field->set_text($PreText);
        $field->add_events(GDK_KEY_PRESS_MASK);
        $field->set_max_length($max_lenght);
        $bbox->pack_start($field, false, false);
        
        GtkFrame::show_all();
    }
}

/***********************************************************/
/* Generic box for text data
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class TextBox extends GtkFrame
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function TextBox(&$field, $max_lenght, $title, $PreText)
    {
        GtkFrame::GtkFrame($title);
        GtkFrame::set_shadow_type(GTK_SHADOW_NONE);
        $bbox = new GtkHButtonBox();
        
        $bbox->set_border_width(5);
        $bbox->set_layout(GTK_BUTTONBOX_SPREAD);
        $bbox->set_spacing(2);
        GtkFrame::add($bbox);
        
        $field->insert(null, null, null, $PreText, -1);
        $field->add_events(GDK_KEY_PRESS_MASK);
        
        $scroll = new GtkScrolledWindow;
        $scroll->set_usize(200,200);
        $scroll->set_policy(GTK_POLICY_NEVER, GTK_POLICY_ALWAYS);
        $scroll->add($field);
        $bbox->pack_start($scroll, true, true);
        
        GtkFrame::show_all();
    }
}
?>