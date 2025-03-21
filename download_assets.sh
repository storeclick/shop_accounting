#!/bin/bash

# ایجاد ساختار پوشه‌ها
mkdir -p assets/css
mkdir -p assets/js
mkdir -p assets/fonts/iran-sans

# دانلود بوت‌استرپ
wget https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css -O assets/css/bootstrap.rtl.min.css
wget https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js -O assets/js/bootstrap.bundle.min.js

# ایجاد فایل‌های سفارشی
touch assets/css/custom.css
touch assets/js/custom.js

echo "فایل‌های مورد نیاز با موفقیت دانلود شدند."