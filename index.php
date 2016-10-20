<?php
require 'flight/Flight.php';

Flight::route('POST /', function(){
    #echo "Starting...";
    #var_dump($_POST);
    if (empty($_POST)){
        echo "empty post";
        return;
    }
    $data = Flight::request()->data;
    $retval = 0;
    try {
        if (! empty($data)){
            if (! empty($data['rundata'])){
                #var_dump($_POST);
                $rundata = json_decode($data['rundata']);
                $retval = handleRunData($rundata);
            }else if (! empty($data['runinfo'])){
                #var_dump($_POST);
                $runinfo = json_decode($data['runinfo']);
                if (! empty($runinfo)){
                    $retval = handleRunInfo($runinfo);
                    #print("\nYCSB RUNID => ".$retval);
                }
            }else if (! empty($data['runlog'])){
                #var_dump($_POST);
                $runlog = json_decode($data['runlog']);
                if (! empty($runlog)){
                    $retval = handleRunLog($runlog);
                    #print("\nYCSB RUNID => ".$retval);
                }
            }else if (! empty($data['dfsio'])){
                #var_dump($_POST);
                $dfsio = json_decode($data['dfsio']);
                if (! empty($dfsio)){
                    $retval = handleDFSIO($dfsio);
                    print("\nDFSIO RUNID => ".$retval);
                }else{
                    print("Empty JSON for DFSIO -> ".$data['dfsio']);
                }
            }else if (! empty($data['terasort'])){
                #var_dump($_POST);
                $terasort = json_decode($data['terasort']);
                if (! empty($terasort)){
                    $retval = handleTeraSort($terasort);
                    print("\TERASORT RUNID => ".$retval);
                }else{
                    print("Empty JSON for TERASORT -> ".$data['terasort']);
                }
            }else if (! empty($data['rwspeed'])){
                #var_dump($_POST);
                $rwspeed = json_decode($data['rwspeed']);
                if (! empty($rwspeed)){
                    $retval = handleRWSpeed($rwspeed);
                    print("\nRWSpeed RUNID => ".$retval);
                }
            }else if (! empty($data['rubixRunInfo'])){
                #var_dump($_POST);
                $rubixinfo = json_decode($data['rubixRunInfo']);
                if (! empty($rubixinfo)){
                    $retval = handleRubixInfo($rubixinfo);
                    print("\nRubixRunInfo RUNID => ".$retval);
                }
            }else if (! empty($data['rubixRunData'])){
                var_dump($_POST);
                $rubixdata = json_decode($data['rubixRunData']);
                if (! empty($rubixdata)){
                    $retval = handleRubixData($rubixdata);
                    print("\nRubixRunData RUNID => ".$retval);
                }
            }else{
                return;
            }
        }
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
});


function handleRunInfo($json){
     try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        #echo 'Running handleRunInfo :: ';
        foreach($json as $key => $value) 
        {
            #print($key." =>".$value);
            if(!validInfoField($key))
            {
                return -1;
            }

            # Save timestamp value to validate if the row already exists
            if($key == "timestamp"){
                $timestamp=$value;
            }
            else if($key == "workload"){
                $value=mysql_real_escape_string($value);
            }

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }
        }
        $runid=getRunIDForTimestamp($timestamp);
        if($runid != 0)
        {
            return $runid;
        }
        insertRecord("tblycsbrun", $fields, $values);
        return getRunIDForTimestamp($timestamp);
    } catch (Exception $e) {
        echo '[handleRunInfo] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validInfoField($field){
    $retval=false;
    switch ($field) {
        case "timestamp":
        case "os":
        case "build":
        case "driver":
        case "totalcpus":
        case "totalmemory":
        case "disktype":
        case "numdisks":
        case "nummfs":
        case "totalspace":
        case "numclients":
        case "numnodes":
        case "tabletype":
        case "numtables":
        case "numregions":

        case "datasize":
        case "rowcount":
        case "rowsize":
        case "network":
        case "description":
        case "workload":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleRunData($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        $wrkldid=NULL;

        #echo 'Running handleRunData ::';
        
        foreach($json as $key => $value) 
        {
            #print("\n".$key." =>".$value."\n");

            # Check if field is valid else return false
            if(!validDataField($key, $value)){
                return -1;
            }

            if($key == "wrkldid"){
                $wrkldid=$value;
            }

            # Save timestamp value and continue as it needs to be translated to runid
            if($key == "timestamp"){
                $timestamp=$value;
                continue;
            }

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }

        }

        $runid=getRunIDForTimestamp($timestamp);
        if($runid == 0)
        {
            return -3;
        } 
        else
        {
            $fields=$fields.",runid";
            $values=$values.",\"".$runid."\"";
        }

        # Check if workload id already exists, if yes, return 
        if (workloadExists($runid, $wrkldid))
        {
            return -5;
        }
        insertRecord("tblycsbstats", $fields, $values);
        return 0;
    } catch (Exception $e) {
        echo '[handleRunData] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validDataField($field, $value){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "wrkldid":
        case "wrkldtype":
        case "threads":
        case "id":
        
        case "throughput":
        case "wavg":
        case "wmin":
        case "wmax":
        case "wp95":
        case "wp99":

        case "ravg":
        case "rmin":
        case "rmax":
        case "rp95":
        case "rp99":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleRunLog($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        $wrkldid=NULL;

        #echo 'Running handleRunLog ::';
        
        foreach($json as $key => $value) 
        {
            #print("\n".$key." =>".$value."\n");

            # Check if field is valid else return false
            if(!validRunLogField($key, $value)){
                return -1;
            }

            if($key == "wrkldid"){
                $wrkldid=$value;
            }

            # Save timestamp value and continue as it needs to be translated to runid
            if($key == "timestamp"){
                $timestamp=$value;
                continue;
            }else if($key == "log"){
                $value=mysql_real_escape_string($value);
            }

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }

        }

        $runid=getRunIDForTimestamp($timestamp);
        if($runid == 0)
        {
            return -3;
        } 
        else
        {
            $fields=$fields.",runid";
            $values=$values.",\"".$runid."\"";
        }

        # Check if runlog id already present, if yes, return 
        if (runlogExists($runid, $wrkldid))
        {
            return -5;
        }
        insertRecord("tblycsbrunlog", $fields, $values);
        return 0;
    } catch (Exception $e) {
        echo '[handleRunLog] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validRunLogField($field, $value){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "wrkldid":
        case "wrkldtype":
        case "id":
        case "log":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}


function handleDFSIO($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        
        echo 'Running handleDFSIO ::';
        
        foreach($json as $key => $value) 
        {
            print($key." =>".$value);
            if(!validDFSIOField($key))
            {
                return -1;
            }
            # Check if field is valid else return false
             # Save timestamp value to validate if the row already exists
            if($key == "timestamp"){
                $timestamp=$value;
            }else if($key == "nodes"){
                $value=mysql_real_escape_string($value);
            }

            #print("\n".$key." =>".$value."\n");
            
            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }
        }
        $runid=getRunIDForTimestamp2($timestamp,"tbldfsio");
        if($runid != 0)
        {
            print("\n [handleDFSIO] Timestamp ".$timestamp." is already present! \n");
            return $runid;
        }
        insertRecord("tbldfsio", $fields, $values);
        return getRunIDForTimestamp2($timestamp,"tbldfsio");
    } catch (Exception $e) {
        echo '[handleDFSIO] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validDFSIOField($field){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "os":
        case "maprbuild":
        case "driver":
        case "description":
        case "disktype":
        case "hadoopversion":
        case "mfsinstances":
        case "writetp":
        case "readtp":
        case "teststatus":
        case "encryption":
        case "nodes":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleTeraSort($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        
        #echo 'Running handleTeraSort ::';
        
        foreach($json as $key => $value) 
        {
            #print($key." =>".$value);
            if(!validTeraSortField($key))
            {
                return -1;
            }
            # Check if field is valid else return false
             # Save timestamp value to validate if the row already exists
            if($key == "timestamp"){
                $timestamp=$value;
            }else if($key == "nodes"){
                $value=mysql_real_escape_string($value);
            }

            #print("\n".$key." =>".$value."\n");

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }
        }
        $runid=getRunIDForTimestamp2($timestamp,"tblterasort");
        if($runid != 0)
        {
            print("\n [handleTeraSort] Timestamp ".$timestamp." is already present! \n");
            return $runid;
        }
        insertRecord("tblterasort", $fields, $values);
        return getRunIDForTimestamp2($timestamp,"tblterasort");
    } catch (Exception $e) {
        echo '[handleTeraSort] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validTeraSortField($field){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "os":
        case "build":
        case "driver":
        case "description":
        case "disktype":
        case "hadoopversion":
        case "nodes":
        case "runtime":
        case "secure":
        case "encryption":
        case "teststatus":
        case "avgmap":
        case "avgreduce":
        case "avgshuffle":
        case "avgmerge":
        case "status":
        case "mfsinstances":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleRWSpeed($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        
        #echo 'Running handleRWSpeed ::';
        
        foreach($json as $key => $value) 
        {
            print($key." =>".$value);
            if(!validRWSpeedField($key))
            {
                return -1;
            }
            # Check if field is valid else return false
             # Save timestamp value to validate if the row already exists
            if($key == "timestamp"){
                $timestamp=$value;
            }else if($key == "nodes"){
                $value=mysql_real_escape_string($value);
            } 
            #print("\n".$key." =>".$value."\n");

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            }else{
                 $fields=$fields.",".$key;
                if(is_bool($value) === true){
                    $boolval = json_encode($value);
                    $values=$values.",\"".$boolval."\"";
                }else{
                   $values=$values.",\"".$value."\"";
               }
           }
        }
        $runid=getRunIDForTimestamp2($timestamp,"tblrwspeed");
        if($runid != 0)
        {
            print("\n [handleRWSpeed] Timestamp ".$timestamp." is already present! \n");
            return $runid;
        }
        insertRecord("tblrwspeed", $fields, $values);
        return getRunIDForTimestamp2($timestamp,"tblrwspeed");
    } catch (Exception $e) {
        echo '[handleRWSpeed] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validRWSpeedField($field){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "os":
        case "build":
        case "driver":
        case "disktype":
        case "description":
        case "hadoopversion":
        case "mfsinstances":
        case "numsp":
        case "networkencryption":
        case "nodes":
        case "repl1localread":
        case "repl1localwrite":
        case "repl1remoteread":
        case "repl1remotewrite":
        case "repl3localread":
        case "repl3localwrite":
        case "repl3remoteread":
        case "repl3remotewrite":
        case "status":
        case "secure":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleRubixInfo($json){
     try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        #echo 'Running handleRubixInfo :: ';
        foreach($json as $key => $value) 
        {
            #print($key." =>".$value);
            if(!validRubixInfoField($key))
            {
                return -1;
            }

            # Save timestamp value to validate if the row already exists
            if($key == "timestamp"){
                $timestamp=$value;
            }

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }
        }
        $runid=getRunIDForTimestamp2($timestamp,"tblrubixruninfo");
        if($runid != 0)
        {
            return $runid;
        }
        insertRecord("tblrubixruninfo", $fields, $values);
        return getRunIDForTimestamp2($timestamp, "tblrubixruninfo");
    } catch (Exception $e) {
        echo '[handleRubixInfo] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validRubixInfoField($field){
    $retval=false;
    switch ($field) {
        case "timestamp":
        case "os":
        case "platform":
        case "buildversion":
        case "hostname":
        case "messagesize":
        case "servercount":
        case "numdisks":
        case "nummfs":
        case "numsp":
        case "description":
        case "duration":
        case "numtopics":
        case "numpartitions":
        case "disktype":
            $retval=true;
            break;
        default:
            $retval=false;
    }
    return $retval;
}

