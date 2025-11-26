<?php
// File: src/controller/stripe_cancel_handler.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'OrderController.php';

// Tải biến môi trường
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$ctrl = new OrderController();
$ctrl->handleStripeCancel();
?>