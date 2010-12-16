<?php
/***********************************************************/
/* List of checkbuttons
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class CheckList
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function CheckList($options)
    {
        $this->widget   = new GtkScrolledWindow;
        $this->viewport = new GtkViewport;
        $this->vbox     = new GtkVBox;
        
        $this->widget->add($this->viewport);
        $this->viewport->add($this->vbox);
        $this->widget->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_ALWAYS);
        
        foreach ($options as $option)
        {
            $element = new GtkCheckButton($option);
            $this->vbox->pack_start($element, false, false);
            $this->elements[] = $element;
        }
        $this->widget->show_all();
    }
    
    function GetValues()
    {
    
        foreach ($this->elements as $element)
        {
            $results[] = $element->get_active();
        }
        return $results;
    }
}
?>