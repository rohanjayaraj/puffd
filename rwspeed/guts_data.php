<?php
$f1="";
$f2="";
$c1="";
$c2="";
$m1="";
$m2="";
$trace="";

foreach ($_GET as $key => $value) {
  if(strcasecmp($key,"f1") == 0){
    $f1=$value;
  }
  else if(strcasecmp($key,"f2") == 0){
    $f2=$value;
  }
  else if(strcasecmp($key,"c1") == 0){
    $c1=$value;
  }
  else if(strcasecmp($key,"c2") == 0){
    $c2=$value;
  }
  else if(strcasecmp($key,"m1") == 0){
    $m1=$value;
  }
  else if(strcasecmp($key,"m2") == 0){
    $m2=$value;
  }
  else if(strcasecmp($key,"trace") == 0){
    $trace=$value;
  }
  else {
    echo "{\"error\":\"$key\"}";
    exit;
  }
}


$jstable=exec("python /var/www/html/puffd/rwspeed/assets/python/parse_guts.py $f1 $f2 $c1 $c2 $m1 $m2 $trace");
echo $jstable;
#$t="{\"aa\": \"aa\"}";
#echo $t
?>

