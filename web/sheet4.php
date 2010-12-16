<script language='javascript'>
    function js4(page)
    {
        div = document.getElementById("tab-pane-41");
        div.style.display = "none";
        div = document.getElementById("tab-pane-42");
        div.style.display = "none";
        
        div = document.getElementById(page);
        div.style.display = "block";
    }
    function js41(page)
    {
        div = document.getElementById("tab-page-411");
        div.style.display = "none";
        div = document.getElementById("tab-page-412");
        div.style.display = "none";
        div = document.getElementById("tab-page-413");
        div.style.display = "none";
        div = document.getElementById("tab-page-414");
        div.style.display = "none";
        div = document.getElementById("tab-page-415");
        div.style.display = "none";
        
        div = document.getElementById(page);
        div.style.display = "block";
    }
    function submit4(type)
    {
        document.sheet4.type.value = type;
        document.sheet4.submit();
    }
        
</script>

<table width=800 cellspacing=0 cellpadding=0>
<tr>
<td width=84 valign=top>
    <map name="menu">
    <area shape="rect" coords="01,01,80,58"    HREF="<?php echo "javascript:js('$url1')";?>">
    <area shape="rect" coords="01,64,80,124"   HREF="<?php echo "javascript:js('$url2')";?>">
    <area shape="rect" coords="01,126,80,186"  HREF="<?php echo "javascript:js('$url3')";?>">
    <area shape="rect" coords="01,188,80,248"  HREF="<?php echo "javascript:js('$url4')";?>">
    
    <area SHAPE="DEFAULT" NOHREF></map>
    <img src='images/bar4.png' usemap="#menu" ismap border=0><br><br>
    <center><a href='index.php'><img src='images/browse.png' border=0><br><?php  echo _a('Reports');?></a>
    </center>
