<?php
/**
 * تنظیمات TinyMCE
 * آخرین بروزرسانی: 1402/12/29
 */

// کلید API تاینی‌ام‌سی‌ای
define('TINYMCE_API_KEY', '0emz75b66b3lrmxv8eoj3jhz6gakhqli6gxtlkqrlh93r2sn'); // کلید API خود را اینجا قرار دهید

// تنظیمات پیش‌فرض ویرایشگر
$default_tinymce_settings = [
    'selector' => '.tinymce', // کلاس پیش‌فرض برای فعال‌سازی
    'language' => 'fa',
    'directionality' => 'rtl',
    'height' => 300,
    'menubar' => true,
    'branding' => false,
    'plugins' => [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    'toolbar' => 'undo redo | blocks | bold italic backcolor | ' .
                'alignright aligncenter alignleft alignjustify | ' .
                'bullist numlist outdent indent | removeformat | help',
    'content_style' => 'body { font-family: Vazirmatn, Tahoma, Arial; }',
    'images_upload_url' => BASE_URL . '/includes/upload_tinymce.php',
    'images_upload_base_path' => '/uploads/editor',
    'images_upload_credentials' => true,
];