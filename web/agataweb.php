<html>
<?php
    # Including the necessary classes and definitions.
    include 'start.php';
    Trans::SetLanguage($lang);
?>

<head>
	<link href="site.css" rel="stylesheet" type="text/css">
</head>
<body>
<form>
<h1>Agata CoreReport :: Query Explorer</h1>
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

</form>
</body>
</html>