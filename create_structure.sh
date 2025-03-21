#!/bin/bash  

# ایجاد پوشه‌ها  
mkdir -p shop_accounting/assets/css  
mkdir -p shop_accounting/assets/js  
mkdir -p shop_accounting/assets/images  
mkdir -p shop_accounting/config  
mkdir -p shop_accounting/includes  
mkdir -p shop_accounting/modules/products  
mkdir -p shop_accounting/modules/inventory  
mkdir -p shop_accounting/modules/sales  
mkdir -p shop_accounting/vendor  

# ایجاد فایل‌ها  
touch shop_accounting/config/database.php  
touch shop_accounting/includes/functions.php  
touch shop_accounting/includes/header.php  
touch shop_accounting/includes/footer.php  
touch shop_accounting/modules/products/add.php  
touch shop_accounting/modules/products/list.php  
touch shop_accounting/modules/products/edit.php  
touch shop_accounting/modules/inventory/stock.php  
touch shop_accounting/modules/inventory/transactions.php  
touch shop_accounting/modules/sales/quick_sale.php  
touch shop_accounting/modules/sales/invoice.php  
touch shop_accounting/modules/sales/invoice_list.php  
touch shop_accounting/.htaccess  
touch shop_accounting/composer.json  
touch shop_accounting/index.php  

echo "ساختار فایل‌ها و پوشه‌ها با موفقیت ایجاد شد."  