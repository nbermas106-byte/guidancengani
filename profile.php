<?php
require_once 'config.php';
require_login();
$u = current_user();
// Add the optional profile-picture field to older installations.
$column = db()->query("SHOW COLUMNS FROM users LIKE 'profile_picture'")->fetch();
if (!$column) db()->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL AFTER password");
$q = db()->prepare('SELECT id, full_name, email, role, password, profile_picture FROM users WHERE id = ?');
$q->execute([$u['id']]);
$profile = $q->fetch();
if (!$profile) { http_response_code(404); exit('Account not found.'); }
$_SESSION['user']['profile_picture'] = $profile['profile_picture'];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['save_account'])) {
        $name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($name === '') $errors[] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        $check = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $check->execute([$email, $u['id']]);
        if ($check->fetch()) $errors[] = 'That email address is already being used by another account.';
        $changePassword = ($newPassword !== '' || $confirmPassword !== '');
        if ($changePassword) {
            if (!password_verify($currentPassword, $profile['password'])) $errors[] = 'Enter your current password to change it.';
            if (strlen($newPassword) < 10) $errors[] = 'Your new password must contain at least 10 characters.';
            if ($newPassword !== $confirmPassword) $errors[] = 'The new password and confirmation do not match.';
        }
        if (!$errors) {
            if ($changePassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                db()->prepare('UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?')->execute([$name, $email, $hash, $u['id']]);
            } else {
                db()->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?')->execute([$name, $email, $u['id']]);
            }
            $_SESSION['user']['full_name'] = $name;
            $_SESSION['user']['email'] = $email;
            flash('success', 'Your account details have been updated.');
            redirect('profile.php');
        }
    } elseif (isset($_POST['remove_picture'])) {
        $old = $profile['profile_picture'];
        db()->prepare('UPDATE users SET profile_picture = NULL WHERE id = ?')->execute([$u['id']]);
        $_SESSION['user']['profile_picture'] = null;
        if ($old && str_starts_with($old, 'uploads/profiles/')) { $path = __DIR__ . '/' . $old; if (is_file($path)) unlink($path); }
        flash('success', 'Your profile picture has been removed.'); redirect('profile.php');
    } elseif (isset($_POST['upload_picture'])) {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Please choose an image to upload.';
        else {
            $file = $_FILES['profile_picture'];
            if ($file['size'] > 2 * 1024 * 1024) $errors[] = 'The image must be 2 MB or smaller.';
            $info = @getimagesize($file['tmp_name']); $mime = $info['mime'] ?? '';
            $types = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
            if (!$info || !isset($types[$mime])) $errors[] = 'Use a JPG, PNG, or WEBP image.';
            if (!$errors) {
                $dir = __DIR__ . '/uploads/profiles';
                if (!is_dir($dir) && !mkdir($dir, 0755, true)) $errors[] = 'The upload folder could not be created.';
                if (!$errors) {
                    $filename = bin2hex(random_bytes(18)) . '.' . $types[$mime]; $relative = 'uploads/profiles/' . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) $errors[] = 'The image could not be saved. Check folder permissions.';
                    else {
                        db()->prepare('UPDATE users SET profile_picture = ? WHERE id = ?')->execute([$relative, $u['id']]);
                        $old = $profile['profile_picture'];
                        if ($old && str_starts_with($old, 'uploads/profiles/')) { $oldPath = __DIR__ . '/' . $old; if (is_file($oldPath)) unlink($oldPath); }
                        $_SESSION['user']['profile_picture'] = $relative; flash('success', 'Your profile picture has been updated.'); redirect('profile.php');
                    }
                }
            }
        }
    }
}
$pageTitle = 'My Profile'; include 'header.php';
?>
<div class="row justify-content-center"><div class="col-lg-8"><div class="content-card profile-edit-card">
 <span class="eyebrow">ACCOUNT SETTINGS</span><h2 class="mt-2 mb-1">My Profile</h2><p class="text-secondary">Manage your name, email address, password, and profile picture.</p>
 <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
 <div class="profile-picture-preview"><?php if (!empty($profile['profile_picture'])): ?><img src="<?= e($profile['profile_picture']) ?>?v=<?= @filemtime(__DIR__ . '/' . $profile['profile_picture']) ?: time() ?>" alt="Your profile picture"><?php else: ?><span><?= e(strtoupper(substr($profile['full_name'],0,1))) ?></span><?php endif; ?></div>
 <div class="text-center mb-4"><strong><?= e($profile['full_name']) ?></strong><div class="text-secondary small"><?= e($profile['email']) ?> · <?= e(ucfirst($profile['role'])) ?></div></div>
 <form method="post" class="mb-4"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><h5>Personal information</h5><div class="mb-3"><label class="form-label fw-bold">Full name</label><input class="form-control" name="full_name" value="<?= e($profile['full_name']) ?>" maxlength="150" required></div><div class="mb-3"><label class="form-label fw-bold">Email address</label><input class="form-control" type="email" name="email" value="<?= e($profile['email']) ?>" maxlength="190" required></div><hr><h5>Change password <span class="text-secondary fw-normal small">(optional)</span></h5><p class="small text-secondary">Leave these fields blank if you do not want to change your password.</p><div class="mb-3"><label class="form-label">Current password</label><input class="form-control" type="password" name="current_password" autocomplete="current-password"></div><div class="mb-3"><label class="form-label">New password</label><input class="form-control" type="password" name="new_password" minlength="10" autocomplete="new-password"><div class="form-text">Use at least 10 characters.</div></div><div class="mb-3"><label class="form-label">Confirm new password</label><input class="form-control" type="password" name="confirm_password" minlength="10" autocomplete="new-password"></div><button class="btn btn-primary w-100" name="save_account" value="1">Save Account Details</button></form>
 <hr><h5>Profile picture</h5><form method="post" enctype="multipart/form-data" class="profile-upload-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="profile_picture" class="form-label fw-bold">Choose a new profile picture</label><input class="form-control" type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required><div class="form-text mb-3">JPG, PNG, or WEBP · Maximum file size: 2 MB.</div><button class="btn btn-outline-primary w-100" name="upload_picture" value="1" type="submit">Upload Profile Picture</button></form>
 <?php if (!empty($profile['profile_picture'])): ?><form method="post" class="mt-2" onsubmit="return confirm('Remove your current profile picture?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="btn btn-outline-secondary w-100" name="remove_picture" value="1" type="submit">Remove Current Picture</button></form><?php endif; ?>
 <a href="dashboard.php" class="btn btn-link w-100 mt-2">Back to dashboard</a>
</div></div></div>
<?php include 'footer.php'; ?>
