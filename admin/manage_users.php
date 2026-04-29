<?php
session_start();
require_once "db.php";
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = intval($_POST['user_id']);
    if (isset($_POST['block'])) {
        // Block user: e.g., set role to 'blocked'
        $conn->query("UPDATE users SET role='blocked' WHERE id=$uid");
    } elseif (isset($_POST['unblock'])) {
        $conn->query("UPDATE users SET role='user' WHERE id=$uid");
    } elseif (isset($_POST['promote'])) {
        $conn->query("UPDATE users SET role='admin' WHERE id=$uid");
    } elseif (isset($_POST['delete'])) {
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    $msg = "Action performed.";
}

$users = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2>Manage Users</h2>
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <?php if ($u['role'] !== 'admin'): ?>
                                <?php if ($u['role'] !== 'blocked'): ?>
                                    <button name="block" class="btn btn-warning btn-sm">Block</button>
                                <?php else: ?>
                                    <button name="unblock" class="btn btn-success btn-sm">Unblock</button>
                                <?php endif; ?>
                                <button name="promote" class="btn btn-info btn-sm">Promote to Admin</button>
                                <button name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')">Delete</button>
                            <?php else: ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>