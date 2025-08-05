<?php
    header('Content-Type: application/json');
    
    require_once('../config.php');
    
    try {
    
        // دریافت تمام شماره‌ها از دیتابیس
        $stmt = $pdo->query("SELECT * FROM virtual_numbers ORDER BY date_time DESC");
        $numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode([
            'success' => true,
            'numbers' => $numbers
        ]);
    
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در اتصال به دیتابیس: ' . $e->getMessage()
        ]);
    }
?>