<?
if(isset($_GET) && is_array($_GET))
{
    foreach ($_GET as $key=>$val)
    {
        ${$key}=$val;
    }
}
if(isset($_POST) && is_array($_POST))
{
    foreach ($_POST as $key=>$val)
    {
        ${$key}=$val;
    }
}
switch ($type)
{
    case ('txt'):
        $content_type = 'text/plain';
        break;
    case ('csv'):
        $content_type = 'text/plain';
        break;
    case 'xml':
        $content_type = 'text/xml';
        break;
    case 'pdf':
        $content_type = 'application/pdf';
        break;
    case 'ps':
        $content_type = 'application/postscript';
        break;
    case 'sxw':
        $content_type = 'application/sxw';
        break;
    case 'dia':
        $content_type = 'application/dia';
        break;
}
if ($type != 'html')
{
    header("Content-type: $content_type");
    header("Content-Disposition: attachment;");
    header("Content-Disposition: filename=\"$download\"");
}
readfile($file);
//echo 'df';
?>
