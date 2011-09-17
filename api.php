<?php

    // Includes
	require_once('config.php');
	require_once('db.php');

    // Declare
    $sql = '';

    // Catch
    $func = ''; if (isset($_POST['func'])) $func = $_POST['func'];
    $lastid = ''; if (isset($_POST['lastid'])) $lastid = $_POST['lastid'];
    $maxcount = '500'; if (isset($_POST['maxcount'])) $maxcount = $_POST['maxcount'];

    // Start PHP Session
	session_start();

    // Decide what we need to do
	switch($func) {
	
        case "current":
        
            $sql = "SELECT * FROM nagios_servicestatus s INNER JOIN nagios_objects o ON s.service_object_id=o.object_id ";
            $sql .= "WHERE current_state <> 0 AND ";
                $sql .= "(problem_has_been_acknowledged = 0 AND notifications_enabled = 1) ";
                //if (is_numeric($lastid)) $sql .= "AND (servicestatus_id > ".$lastid.") ";
            $sql .= "ORDER BY problem_has_been_acknowledged, notifications_enabled DESC, status_update_time DESC ";
            $sql .= "LIMIT 500;";
            break;
            
        case "servicestatus":
            
            $sql = "SELECT * FROM nagios_servicestatus s  ";
            $sql .= "INNER JOIN nagios_objects o ON s.service_object_id=o.object_id ";
            $sql .= "WHERE current_state <> 0 AND ";
                $sql .= "(problem_has_been_acknowledged = 1 OR notifications_enabled = 0) ";
                //if (is_numeric($lastid)) $sql .= "AND (servicestatus_id > ".$lastid.") ";
            $sql .= "ORDER BY problem_has_been_acknowledged DESC, status_update_time ";
            $sql .= "DESC LIMIT 500;";
	       break;
	       
	   case "notifications":
	   
	       $sql = "SELECT n.*, o.name1, o.name2 FROM nagios_notifications n ";
	       $sql .= "INNER JOIN nagios_objects o ON n.object_id=o.object_id ";
	       if (is_numeric($lastid)) $sql .= "WHERE (notification_id > ".$lastid.") ";
	       $sql .= "ORDER BY start_time DESC LIMIT ".$maxcount.";";
	       break;
	       
	   default:
	       $fail = array('OK'=>0, 'ERROR'=>'Bad Request');
	       print json_encode($fail);
	       exit;
	       break;
	}
	
	// Prep Database
	$my_db = new DB();
	$my_db->construct($g_db_host, $g_db_name, $g_db_user, $g_db_pass);
	$my_db->connect();
	$my_db->select();
	if ($sql) {
    	$result = $my_db->query($sql);
        if ($result) {
            $nag = array();
            while ($row = mysql_fetch_array($result)) array_push($nag, $row);
            mysql_free_result($result);
            print json_encode($nag);
        }
	} else {
	   $fail = array('OK'=>0, 'ERROR'=>'No SQL');
	   print json_encode($fail);
	}
	$my_db->disconnect();
	
?>
