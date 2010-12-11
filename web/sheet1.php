<link href="site.css" rel="stylesheet" type="text/css">

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="calendar/calendar-win2k-cold-1.css" title="win2k-cold-1" />
<!-- main calendar program -->
<script type="text/javascript" src="calendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="calendar/lang/calendar-br.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="calendar/calendar-setup.js"></script>

<script language='javascript'>
    function MySubmit(form)
    {
        var winLeft = (screen.width - 600) / 2;
	    var winTop = (screen.height - 600) / 2;
	    var winTop = 50;
        windowName = "myWin";
        var windowFeatures = "width=600,height=400,status,scrollbars,resizable,left=" + winLeft + ",top=" + winTop 
        newWindow = window.open('', windowName, windowFeatures);
        form.target = 'myWin';
        form.submit();
    }
</script>

<?
$url1 = "javascript:js('tab-page-1')";
$url2 = "javascript:js('tab-page-2')";
$url3 = "javascript:js('tab-page-3')";
$url4 = "javascript:js('tab-page-4')";
?>

<table width=800 cellspacing=0 cellpadding=0>
<tr>
<td width=84 valign=top>
    <map name="menu">
    <area shape="rect" coords="01,01,80,58"    HREF="<?echo $url1;?>">
    <area shape="rect" coords="01,64,80,124"   HREF="<?echo $url2;?>">
    <area shape="rect" coords="01,126,80,186"  HREF="<?echo $url3;?>">
    <area shape="rect" coords="01,188,80,248"  HREF="<?echo $url4;?>">
    
    <area SHAPE="DEFAULT" NOHREF></map>
    <img src='imagens/bar1.png' usemap="#menu" ismap border=0><br><br>
    <center><a href='index.php'><img src='imagens/browse.png' border=0><br><? echo _a('Reports');?></a>
    </center>
