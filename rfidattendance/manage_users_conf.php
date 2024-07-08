<?php
require 'connectDB.php';

// Add user
if (isset($_POST['Add'])) {
    // Check if all required fields are set
    if (isset($_POST['name'], $_POST['number'], $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['dev_uid'], $_POST['gender'], $_POST['card_uid'])) {
        $Uname = $_POST['name'];
        $Number = $_POST['number'];
        $Email = $_POST['email'];
        $dev_uid = $_POST['dev_uid'];
        $Gender = $_POST['gender'];
        $Phone = $_POST['phone']; // New field: phone number
        $Address = $_POST['address']; // New field: address
        $card_uid = $_POST['card_uid']; // New field: card UID

        // Check if all fields are filled
        if (!empty($Uname) && !empty($Number) && !empty($Email) && !empty($Phone) && !empty($Address) && !empty($card_uid)) {
            // Check if the serial number is already taken
            $sql_check_serial = "SELECT serialnumber FROM users WHERE serialnumber=?";
            $stmt_check_serial = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt_check_serial, $sql_check_serial)) {
                echo "SQL_Error";
                exit();
            } else {
                mysqli_stmt_bind_param($stmt_check_serial, "d", $Number);
                mysqli_stmt_execute($stmt_check_serial);
                $result_check_serial = mysqli_stmt_get_result($stmt_check_serial);
                if (mysqli_num_rows($result_check_serial) > 0) {
                    echo "The serial number is already taken!";
                    exit();
                } else {
                    // Get the device department
                    $sql_device_dep = "SELECT device_dep FROM devices WHERE device_uid=?";
                    $stmt_device_dep = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt_device_dep, $sql_device_dep)) {
                        echo "SQL_Error";
                        exit();
                    } else {
                        mysqli_stmt_bind_param($stmt_device_dep, "s", $dev_uid);
                        mysqli_stmt_execute($stmt_device_dep);
                        $result_device_dep = mysqli_stmt_get_result($stmt_device_dep);
                        if ($row_device_dep = mysqli_fetch_assoc($result_device_dep)) {
                            $dev_name = $row_device_dep['device_dep'];
                        } else {
                            $dev_name = "All";
                        }
                    }
                    // Add user to the database
                    $sql_add_user = "INSERT INTO users (username, serialnumber, gender, email, phone_number, address, device_uid, device_dep, card_uid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_add_user = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt_add_user, $sql_add_user)) {
                        echo "SQL_Error";
                        exit();
                    } else {
                        mysqli_stmt_bind_param($stmt_add_user, "sssssssss", $Uname, $Number, $Gender, $Email, $Phone, $Address, $dev_uid, $dev_name, $card_uid);
                        mysqli_stmt_execute($stmt_add_user);
                        echo "User added successfully!";
                        exit();
                    }
                }
            }
        } else {
            echo "Empty Fields";
            exit();
        }
    } else {
        echo "Some required fields are missing!";
        exit();
    }
}

