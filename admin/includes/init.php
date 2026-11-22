<?php
// admin/includes/init.php — bootstrap لكل صفحات الأدمن
if (!defined('SK_ADMIN')) { define('SK_ADMIN', true); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

auth_session_start();