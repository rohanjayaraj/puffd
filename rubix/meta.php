
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
   		 }else if (strcasecmp($key,"hostname") == 0) {
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

	$statement = "SELECT runid,timestamp,os,platform,buildversion,hostname,messagesize,duration,numtopics,numpartitions,servercount,numdisks,nummfs,numsp,disktype,description FROM tblrubixruninfo";

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
		$statement=$statement." buildversion like '%".$build."%' ";
		$appendAND=TRUE;
	}
	if (! empty($os)){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." os like '%".$os."%' ";
		$appendAND=TRUE;
	}
	if (! empty($driver) ){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." hostname like '%".$driver."%' ";
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
	  $meta=$meta."\"platform\":\"".$row['platform']."\",";
	  $meta=$meta."\"buildversion\":\"".$row['buildversion']."\",";
	  $meta=$meta."\"hostname\":\"".$row['hostname']."\",";
	  $meta=$meta."\"messagesize\":".$row['messagesize'].",";
	  $meta=$meta."\"duration\":".$row['duration'].",";
	  $meta=$meta."\"numtopics\":".$row['numtopics'].",";
	  $meta=$meta."\"numpartitions\":".$row['numpartitions'].",";
	  $meta=$meta."\"servercount\":".$row['servercount'].",";
	  $meta=$meta."\"numdisks\":".$row['numdisks'].",";
	  $meta=$meta."\"nummfs\":".$row['nummfs'].",";
	  $meta=$meta."\"numsp\":".$row['numsp'].",";
	  $meta=$meta."\"disktype\":\"".$row['disktype']."\",";
	  $meta=$meta."\"description\":\"".$row['description']."\"";
	  $meta=$meta."}";
	} 

	mysql_close($con); 

	if (! empty($meta)){
       echo "[".$meta."]";
	}
?>
