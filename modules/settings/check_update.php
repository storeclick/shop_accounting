<?php
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/update_checker.php';

// بررسی دسترسی مدیر
checkAdminAccess();

$message = '';
$messageType = '';

$updateChecker = new UpdateChecker();

if (isset($_POST['check_update'])) {
    $result = $updateChecker->checkForUpdates();
    
    if ($result['hasUpdate']) {
        $message = "نسخه جدید {$result['newVersion']} در تاریخ {$result['releaseDate']} منتشر شده است.";
        $messageType = 'info';
    } else {
        $message = isset($result['error']) 
            ? "خطا در بررسی بروزرسانی: {$result['error']}"
            : 'شما از آخرین نسخه استفاده می‌کنید.';
        $messageType = isset($result['error']) ? 'danger' : 'success';
    }
}

if (isset($_POST['install_update'])) {
    $result = $updateChecker->installUpdate();
    
    if ($result['success']) {
        $message = 'بروزرسانی با موفقیت نصب شد.';
        $messageType = 'success';
    } else {
        $message = "خطا در نصب بروزرسانی: {$result['error']}";
        $messageType = 'danger';
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">بررسی بروزرسانی</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <h5>نسخه فعلی: <?php echo APP_VERSION; ?></h5>
                        <p class="text-muted">آخرین بروزرسانی: <?php echo APP_LAST_UPDATE; ?></p>
                    </div>

                    <form method="post" class="mb-3">
                        <button type="submit" name="check_update" class="btn btn-primary w-100">
                            <i class="bi bi-cloud-download me-2"></i>
                            بررسی وجود نسخه جدید
                        </button>
                    </form>

                    <?php if (isset($result) && $result['hasUpdate']): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5>تغییرات نسخه <?php echo $result['newVersion']; ?>:</h5>
                                <ul class="list-unstyled">
                                    <?php foreach ($result['changes'] as $change): ?>
                                        <li><i class="bi bi-check2 me-2"></i><?php echo $change; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <form method="post">
                                    <button type="submit" name="install_update" class="btn btn-success w-100">
                                        <i class="bi bi-download me-2"></i>
                                        دانلود و نصب بروزرسانی
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-2"></i>راهنما:</h6>
                        <ul class="mb-0">
                            <li>قبل از نصب بروزرسانی، از اطلاعات خود پشتیبان تهیه کنید.</li>
                            <li>در طول فرآیند بروزرسانی صفحه را نبندید.</li>
                            <li>در صورت بروز مشکل با پشتیبانی تماس بگیرید.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>