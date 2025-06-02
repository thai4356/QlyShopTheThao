<?php
require_once __DIR__ . '/../../model/admin/AdminUser.php';

$userModel = new AdminUser();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $users = $userModel->getAll();
        include __DIR__ . '/../../view/ViewAdmin/users.php';
        break;

    case 'delete':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            $userModel->delete($id);
        }
        header("Location: AdminUserController.php?action=list");
        exit;

    default:
        echo "Hành động không hợp lệ.";
        break;
}
