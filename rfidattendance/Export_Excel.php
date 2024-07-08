<?php
require 'connectDB.php';
session_start();

if (isset($_POST["To_Excel"])) {
    // Initialize search query
    $searchQuery = "";

    // Start date filter
    if (!empty($_POST['date_sel_start'])) {
        $Start_date = $_POST['date_sel_start'];
        $searchQuery .= "checkindate = '$Start_date'";
    }

    // End date filter
    if (!empty($_POST['date_sel_end'])) {
        $End_date = $_POST['date_sel_end'];
        if (!empty($searchQuery)) $searchQuery .= " AND ";
        $searchQuery .= "checkindate BETWEEN '$Start_date' AND '$End_date'";
    }

    // Append other filters similarly

    $sql = "SELECT * FROM users_logs";
    if (!empty($searchQuery)) {
        $sql .= " WHERE $searchQuery";
    }
    $sql .= " ORDER BY id DESC";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=User_Log.xls');
        
        echo '<table class="table" bordered="1">';
        echo '<tr>
                <th>ID</th>
                <th>Name</th>
                <th>Serial Number</th>
                <th>Card UID</th>
                <th>Device ID</th>
                <th>Device Dep</th>
                <th>Date log</th>
                <th>Time In</th>
                <th>Time Out</th>
              </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>'.$row['id'].'</td>
                    <td>'.$row['username'].'</td>
                    <td>'.$row['serialnumber'].'</td>
                    <td>'.$row['card_uid'].'</td>
                    <td>'.$row['device_uid'].'</td>
                    <td>'.$row['device_dep'].'</td>
                    <td>'.$row['checkindate'].'</td>
                    <td>'.$row['timein'].'</td>
                    <td>'.$row['timeout'].'</td>
                  </tr>';
        }
        echo '</table>';
        exit();
    } else {
        header('Location: UsersLog.php');
        exit();
    }
}
?>