</td>
<td width=716 align=left valign=top>
    <table width=100% cellspacing=0 border=1>
    <tr>
    <td valign=top>
    <table width=100% cellspacing=0 border=0>
    <tr class=tabletitle height=30>
    <td colspan=4>
        <b>&nbsp;Agata CoreReport:: <? echo _a('Report Generation'); ?></b>
    </td>
    </tr>
    <?
        $Report = CoreReport::OpenReport($file);
        $Blocks = CoreReport::ExtractBlock($Report['Report']['DataSet']);
        $datasource = $Report['Report']['DataSet']['DataSource']['Name'];
    ?>
    <form name=sheet1 method=post action=restrictions.php onsubmit="return MySubmit(this);">
    <tr class=tablepath>
    <td colspan=4>
        &nbsp;<?echo _a('Project Name'); ?>
    
    </td>
    </tr>

    <tr class=line1>
        <td width=6%>  </td>
        <td width=10% align=center>
        <img src='imagens/ico_db.png' border=0></td>
        <td colspan=2 width=84%> <? echo _a('Project Name') . ':'; ?>
        <select name="connection">
        <?
        $projects = array_keys(Project::ReadProjects());
        foreach ($projects as $project)
        {
            $x = ($project == $datasource ? 'selected' : '');
            echo "<option value=\"$project\" $x>$project</option>\n";
        }
        ?>
        </select>
        </td>
    </tr>

    <tr class=tablepath>
    <td colspan=4>
        &nbsp;<?echo _a('File') . ' : ' . $file; ?>
    </td>
    </tr>
    <?
    $images['From']     = 'imagens/ico_table.png';
    $images['Group by'] = 'imagens/ico_group.png';
    $images['Order by'] = 'imagens/ico_sort.png';
    
    $ClauseLabel['From']      = _a('Tables');
    $ClauseLabel['Group by']  = _a('Groups');
    $ClauseLabel['Order by']  = _a('Ordering');
    
    foreach ($Blocks as $Clause => $Content)
    {
        echo '<tr>';
        echo '<td class=line1 colspan=1 valign=top>';
        echo '<td class=line1 colspan=1 valign=top>';
        echo '</td>';
        echo '<td class=line1 colspan=2 valign=top>';

        if ($Clause == 'Select')
        {
            $select = MyExplode(trim($Content[1]));
            $i = 0;
            echo '<br>';
            echo '<table width=100% border=1 cellpadding=0 cellspacing=0>';
            echo '<tr><td>';
            echo '<table width=100% border=0 cellpadding=0 cellspacing=0>';
            $group_table=-1;
            foreach ($select as $piece)
            {
                if (!$Report['Report']['DataSet']['Query']['AgataWeb']['Select'])
                {
                    $checked = 'checked';
                }
                else
                {
                    $checked = (strpos($Report['Report']['DataSet']['Query']['AgataWeb']['Select'], $piece) !== false) ? 'checked' : '';
                }
                
                $pieces = explode('.', $piece);
                $table = $pieces[0];
                if (($table != $group_table) and (count($pieces) == 2))
    	        {
                    echo '<tr><td colspan=3 class=tablepath>' . _a('Columns') .  ' : ' ._a('Table') . ' ' . $table . ' </td></tr>';
                }
                $group_table = $table;
                
                echo '<tr><td>';
                $label = $piece;
                if (eregi(' as ', $piece))
                {
                    //$pieces = explode(' as ', $piece);
                    $pieces = preg_split('/ as /i', $piece);
                    $label = str_replace("\"", '', $pieces[1]);
                }
                /*elseif (ereg(' AS ', $piece))
                {
                    $pieces = explode(' AS ', $piece);
                    $label = str_replace("\"", '', $pieces[1]);
                }*/
                $piece = ereg_replace("'", "`", $piece);
                echo "<input type='checkbox' $checked name='SelectFields[$i]' value='$piece'><img src='imagens/ico_field.png'> $label";
                echo '</td></tr>';
                echo "\n";
                $i ++;
            }
            echo "</table>\n";
            echo '</td></tr>';
            echo "</table>\n";
        }
        else if ($Clause == 'Where')
        {
            //$pieces = explode(' and ', trim($Content[1]));
            $pieces = preg_split('/ and /i', trim($Content[1]));
            if ($pieces)
            {
                echo '<br>';
                echo '<table width=100% border=1>';
                echo '<tr><td colspan=3 class=tablepath>' . _a('Constraints') . '</td></tr>';
                foreach ($pieces as $piece)
                {
                    echo '<tr><td>';
                    echo "<img src='imagens/ico_constraint.png'> $piece<br>\n";
                    echo "</td></tr>\n";
                }
                echo '</table>';
            }
        }
        else
        {
            if ($Clause === 'From')
            {
                $pieces = get_tables_from(trim($Content[1]));
            }
            else
            {
                $pieces = MyExplode(trim($Content[1]));
            }
            if ($pieces)
            {
                echo '<br>';
                echo '<table width=100% border=1>';
                echo '<tr><td colspan=3 class=tablepath>' . $ClauseLabel[$Clause] . '</td></tr>';
                foreach ($pieces as $piece)
                {
                    if ($images[$Clause])
                    {
                        $image = $images[$Clause];
                        echo '<tr><td>';
                        echo "<img src='$image'> $piece<br>\n";
                        echo '</td></tr>';
                    }
                    else
                    {
                        echo '<tr><td>';
                        echo "$piece<br>\n";
                        echo '</td></tr>';
                    }
                }
                echo "</table>\n";
            }
        }
        echo '</td>';
        echo '</tr>'; 
        echo "\n";
    }

    //$parameters = GetParameters($Report['Report']['DataSet']['Query']['Where']);
    if ($Report['Report']['Parameters'])
    {
        //$parameters = array_keys($Report['Report']['Parameters']);
        $parameters = $Report['Report']['Parameters'];
    }
    if ($parameters)
    {
        ?>
        <tr class=tablepath>
        <td colspan=4>
            &nbsp;<?echo _a('Parameters'); ?>
        </td>
        </tr>
        <?
        foreach ($parameters as $parameter => $properties)
        {
            //$value = $Report['Report']['Parameters'][$parameter]['value'];
            $value = $properties['value'];
            $mask  = $properties['mask'];
            $newmask = $mask;
            $newmask = str_replace('dd', '%d',   $newmask);
            $newmask = str_replace('mm', '%m',   $newmask);
            $newmask = str_replace('yyyy', '%Y', $newmask);
            $parameter = "\$$parameter";
            
            ?>
            <tr class=line1> <td width=6%>  </td>
                             <td width=10% align=center><img src='imagens/ico_param.png' border=0>
                             </td>
                             <td width=44%>
                             <? echo $parameter; ?>
                             </td>
                             <td width=44% align=left>
                             <?
                             if (strstr($mask, 'dd') and strstr($mask, 'mm') and strstr($mask, 'yyyy'))
                             {
                                 ?>
                                <input type="text" value='<? echo $value; ?>' name=Parameters[<? echo $parameter; ?>] id="f_date_c" readonly="1"/>
                                <img src="imagens/popdate.png" id="f_trigger_c" style="cursor: pointer; border: 1px solid red;" title="Date selector"
                                        onmouseover="this.style.background='red';" onmouseout="this.style.background=''" />
                                        
                                <script type="text/javascript">
                                    Calendar.setup({
                                        inputField     :    "f_date_c",     // id of the input field
                                        ifFormat       :    "<? echo $newmask;//"%Y-%m-%d" ?>",      // format of the input field
                                        button         :    "f_trigger_c",  // trigger for the calendar (button ID)
                                        align          :    "Tl",           // alignment (defaults to "Bl")
                                        firstDay       :     0,
                                        singleClick    :    true
                                    });
                                </script>
                                 <?
                             }
                             else
                             {
                                 ?>
                                 <input type=entry value='<? echo $value; ?>' name=Parameters[<? echo $parameter; ?>] maxwidth=100>
                                 <?
                             }
                             ?>
                             </td>
            </td>
            </tr>
            <?
        }
        ?>
        </td>
        </tr>
        <?
    }


    ?>
    <input type=hidden name=file value=<? echo $file; ?>>
    <input type=hidden name=type value='report'>
    <input type=hidden name=lang value=<? echo $lang; ?>>
    
    <tr class=line1> <td colspan=4 align=right height=30>
        <a class=link href="javascript:MySubmit(document.sheet1)"><img src='imagens/proceed.png' border=0></a>
        &nbsp;&nbsp;&nbsp;<br>
        <a class=link href="javascript:MySubmit(document.sheet1)"><? echo _a('Proceed'); ?></a>&nbsp;&nbsp;
        </p><br>
    </td></tr>
    </form>
    </td>
    </tr>
    </table>
    </td>
    <td bgcolor="#8280fe" valign=top width=32 >
        <img src='imagens/image.png'>
    </td>
    </tr>
    </table>
</td>
</tr>
</table>
