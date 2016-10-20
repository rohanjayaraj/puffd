
<?php
    #echo "FETCH HELLO WORLD\n";
    $timestamp="";
    $dbhost="10.10.88.185";
    
    foreach ($_GET as $key => $value) {
   		 #print("\n".$key." => ".$value);
   		 if(strcasecmp($key,"timestamp") == 0){
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$timestamp=(empty($timestamp)) ? "'".$val."'" : $timestamp.",".$val;
   			}
   		 } 	
	}

	$con = mysql_connect($dbhost,"root","mapr"); 
	if (!$con) 
	{ 
		die('Could not connect: ' . mysql_error()); 
	} 

	mysql_select_db("perfdb", $con); 

	$statement = "SELECT a.runid, a.build, a.description, a.disktype, a.timestamp, b.wrkldid, b.wrkldtype, b.log FROM tblycsbrun a, tblycsbrunlog b where a.runid=b.runid ";

	if (! empty($timestamp)) 
	{
		$statement=$statement." AND a.timestamp in (".$timestamp.") ORDER BY FIELD(timestamp,".$timestamp."),id";
	}
	else {
		$statement=$statement." ORDER BY runid,id DESC LIMIT 1";
	}

	#print("Statement : ".$statement);
	$result = mysql_query($statement);
	$data="";
	$prevrunid=NULL;

	while($row = mysql_fetch_array($result)) 
	{ 
	 	#var_dump($row);
	  
	  	$currunid=$row['runid'];
	  
	  	if ($prevrunid == $currunid){
		  	$data=$data.",{";
		}else {
			if (empty($data)){
				$data="{";
			}else {
				$data=$data."]},{";
			}
			$data=$data."\"runid\":".$row['runid'].",";
			$data=$data."\"timestamp\":".$row['timestamp'].",";
			$data=$data."\"description\":\"".$row['description']."\",";
			$data=$data."\"disktype\":\"".$row['disktype']."\",";
			$data=$data."\"build\":\"".$row['build']."\",";

			$data=$data."\"data\": [{";
			$prevrunid=$currunid;
		}

		$data=$data."\"wrkldid\":\"".$row['wrkldid']."\",";
		$data=$data."\"wrkldtype\":\"".$row['wrkldtype']."\",";
		$data=$data."\"log\":".$row['log']."";
		$data=$data."}";
	} 

	mysql_close($con); 

	if (! empty($data)){
       echo "[".$data."]}]";
	}
?>
