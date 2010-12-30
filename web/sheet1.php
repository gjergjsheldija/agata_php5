<?php
    # Including the necessary classes and definitions.
    include 'start.php';
    Trans::SetLanguage($lang);
    
    $Report = CoreReport::OpenReport($file);
    $Blocks = CoreReport::ExtractBlock($Report['Report']['DataSet']);
    $datasource = $Report['Report']['DataSet']['DataSource']['Name'];

   ?>
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
    function MySubmit(form)  {
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
<form name=sheet1 method=post action=restrictions.php onsubmit="return MySubmit(this);">

<h1>Agata CoreReport :: <?php echo _a('Report Generation'); ?></h1>
<ul id="nav">
	<li>
		<a href="sheet1.php?file=<?php echo $_REQUEST['file'];?>">Report</a>
	</li>
	<li>
		<a href="sheet2.php?file=<?php echo $_REQUEST['file'];?>">Grouping</a>
	</li>
	<li>
		<a href="sheet3.php?file=<?php echo $_REQUEST['file'];?>">Graphing</a>
	</li>
	<li>
		<a href="sheet4.php?file=<?php echo $_REQUEST['file'];?>">Merging</a>
	</li>	
</ul>

<h2><?php echo _a('Project Name'); ?></h2>
<input type="hidden" name="connection" value="<?php echo $datasource; ?>" />

<fieldset class="settings">
    <?php
    $images['From']     = 'images/ico_table.png';
    $images['Group by'] = 'images/ico_group.png';
    $images['Order by'] = 'images/ico_sort.png';
    
    $ClauseLabel['From']      = _a('Tables');
    $ClauseLabel['Group by']  = _a('Groups');
    $ClauseLabel['Order by']  = _a('Ordering');
    
    foreach ($Blocks as $Clause => $Content) {
        if ($Clause == 'Select') {
            $select = MyExplode(trim($Content[1]));
            $i = 0;
            $group_table=-1;
            foreach ($select as $piece) {
                if (!$Report['Report']['DataSet']['Query']['AgataWeb']['Select']) {
                    $checked = 'checked';
                } else {
                    $checked = (strpos($Report['Report']['DataSet']['Query']['AgataWeb']['Select'], $piece) !== false) ? 'checked' : '';
                }
                
                $pieces = explode('.', $piece);
                $table = $pieces[0];
                if (($table != $group_table) and (count($pieces) == 2)) {
                    echo '<legend>' . _a('Columns') .  ' : ' ._a('Table') . ' ' . $table  . '</legend>';
                }
                $group_table = $table;
                
                $label = $piece;
                if (eregi(' as ', $piece)) {
                    $pieces = preg_split('/ as /i', $piece);
                    $label = str_replace("\"", '', $pieces[1]);
                }

                $piece = ereg_replace("'", "`", $piece);
                echo "<input type='checkbox' $checked name='SelectFields[$i]' value='$piece'><img src='images/ico_field.png'> $label <br>";
                $i ++;
            }
        } 
    }
?>
</fieldset>
<?php 
    if ($Report['Report']['Parameters']) {

        $parameters = $Report['Report']['Parameters'];
    }
    if ($parameters) {
        ?>
<fieldset class="settings">
        <legend><?php echo _a('Parameters'); ?></legend>

        <?php
        foreach ($parameters as $parameter => $properties) {
            //$value = $Report['Report']['Parameters'][$parameter]['value'];
            $value = $properties['value'];
            $mask  = $properties['mask'];
            $newmask = $mask;
            $newmask = str_replace('dd', '%d',   $newmask);
            $newmask = str_replace('mm', '%m',   $newmask);
            $newmask = str_replace('yyyy', '%Y', $newmask);
            $parameter = "\$$parameter";
            
            ?>
            <img src='images/ico_param.png' border=0>
                             <?php echo $parameter; ?>
                             <?php 
                             if (strstr($mask, 'dd') and strstr($mask, 'mm') and strstr($mask, 'yyyy')) {
                                 ?>
                                <input type="text" value='<?php echo $value; ?>' name=Parameters[<?php echo $parameter; ?>] id="f_date_c" readonly="1"/>
                                <img src="images/popdate.png" id="f_trigger_c" style="cursor: pointer; border: 1px solid red;" title="Date selector" onmouseover="this.style.background='red';" onmouseout="this.style.background=''" />
                                        
                                <script type="text/javascript">
                                    Calendar.setup({
                                        inputField     :    "f_date_c",     // id of the input field
                                        ifFormat       :    "<?php echo $newmask;//"%Y-%m-%d" ?>",      // format of the input field
                                        button         :    "f_trigger_c",  // trigger for the calendar (button ID)
                                        align          :    "Tl",           // alignment (defaults to "Bl")
                                        firstDay       :     0,
                                        singleClick    :    true
                                    });
                                </script>
                                 <?php
                             } else {
                                 ?>
                                 <input type=entry value='<?php echo $value; ?>' name=Parameters[<?php echo $parameter; ?>] maxwidth=100>
                                 <?php
                             }
                             ?>
            <?php
        }
        ?>
        <?php
    }

    ?>
</fieldset>
    <input type=hidden name=file value=<?php echo $file; ?>>
    <input type=hidden name=type value='report'>
    <input type=hidden name=lang value=<?php echo $lang; ?>>
    

    <a class=link href="javascript:MySubmit(document.sheet1)"><img src='images/proceed.png' border=0></a><br>
    <a class=link href="javascript:MySubmit(document.sheet1)"><?php echo _a('Proceed'); ?></a><br>

    </form>
