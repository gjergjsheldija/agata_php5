<html>
<?
    # Including the necessary classes and definitions.
    include 'start.php';
    Trans::SetLanguage($lang);
?>

<head>
<script language='javascript'>
    function js(page)
    {
        div = document.getElementById("tab-page-1");
        div.style.display = "none";
        div = document.getElementById("tab-page-2");
        div.style.display = "none";
        div = document.getElementById("tab-page-3");
        div.style.display = "none";
        div = document.getElementById("tab-page-4");
        div.style.display = "none";
        
        div = document.getElementById(page);
        div.style.display = "block";
    }
</script>

<link href="tab2_files/tab.css" rel="StyleSheet" type="text/css">
</head>

<div id="tab-pane-1" class="dynamic-tab-pane-control tab-pane">

    <div id="tab-page-1" class="tab-page" style="display: block;">
        <? include_once 'sheet1.php'; ?>

    </div>

    <div id="tab-page-2" class="tab-page" style="display: none;">
        <? include_once 'sheet2.php'; ?>

    </div>

    <div id="tab-page-3" class="tab-page" style="display: none;">
        <? include_once 'sheet3.php'; ?>

    </div>

    <div id="tab-page-4" class="tab-page" style="display: none;">
        <? include_once 'sheet4.php'; ?>

    </div>
</div>

</html>