<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'OrderController.php'; // Adjust path if necessary

$controller = new OrderController();
$controller->handlePayOSCancel();