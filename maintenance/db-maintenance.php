<?php
	include '../config.php';
	
	try {
		// database connection
		$db = new PDO(
			"mysql:host=$mysql_server;dbname=$mysql_database;charset=utf8mb4",
			$mysql_user,
			$mysql_password,
			array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false
			)
		);

		// Get count of records to be deleted
		$countQuery = "SELECT COUNT(*) as count FROM SystemEvents WHERE ReceivedAt < CURDATE() - INTERVAL ? day";
		$stmt = $db->prepare($countQuery);
		$stmt->execute([$keep_logs_for_days]);
		$count = $stmt->fetch()['count'];

		// Delete old records
		$query = "DELETE FROM SystemEvents WHERE ReceivedAt < CURDATE() - INTERVAL ? day";
		$stmt = $db->prepare($query);
		$stmt->execute([$keep_logs_for_days]);
		$deleted = $stmt->rowCount();

		// Log the cleanup
		error_log("Database maintenance completed: Deleted $deleted records out of $count old records");
	} catch (PDOException $e) {
		error_log("Database maintenance failed: " . $e->getMessage());
		exit(1);
	}
?>
