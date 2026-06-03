<?php
date_default_timezone_set('Asia/Bangkok');
$conn = mysqli_connect("localhost", "root", "", "restaurant_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

