<?php
/***********************************************************/
/* Button class, builds different kind of buttons
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Button extends GtkButton
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function Button($function, $message, $image, $kind, $param = null, $showtip = true)
    {
        GtkButton::GtkButton();

        if ($showtip)
        {
            $this->tooltips = $tooltips = new GtkTooltips;
            $tooltips->set_tip($this, $message, 'ContextHelp/buttons/1');
        }

        if ($function)
        {
            if ($param)
            {
                GtkButton::connect_object_after('clicked', $function, $param);
            }
            else
            {
                GtkButton::connect_object_after('clicked', $function);
            }
        }

        $pixmapConfig = new GtkPixmap($image[0], $image[1]);
        $text = $this->text = new GtkLabel('  ' . $message . '  ');

        if ($kind == IMAGEBUTTON)
        {
            $boxConfig = new GtkHBox();
            $boxConfig->pack_start($pixmapConfig, false, false);
            $boxConfig->pack_start($text, true, true);
        }
        else if ($kind == ICONBUTTON)
        {
            $boxConfig = new GtkHBox();
            $boxConfig->pack_start($pixmapConfig, true, true);
        }
        else if ($kind == TOPBUTTON)
        {
            $boxConfig = new GtkVBox();
            $boxConfig->pack_start($pixmapConfig, false, false);
            $boxConfig->pack_start($text, true, true);
        }
        $boxConfig->show();
        $pixmapConfig->show();
        
        GtkButton::add($boxConfig);
        GtkButton::show();
    }

    /***********************************************************/
    /* Changes the caption
    /***********************************************************/
    function set_label($label)
    {
        $text = $this->text;
        $text->set_text('  '. $label . '  ');
        $tooltips = $this->tooltips;
        $tooltips->set_tip($this, $label, 'ContextHelp/buttons/1');
    }
}

/***********************************************************/
/* Void Button, with undefined action at construction
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class VoidButton extends Button
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function VoidButton($message, $image, $kind)
    {
        Button::Button(null, $message, $image, $kind, null);
    }
}

/***********************************************************/
/* Dark Button class
/* by Pablo Dall'Oglio 2004-2006
/***********************************************************/
class DarkButton extends GtkButton
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function DarkButton($text)
    {
        GtkButton::GtkButton($text);
        GtkButton::set_border_width(0);
        GtkButton::set_usize(-1, 28);
        $child = $this->child;
        GtkButton::remove($child);

        $tmp = new gtkeventbox;
        $tmp->set_border_width(0);
        $style = $tmp->style;
        $style = $style->copy();
        $style->bg[GTK_STATE_NORMAL] = new GdkColor(46036, 46036, 46036);
        $style->bg[GTK_STATE_ACTIVE] = new GdkColor(46036, 46036, 46036);
        $style->bg[GTK_STATE_PRELIGHT] = new GdkColor(46036, 46036, 46036);
        $style->bg[GTK_STATE_SELECTED] = new GdkColor(46036, 46036, 46036);
        $style->bg[GTK_STATE_INSENSITIVE] = new GdkColor(46036, 46036, 46036);
        $tmp->set_style($style);
        $tmp->add(new GtkLabel($text));
        GtkButton::set_relief(GTK_RELIEF_NONE);
        GtkButton::add($tmp);
    }
}
?>