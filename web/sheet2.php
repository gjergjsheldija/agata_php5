<table width=800 cellspacing=0 cellpadding=0>
<tr>
<td width=84 valign=top>
    <map name="menu">
    <area shape="rect" coords="01,01,80,58"    HREF="<?echo $url1;?>">
    <area shape="rect" coords="01,64,80,124"   HREF="<?echo $url2;?>">
    <area shape="rect" coords="01,126,80,186"  HREF="<?echo $url3;?>">
    <area shape="rect" coords="01,188,80,248"  HREF="<?echo $url4;?>">
    
    <area SHAPE="DEFAULT" NOHREF></map>
    <img src='imagens/bar2.png' usemap="#menu" ismap border=0><br><br>
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
    
    <tr class=tablepath>
    <td colspan=4>
        &nbsp;
    <? echo _a('Report Levels'); ?>
    
    </td>
    </tr>
    <?
        $Report = CoreReport::OpenReport($file);
        $Breaks = CoreReport::ExtractBreaks($Report);
        $Elements  = MyExplode(trim($Report['Report']['DataSet']['Query']['Select']), _a('Column'), true);
        if ($Breaks)
        {
            $i = 1;
            $line = 1;
            foreach ($Breaks as $break=>$formula)
            {
                $line = $line ==1 ? 2: 1;
                $i ++;
    
                echo "<tr class=line$line>";
                echo '<td colspan=4>';
                echo str_repeat('<blockquote>', $i);
                echo '<img src=imagens/ico_level.png>&nbsp;&nbsp;';
                if ($break == 0)
                {
                    echo _a('Level')  . " $break : " . _a('Grand Total') . '<br>';
                }
                else
                {
                    echo $Elements[$break] . '<br>';
                }

                $Formulas = CoreReport::TranslateFormulas($Report['Report']['DataSet']['Query']['Select'], $formula);
                foreach ($Formulas as $Formula)
                {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  $Formula<br>";
                }
                echo str_repeat('</blockquote>', $i);
                echo '</td>';
                echo '</tr>';
            }
        }
    ?>
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
