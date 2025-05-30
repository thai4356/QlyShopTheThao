<?php
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "<br>";
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    echo "HTTP_X_FORWARDED_FOR: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . "<br>";
} else {
    echo "HTTP_X_FORWARDED_FOR: Not set<br>";
}
?>