function handleRubixData($json)
{
    try {
        $fields=NULL;
        $values=NULL;
        $timestamp=NULL;
        $testid=NULL;

        #echo 'Running handleRubixData ::';
        
        foreach($json as $key => $value) 
        {
            #print("\n".$key." =>".$value."\n");

            # Check if field is valid else return false
            if(!validRubixDataField($key)){
                return -1;
            }

            if($key == "testid"){
                $testid=$value;
            }

            # Save timestamp value and continue as it needs to be translated to runid
            if($key == "timestamp"){
                $timestamp=$value;
                continue;
            }

            if(is_null($fields)){
                $fields=$key;
                $values="\"".$value."\"";
            } else if(strlen($value) == 0){
                $fields=$fields.",".$key;
                $values=$values.",NULL";
            } else {
                $fields=$fields.",".$key;
                $values=$values.",\"".$value."\"";
           }

        }

        $runid=getRunIDForTimestamp2($timestamp,"tblrubixruninfo");
        if($runid == 0)
        {
            print("\n [handleRubixData] Timestamp ".$timestamp." is NOT added to tblrubixruninfo table yet! \n");
            return -3;
        } 
        else
        {
            $fields=$fields.",runid";
            $values=$values.",\"".$runid."\"";
        }

        # Check if workload id already exists, if yes, return 
        if (rubixDataExists($runid, $testid))
        {
            return -5;
        }
        insertRecord("tblrubixrundata", $fields, $values);
        return 0;
    } catch (Exception $e) {
        echo '[handleRubixData] Caught exception: ',  $e->getMessage(), "\n";
        return -4;
    }   
}

