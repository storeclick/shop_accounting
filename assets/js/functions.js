/**
 * توابع عمومی جاوااسکریپت
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// تبدیل اعداد به فرمت هزار رقمی
function formatPrice(price) {
    if (!price) return '۰';
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// تبدیل اعداد به فارسی
function toPersianNum(num) {
    if (!num) return '۰';
    const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/[0-9]/g, w => persian[w]);
}

// باز و بسته کردن منوی موبایل
function toggleMobileMenu() {
    document.querySelector('.mobile-menu').classList.toggle('active');
    document.querySelector('.mobile-menu-overlay').classList.toggle('active');
    document.body.classList.toggle('mobile-menu-open');
}

// بستن منوی موبایل با کلیک خارج از منو
document.addEventListener('click', function(e) {
    if (document.querySelector('.mobile-menu.active')) {
        if (!e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
            toggleMobileMenu();
        }
    }
});

// فرمت‌بندی خودکار فیلدهای قیمت
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.price-format').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            this.value = formatPrice(value);
        });
        
        // فرمت‌بندی مقدار اولیه
        if (input.value) {
            input.value = formatPrice(input.value.replace(/[^\d]/g, ''));
        }
    });
    
    // تبدیل اعداد به فارسی
    document.querySelectorAll('.persian-number').forEach(function(el) {
        el.textContent = toPersianNum(el.textContent);
    });
});

// اعتبارسنجی فرم‌ها
function validateForm(form) {
    let isValid = true;
    
    // بررسی فیلدهای اجباری
    form.querySelectorAll('[required]').forEach(function(input) {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            
            // نمایش پیام خطا
            let feedback = input.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'این فیلد الزامی است';
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    // بررسی قیمت
    let priceInputs = form.querySelectorAll('.price-format[required]');
    priceInputs.forEach(function(input) {
        let price = parseInt(input.value.replace(/[^\d]/g, '') || 0);
        if (price <= 0) {
            isValid = false;
            input.classList.add('is-invalid');
            
            let feedback = input.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'قیمت باید بزرگتر از صفر باشد';
        }
    });
    
    return isValid;
}
// تابع تأیید حذف
function confirmDelete(message) {
    return confirm(message || 'آیا از حذف این مورد اطمینان دارید؟');
}