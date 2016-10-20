
<?php
    #echo "FETCH HELLO WORLD\n";
    $dbhost="10.10.88.185";
    $runid="";
    $timestamp="";

    foreach ($_GET as $key => $value) {
   		 #print("\n".$key." => ".$value);
   		 if(strcasecmp($key,"runid") == 0){
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$runid=(empty($runid)) ? "'".$val."'" : $runid.",".$val;
   			}
   		 }else if(strcasecmp($key,"timestamp") == 0){
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

	$statement = "SELECT b.runid,b.timestamp,b.os,b.platform,b.buildversion,b.hostname,b.messagesize,b.duration,b.numtopics,b.numpartitions,b.servercount,b.numdisks,b.nummfs,b.numsp,b.disktype,b.description, a.testid, a.testtype, a.replfactor, a.compression, a.numclients, a.throughput, a.initthroughput, a.ratedrop, a.avgtimetofinish, a.stddevduration, a.avglag, a.avgofminlag, a.avgofmaxlag, a.absminlag, a.absmaxlag FROM tblrubixrundata a, tblrubixruninfo b where a.runid=b.runid ";

	if (! empty($runid)) {
		$statement=$statement." AND b.runid in (".$runid.") ORDER BY runid,testid DESC";
	}
	else if (! empty($timestamp)) 
	{
		$statement=$statement." AND b.timestamp in (".$timestamp.") ORDER BY FIELD(timestamp,".$timestamp."),testid";
	}
	else {
		$statement=$statement." ORDER BY runid,testid DESC LIMIT 1";
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
			$data=$data."\"os\":\"".$row['os']."\",";
			$data=$data."\"platform\":\"".$row['platform']."\",";
			$data=$data."\"buildversion\":\"".$row['buildversion']."\",";
			$data=$data."\"hostname\":\"".$row['hostname']."\",";
			$data=$data."\"messagesize\":".$row['messagesize'].",";
			$data=$data."\"duration\":".$row['duration'].",";
			$data=$data."\"numtopics\":".$row['numtopics'].",";
			$data=$data."\"numpartitions\":".$row['numpartitions'].",";
			$data=$data."\"servercount\":".$row['servercount'].",";
			$data=$data."\"disktype\":\"".$row['disktype']."\",";
			$data=$data."\"numdisks\":".$row['numdisks'].",";
			$data=$data."\"nummfs\":".$row['nummfs'].",";
			$data=$data."\"numsp\":".$row['numsp'].",";
			$data=$data."\"disktype\":\"".$row['disktype']."\",";
			$data=$data."\"description\":\"".$row['description']."\",";

			$data=$data."\"data\": [{";
			$prevrunid=$currunid;
		}

		$data=$data."\"testid\":\"".$row['testid']."\",";
		$data=$data."\"testtype\":\"".$row['testtype']."\",";
		$data=$data."\"replfactor\":".$row['replfactor'].",";
		$data=$data."\"compression\":\"".$row['compression']."\",";
		$data=$data."\"numclients\":".$row['numclients'].",";
		$data=$data."\"throughput\":".$row['throughput'];
		$data=is_null($row['initthroughput'])?$data:$data.",\"initthroughput\":".$row['initthroughput'];
		$data=is_null($row['ratedrop'])?$data:$data.",\"ratedrop\":".$row['ratedrop'];
		$data=is_null($row['avgtimetofinish'])?$data:$data.",\"avgtimetofinish\":".$row['avgtimetofinish'];
		$data=is_null($row['stddevduration'])?$data:$data.",\"stddevduration\":".$row['stddevduration'];
		$data=is_null($row['avglag'])?$data:$data.",\"avglag\":".$row['avglag'];
		$data=is_null($row['avgofminlag'])?$data:$data.",\"avgofminlag\":".$row['avgofminlag'];
		$data=is_null($row['avgofmaxlag'])?$data:$data.",\"avgofmaxlag\":".$row['avgofmaxlag'];
		$data=is_null($row['absminlag'])?$data:$data.",\"absminlag\":".$row['absminlag'];
		$data=is_null($row['absmaxlag'])?$data:$data.",\"absmaxlag\":".$row['absmaxlag'];
		$data=$data."}";
	} 

	mysql_close($con); 

	if (! empty($data)){
       echo "[".$data."]}]";
	}
?>
