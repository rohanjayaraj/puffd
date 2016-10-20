<?php
#echo "FETCH HELLO WORLD\n";
$dbhost="10.10.88.185";
$runid="";
$timestamp="";
$os="";
$build="";
$mfsinstances="";
$numsp="";
$nodes="";
$desc="";
$status="";
$disktype="";
$driver="";
$secure="";
$networkencryption="";
$hadoopversion="";
$repl1localread="";
$repl1localwrite="";
$repl3localread="";
$repl3localwrite="";
$repl1remoteread="";
$repl1remotewrite="";
$repl3remoteread="";
$repl3remotewrite="";

foreach ($_GET as $key => $value) {
  #print("\n".$key." => ".$value);
  if(strcasecmp($key,"runid") == 0){
    $runarr = explode(',', $value);
    foreach ($runarr as $val){
      $runid=(empty($runid)) ? $val : $runid.",".$val;
    }
  }else if (strcasecmp($key,"timestamp") == 0) {
    $runarr = explode(',', $value);
    foreach ($runarr as $val){
      $timestamp=(empty($timestamp)) ? "'".$val."'" : $timestamp.",'".$val."'";
    }
  }else if (strcasecmp($key,"build") == 0) {
    $build=$value;
  }else if (strcasecmp($key,"os") == 0) {
    $os=$value;
  }else if (strcasecmp($key,"mfsinstances") == 0) {
    $mfsinstances=$value;
  }else if (strcasecmp($key,"description") == 0) {
    $desc=$value;
  }else if (strcasecmp($key,"driver") == 0) {
    $driver=$value;
  }else if (strcasecmp($key,"rows") == 0) {
    $rows=$value;
  }	 	
}

$con = mysql_connect($dbhost,"root","mapr"); 
if (!$con) 
{ 
  die('Could not connect: ' . mysql_error()); 
} 

mysql_select_db("perfdb", $con); 

$statement = "SELECT runid,timestamp,os,build,mfsinstances,numsp,nodes,description,status,disktype,driver,secure,networkencryption,hadoopversion,repl1localread,repl1localwrite,repl1remoteread,repl1remotewrite,repl3localread,repl3localwrite,repl3remoteread,repl3remotewrite FROM tblrwspeed";

if (! empty($runid) || ! empty($build) || ! empty($os) || ! empty($mfsinstances) || ! empty($runid) || ! empty($timestamp) || ! empty($desc) || ! empty($driver)) {
  $statement=$statement." WHERE ";
}

$appendAND=FALSE;
if (! empty($runid) ){
  $statement=$statement." runid in (".$runid.") ";
  $appendAND=TRUE;
}
if (! empty($timestamp) ){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." timestamp in (".$timestamp.") ";
  $appendAND=TRUE;
}
if (! empty($build) ){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." build like '%".$build."%' ";
  $appendAND=TRUE;
}
if (! empty($os)){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." os like '%".$os."%' ";
  $appendAND=TRUE;
}
if (! empty($mfsinstances) ){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." mfsinstances in (".$mfsinstances.") ";
  $appendAND=TRUE;
}

if (! empty($desc) ){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." description like '%".$desc."%' ";
  $appendAND=TRUE;
}

if (! empty($driver) ){
  $statement=($appendAND)?$statement." AND ":$statement;
  $statement=$statement." driver like '%".$driver."%' ";
  $appendAND=TRUE;
}

$statement = $statement."  order by runid desc";
if (! empty($rows) ){
  if(is_numeric($rows)){
    $statement = $statement."  LIMIT ".$rows;
  }
}else if(! $appendAND){
  $statement=$statement."  LIMIT 1";
}

#print("Statement : ".$statement);
$result = mysql_query($statement);
$meta="";

while($row = mysql_fetch_array($result)) 
{ 
  #var_dump($row);
  if (! empty($meta)){
    $meta=$meta.",{";
  }else {
    $meta="{";
  }
  $meta=$meta."\"runid\":".$row["runid"].",";
  $meta=$meta."\"timestamp\":".$row['timestamp'].",";
  $meta=$meta."\"os\":\"".$row['os']."\",";
  $meta=$meta."\"build\":\"".$row['build']."\",";
  $meta=$meta."\"mfsinstances\":".$row['mfsinstances'].",";
  $meta=$meta."\"numsp\":".$row['numsp'].",";

  $meta=$meta."\"nodes\":".$row['nodes'].",";

  $meta=$meta."\"description\":\"".$row['description']."\",";
  $meta=$meta."\"status\":\"".$row['status']."\",";
  $meta=$meta."\"disktype\":\"".$row['disktype']."\",";

  $meta=$meta."\"driver\":\"".$row['driver']."\",";
  $meta=$meta."\"secure\":\"".$row['secure']."\",";
  $meta=$meta."\"networkencryption\":\"".$row['networkencryption']."\",";
  $meta=$meta."\"hadoopversion\":\"".$row['hadoopversion']."\",";

  $meta=$meta."\"repl1localread\":".$row['repl1localread'].",";
  $meta=$meta."\"repl1localwrite\":".$row['repl1localwrite'].",";
  $meta=$meta."\"repl1remoteread\":".$row['repl1remoteread'].",";
  $meta=$meta."\"repl1remotewrite\":".$row['repl1remotewrite'].",";
  $meta=$meta."\"repl3localread\":".$row['repl3localread'].",";
  $meta=$meta."\"repl3localwrite\":".$row['repl3localwrite'].",";
  $meta=$meta."\"repl3remoteread\":".$row['repl3remoteread'].",";
  $meta=$meta."\"repl3remotewrite\":".$row['repl3remotewrite'];

  $meta=$meta."}";
} 

mysql_close($con); 

if (! empty($meta)){
  echo "[".$meta."]";
}
?>
