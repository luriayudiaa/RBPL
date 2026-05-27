<?php
// logout.php - Letakkan di ROOT folder
session_start();
session_destroy();
header("Location: index.php");
exit();
?>
