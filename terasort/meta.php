
<?php
    #echo "FETCH HELLO WORLD\n";
    $dbhost="10.10.88.185";
    $runid="";
    $timestamp="";
    $build="";
    $os="";
    $driver="";
    $desc="";
    $rows="";

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
   		 }else if (strcasecmp($key,"driver") == 0) {
   		 	$driver=$value;
   		 }else if (strcasecmp($key,"description") == 0) {
   		 	$desc=$value;
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

	$statement = "SELECT runid,timestamp,os,build,driver,description,disktype,hadoopversion,mfsinstances,avgmap,avgreduce,avgmerge,avgshuffle,teststatus,runtime,secure,encryption FROM tblterasort";

	if (! empty($runid) || ! empty($build) || ! empty($os) || ! empty($driver) || ! empty($runid) || ! empty($timestamp) || ! empty($desc)) {
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
	if (! empty($driver) ){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." driver like '%".$driver."%' ";
		$appendAND=TRUE;
	}

	if (! empty($desc) ){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." description like '%".$desc."%' ";
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
	  $meta=$meta."\"driver\":\"".$row['driver']."\",";
	  $meta=$meta."\"description\":\"".$row['description']."\",";
	  $meta=$meta."\"disktype\":\"".$row['disktype']."\",";
	  $meta=$meta."\"hadoopversion\":\"".$row['hadoopversion']."\",";

	  $meta=$meta."\"mfsinstances\":".$row['mfsinstances'].",";
	  $meta=$meta."\"avgmap\":".$row['avgmap'].",";
	  $meta=$meta."\"avgshuffle\":".$row['avgshuffle'].",";
	  $meta=$meta."\"avgmerge\":".$row['avgmerge'].",";
	  $meta=$meta."\"avgreduce\":".$row['avgreduce'].",";
	  $meta=$meta."\"runtime\":".$row['runtime'].",";
	  
	  $meta=$meta."\"secure\":\"".$row['secure']."\",";
	  $meta=$meta."\"encryption\":\"".$row['encryption']."\",";
	  $meta=$meta."\"teststatus\":\"".$row['teststatus']."\"";
	  
	  $meta=$meta."}";
	} 

	mysql_close($con); 

	if (! empty($meta)){
       echo "[".$meta."]";
	}
?>
