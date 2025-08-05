<?php
    require_once('../config.php');
    
    try {
        
        $number = $_GET['number'];
        $status = $_GET['status'];
        
        date_default_timezone_set('Asia/Tehran');
        $currentDateTime = date('Y-m-d H:i:s');
        
        $query = "SELECT * FROM `virtual_numbers` WHERE number = :number";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':number', $number, PDO::PARAM_STR);
        $stmt->execute();
        
        
        if ($stmt->rowCount() > 0) {
            $updateQuery = "UPDATE `virtual_numbers` SET status = :status, 
                               date_time = :date_time  WHERE number = :number";
            
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':status', $status, PDO::PARAM_STR);
            $updateStmt->bindParam(':date_time', $currentDateTime, PDO::PARAM_STR);
            $updateStmt->bindParam(':number', $number, PDO::PARAM_STR);
            if ($updateStmt->execute()) {
                echo "Status updated successfully for number: " . htmlspecialchars($number);
            } else {
                echo "Failed to update status";
            }
        } else {
            echo "Number not found in database: " . htmlspecialchars($number);
        }
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?>