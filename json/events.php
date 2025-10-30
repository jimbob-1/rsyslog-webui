<?php
	include '../config.php';

	header( 'Content-Type: application/json' );
			
	$query = "SELECT *, LEFT(Message, 120) AS SmallMessage FROM SystemEvents ";
	$wherestring = "";
	$conditional = "AND";
	if (isset($_GET[ "search" ])){
		$searchstring = $_GET[ "search" ];
	}else{
		$searchstring = "";
	}

	$qArray = array();
	
	if( $searchstring != "" )
	{
		if (!strpbrk($searchstring,'=<>')){
			//Skip processing statements by excluding expected operators
			$conditional = "OR";
			if (preg_match('/^\"{0,1}\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]\"{0,1}$/', $searchstring)){
				//If it loosely matches datetime format: 2000-02-31 25:00:90, assume date timestamp
				$searchstring_tmp = " \"Date\"=\"".str_replace(" ", "T", trim($searchstring,'"'))."\"";
				//Greater than midnight if only YYYY-MM-DD format specified
			}elseif (preg_match('/^\"{0,1}\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\"{0,1}$/', $searchstring)){
				// Matches YYYY-MM-DD format, pad for sanity and SQL search
				$searchstring_tmp = " \"Date\">=\"".trim($searchstring,'"')."T00:00:00\"";
			}else{
				$searchstring_tmp = "\"Message\"=\"".trim(str_replace(" ","%20",$searchstring),'"')."\"";
				$searchstring_tmp .= " \"FromHost\"=\"".trim($searchstring,'"')."\"";
				$searchstring_tmp .= " \"Date\"=\"".trim($searchstring,'"')."\"";
			}
			$searchstring = urlencode($searchstring_tmp);
		}


		$wherestring = "";
		$urlencoded = trim( urldecode( $searchstring ) );
		$urlencoded = str_replace("*","%",$urlencoded);
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
				if( $position == 3 && $keysarray[$y] != "\"" ) { $keyvalue .= $keysarray[$y];}
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
			
			//echo $keyname.$keyvalue;

			if( $expression != "=" && $expression != "<>" && $expression != "<" && $expression != ">" && $expression != ">=" && $expression != "<="){
				//exit();
				break;
			}
			
			if( $expression == "=" && $keyname != "DeviceReportedTime" ) $expression = "LIKE";

			
			$qArray[ "param".strval( $x ) ] = strtolower(str_replace("%20", " ", $keyvalue)); 

			if( $wherestring == "" ){
				if ($keyname != "DeviceReportedTime" && $keyname != "ReceivedAt"){
					$wherestring = " WHERE LOWER($keyname) $expression LOWER(:param".strval( $x ).") ";
					//Only lowercase non time related fields, weird issue with mysql
				}else{
					$wherestring = " WHERE $keyname $expression LOWER(:param".strval( $x ).") ";
				}
			}else{
				if ($keyname != "DeviceReportedTime" && $keyname != "ReceivedAt"){
					//Only lowercase non time related fields, weird issue with mysql
					$wherestring .= " $conditional LOWER($keyname) $expression LOWER(:param".strval( $x ).") "; 
				}else{
					$wherestring .= " $conditional $keyname $expression LOWER(:param".strval( $x ).") "; 
				}
			}
		}
	}
	$db = new PDO( "mysql:host=$mysql_server;dbname=$mysql_database;charset=utf8", $mysql_user, $mysql_password );
	$rows = array();
	$stmt = $db->prepare($query . $wherestring . " ORDER BY ID DESC LIMIT 2000");
	if( $stmt->execute( $qArray ) )
	foreach( $stmt as $row ) {
		$rows[] = $row;
	}

	echo json_encode( $rows );

?>
