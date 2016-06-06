
<?php
    $runid="";
    
    foreach ($_GET as $key => $value) {
   		 #print("\n".$key." => ".$value);
   		 if(strcasecmp($key,"runid") == 0){
   		 	$runarr = explode(',', $value);
   		 	foreach ($runarr as $val){
   		 		$runid=(empty($runid)) ? "'".$val."'" : $runid.",".$val;
   			}
   		 }else {
   		 	braek;
   		 } 	
	}

	if (empty($runid)) {
		return;
	}

	$con = mysql_connect("localhost","root",""); 
	if (!$con) 
	{ 
		die('Could not connect: ' . mysql_error()); 
	} 

	mysql_select_db("perfdb", $con); 

	$statement = "SELECT b.timestamp, a.wrkldid, a.wrkldtype, a.threads, a.throughput, a.wavg, a.wmin, a.wmax, a.wp95, a.wp99, a.ravg, a.rmin, a.rmax, a.rp95, a.rp99 FROM tblycsbstats a, tblycsbrun b where a.runid=b.runid ";

	$statement=$statement." AND b.timestamp in (".$runid.") ORDER BY b.runid ASC";


	#print("Statement : ".$statement);
	$result = mysql_query($statement);
	echo "<div STYLE=\"font-family: Arial, font-size: 10px;\"><table border='1' cellpadding='5'";
	echo "<tr>";
	echo "<th>Run ID</th>";
	echo "<th>Workload Type</th>";
	echo "<th>Workload ID</th>";
	echo "<th># of Threads</th>";
	echo "<th>Results</th>";
	echo "</tr>";
	while($row = mysql_fetch_array($result)) 
	{ 
		#var_dump($row);
		echo "<tr>";
	 	

	 	echo "<td>";
		echo $row['timestamp'];
		echo "</td><td>";
		echo $row['wrkldtype'];
		echo "</td><td>";
		echo $row['wrkldid'];
		echo "</td><td>";
		echo $row['threads'];
		echo "</td><td>";
		$data="";
		$data=$data."=SPLIT(\"".$row['throughput'].",";
		$data=$data.(is_null($row['wavg'])?" ":$row['wavg']).",";
		$data=$data.(is_null($row['wmin'])?" ":$row['wmin']).",";
		$data=$data.(is_null($row['wmax'])?" ":$row['wmax']).",";
		$data=$data.(is_null($row['wp95'])?" ":$row['wp95']).",";
		$data=$data.(is_null($row['wp99'])?" ":$row['wp99']).",";
		$data=$data.(is_null($row['ravg'])?" ":$row['ravg']).",";
		$data=$data.(is_null($row['rmin'])?" ":$row['rmin']).",";
		$data=$data.(is_null($row['rmax'])?" ":$row['rmax']).",";
		$data=$data.(is_null($row['rp95'])?" ":$row['rp95']).",";
		$data=$data.(is_null($row['rp99'])?" ":$row['rp99']);
		$data=$data."\",\",\")";
		echo "$data";
		echo "</td></tr>";
	} 
	echo "</table></div>";

	mysql_close($con); 
?>