<?php
	include '../config.php';

	header( 'Content-Type: application/json' );
			
	// Get sort parameters
	$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ID';
	$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

	// Validate sort field to prevent SQL injection
	$validSortFields = ['ID', 'Priority', 'ReceivedAt', 'Facility', 'FromHost', 'SysLogTag', 'Message'];
	if (!in_array($sort, $validSortFields)) {
		$sort = 'ID';
	}

	// Validate order
	if ($order !== 'ASC' && $order !== 'DESC') {
		$order = 'DESC';
	}

	$query = "SELECT *, LEFT(Message, 120) AS SmallMessage FROM SystemEvents ";
	$wherestring = "";
	if (isset($_GET[ "search" ])){
		$searchstring = $_GET[ "search" ];
	}else{
		$searchstring = "";
	}

	$qArray = array();
	
	if( $searchstring != "" )
	{
		$wherestring = "";
		$urlencoded = trim( urldecode( $searchstring ) );
		$array = explode( " ", $urlencoded );
		
		for( $x = 0; $x < count( $array ); $x++ )
		{

			$keysarray = str_split( trim( $array[ $x ] ) );
			$keyname = "";
			$keyvalue = "";
			$expression = "";
			$position = 0;
						
			for( $y = 0; $y < count( $keysarray ); $y++ )
			{
				if( $position == 3 && $keysarray[$y] == "\"" ) { $position = 4; }
				if( $position == 3 && $keysarray[$y] != "\"" ) { $keyvalue .= $keysarray[$y]; }
				if( $position == 2 && $keysarray[$y] == "\"" ) { $position = 3; }
				if( $position == 2 && $keysarray[$y] != "\"" ) { $expression .= $keysarray[$y]; }
				if( $position == 1 && $keysarray[$y] == "\"" ) { $position = 2; }
				if( $position == 1 && $keysarray[$y] != "\"" ) { $keyname .= $keysarray[$y]; }
				if( $position == 0 && $keysarray[$y] == "\"" ) { $position = 1; }
			}	

			if( $keyname == "Host" ) $keyname = "FromHost";
			if( $keyname == "Facility" ) 
			{
				$keyname = "Facility";
				
				if( $keyvalue == "KERNEL-MESSAGE" ) { $keyvalue = "0"; }
				if( $keyvalue == "USER-MESSAGE" ) { $keyvalue = "1"; }
				if( $keyvalue == "MAIL-SYSTEM" ) { $keyvalue = "2"; }
				if( $keyvalue == "SECURITY-DAEMON" ) { $keyvalue = "3"; }
				if( $keyvalue == "AUTH-MESSAGE" ) { $keyvalue = "4"; }
				if( $keyvalue == "SYSLOGD" ) { $keyvalue = "5"; }
				if( $keyvalue == "PRINTER" ) { $keyvalue = "6"; }
				if( $keyvalue == "NETWORK" ) { $keyvalue = "7"; }
				if( $keyvalue == "UUCP" ) { $keyvalue = "8"; }
				if( $keyvalue == "CRON" ) { $keyvalue = "9"; }
				if( $keyvalue == "AUTH-MESSAGE-10" ) { $keyvalue = "10"; }
				if( $keyvalue == "FTP" ) { $keyvalue = "11"; }
				if( $keyvalue == "NTP" ) { $keyvalue = "12"; }
				if( $keyvalue == "LOG-AUDIT" ) { $keyvalue = "13"; }
				if( $keyvalue == "LOG-ALERT" ) { $keyvalue = "14"; }
				if( $keyvalue == "CLOCK-DAEMON" ) { $keyvalue = "15"; }
				if( $keyvalue == "LOCAL0" ) { $keyvalue = "16"; }
				if( $keyvalue == "LOCAL1" ) { $keyvalue = "17"; }
				if( $keyvalue == "LOCAL2" ) { $keyvalue = "18"; }
				if( $keyvalue == "LOCAL3" ) { $keyvalue = "19"; }
				if( $keyvalue == "LOCAL4" ) { $keyvalue = "20"; }
				if( $keyvalue == "LOCAL5" ) { $keyvalue = "21"; }
				if( $keyvalue == "LOCAL6" ) { $keyvalue = "22"; }
				if( $keyvalue == "LOCAL7" ) { $keyvalue = "23"; }
			}
			if( $keyname == "Date" ) $keyname = "DeviceReportedTime";
			if( $keyname == "Severity" ) 
			{
				$keyname = "Priority";
				$keyvalue = strtoupper( $keyvalue );
				
				if( $keyvalue == "EMERGENCY" ) $keyvalue = "0";
				if( $keyvalue == "ALERT" ) $keyvalue = "1";
				if( $keyvalue == "CRITICAL" ) $keyvalue = "2";
				if( $keyvalue == "ERROR" ) $keyvalue = "3";
				if( $keyvalue == "WARNING" ) $keyvalue = "4";
				if( $keyvalue == "NOTICE" ) $keyvalue = "5";
				if( $keyvalue == "INFO" ) $keyvalue = "6";
				if( $keyvalue == "DEBUG" ) $keyvalue = "7";
			}
			if( $keyname == "Syslogtag" ) $keyname = "SysLogTag";
			if( $keyname == "Messagetype" ) $keyname = "Messagetype";
			
			if( $expression != "=" && $expression != "<>" && $expression != "<" && $expression != ">" )
				exit();
			
			if( $expression == "=" ) $expression = "LIKE";
			
			
			$qArray[ "param".strval( $x ) ] = $keyvalue; 
			
			if( $wherestring == "" )
				$wherestring = " WHERE $keyname $expression :param".strval( $x )." ";
			else
				$wherestring .= " AND $keyname $expression :param".strval( $x )." "; 
		}
	}
	
	try {
		if (!$pdo) {
			throw new Exception("Database connection not available");
		}

		$rows = array();
		$stmt = $pdo->prepare($query . $wherestring . " ORDER BY $sort $order LIMIT 2000");

		if( $stmt->execute( $qArray ) )
		foreach( $stmt as $row ) {
			// Format dates for consistency
			$row['ReceivedAt'] = date('Y-m-d H:i:s', strtotime($row['ReceivedAt']));
			if ($row['DeviceReportedTime']) {
				$row['DeviceReportedTime'] = date('Y-m-d H:i:s', strtotime($row['DeviceReportedTime']));
			}
			$rows[] = $row;
		}

		echo json_encode( $rows );

	} catch (Exception $e) {
		http_response_code(500);
		echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
	}

?>
