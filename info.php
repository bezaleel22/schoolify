<?php
@mysqli_connect("schoolifydb-yzrofu", "root", "paxxw0rd@2791", "edusmsdb", 3306);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    echo "Connected to MySQL";
}
// phpinfo();
