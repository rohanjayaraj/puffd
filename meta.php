
<?php
    #echo "FETCH HELLO WORLD\n";
    $runid="";
    $build="";
    $os="";
    $driver="";

    foreach ($_GET as $key => $value) {
   		 #print("\n".$key." => ".$value);
   		 if(strcasecmp($key,"runid") == 0){
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$runid=(empty($runid)) ? $val : $runid.",".$val;
   			}
   		 }else if (strcasecmp($key,"build") == 0) {
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$build=(empty($build)) ? "'".$val."'" : $build.",'".$val."'";
   		 	}
   		 }else if (strcasecmp($key,"os") == 0) {
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$os=(empty($os)) ? "'".$val."'" : $os.",'".$val."'";
   		 	}
   		 }else if (strcasecmp($key,"driver") == 0) {
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$driver=(empty($driver)) ? "'".$val."'" : $driver.",'".$val."'";
   		 	}
   		 }	 	
	}

	$con = mysql_connect("localhost","root",""); 
	if (!$con) 
	{ 
		die('Could not connect: ' . mysql_error()); 
	} 

	mysql_select_db("perfdb", $con); 

	$statement = "SELECT runid,timestamp,os,build,driver,totalcpus,totalmemory,totalspace,numclients,numnodes,numtables,numregions,datasize,rowsize,network,description FROM tblycsbrun";

	if (! empty($runid) || ! empty($build) || ! empty($os) || ! empty($driver) ) {
		$statement=$statement." WHERE ";
	}else {
		$statement=$statement."  order by runid desc LIMIT 10 ";
	}

	$appendAND=FALSE;
	if (! empty($runid) ){
		$statement=$statement." runid in (".$runid.") ";
		$appendAND=TRUE;
	}
	if (! empty($build) ){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." build in (".$build.") ";
		$appendAND=TRUE;
	}
	if (! empty($os)){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." os in (".$os.") ";
		$appendAND=TRUE;
	}
	if (! empty($driver) ){
		$statement=($appendAND)?$statement." AND ":$statement;
		$statement=$statement." driver in (".$driver.") ";
	}

	if($appendAND){
		$statement = $statement."  order runid by desc";
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
	  $meta=$meta."\"totalcpus\":".$row['totalcpus'].",";
	  $meta=$meta."\"totalmemory\":".$row['totalmemory'].",";
	  $meta=$meta."\"totalspace\":".$row['totalspace'].",";
	  $meta=$meta."\"numclients\":".$row['numclients'].",";
	  $meta=$meta."\"numnodes\":".$row['numnodes'].",";
	  $meta=$meta."\"numtables\":".$row['numtables'].",";
	  $meta=$meta."\"numregions\":".$row['numregions'].",";
	  $meta=$meta."\"datasize\":".$row['datasize'].",";
	  $meta=$meta."\"rowsize\":\"".$row['rowsize']."\",";
	  $meta=$meta."\"network\":\"".$row['network']."\",";
	  $meta=$meta."\"description\":\"".$row['description']."\"";
	  $meta=$meta."}";
	} 

	mysql_close($con); 

	if (! empty($meta)){
       echo "[".$meta."]";
	}
?>