<?
# Including the necessary classes and definitions.
include 'start.php';
?>
<link href="site.css" rel="stylesheet" type="text/css">
<form name=layout method=post action=generate.php>
<input type=hidden name=file value=<? echo $file; ?>>
<input type=hidden name=type value='report'>
<input type=hidden name=lang value=<? echo $lang; ?>>
<input type=hidden name=connection            value=<? echo $connection; ?>>
<input type=hidden name=Parameters            value='<? echo $Parameters; ?>'>
<input type=hidden name=SelectFields          value='<? echo $SelectFields; ?>'>
<input type=hidden name=constraint_fields     value='<? echo serialize($constraint_fields); ?>'>
<input type=hidden name=constraint_entries    value='<? echo serialize($constraint_entries); ?>'>
<input type=hidden name=constraint_operators  value='<? echo serialize($constraint_operators); ?>'>
<input type=hidden name=ordering              value='<? echo serialize($ordering); ?>'>
<?
$Layouts = getSimpleDirArray(AGATA_PATH . '/layout');
if ($Layouts)
{
    $Report = CoreReport::OpenReport($file);
    
	echo '<table width=100% border=1 cellspacing=0 cellpadding=0>';
	echo '<tr class=tablepath>';
	echo '<td colspan=4>';
	echo '&nbsp;'. _a('Choose the Layout') . ' : ';
	echo '</td>';
	echo '</tr>';
	$i = 0;
	echo '<tr class=line1> <td width=6%>  </td>';
	echo '<td width=10% align=center>';
	echo '<img src=\'imagens/ico_layout.png\' border=0></td>';
	echo '<td colspan=2 width=84%>' .  _a('Layout Name') . ' : ';
	echo '<select name="layout">';

	foreach ($Layouts as $layout)
	{
	    $layout = substr($layout, 0, -4);
        $selected = ($layout == trim($Report['Report']['Properties']['Layout'])) ? 'SELECTED' : '';
	    echo "<option $selected value=\"$layout\">$layout</option>";
	}
	echo '</select>';
	echo '</td>';
	echo '</tr>';
}
?>

<tr class=tablepath>
<td colspan=4>
&nbsp;<?echo _a('File') . ' : ' . $file; ?>
</td>
</tr>

<tr class=line1>
<td width=6%></td>
<td width=10% align=center>
<a href=javascript:back()><img src='imagens/ico_back.png' border=0></a>
</td>
<td colspan=2>
</td>
</tr>
<a name=radio> 
<?
$formats = array('txt'  => 'TXT',
                 'html' => 'HTML',
                 'pdf'  => 'PDF',
                 'xml'  => 'XML',
                 'csv'  => 'CSV',
                 'sxw'  => 'OpenOffice');
$i=0;
foreach ($formats as $key => $format)
{
    $checked = ($Report['Report']['Properties']['Format'] == $key) ? 'checked' : '';
    echo "<tr class=line1> <td width=4% align=right> <input type=radio name=mimetype value='$key' $checked> </td>\n";
    echo "         <td width=10% align=center> <a href=\"#radio\" onclick=\"javascript:document.layout.mimetype[$i].checked=true\">\n";
    echo "         <img src='imagens/ico_{$key}.png' border=0></a> </td>\n";
    echo "         <td colspan=2 width=86% align=left> <a href=\"#radio\" onclick=\"javascript:document.layout.mimetype[$i].checked=true\">$format</a></td>\n";
    echo "</tr>";
    $i ++;
}
echo "<tr class=tabletitle> <td colspan=3 width=4% align=left> Open Office Parser </td>\n";
echo "</tr>\n";
echo "<tr class=line1> <td width=4% align=right> <input type=radio name=mimetype value='oop'> </td>\n";
echo "         <td width=10% align=center> <a href=\"#radio\" onclick=\"javascript:document.layout.mimetype[$i].checked=true\">\n";
echo "         <img src='imagens/ico_sxw.png' border=0></a> </td>\n";
echo "         <td colspan=2 width=86% align=left> <a href=\"#radio\" onclick=\"javascript:document.layout.mimetype[$i].checked=true\">$format</a></td>\n";
echo "</tr>\n";
?>
</table>

<p align=right>
    <a class=link href="javascript:document.layout.submit()"><img src='imagens/download.png' border=0></a>
    &nbsp;&nbsp;<br>
    <a class=link href="javascript:document.layout.submit()"><? echo _a('Download'); ?></a>
</p>
</form>
