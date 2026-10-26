<?php
session_start();
header('Location: ' . (isset($_SESSION['user_id']) ? 'panel.php' : 'login.php'));
exit;
