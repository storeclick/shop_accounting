<?php
/**
 * آپلود تصاویر TinyMCE
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/tinymce.php';

// بررسی درخواست آپلود
if (!isset($_FILES['file']['name'])) {
    die(json_encode(['error' => 'فایلی برای آپلود یافت نشد']));
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// بررسی خطاهای آپلود
if ($fileError !== 0) {
    die(json_encode(['error' => 'خطا در آپلود فایل']));
}

// بررسی سایز فایل
if ($fileSize > MAX_FILE_SIZE) {
    die(json_encode(['error' => 'سایز فایل بیشتر از حد مجاز است']));
}

// بررسی پسوند فایل
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
    die(json_encode(['error' => 'پسوند فایل مجاز نیست']));
}

// ایجاد نام یکتا برای فایل
$newFileName = uniqid('img_') . '.' . $fileExt;
$uploadPath = UPLOAD_PATH . '/editor/' . date('Y/m/');

// ایجاد پوشه در صورت عدم وجود
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

// آپلود فایل
$destination = $uploadPath . $newFileName;
if (move_uploaded_file($fileTmpName, $destination)) {
    $location = str_replace(BASE_PATH, BASE_URL, $destination);
    echo json_encode(['location' => $location]);
} else {
    echo json_encode(['error' => 'خطا در ذخیره فایل']);
}