<?php
/**
 * Created by PhpStorm.
 * Time: 1:38
 */
//phpinfo();
set_time_limit(3000);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    require_once('xls_win.php');
} else {
    require_once('xls_linux.php');
}

