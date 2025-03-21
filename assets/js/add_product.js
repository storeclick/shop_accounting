/**
 * اسکریپت‌های مربوط به صفحه افزودن محصول
 * تاریخ آخرین ویرایش: 1402/12/29
 */

// تولید کد محصول
function generateProductCode() {
    const timestamp = Date.now().toString().slice(-6);
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    document.getElementById('product_code').value = `PRD-${timestamp}${random}`;
}

// جستجوی دسته‌بندی
function setupCategorySearch(inputId, suggestionsId, hiddenInputId, parentId = null) {
    const input = $(`#${inputId}`);
    const suggestions = $(`#${suggestionsId}`);
    const hiddenInput = $(`#${hiddenInputId}`);
    let searchTimeout;

    input.on('input', function() {
        const query = $(this).val();
        
        // پاک کردن تایمر قبلی
        clearTimeout(searchTimeout);
        
        // اگر فیلد خالی شد
        if (query.length === 0) {
            suggestions.hide();
            hiddenInput.val('');
            return;
        }

        // تاخیر در جستجو برای جلوگیری از درخواست‌های مکرر
        searchTimeout = setTimeout(() => {
            if (query.length > 1) {
                $.ajax({
                    url: BASE_URL + '/modules/products/process/search_category.php',
                    method: 'GET',
                    data: { 
                        q: query,
                        parent_id: parentId ? $(`#${parentId}`).val() : ''
                    },
                    dataType: 'json',
                    success: function(categories) {
                        suggestions.empty();
                        
                        if (categories.length > 0) {
                            const ul = $('<ul class="list-unstyled mb-0">');
                            categories.forEach(category => {
                                ul.append(
                                    $('<li class="p-2 hover-bg-light cursor-pointer">')
                                        .text(category.category_name)
                                        .data('id', category.id)
                                        .click(function() {
                                            input.val(category.category_name);
                                            hiddenInput.val(category.id);
                                            suggestions.hide();
                                            
                                            // اگر دسته اصلی تغییر کرد
                                            if (inputId === 'search_category') {
                                                $('#search_subcategory').val('');
                                                $('#subcategory_id').val('');
                                            }
                                        })
                                );
                            });
                            suggestions.html(ul).show();
                        } else {
                            suggestions.html('<div class="p-2">موردی یافت نشد</div>').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('خطا در جستجوی دسته‌بندی:', error);
                        suggestions.html('<div class="p-2 text-danger">خطا در جستجو</div>').show();
                    }
                });
            } else {
                suggestions.hide();
            }
        }, 300); // تاخیر 300 میلی‌ثانیه‌ای
    });

    // بستن لیست پیشنهادها با کلیک خارج از آن
    $(document).click(function(event) {
        if (!$(event.target).closest(`#${inputId}, #${suggestionsId}`).length) {
            suggestions.hide();
        }
    });

    // جلوگیری از ارسال فرم با زدن دکمه Enter در فیلد جستجو
    input.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
        }
    });
}

// مدیریت آپلود تصاویر
function setupImageUpload() {
    // پیش‌نمایش تصویر اصلی
    document.getElementById('main_image').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('main_image_preview').src = event.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // پیش‌نمایش گالری تصاویر
    document.getElementById('gallery_images').addEventListener('change', function(e) {
        const container = document.getElementById('gallery_container');
        const files = Array.from(e.target.files);
        
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'gallery-item';
                div.innerHTML = `
                    <img src="${event.target.result}" alt="تصویر گالری">
                    <span class="remove-image">&times;</span>
                `;
                container.insertBefore(div, document.querySelector('.image-preview'));

                // اضافه کردن رویداد حذف تصویر
                div.querySelector('.remove-image').addEventListener('click', function() {
                    div.remove();
                });
            };
            reader.readAsDataURL(file);
        });
    });
}

// اعتبارسنجی فرم
function validateForm() {
    let isValid = true;
    const requiredFields = document.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // بررسی قیمت‌ها
    const purchasePrice = parseInt(document.getElementById('purchase_price').value) || 0;
    const salePrice = parseInt(document.getElementById('sale_price').value) || 0;
    
    if (salePrice < purchasePrice) {
        alert('قیمت فروش نمی‌تواند کمتر از قیمت خرید باشد!');
        document.getElementById('sale_price').classList.add('is-invalid');
        isValid = false;
    }

    return isValid;
}

// راه‌اندازی صفحه
$(document).ready(function() {
    // تنظیم متغیر BASE_URL
    if (typeof BASE_URL === 'undefined') {
        BASE_URL = window.location.origin + '/shop_accounting';
    }

    // راه‌اندازی جستجوی دسته‌بندی‌ها
    setupCategorySearch('search_category', 'category_suggestions', 'category_id');
    setupCategorySearch('search_subcategory', 'subcategory_suggestions', 'subcategory_id', 'category_id');
    
    // راه‌اندازی آپلود تصاویر
    setupImageUpload();

    // اعتبارسنجی فرم قبل از ارسال
    $('#add-product-form').on('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            const formData = new FormData(this);
            
            $.ajax({
                url: BASE_URL + '/modules/products/process/add_product.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('محصول با موفقیت ثبت شد.');
                            window.location.href = BASE_URL + '/modules/products/list.php';
                        } else {
                            alert('خطا در ثبت محصول: ' + result.message);
                        }
                    } catch (e) {
                        alert('خطا در پردازش پاسخ سرور');
                        console.error(e);
                    }
                },
                error: function() {
                    alert('خطا در ارتباط با سرور');
                }
            });
        }
    });

    // نمایش پیش‌نمایش برای فیلدهای عددی
    $('input[type="number"]').on('input', function() {
        const value = parseInt($(this).val()) || 0;
        const formattedValue = new Intl.NumberFormat('fa-IR').format(value);
        $(this).attr('title', formattedValue + ' تومان');
    });

    // پاک کردن کلاس is-invalid هنگام تغییر مقدار فیلد
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // فعال‌سازی ویرایشگر متن
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#description, #specifications',
            directionality: 'rtl',
            language: 'fa',
            plugins: 'lists link image table',
            toolbar: 'undo redo | formatselect | bold italic | alignright aligncenter alignleft | bullist numlist | link image | table',
            height: 300
        });
    }
});