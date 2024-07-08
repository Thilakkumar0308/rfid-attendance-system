<div class="table-responsive-sm" style="max-height: 870px;"> 
    <table class="table">
        <thead class="table-primary">
            <tr>
                <th>Card UID</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Reg.No</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Date</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody class="table-secondary">
            <?php
            // Connect to database
            require 'connectDB.php';

            $sql = "SELECT * FROM users ORDER BY id DESC";
            $result = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($result, $sql)) {
                echo '<p class="error">SQL Error</p>';
            } else {
                mysqli_stmt_execute($result);
                $resultl = mysqli_stmt_get_result($result);
                if (mysqli_num_rows($resultl) > 0) {
                    while ($row = mysqli_fetch_assoc($resultl)) {
                        ?>
                        <tr>
                            <td><?php
                                if ($row['card_select'] == 1) {
                                    echo "<span><i class='glyphicon glyphicon-ok' title='The selected UID'></i></span>";
                                }
                                $card_uid = $row['card_uid'];
                                ?>
                                <form>
                                    <button type="button" class="select_btn" id="<?php echo $card_uid; ?>" title="select this UID"><?php echo $card_uid; ?></button>
                                </form>
                            </td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['gender']; ?></td>
                            <td><?php echo $row['serialnumber']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php
                                if (isset($row['phone'])) {
                                    echo $row['phone'];
                                } else {
                                    echo "N/A"; // Placeholder if phone number is not set
                                }
                                ?></td> <!-- Changed to access 'phone' key -->
                            <td><?php
                                if (isset($row['address'])) {
                                    echo $row['address'];
                                } else {
                                    echo "N/A"; // Placeholder if address is not set
                                }
                                ?></td> <!-- Added address column -->
                            <td><?php echo $row['user_date']; ?></td>
                            <td><?php echo ($row['device_dep'] == "0") ? "All" : $row['device_dep']; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="9">No records found</td></tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>
