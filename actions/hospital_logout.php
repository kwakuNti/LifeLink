<?php
session_start();
session_unset();
session_destroy();
header("Location: ../templates/hospital_login.php?status=success&message=Logged out successfully.");
exit();
?>
