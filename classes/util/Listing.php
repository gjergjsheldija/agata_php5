<?php
/***********************************************************/
/* Listing class
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Listing extends GtkCList
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function Listing($Title)
    {
        GtkCList::GtkCList(count($Title),$Title);
        GtkCList::set_selection_mode(GTK_SELECTION_SINGLE);
        $this->count = count($Title);
        for ($n=0; $n<=count($Title); $n++)
        {
            GtkCTree::set_column_resizeable($n, true);
        }
    }

    /***********************************************************/
    /* Append new items
    /***********************************************************/
    function AppendLineItems($Contents, $style = null)
    {
        if (count($Contents) == $this->count)
        {
            $row = GtkCList::append($Contents);
            if ($row % 2 != 0)
            {
                GtkCList::set_background($row, new GdkColor('#EEEEEE'));
            }
        }
    }

    /***********************************************************/
    /* Append new items
    /***********************************************************/
    function AppendItems($Contents, $style = null)
    {
        if (count($Contents) > 0)
        {
            foreach ($Contents as $content)
            {
                $this->AppendLineItems($content);
            }
        }
    }
}
?>