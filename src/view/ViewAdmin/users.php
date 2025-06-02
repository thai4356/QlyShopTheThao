<?php if (!isset($users) || !is_array($users)): ?>
    <p>Dữ liệu không tồn tại. Vui lòng truy cập đúng qua controller.</p>
<?php else: ?>
    <h2>Danh sách người dùng</h2>
    <table border="1" cellpadding="8">
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Xác thực</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                <td><?= $u['is_verified'] ? '✅' : '❌' ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td>
                    <a href="../../controller/admin/AdminUserController.php?action=delete&id=<?= $u['id'] ?>"
                       onclick="return confirm('Xóa người dùng này?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>