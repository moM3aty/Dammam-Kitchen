<?php
require_once __DIR__ . '/includes/init.php';
auth_logout();
redirect(SITE_URL . '/admin/login.php');