</td>
<td width=716 align=left valign=top>
    <table width=100% cellspacing=0 border=1>
    <tr>
    <td valign=top>
    <table width=100% cellspacing=0 border=0>
    <tr class=tabletitle height=30>
    <td colspan=4>
        <b>&nbsp;Agata CoreReport:: <?php  echo _a('Merge Tool'); ?></b>
    </td>
    </tr>

    <form name=sheet4 method=post action=generate.php>
    <tr class=tablepath>
    <td colspan=4>
        &nbsp;<?php echo _a('Project Name'); ?>
    
    </td>
    </tr>

    <tr class=line1>
        <td width=6%>  </td>
        <td width=10% align=center>
        <img src='images/ico_db.png' border=0></td>
        <td colspan=2 width=84%> <?php  echo _a('Project Name') . ':'; ?>
        <select name="connection">
        <?php 
        $Report = CoreReport::OpenReport($file);
        $datasource = $Report['Report']['DataSet']['DataSource']['Name'];
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
        <?php 
            $tab_sheet = !$tab_sheet ? 1 : $tab_sheet;
        ?>
        &nbsp;<?php echo _a('File') . ' : ' . $file; ?>
        <input type=hidden name=file value=<?php  echo $file; ?>>
        <input type=hidden name=type value=<?php  echo ($tab_sheet == 1 ? 'merge' : 'label'); ?>>
    </td>
    </tr>


    <tr align=left>
    <td colspan=4 width=94 valign=top class=line1>
    <?php 
        $sub_tab   = !$sub_tab   ? 1 : $sub_tab;
        $content[1] = $Report['Report']['Merge']['ReportHeader'];
        $content[2] = $Report['Report']['Merge']['Details']['Detail1']['GroupHeader'];
        $content[3] = $Report['Report']['Merge']['Details']['Detail1']['Body'];
        $content[4] = $Report['Report']['Merge']['Details']['Detail1']['GroupFooter'];
        $content[5] = $Report['Report']['Merge']['ReportFooter'];
        
        if ($lang == 'pt')
        {
            echo "<map name='merge_menu'>";
            echo "<area shape='rect' coords='01,01,186,31'  HREF=javascript:js4('tab-pane-41')>";
            echo "<area shape='rect' coords='191,03,350,35' HREF=javascript:js4('tab-pane-42')>";
            echo "<area SHAPE='DEFAULT' NOHREF></map>";

            echo "<map name='merge_submenu'>";
            echo "<area shape='rect' coords='01,01,88,22'     HREF=javascript:js41('tab-page-411')>";
            echo "<area shape='rect' coords='90,01,232,22'    HREF=javascript:js41('tab-page-412')>";
            echo "<area shape='rect' coords='235,01,305,22'   HREF=javascript:js41('tab-page-413')>";
            echo "<area shape='rect' coords='312,01,430,22'   HREF=javascript:js41('tab-page-414')>";
            echo "<area shape='rect' coords='437,01,500,22'   HREF=javascript:js41('tab-page-415')>";
            echo "<area SHAPE='DEFAULT' NOHREF></map>";
        }
        else
        {
            echo "<map name='merge_menu'>";
            echo "<area shape='rect' coords='01,01,107,31'  HREF=javascript:js4('tab-pane-41')>";
            echo "<area shape='rect' coords='114,03,207,35' HREF=javascript:js4('tab-pane-42')>";
            echo "<area SHAPE='DEFAULT' NOHREF></map>";

            echo "<map name='merge_submenu'>";
            echo "<area shape='rect' coords='01,01,67,22'     HREF=javascript:js41('tab-page-411')>";
            echo "<area shape='rect' coords='72,01,171,22'    HREF=javascript:js41('tab-page-412')>";
            echo "<area shape='rect' coords='178,01,230,22'   HREF=javascript:js41('tab-page-413')>";
            echo "<area shape='rect' coords='236,01,328,22'   HREF=javascript:js41('tab-page-414')>";
            echo "<area shape='rect' coords='335,01,394,22'   HREF=javascript:js41('tab-page-415')>";
            echo "<area SHAPE='DEFAULT' NOHREF></map>";
        }
        
        $lang = ($lang == 'pt' ? 'pt' : 'en');
        echo '<table height=100% border=1><tr><td>';
        echo "<div id=\"tab-pane-41\" class=\"dynamic-tab-pane-control tab-pane\" style=\"display: block;\">";
        echo "<img src='images/merge1_{$lang}.png' usemap='#merge_menu' ismap border=0>";

        $submit = _a('Export to PDF File');
        
        for ($n=1; $n<=5; $n++)
        {
            $activate_sub = ($n == $sub_tab ? 'block' : 'none');
            echo "    <div id=\"tab-page-41{$n}\" class=\"tab-page\" style=\"display: $activate_sub;\">";
            echo "        <textarea name=textmerge[$n] cols=100 rows=16>" . $content[$n] . '</textarea>';
            echo '        <br>';
            echo "         <img src='images/submerge{$n}_{$lang}.png' usemap='#merge_submenu' ismap border=0>";
            echo '    </div>';
        }
        echo "<p align=center><a class=link href=\"javascript:submit4('merge')\"> <img src='images/pdf.png' border=0><br>$submit</a></p><br>";
        echo '</div>';

        echo "<div id=\"tab-pane-42\" class=\"dynamic-tab-pane-control tab-pane\" style=\"display: none;\">";
        echo "    <img src='images/merge2_{$lang}.png' usemap='#merge_menu' ismap border=0>";
        echo "    <div id=\"tab-page-41{$n}\" class=\"tab-page\" style=\"display: block;\">";
        echo '      <textarea name=label cols=100 rows=10>' . $Report['Report']['Label']['Body'] . '</textarea>';
        echo '    </div>';
        echo "<p align=center><a href=\"javascript:submit4('label')\"> <img src='images/pdf.png' border=0><br>$submit</a></p><br>";
        echo '</div>';
        echo '</table>';
    ?>
    </td>
    </tr>
    <?php 
        //$parameters = GetParameters($Report['Report']['DataSet']['Query']['Where']);
        if(is_array($Report['Report']['Parameters']) || !is_null($Report['Report']['Parameters']))
        	$parameters = array_keys($Report['Report']['Parameters']);
        if ($parameters)
        {
            ?>
            <tr class=tablepath>
            <td colspan=4>
                &nbsp;<?php echo _a('Parameters'); ?>
            </td>
            </tr>
            <?php 
            foreach ($parameters as $parameter)
            {
                $value = $Report['Report']['Parameters'][$parameter]['value'];
                $parameter = "\$$parameter";
                ?>
                <tr class=line1> <td width=6%>  </td>
                                 <td width=10% align=center><img src='images/ico_param.png' border=0>
                                 </td>
                                 <td width=44%>
                                 <?php  echo $parameter; ?>
                                 </td>
                                 <td width=44% align=left>
                                 <input type=entry value='<?php  echo $value; ?>' name=Parameters[<?php  echo $parameter; ?>] maxwidth=100>
                                 </td>
                </td>
                </tr>
                <?php 
            }
            ?>
            </td>
            </tr>
        <?php 
        }
        ?>

    </form>
    </table>
    </td>
    <td bgcolor="#8280fe" valign=top width=32 >
        <img src='images/image.png'>
    </td>
    </tr>
    </table>
</td>
</tr>
</table>