function validRubixDataField($field){
    $retval=false;
    switch ($field) {
        case "timestamp": 
        case "testid": 
        case "testtype":
        case "replfactor":
        case "compression":
        case "numclients":
        
        case "throughput":
        case "initthroughput":
        case "ratedrop":
        case "avgtimetofinish":
        case "stddevduration":
        case "avglag":

        case "avgofminlag":
        case "avgofmaxlag":
        case "absminlag":
        case "absmaxlag":
            $retval=true;
            break;
        default:
            print("\n [validRubixDataField] Invalid field ".$field."\n");
            $retval=false;
    }
    return $retval;
}

function getRunIDForTimestamp($timestamp){
    $tsrunid=0;
    if($timestamp == NULL){
        return $tsrunid;
    }

    try {
        $statement="select runid from tblycsbrun where timestamp=".$timestamp;
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $stmt = $db->query($statement);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $tsrunid="0";
        foreach($data as $row) {
           $tsrunid=$row['runid'];
           break;
        }
    } catch (Exception $e) {
        echo '[getRunIDForTimestamp] Caught exception: ',  $e->getMessage(), "\n";
    } 
    return $tsrunid;
}

function getRunIDForTimestamp2($timestamp,$table){
    $tsrunid=0;
    if($timestamp == NULL){
        return $tsrunid;
    }

    try {
        $statement="select runid from ".$table." where timestamp=".$timestamp;
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $stmt = $db->query($statement);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $tsrunid="0";
        foreach($data as $row) {
           $tsrunid=$row['runid'];
           break;
        }
    } catch (Exception $e) {
        echo '[getRunIDForTimestamp2] Caught exception: ',  $e->getMessage(), "\n";
    } 
    return $tsrunid;
}

