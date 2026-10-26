<?php
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and basic sanitize
    $fullName = $_POST['fullName'];
    $cnic     = $_POST['cnic'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $userType = $_POST['userType']; // 'farmer' or 'buyer'
    $address  = $_POST['address'];
    $city     = $_POST['city'];
    $country  = $_POST['country'];
    
    // Hash the password for security
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into the main users table
        $sql = "INSERT INTO users (full_name, cnic, email, phone, password_hash, role, address, city, country) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $fullName, $cnic, $email, $phone, $password, $userType, $address, $city, $country);
        $stmt->execute();
        
        $userId = $conn->insert_id; // Get the generated user_id

        // 2. Insert into specific profile tables based on role
        if ($userType === 'farmer') {
            $farmLoc  = $_POST['farmLocation'];
            $farmSize = $_POST['farmSize'];
            $crops    = $_POST['cropTypes'];
            
            $sqlFarmer = "INSERT INTO farmer_profiles (farmer_id, farm_location, farm_size_acres, crop_types) VALUES (?, ?, ?, ?)";
            $stmtF = $conn->prepare($sqlFarmer);
            $stmtF->bind_param("isds", $userId, $farmLoc, $farmSize, $crops);
            $stmtF->execute();
            
        } elseif ($userType === 'buyer') {
            $bType = $_POST['buyerType'];
            $cName = $_POST['companyName'];
            $bReg  = $_POST['businessReg'];
            
            $sqlBuyer = "INSERT INTO buyer_profiles (buyer_id, buyer_type, company_name, business_reg_number) VALUES (?, ?, ?, ?)";
            $stmtB = $conn->prepare($sqlBuyer);
            $stmtB->bind_param("isss", $userId, $bType, $cName, $bReg);
            $stmtB->execute();
        }

        // Commit if all queries succeed
        $conn->commit();
        header("Location: ../../html/login.html?signup=success");
        exit();

    } catch (Exception $e) {
        // Rollback if anything fails
        $conn->rollback();
        echo "Error creating account: " . $e->getMessage();
    }
}
?>