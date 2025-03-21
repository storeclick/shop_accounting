/**
 * تنظیمات و توابع عمومی پنل مدیریت
 */

// اعتبارسنجی فرم‌ها
(function() {
    'use strict';
    
    // اعتبارسنجی تمام فرم‌های دارای کلاس needs-validation
    document.querySelectorAll('.needs-validation').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // تبدیل خودکار اعداد به فرمت هزار رقمی
    document.querySelectorAll('.price-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = Number(value).toLocaleString();
        });
    });
})();

// نمایش/مخفی کردن منوی کناری در حالت موبایل
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.addEventListener('show.bs.offcanvas', function() {
            document.body.classList.add('sidebar-open');
        });
        sidebar.addEventListener('hide.bs.offcanvas', function() {
            document.body.classList.remove('sidebar-open');
        });
    }
});

// نمایش پیام‌های سیستم
function showMessage(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.content .container-fluid');
    if (container) {
        container.insertBefore(alert, container.firstChild);
        
        // حذف خودکار پیام بعد از 5 ثانیه
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

// تایید حذف
function confirmDelete(message = 'آیا از حذف این مورد اطمینان دارید؟') {
    return confirm(message);
}