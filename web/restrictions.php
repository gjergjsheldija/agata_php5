<?php
# Including the necessary classes and definitions.
include 'start.php';
print_r($_REQUEST);
?>

<link href="site.css" rel="stylesheet" type="text/css">

<form name=restrictions method=post action=layout.php>
<input type=hidden name=file value=<?php echo $file; ?>>
<input type=hidden name=type value='report'>
<input type=hidden name=lang value=<?php echo $lang; ?>>
<input type=hidden name=connection value=<?php echo $connection; ?>>
<?php
if ($Parameters) {
    foreach ($Parameters as $key =>$content) {
        $newParam[$key] = ereg_replace("'", "`", $content);
    }
}
?>
<input type=hidden name=Parameters   value='<?php echo serialize($newParam); ?>'>
<input type=hidden name=SelectFields value='<?php echo serialize($SelectFields); ?>'>

<?php
$Report       = CoreReport::OpenReport($file);
$operators    = array('=', '>', '<', '<>', '>=', '<=', 'like', 'not like');
//$constraints  = explode(' and ', $Report['Report']['DataSet']['Query']['AgataWeb']['Where']);
$constraints  = preg_split('/ and /i', $Report['Report']['DataSet']['Query']['AgataWeb']['Where']);
$ordering     = MyExplode($Report['Report']['DataSet']['Query']['AgataWeb']['OrderBy']);

echo '<table width=100% border=1 cellspacing=0 cellpadding=0>';
echo '<tr><td colspan=3 class=tablepath>';
echo 'Additional restrictions:';
echo '</td></tr>';
echo "\n";
$i = 0;
if ($SelectFields)
{
    for ($n=1; $n<=5; $n ++)
    {
        $split = preg_split('(( = )|( > )|( < )|( <> )|( >= )|( <= )|( like )|( not like ))',$constraints[$n-1], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        echo '</pre>';
        echo '<tr class=line1><td width=60%>';
        echo "<img src='images/ico_constraint.png'>\n";
        echo "<select NAME='constraint_fields[$i]'>\n";
        
        foreach ($SelectFields as $piece)
        {
            $clean_caption = $piece;
            $clean_piece   = $piece;
            if (eregi(" as ", $piece))
            {
                $tmp = preg_split('/ as /i', $piece, 2);
                $clean_piece   = $tmp[0];
                $clean_caption = $tmp[1];
            }
            $selected = ($clean_piece == $split[0]) ? 'SELECTED' : '';
            echo "<option $selected VALUE='$clean_piece'>$clean_caption</option>\n";
        }
        echo "</select>\n";
        echo '</td><td width=10%>';
        
        echo "<select NAME='constraint_operators[$i]'>\n";
        foreach ($operators as $operator)
        {
            $selected = ($operator == trim($split[1])) ? 'SELECTED' : '';
            echo "<option $selected VALUE='$operator'>$operator</option>\n";
        }
        echo "</select>\n";
        echo "</td><td width=30% align=right>\n";
        $split[2] = str_replace("'", "", $split[2]);
        echo " <input type=text value='{$split[2]}' name='constraint_entries[$i]'>\n";
        echo '</td></tr>';
        echo "\n";
        $i ++;
    }
}
echo '</table>';
echo '<br>';
echo '<table width=100% border=1 cellpadding=0 cellspacing=0>';
echo '<tr><td colspan=2 class=tablepath>' . _a('Ordering') . '</td></tr>';
for ($n=1; $n<=4; $n++)
{
    echo "<tr class=line1><td><img src='images/ico_field.png'> Ordena��o $n :</td>";
	echo "<td align=center><select name=ordering[$n]>";
	$i = 0;
	echo "<option value=''></option>";
	foreach ($SelectFields as $piece)
	{
	    $field = $piece;
        $clean_caption = $field;
	    if (eregi(" as ", $piece))
	    {
            $tmp = preg_split('/ as /i', $piece, 2);
            $field = $tmp[0];
            $clean_caption = $tmp[1];
	    }
        $selected = ($field == trim($ordering[$n])) ? 'SELECTED' : '';
	    echo "<option $selected value='$field'>$clean_caption</option>";
	    echo "\n";
	    $i ++;
	}
	echo "</select>\n";
	echo '</td></tr>';
}
echo "</table>\n";
?>
<p align=right>
    <a class=link href="javascript:document.restrictions.submit()"><img src='images/proceed.png' border=0></a>
    <a class=link href="javascript:document.restrictions.submit()"><?php echo _a('Proceed'); ?></a>
</p>
</form>
