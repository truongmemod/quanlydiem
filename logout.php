<?php
require_once 'config.php';

// Xóa toàn bộ session
session_destroy();

// Chuyển về trang login
header("Location: login.php");
exit;
