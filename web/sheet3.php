<?php 
// Including the necessary classes and definitions.
include 'start.php';
Trans::SetLanguage($lang);

$action1 = "generate.php?file=$file&lang=$lang&type=lines";
$action2 = "generate.php?file=$file&lang=$lang&type=bars";

$Report = CoreReport::OpenReport($file);
$datasource = $Report['Report']['DataSet']['DataSource']['Name'];
$projects = array_keys(Project::ReadProjects());
?>

<script language="javascript">
    function submit(value) {
        if (document.sheet3.columns.selectedIndex > -1) {
            if (document.sheet3.legend.selectedIndex > -1) {
                document.sheet3.type.value=value;
                document.sheet3.submit();
            } else {
                if (document.sheet3.orientation2.checked == true) {
                    alert('<?php  echo _a('Legend'); ?>');
                } else {
                    document.sheet3.type.value=value;
                    document.sheet3.submit();
                }
            }
        } else {
            alert('<?php  echo _a('Select columns to plot'); ?>');
        }
    }
</script>
<link href="site.css" rel="stylesheet" type="text/css">

<form name="sheet3" method="post" action="generate.php">

<h1>Agata CoreReport :: <?php echo _a('Report Generation'); ?></h1>
<ul id="nav">
	<li>
		<a href="sheet1.php?file=<?php echo $_REQUEST['file'];?>">Report</a>
	</li>
	<li>
		<a href="sheet2.php?file=<?php echo $_REQUEST['file'];?>">Grouping</a>
	</li>
	<li class="active">
		<a href="sheet3.php?file=<?php echo $_REQUEST['file'];?>">Graphing</a>
	</li>
	<li>
		<a href="sheet4.php?file=<?php echo $_REQUEST['file'];?>">Merging</a>
	</li>
	<li style="float:right">
		<a href="browse.php?goal=1">Reports</a>
	</li>	
</ul>
<input type="hidden" name="connection" value="<?php echo $datasource; ?>" />
<input type="hidden" name="file" value="<?php  echo $file; ?>" />
<input type="hidden" name="type" value="xxx" />

<table width=800 cellspacing=0 cellpadding=0>



<tr>
<td width=716 align=left valign=top>

    <table width=100% cellspacing=0 border=0>


    <tr align=left>
    <td colspan=1 width=94 valign=top class=line1>
        <?php 
          echo "<a href=\"javascript:submit('lines')\"> <img src='images/lines.png' border=0></a><br>";
          echo "<a href=\"javascript:submit('bars')\"> <img src='images/bars.png' border=0></a><br>";
        ?>
    </td>
    <td colspan=3 valign=top>
        <table width=100% class=line1 cellspacing=0>
        <tr> <td>   <?php  echo (_a('Title')); ?>           </td><td> <input name=title  type=text value="<?php  echo $Report['Report']['Graph']['Title'];?>"> </td></tr>
        <tr> <td>   <?php  echo ('X ' . _a('Title')); ?>    </td><td> <input name=titlex type=text value="<?php  echo $Report['Report']['Graph']['TitleX'];?>"> </td></tr>
        <tr> <td>   <?php  echo ('Y ' . _a('Title')); ?>    </td><td> <input name=titley type=text value="<?php  echo $Report['Report']['Graph']['TitleY'];?>"> </td></tr>
        <tr> <td>   <?php  echo (_a('Introduction')); ?>    </td><td> <textarea name=description cols=40 rows=6><?php  echo $Report['Report']['Graph']['Description'];?></textarea> </td></tr>
        <tr> <td>   <?php  echo (_a('Width')); ?>           </td><td> <input name=width  type=text value="<?php  echo $Report['Report']['Graph']['Width'];?>"> </td></tr>
        <tr> <td>   <?php  echo (_a('Height')); ?>          </td><td> <input name=height type=text value="<?php  echo $Report['Report']['Graph']['Height'];?>"> </td></tr>
        <tr> <td>   <?php  echo (_a('Plotted Columns')); ?> </td>
             <td>   <select multiple="multiple" size="10" name="columns[]" id="columns">

                    <?php 
                        $Elements  = MyExplode(trim($Report['Report']['DataSet']['Query']['Select']), _a('Column'), true);
                        foreach ($Elements as $element) {
                              echo "<option value=\"" . urlencode($element) . "\">$element</option>";
                        }
                    ?>
                    </select>
             </td>
        </tr>
        <tr>
            <td>
                <?php  echo _a('Result'); ?>
            </td>
            <td>
                <INPUT TYPE=RADIO NAME="saida" VALUE="sxw">OpenOffice<BR>
                <INPUT TYPE=RADIO NAME="saida" checked VALUE="html">HTML<BR>
            </td>
        </tr>
        <tr>
            <td>
                <br>
                <?php  echo _a('Orientation'); ?>
            </td>
            <td>
                <br>
                <INPUT TYPE=RADIO id="orientation1" NAME="orientation" checked VALUE="columns" onClick="javascript:document.sheet3.legend.disabled=true">Columns<BR>
                <INPUT TYPE=RADIO id="orientation2" NAME="orientation" VALUE="lines" onClick="javascript:document.sheet3.legend.disabled=false">Lines<BR>
            </td>
        </tr>

        <tr><td> <?php  echo (_a('Legend')); ?> </td>
            <td> <select size="10" name="legend[]" id="legend" disabled>
                <?php 
                    $Elements  = MyExplode(trim($Report['Report']['DataSet']['Query']['Select']), _a('Column'), true);
                    foreach ($Elements as $element)
                    {
                          echo "<option value=\"" . urlencode($element) . "\">$element</option>";
                    }
                ?>
                </select>
                <br>
            </td>
        </tr>

    </table>
    </td>
    </tr>
    <?php 
        if(is_array($Report['Report']['Parameters']) || !is_null($Report['Report']['Parameters']))
        	$parameters = array_keys($Report['Report']['Parameters']);
        
        	if ($parameters) {
            ?>
            <tr class=tablepath>
            <td colspan=4>
                &nbsp;<?php echo _a('Parameters'); ?>
            </td>
            </tr>
            <?php 
            foreach ($parameters as $parameter) {
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
    </table>
    
    </td>
</tr>
</table>
</form>