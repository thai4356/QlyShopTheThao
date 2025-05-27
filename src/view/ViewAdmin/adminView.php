<?php
session_start();

if ( $_SESSION['role'] != 2) {
    // Chặn quyền, có thể redirect hoặc báo lỗi
    header("Location: /QlyShopTheThao/src/view/access-denied.php"); // 👉 Chuyển hướng ra trang thông báo
    exit(); // 🚨 Bắt buộc phải dừng script ngay sau header
}

$conn = require_once "../../model/Connect.php";

$limit = 5; // Số lượng user/admin hiển thị trên mỗi trang
// Xác định trang hiện tại
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Tính toán vị trí bắt đầu lấy dữ liệu
$start = ($page - 1) * $limit;

// Lấy ID và username từ session
$currentUserId = $_SESSION['user_id'];
$currentAdminEmail = $_SESSION['username']; // Lấy email của admin đang đăng nhập

// Xóa user
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId !== $currentUserId) { // Không được tự xóa mình
        $stmt = $conn->prepare("DELETE FROM username WHERE id = ?");
        $stmt->execute([$deleteId]);
    }
}

// Cập nhật role (CHẶN HẲN SERVER-SIDE)
if (isset($_POST['update_role'])) {
    $updateId = (int)$_POST['user_id'];
    $newRole = (int)$_POST['roleid'];

    if ($updateId === $currentUserId) {
        die(' Không thể thay đổi quyền của chính mình!');
    }

    $stmt = $conn->prepare("UPDATE username SET roleid = ? WHERE id = ?");
    $stmt->execute([$newRole, $updateId]);
}

// Tìm kiếm và load danh sách admins
$searchAdmin = isset($_GET['search_admin']) ? trim($_GET['search_admin']) : '';
$adminWhereClause = "WHERE u.roleid = 2 AND u.id != :currentUserId";
if (!empty($searchAdmin)) {
    $adminWhereClause .= " AND u.email LIKE :searchAdmin";
}

$stmt = $conn->prepare("
    SELECT u.id, u.email, r.name as role_name, u.is_verified, u.created_at
    FROM username u
    JOIN role r ON u.roleid = r.id
    $adminWhereClause
    LIMIT :start, :limit
");
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
if (!empty($searchAdmin)) {
    $stmt->bindValue(':searchAdmin', '%' . $searchAdmin . '%', PDO::PARAM_STR);
}
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tổng số lượng admins sau khi tìm kiếm
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM username u
    WHERE u.roleid = 2 AND u.id != :currentUserId
    " . (!empty($searchAdmin) ? "AND u.email LIKE :searchAdmin" : "")
);
$stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
if (!empty($searchAdmin)) {
    $stmt->bindValue(':searchAdmin', '%' . $searchAdmin . '%', PDO::PARAM_STR);
}
$stmt->execute();
$totalAdmins = $stmt->fetchColumn();
$totalPagesAdmins = ceil($totalAdmins / $limit);

// Tìm kiếm và load danh sách users
$searchUser = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';
$userWhereClause = "WHERE u.roleid = 1 AND u.id != :currentUserId";
if (!empty($searchUser)) {
    $userWhereClause .= " AND u.email LIKE :searchUser";
}

$stmt = $conn->prepare("
    SELECT u.id, u.email, r.name as role_name, u.is_verified, u.created_at
    FROM username u
    JOIN role r ON u.roleid = r.id
    $userWhereClause
    LIMIT :start, :limit
");
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
if (!empty($searchUser)) {
    $stmt->bindValue(':searchUser', '%' . $searchUser . '%', PDO::PARAM_STR);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tổng số lượng users sau khi tìm kiếm
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM username u
    WHERE u.roleid = 1 AND u.id != :currentUserId
    " . (!empty($searchUser) ? "AND u.email LIKE :searchUser" : "")
);
$stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
if (!empty($searchUser)) {
    $stmt->bindValue(':searchUser', '%' . $searchUser . '%', PDO::PARAM_STR);
}
$stmt->execute();
$totalUsers = $stmt->fetchColumn();
$totalPagesUsers = ceil($totalUsers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quản lý User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-center">👥 Quản lý User</h2>

    <div class="alert alert-info" role="alert">
        Xin chào, Admin: <strong><?= htmlspecialchars($currentAdminEmail) ?></strong>
    </div>

    <h4 class="mt-4">Tìm kiếm Admins</h4>
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Nhập email admin để tìm..." name="search_admin" value="<?= htmlspecialchars(isset($_GET['search_admin']) ? $_GET['search_admin'] : '') ?>">
            <button class="btn btn-outline-primary" type="submit">Tìm kiếm</button>
        </div>
    </form>

    <h4 class="mt-4">Danh sách Admins</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Đã xác thực?</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars($admin['id']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['role_name']) ?></td>
                    <td><?= $admin['is_verified'] ? '✔️' : '❌' ?></td>
                    <td><?= htmlspecialchars($admin['created_at']) ?></td>
                    <td>
                        Không thể sửa quyền Admin
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Admin pagination">
        <ul class="pagination justify-content-center mt-3">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= isset($_GET['search_admin']) ? '&search_admin=' . htmlspecialchars($_GET['search_admin']) : '' ?>">Trước</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesAdmins; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['search_admin']) ? '&search_admin=' . htmlspecialchars($_GET['search_admin']) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPagesAdmins): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= isset($_GET['search_admin']) ? '&search_admin=' . htmlspecialchars($_GET['search_admin']) : '' ?>">Sau</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <h4 class="mt-4">Tìm kiếm Users</h4>
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Nhập email user để tìm..." name="search_user" value="<?= htmlspecialchars(isset($_GET['search_user']) ? $_GET['search_user'] : '') ?>">
            <button class="btn btn-outline-success" type="submit">Tìm kiếm</button>
        </div>
    </form>

    <h4 class="mt-4">Danh sách Users</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Đã xác thực?</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php if ($user['id'] !== $currentUserId): ?>
                            <form method="post" class="d-flex">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="roleid" class="form-control form-control-sm" style="width: 120px;" required>
                                    <option value="1" <?= $user['role_name'] == 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="2" <?= $user['role_name'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_role" class="btn btn-sm btn-primary ms-2">Cập nhật</button>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($user['role_name']) ?> 🔒
                        <?php endif; ?>
                    </td>
                    <td><?= $user['is_verified'] ? '✔️' : '❌' ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <?php if ($user['id'] !== $currentUserId): ?>
                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa user này?')">🗑 Xóa</a>
                        <?php else: ?>
                            Không thể thay đổi chính mình
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="User pagination">
        <ul class="pagination justify-content-center mt-3">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= isset($_GET['search_user']) ? '&search_user=' . htmlspecialchars($_GET['search_user']) : '' ?>">Trước</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPagesUsers; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['search_user']) ? '&search_user=' . htmlspecialchars($_GET['search_user']) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPagesUsers): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= isset($_GET['search_user']) ? '&search_user=' . htmlspecialchars($_GET['search_user']) : '' ?>">Sau</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
</body>
</html>