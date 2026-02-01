<?php
require $_SERVER['DOCUMENT_ROOT'].'/Mobilecare_monitoring/config.php';

session_start();
session_unset();
session_destroy();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

header('Location: '.BASE_URL.'Login/index.php');
exit;
?>
