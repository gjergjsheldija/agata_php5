<?php
// Including the necessary classes and definitions.
include 'start.php';
Trans::SetLanguage($lang);
    
$Report = CoreReport::OpenReport($file);
$Breaks = CoreReport::ExtractBreaks($Report);
$Elements  = MyExplode(trim($Report['Report']['DataSet']['Query']['Select']), _a('Column'), true);

   ?>
   <link href="site.css" rel="stylesheet" type="text/css">
<form>

<h1>Agata CoreReport :: <?php echo _a('Report Generation'); ?></h1>
<ul id="nav">
	<li>
		<a href="sheet1.php?file=<?php echo $_REQUEST['file'];?>">Report</a>
	</li>
	<li class="active">
		<a href="sheet2.php?file=<?php echo $_REQUEST['file'];?>">Grouping</a>
	</li>
	<li>
		<a href="sheet3.php?file=<?php echo $_REQUEST['file'];?>">Graphing</a>
	</li>
	<li>
		<a href="sheet4.php?file=<?php echo $_REQUEST['file'];?>">Merging</a>
	</li>
	<li style="float:right">
		<a href="browse.php?goal=1">Reports</a>
	</li>	
</ul>

<h2><?php  echo _a('Report Levels'); ?></h2>
<fieldset class="settings">   
    <?php 

        if ($Breaks) {
            foreach ($Breaks as $break=>$formula) {
    
                if ($break == 0) {
                    echo "<label>" . _a('Level')  . " $break : " . _a('Grand Total') . '</label>';
                } else {
                    echo "<label>" . $Elements[$break]  . '</label>';
                }

                $Formulas = CoreReport::TranslateFormulas($Report['Report']['DataSet']['Query']['Select'], $formula);
                foreach ($Formulas as $Formula) {
                    echo "<label>$Formula</label>";
                }
            }
        } else {
			echo _('This report has no groups');        	
        }
?>
</fieldset>