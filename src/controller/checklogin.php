<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../ViewUser/Index.php");
    exit;
}
