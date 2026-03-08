<?php
$host = getenv('MYSQL_HOST');
$dbname = getenv('MYSQL_DATABASE');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$port = getenv('MYSQL_PORT') ?: 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';
$editRecord = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name  = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role  = trim($_POST['role']);
        if ($name && $email) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $role]);
            $message = "✅ Record created successfully.";
        }
    }

    if ($action === 'update') {
        $id    = (int)$_POST['id'];
        $name  = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role  = trim($_POST['role']);
        if ($id && $name && $email) {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $id]);
            $message = "✏️ Record updated successfully.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    $message = "🗑️ Record deleted.";
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $editRecord = $stmt->fetch(PDO::FETCH_ASSOC);
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD App</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>⚡ User Manager</h1>
        <p class="subtitle">Simple PHP CRUD Application</p>
    </header>

    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <section class="form-section">
        <h2><?= $editRecord ? '✏️ Edit Record' : '➕ Add New Record' ?></h2>
        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="<?= $editRecord ? 'update' : 'create' ?>">
            <?php if ($editRecord): ?>
                <input type="hidden" name="id" value="<?= $editRecord['id'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Full name" required
                           value="<?= htmlspecialchars($editRecord['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" required
                           value="<?= htmlspecialchars($editRecord['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <?php foreach (['Admin','Editor','Viewer'] as $r): ?>
                            <option value="<?= $r ?>" <?= ($editRecord['role'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $editRecord ? 'Update Record' : 'Add Record' ?>
                </button>
                <?php if ($editRecord): ?>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="table-section">
        <h2>📋 All Records <span class="count"><?= count($users) ?></span></h2>
        <?php if (empty($users)): ?>
            <div class="empty">No records found. Add one above!</div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="badge badge-<?= strtolower($user['role']) ?>"><?= $user['role'] ?></span></td>
                        <td class="actions">
                            <a href="?edit=<?= $user['id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-delete"
                               onclick="return confirm('Delete this record?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</div>
</body>

</html>
