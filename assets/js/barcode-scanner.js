/**
 * اسکنر بارکد با استفاده از دوربین
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

class BarcodeScanner {
    constructor(options = {}) {
        this.options = {
            // تنظیمات پیش‌فرض دوربین
            video: {
                width: { min: 640, ideal: 1280, max: 1920 },
                height: { min: 480, ideal: 720, max: 1080 },
                facingMode: "environment"
            },
            ...options
        };
        
        this.scanning = false;
        this.activeStream = null;
    }
    
    // شروع اسکن
    async start(callback) {
        try {
            // ایجاد مودال
            this.createModal();
            
            // دریافت دسترسی به دوربین
            const stream = await navigator.mediaDevices.getUserMedia({
                video: this.options.video
            });
            
            this.activeStream = stream;
            
            // نمایش تصویر دوربین
            const video = document.getElementById('barcode-scanner-video');
            video.srcObject = stream;
            video.play();
            
            // شروع اسکن
            this.scanning = true;
            this.scan(callback);
            
        } catch (error) {
            console.error('خطا در دسترسی به دوربین:', error);
            alert('خطا در دسترسی به دوربین. لطفاً مجوز دسترسی را تایید کنید.');
        }
    }
    
    // توقف اسکن
    stop() {
        this.scanning = false;
        
        if (this.activeStream) {
            this.activeStream.getTracks().forEach(track => track.stop());
            this.activeStream = null;
        }
        
        const modal = document.getElementById('barcode-scanner-modal');
        if (modal) {
            bootstrap.Modal.getInstance(modal).hide();
        }
    }
    
    // اسکن بارکد
    async scan(callback) {
        const video = document.getElementById('barcode-scanner-video');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const barcodeDetector = new BarcodeDetector({
            formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e']
        });
        
        const detect = async () => {
            if (!this.scanning) return;
            
            try {
                // گرفتن فریم از دوربین
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // تشخیص بارکد
                const barcodes = await barcodeDetector.detect(canvas);
                
                if (barcodes.length > 0) {
                    // بارکد پیدا شد
                    const barcode = barcodes[0];
                    
                    // صدای بیپ
                    this.playBeep();
                    
                    // توقف اسکن
                    this.stop();
                    
                    // فراخوانی callback
                    if (typeof callback === 'function') {
                        callback(barcode.rawValue);
                    }
                    
                    return;
                }
                
                // ادامه اسکن
                requestAnimationFrame(detect);
                
            } catch (error) {
                console.error('خطا در اسکن بارکد:', error);
                this.stop();
                alert('خطا در اسکن بارکد. لطفاً مجدداً تلاش کنید.');
            }
        };
        
        // شروع اسکن
        detect();
    }
    
    // ایجاد مودال اسکنر
    createModal() {
        // حذف مودال قبلی اگر وجود دارد
        const existingModal = document.getElementById('barcode-scanner-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // ایجاد مودال جدید
        const modal = document.createElement('div');
        modal.id = 'barcode-scanner-modal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">اسکن بارکد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="barcode-scanner-container">
                            <video id="barcode-scanner-video" playsinline></video>
                            <div class="barcode-scanner-overlay">
                                <div class="barcode-scanner-line"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // نمایش مودال
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // وقتی مودال بسته شد، اسکن متوقف شود
        modal.addEventListener('hidden.bs.modal', () => {
            this.stop();
        });
    }
    
    // پخش صدای بیپ
    playBeep() {
        const audio = new Audio('data:audio/wav;base64,UklGRl9YQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAAAABqYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYmJiYg==');
        audio.play();
    }
}

// اضافه کردن استایل‌های مورد نیاز
const style = document.createElement('style');
style.textContent = `
    .barcode-scanner-container {
        position: relative;
        width: 100%;
        height: 300px;
        overflow: hidden;
        background: #000;
    }
    
    #barcode-scanner-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .barcode-scanner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .barcode-scanner-line {
        width: 80%;
        height: 2px;
        background: red;
        box-shadow: 0 0 8px red;
        animation: scan 2s linear infinite;
    }
    
    @keyframes scan {
        0% {
            transform: translateY(-50px);
        }
        50% {
            transform: translateY(50px);
        }
        100% {
            transform: translateY(-50px);
        }
    }
`;
document.head.appendChild(style);

// تابع راه‌اندازی اسکنر
function initBarcodeScanner(inputElement) {
    // بررسی پشتیبانی مرورگر از BarcodeDetector
    if (!('BarcodeDetector' in window)) {
        console.warn('مرورگر شما از اسکن بارکد پشتیبانی نمی‌کند.');
        return;
    }
    
    const scanner = new BarcodeScanner();
    
    // اضافه کردن دکمه اسکن
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-outline-secondary';
    button.innerHTML = '<i class="bi bi-upc-scan"></i>';
    button.title = 'اسکن بارکد';
    
    button.addEventListener('click', () => {
        scanner.start(barcode => {
            if (inputElement) {
                inputElement.value = barcode;
                // فعال کردن رویداد input برای اعمال validation
                inputElement.dispatchEvent(new Event('input'));
            }
        });
    });
    
    // جایگزینی دکمه قبلی
    const oldButton = inputElement.parentElement.querySelector('button');
    if (oldButton) {
        oldButton.parentElement.replaceChild(button, oldButton);
    }
}