// Update an existing user 
if (isset($_POST['Update'])) {
    // Check if all required fields are set
    if (isset($_POST['user_id'], $_POST['name'], $_POST['number'], $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['dev_uid'], $_POST['gender'])) {
        $user_id = $_POST['user_id'];
        $Uname = $_POST['name'];
        $Number = $_POST['number'];
        $Email = $_POST['email'];
        $dev_uid = $_POST['dev_uid'];
        $Gender = $_POST['gender'];
        $Phone = $_POST['phone']; // New field: phone number
        $Address = $_POST['address']; // New field: address

        // Check if the user exists
        $sql_check_user = "SELECT add_card FROM users WHERE id=?";
        $stmt_check_user = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt_check_user, $sql_check_user)) {
            echo "SQL_Error";
            exit();
        } else {
            mysqli_stmt_bind_param($stmt_check_user, "i", $user_id);
            mysqli_stmt_execute($stmt_check_user);
            $result_check_user = mysqli_stmt_get_result($stmt_check_user);
            if ($row_check_user = mysqli_fetch_assoc($result_check_user)) {
                if ($row_check_user['add_card'] == 0) {
                    echo "First, You need to add the User!";
                    exit();
                } else {
                    // Check if any field is filled
                    if (empty($Uname) && empty($Number) && empty($Email) && empty($Phone) && empty($Address)) {
                        echo "Empty Fields";
                        exit();
                    } else {
                        // Check if the serial number is already taken
                        $sql_check_serial = "SELECT serialnumber FROM users WHERE serialnumber=? AND id != ?";
                        $stmt_check_serial = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt_check_serial, $sql_check_serial)) {
                            echo "SQL_Error";
                            exit();
                        } else {
                            mysqli_stmt_bind_param($stmt_check_serial, "di", $Number, $user_id);
                            mysqli_stmt_execute($stmt_check_serial);
                            $result_check_serial = mysqli_stmt_get_result($stmt_check_serial);
                            if (!mysqli_fetch_assoc($result_check_serial)) {
                                // Get the device department
                                $sql_device_dep = "SELECT device_dep FROM devices WHERE device_uid=?";
                                $stmt_device_dep = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($stmt_device_dep, $sql_device_dep)) {
                                    echo "SQL_Error";
                                    exit();
                                } else {
                                    mysqli_stmt_bind_param($stmt_device_dep, "s", $dev_uid);
                                    mysqli_stmt_execute($stmt_device_dep);
                                    $result_device_dep = mysqli_stmt_get_result($stmt_device_dep);
                                    if ($row_device_dep = mysqli_fetch_assoc($result_device_dep)) {
                                        $dev_name = $row_device_dep['device_dep'];
                                    } else {
                                        $dev_name = "All";
                                    }
                                }
                                // Update user information
                                $sql_update_user = "UPDATE users SET username=?, serialnumber=?, gender=?, email=?, phone_number=?, address=?, device_uid=?, device_dep=? WHERE id=?";
                                $stmt_update_user = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($stmt_update_user, $sql_update_user)) {
                                    echo "SQL_Error";
                                    exit();
                                } else {
                                    mysqli_stmt_bind_param($stmt_update_user, "ssssssssi", $Uname, $Number, $Gender, $Email, $Phone, $Address, $dev_uid, $dev_name, $user_id);
                                    mysqli_stmt_execute($stmt_update_user);
                                    echo "User updated successfully!";
                                    exit();
                                }
                            } else {
                                echo "The serial number is already taken!";
                                exit();
                            }
                        }
                    }
                }    
            } else {
                echo "There's no selected User to be updated!";
                exit();
            }
        }
    } else {
        echo "Some required fields are missing!";
        exit();
    }
}

// Select fingerprint 
if (isset($_GET['select'])) {
    if (isset($_GET['card_uid'])) {
        $card_uid = $_GET['card_uid'];
        $sql = "SELECT * FROM users WHERE card_uid=?";
        $stmt_select_fingerprint = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt_select_fingerprint, $sql)) {
            echo "SQL_Error_Select";
            exit();
        } else {
            mysqli_stmt_bind_param($stmt_select_fingerprint, "s", $card_uid);
            mysqli_stmt_execute($stmt_select_fingerprint);
            $result_select_fingerprint = mysqli_stmt_get_result($stmt_select_fingerprint);
            header('Content-Type: application/json');
            $data = array();
            while ($row = mysqli_fetch_assoc($result_select_fingerprint)) {
                $data[] = $row;
            }
            mysqli_stmt_close($stmt_select_fingerprint);
            mysqli_close($conn);
            print json_encode($data);
        }
    } else {
        echo "Card UID is missing!";
        exit();
    }
}

// Delete user 
if (isset($_POST['delete'])) {
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        if (empty($user_id)) {
            echo "There's no selected user to remove";
            exit();
        } else {
            $sql_delete_user = "DELETE FROM users WHERE id=?";
            $stmt_delete_user = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt_delete_user, $sql_delete_user)) {
                echo "SQL_Error_delete";
                exit();
            } else {
                mysqli_stmt_bind_param($stmt_delete_user, "i", $user_id);
                mysqli_stmt_execute($stmt_delete_user);
                echo "User deleted successfully!";
                exit();
            }
        }
    } else {
        echo "User ID is missing!";
        exit();
    }
}
?>
