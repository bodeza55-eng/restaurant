<?php
include("../config/db.php");

if(isset($_POST['food_id'])){

  $id = $_POST['food_id'];

  $q = mysqli_query($conn,"SELECT status FROM foods WHERE food_id='$id'");
  $row = mysqli_fetch_assoc($q);

  $newStatus = ($row['status']=="available") ? "out" : "available";

  mysqli_query($conn,
    "UPDATE foods SET status='$newStatus' WHERE food_id='$id'"
  );

  echo "success";
}
?>
