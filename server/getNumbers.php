<?php
    require_once('../config.php');
    
    try {
        
        date_default_timezone_set('Asia/Tehran');
        $currentTime = new DateTime();
        $threeHoursAgo = (new DateTime())->sub(new DateInterval('PT3H'));
        
     
        $query = "SELECT number, status, date_time FROM `virtual_numbers` ORDER BY id ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $numbers = "";
        $count = 0;
        
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $num) {
            $numberTime = new DateTime($num['date_time']);
            
            if ($num['status'] === 'ACTIVE' && $numberTime >= $threeHoursAgo) {
                continue;
            }
            
            $numbers .= $num['number'] . ",";
            
            $count +=1;
        }
        
        // remove last ,
        $numbers = rtrim($numbers, ',');
        
        // echo $count."|".$numbers;
        echo $numbers;
    } catch (PDOException $e) {
        echo "Error to get data: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error in date processing: " . $e->getMessage();
    }
?>