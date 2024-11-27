<?php
@mysqli_connect("postgres", "schoolify", "paxxw0rd@2791", "schoolifydb", 3306);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    echo "Connected to MySQL";
}
// phpinfo();