function runlogExists($runid, $wrkldid){
     try {
        $statement="select wrkldid from tblycsbrunlog where runid=".$runid." and wrkldid='".$wrkldid."'";
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $stmt = $db->query($statement);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $row) {
           return 1;
        }
    } catch (Exception $e) {
        echo '[runlogExists] Caught exception: ',  $e->getMessage(), "\n";
    }
    return 0;
}

function workloadExists($runid, $wrkldid){
    try {
        $statement="select wrkldid from tblycsbstats where runid=".$runid." and wrkldid='".$wrkldid."'";
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $stmt = $db->query($statement);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $row) {
           return 1;
        }
    } catch (Exception $e) {
        echo '[workloadExists] Caught exception: ',  $e->getMessage(), "\n";
    }
    return 0;
}

function rubixDataExists($runid, $recordid){
    try {
        $statement="select testid from tblrubixrundata where runid=".$runid." and testid='".$recordid."'";
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $stmt = $db->query($statement);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $row) {
           return 1;
        }
    } catch (Exception $e) {
        echo '[rubixDataExists] Caught exception: ',  $e->getMessage(), "\n";
    }
    return 0;
}

function insertRecord($table, $fields, $values) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=perfdb', 'root', '');
        $statement="insert into ".$table." (".$fields.") VALUES (".$values.")";
        $stmt = $db->prepare($statement);
        $stmt->execute();
        #print("\nStatement : ".$statement);
    }catch (Exception $e) {
        echo '[insertRecord] Caught exception: ',  $e->getMessage(), "\n";
    } 
}

Flight::start();

?>
