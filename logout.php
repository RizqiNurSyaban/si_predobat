<?php
// logout.php
require_once __DIR__ . '/config/auth.php';
logout_user();
redirect('login.php');

