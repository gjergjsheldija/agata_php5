<?php
/***********************************************************/
/* SimpleTree, a wrapper for GtkCtree
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class SimpleTree extends GtkCTree
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function SimpleTree($Title)
    {
        if (is_array($Title))
        {
            GtkCTree::GtkCTree(count($Title),0,$Title);
            $this->count = count($Title);
            for ($n=0; $n<=count($Title); $n++)
            {
                GtkCTree::set_column_resizeable($n, true);
            }
        }
        else
        {
            GtkCTree::GtkCTree(1,0,array($Title));
            $this->count = 1;
        }
        GtkCTree::set_selection_mode(GTK_SELECTION_EXTENDED);
        GtkCTree::set_line_style(GTK_CTREE_LINES_SOLID);
    }

    /***********************************************************/
    /* Append a subtree
    /***********************************************************/
    function AppendSubTree($Title, $Pixmap, $parent = null)
    {
        if (is_array($Title))
        $node = $Title;
        else
        $node = array($Title);
        
        $root = GtkCTree::insert_node($parent, null, $node, 5,
        $Pixmap[0], $Pixmap[1], $Pixmap[0], $Pixmap[1], false, false);
        return $root;
    }

    /***********************************************************/
    /* Append many items
    /***********************************************************/
    function AppendItems($root, $Array, $Pixmap)
    {
        foreach ($Array as $Item)
        {
            if ($Item[0])
            {
                $node = GtkCTree::insert_node($root, null, array($Item[1]), 5,
                $Pixmap[0], $Pixmap[1], $Pixmap[0], $Pixmap[1], false, false);

                GtkCTree::node_set_row_data( $node, $Item[0]);
            }
            else
            {
                $node = GtkCTree::insert_node($root, null, array(''), 5,
                                  null, null, null, null, false, false);
                GtkCTree::node_set_row_data( $node, $Item[0]);
            }
        }
    }

    /***********************************************************/
    /* Append one line
    /***********************************************************/
    function AppendLineItems($root, $Array, $Pixmap, $style = null)
    {
        if ($this->count == count($Array))
        {
            $node = GtkCTree::insert_node($root, null, $Array, 5,
                    $Pixmap[0], $Pixmap[1], $Pixmap[0], $Pixmap[1], false, false);
        }
        
        if ($style)
        {
            GtkCTree::node_set_row_style( $node, $style);
            
        }
        return $node;
    }

    /***********************************************************/
    /* Returns the current node content
    /***********************************************************/
    function GetData()
    {
        $selection = $this->selection;
        $Info = GtkCTree::node_get_row_data($selection[0]);
        
        return $Info;
    }

    /***********************************************************/
    /* Changes the current node information
    /***********************************************************/
    function SetData($node, $info)
    {
        GtkCTree::node_set_row_data($node, $info);
    }
}
?>