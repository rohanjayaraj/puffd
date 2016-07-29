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
