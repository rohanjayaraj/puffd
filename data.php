
<?php
    #echo "FETCH HELLO WORLD\n";
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

	$con = mysql_connect("localhost","root",""); 
	if (!$con) 
	{ 
		die('Could not connect: ' . mysql_error()); 
	} 

	mysql_select_db("perfdb", $con); 

	$statement = "SELECT b.runid,b.timestamp,b.os,b.build,b.driver,b.totalcpus,b.totalmemory,b.totalspace,b.numclients,b.numnodes,b.numtables,b.numregions, b.datasize, b.rowsize, b.network, b.description, a.wrkldid, a.wrkldtype, a.threads, a.throughput, a.wavg, a.wmin, a.wmax, a.wp95, a.wp99, a.ravg, a.rmin, a.rmax, a.rp95, a.rp99 FROM tblycsbstats a, tblycsbrun b where a.runid=b.runid ";

	if (! empty($runid)) {
		$statement=$statement." AND runid in (".$runid.") ORDER BY runid DESC";
	}
	else if (! empty($timestamp)) 
	{
		$statement=$statement." AND timestamp in (".$timestamp.") ORDER BY runid DESC";
	}
	else {
		$statement=$statement." ORDER BY runid DESC LIMIT 6";
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
			$data=$data."\"build\":\"".$row['build']."\",";
			$data=$data."\"driver\":\"".$row['driver']."\",";
			$data=$data."\"totalcpus\":".$row['totalcpus'].",";
			$data=$data."\"totalmemory\":".$row['totalmemory'].",";
			$data=$data."\"totalspace\":".$row['totalspace'].",";
			$data=$data."\"numclients\":".$row['numclients'].",";
			$data=$data."\"numnodes\":".$row['numnodes'].",";
			$data=$data."\"numtables\":".$row['numtables'].",";
			$data=$data."\"numregions\":".$row['numregions'].",";
			$data=$data."\"datasize\":".$row['datasize'].",";
			$data=$data."\"rowsize\":\"".$row['rowsize']."\",";
			$data=$data."\"network\":\"".$row['network']."\",";
			$data=$data."\"description\":\"".$row['description']."\",";

			$data=$data."\"data\": [{";
			$prevrunid=$currunid;
		}

		$data=$data."\"wrkldid\":\"".$row['wrkldid']."\",";
		$data=$data."\"wrkldtype\":\"".$row['wrkldtype']."\",";
		$data=$data."\"threads\":".$row['threads'].",";
		$data=$data."\"throughput\":".$row['throughput'];
		$data=is_null($row['wavg'])?$data:$data.",\"wavg\":".$row['wavg'];
		$data=is_null($row['wmin'])?$data:$data.",\"wmin\":".$row['wmin'];
		$data=is_null($row['wmax'])?$data:$data.",\"wmax\":".$row['wmax'];
		$data=is_null($row['wp95'])?$data:$data.",\"wp95\":".$row['wp95'];
		$data=is_null($row['wp99'])?$data:$data.",\"wp99\":".$row['wp99'];
		$data=is_null($row['ravg'])?$data:$data.",\"ravg\":".$row['ravg'];
		$data=is_null($row['rmin'])?$data:$data.",\"rmin\":".$row['rmin'];
		$data=is_null($row['rmax'])?$data:$data.",\"rmax\":".$row['rmax'];
		$data=is_null($row['rp95'])?$data:$data.",\"rp95\":".$row['rp95'];
		$data=is_null($row['rp99'])?$data:$data.",\"rp99\":".$row['rp99'];
		$data=$data."}";
	} 

	mysql_close($con); 

	if (! empty($data)){
       echo "[".$data."]}]";
	}
?>