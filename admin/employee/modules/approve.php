<?php

function generateID()
{
    include_once('.../../../../../db_connection.php');
    $conn = OpenCon();
    $sql = "SELECT emp_id FROM `employee_details` WHERE emp_id like 'BBEM%' ORDER BY emp_id DESC LIMIT 1;";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $num = ((int) substr($row["emp_id"], 4)) + 1;
        return ($num < 10 ? "BBEM0" . ($num) : "BBEM" . $num);
    } else
        return "BBEM01";
}
function approve_employee()
{
    include '.../../../../../db_connection.php';
    $conn = OpenCon();
    $clickedEmailID = $_POST['approve_btn'];
    $query = "SELECT * FROM `pending_employee_details` WHERE email_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $clickedEmailID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($emp_name, $date_of_birth, $address, $email_id, $aadhar_no, $pan_no, $password, $profile_photo_link, $phone_no, $date_of_joining);
    $emp_id = generateID();
    while ($stmt->fetch()) {
        $insertQuery = "INSERT INTO `employee_details` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->bind_param("sssssissis", $emp_id, $emp_name, $date_of_birth, $address, $email_id, $aadhar_no, $pan_no, $profile_photo_link, $phone_no, $date_of_joining);
        $stmtInsert->execute();
        $deleteQuery = "DELETE FROM `pending_employee_details` WHERE email_id=?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->bind_param("s", $clickedEmailID);
        $stmtDelete->execute();
        $stmtInsert->close();
        $stmtDelete->close();
        $loginQuery = "INSERT INTO `login` VALUES (?,?)";
        $loginInsert = $conn->prepare($loginQuery);
        $loginInsert->bind_param("ss", $emp_id, $password);
        $loginInsert->execute();
        $loginInsert->close();
    }

    $stmt->close();

    CloseCon($conn);
}
?>