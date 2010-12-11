<?

  $fd = fopen ("gtkrc", "r");
  $fd2 = fopen ("gtkrc2", "w");

  while (!feof ($fd))
  {
    $buffer = fgets($fd, 500);
    $buffer = ereg_replace(chr(13), '', $buffer);
    fwrite($fd2,$buffer); 

  }

  fclose($fd);
  fclose($fd2);
?>
