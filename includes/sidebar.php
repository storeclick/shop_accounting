<?php
/**
 * منوی کناری برنامه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// تعریف منوهای اصلی برنامه
$menuItems = [
    [
        'title' => 'داشبورد',
        'icon' => 'bi bi-speedometer2',
        'url' => BASE_URL . '/dashboard.php'
    ],
    [
        'title' => 'محصولات',
        'icon' => 'bi bi-box',
        'children' => [
            [
                'title' => 'افزودن محصول',
                'icon' => 'bi bi-plus-lg',
                'url' => BASE_URL . '/modules/products/add.php',
                'permission' => 'products_add'
            ],
            [
                'title' => 'لیست محصولات',
                'icon' => 'bi bi-list',
                'url' => BASE_URL . '/modules/products/list.php',
                'permission' => 'products_view'
            ],
            [
                'title' => 'دسته‌بندی‌ها',
                'icon' => 'bi bi-diagram-3',
                'url' => BASE_URL . '/modules/products/categories.php',
                'permission' => 'products_categories'
            ],
            [
                'title' => 'انبارداری',
                'icon' => 'bi bi-box-seam',
                'url' => BASE_URL . '/modules/products/inventory.php',
                'permission' => 'products_inventory'
            ]
        ]
    ],
    [
        'title' => 'فروش',
        'icon' => 'bi bi-cart',
        'children' => [
            [
                'title' => 'فروش سریع',
                'icon' => 'bi bi-lightning',
                'url' => BASE_URL . '/modules/sales/quick.php',
                'permission' => 'sales_add'
            ],
            [
                'title' => 'فاکتور فروش',
                'icon' => 'bi bi-receipt',
                'url' => BASE_URL . '/modules/sales/invoice.php',
                'permission' => 'sales_add'
            ],
            [
                'title' => 'لیست فروش‌ها',
                'icon' => 'bi bi-list-check',
                'url' => BASE_URL . '/modules/sales/list.php',
                'permission' => 'sales_view'
            ]
        ]
    ],
    [
        'title' => 'گزارشات',
        'icon' => 'bi bi-graph-up',
        'children' => [
            [
                'title' => 'گزارش فروش',
                'icon' => 'bi bi-bar-chart',
                'url' => BASE_URL . '/modules/reports/sales.php',
                'permission' => 'reports_view'
            ],
            [
                'title' => 'گزارش موجودی',
                'icon' => 'bi bi-boxes',
                'url' => BASE_URL . '/modules/reports/inventory.php',
                'permission' => 'reports_view'
            ],
            [
                'title' => 'گزارش سود و زیان',
                'icon' => 'bi bi-piggy-bank',
                'url' => BASE_URL . '/modules/reports/profit.php',
                'permission' => 'reports_view'
            ]
        ]
    ],
    [
        'title' => 'تنظیمات',
        'icon' => 'bi bi-gear',
        'url' => BASE_URL . '/settings.php',
        'permission' => 'settings'
    ]
];

// تابع بررسی دسترسی به منو
function hasMenuAccess($permission) {
    if (empty($permission)) return true;
    
    if (!isset($_SESSION['user_role'])) return false;
    
    $role = $_SESSION['user_role'];
    $permissions = USER_ROLES[$role]['permissions'];
    
    return in_array('all', $permissions) || in_array($permission, $permissions);
}

// تابع نمایش منو
function renderMenuItem($item) {
    // بررسی دسترسی
    if (isset($item['permission']) && !hasMenuAccess($item['permission'])) {
        return '';
    }
    
    // آیا این آیتم منو زیرمنو دارد؟
    $hasChildren = isset($item['children']) && !empty($item['children']);
    
    // آیا این آیتم یا یکی از زیرمنوهایش فعال است؟
    $isActive = false;
    $currentUrl = $_SERVER['PHP_SELF'];
    
    if ($hasChildren) {
        foreach ($item['children'] as $child) {
            if (strpos($currentUrl, parse_url($child['url'], PHP_URL_PATH)) !== false) {
                $isActive = true;
                break;
            }
        }
    } else {
        $isActive = strpos($currentUrl, parse_url($item['url'], PHP_URL_PATH)) !== false;
    }
    
    $output = '';
    
    if ($hasChildren) {
        $output .= sprintf(
            '<li class="nav-item">
                <a class="nav-link %s" href="#menu-%s" data-bs-toggle="collapse" role="button">
                    <i class="%s"></i>
                    <span>%s</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse %s" id="menu-%s">
                    <ul class="nav nav-sm flex-column">',
            $isActive ? 'active' : '',
            md5($item['title']),
            $item['icon'],
            $item['title'],
            $isActive ? 'show' : '',
            md5($item['title'])
        );
        
        foreach ($item['children'] as $child) {
            $output .= renderMenuItem($child);
        }
        
        $output .= '</ul></div></li>';
        
    } else {
        $output .= sprintf(
            '<li class="nav-item">
                <a class="nav-link %s" href="%s">
                    <i class="%s"></i>
                    <span>%s</span>
                </a>
            </li>',
            $isActive ? 'active' : '',
            $item['url'],
            $item['icon'],
            $item['title']
        );
    }
    
    return $output;
}
?>

<!-- منوی کناری -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/dashboard.php" class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/images/logo-sm.png" alt="<?= SITE_TITLE ?>" height="30">
        </a>
        <button class="btn p-0 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div class="sidebar-body">
        <ul class="nav flex-column" id="sidebarMenu">
            <?php foreach ($menuItems as $item): ?>
                <?= renderMenuItem($item) ?>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <?php if ($hasUpdate): ?>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/update.php" class="btn btn-warning btn-sm w-100">
            <i class="bi bi-download"></i>
            بروزرسانی جدید
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- استایل منوی کناری -->
<style>
.sidebar {
    width: 250px;
    height: 100%;
    background: #f8f9fa;
    border-left: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #dee2e6;
}

.sidebar-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

.sidebar .nav-link {
    color: #333;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.sidebar .nav-link:hover {
    background: #e9ecef;
}

.sidebar .nav-link.active {
    color: #0d6efd;
    background: #e7f1ff;
}

.sidebar .nav-link i {
    width: 1.5rem;
    margin-left: 0.5rem;
    font-size: 1.1rem;
}

.sidebar .nav-link i.ms-auto {
    margin-left: 0;
    transition: transform 0.3s;
}

.sidebar .nav-link[aria-expanded="true"] i.ms-auto {
    transform: rotate(180deg);
}

.sidebar .nav-sm {
    padding-right: 2rem;
    font-size: 0.9rem;
}

.sidebar .nav-sm .nav-link {
    padding: 0.4rem 1rem;
}

@media (max-width: 992px) {
    .sidebar {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>

<script>
// مدیریت نمایش/مخفی کردن منو در موبایل
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth < 992) {
        document.body.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && 
                !e.target.matches('[data-bs-toggle="collapse"]')) {
                sidebar.classList.remove('show');
            }
        });
    }
});
</script>