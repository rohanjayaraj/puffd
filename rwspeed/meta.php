
<?php
    //echo "FETCH HELLO WORLD\n";
    $runid="";
    $timestamp="";
    $os="";
    $build="";
    $desc="";
    $rows="";

    foreach ($_GET as $key => $value) {
   		 //print("\n".$key." => ".$value);
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
   		 }else if (strcasecmp($key,"description") == 0) {
   		 	$desc=$value;
   		 }else if (strcasecmp($key,"rows") == 0) {
   		 	$rows=$value;
   		 }	 	
	}
	
	$con = mysql_connect("10.10.88.136","root",""); 
	if (!$con) 
	{ 
		die('Could not connect: ' . mysql_error()); 
	} 

	mysql_select_db("perfdb", $con); 

	$statement = "SELECT runid,timestamp,os,build,mfsinstances,disktype,description,repl1localread,repl1localwrite,repl1remoteread,repl1remotewrite,repl3localread,repl3localwrite,repl3remoteread,repl3remotewrite FROM tblrwspeed";

	if (! empty($runid) || ! empty($build) || ! empty($os) || ! empty($desc)) {
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
	  $meta=$meta."\"build\":\"".$row['build']."\",";
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
