/**
 * فایل جاوااسکریپت اختصاصی برنامه
 */

$(document).ready(function() {
    // فرمت کردن اعداد به فارسی
    function formatNumber(num) {
        return new Intl.NumberFormat('fa-IR').format(num);
    }

    // تبدیل اعداد انگلیسی به فارسی
    function toFaDigits(num) {
        if (!num) return '';
        return num.toString().replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    }

    // فرمت کردن همه اعداد در صفحه
    $('.number-format').each(function() {
        var num = $(this).text();
        $(this).text(formatNumber(num));
    });

    // نمایش پیام‌های موفقیت و خطا
    function showMessage(message, type = 'success') {
        const alert = $('<div>')
            .addClass(`alert alert-${type} alert-dismissible fade show`)
            .html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `);
        
        $('#messages').append(alert);
        
        // حذف خودکار پیام بعد از 5 ثانیه
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    // تایید حذف
    $('.delete-confirm').click(function(e) {
        if (!confirm('آیا از حذف این مورد اطمینان دارید؟')) {
            e.preventDefault();
        }
    });

    // آپلود تصویر
    $('.custom-file-input').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
        
        // نمایش پیش‌نمایش تصویر
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // انتخاب خودکار محتوای فیلدهای عددی
    $('.number-only').focus(function() {
        $(this).select();
    });

    // فقط اجازه ورود اعداد
    $('.number-only').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // محاسبه خودکار قیمت فروش
    $('#buyPrice').on('input', function() {
        var buyPrice = parseInt($(this).val().replace(/,/g, '')) || 0;
        var profit = Math.ceil(buyPrice * 0.2); // 20 درصد سود
        var sellPrice = buyPrice + profit;
        $('#sellPrice').val(formatNumber(sellPrice));
    });

    // جستجوی آنی در جداول
    $('#tableSearch').on('input', function() {
        var value = $(this).val().toLowerCase();
        $('#dataTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // مرتب‌سازی جداول
    $('.sortable').click(function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tr:gt(0)').toArray().sort(comparator($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
    });
});