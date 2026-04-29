<?php
// logout.php - Letakkan di ROOT folder
session_start();
session_destroy();
header("Location: login.php");
exit();
?>