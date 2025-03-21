<?php
class UpdateChecker {
    private $currentVersion;
    private $updateServer;
    private $updateInfo;
    
    public function __construct() {
        $this->currentVersion = APP_VERSION;
        $this->updateServer = UPDATE_SERVER;
    }
    
    /**
     * بررسی وجود نسخه جدید
     */
    public function checkForUpdates() {
        try {
            $ch = curl_init($this->updateServer . '/version.json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            
            if ($result === false) {
                throw new Exception('خطا در ارتباط با سرور بروزرسانی');
            }
            
            $this->updateInfo = json_decode($result, true);
            
            if (version_compare($this->updateInfo['version'], $this->currentVersion, '>')) {
                return [
                    'hasUpdate' => true,
                    'newVersion' => $this->updateInfo['version'],
                    'releaseDate' => $this->updateInfo['releaseDate'],
                    'changes' => $this->updateInfo['changes'],
                    'downloadUrl' => $this->updateInfo['downloadUrl']
                ];
            }
            
            return ['hasUpdate' => false];
            
        } catch (Exception $e) {
            error_log('خطا در بررسی بروزرسانی: ' . $e->getMessage());
            return [
                'hasUpdate' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * دانلود و نصب بروزرسانی
     */
    public function installUpdate() {
        try {
            // ایجاد پشتیبان از فایل‌های فعلی
            $this->createBackup();
            
            // دانلود فایل بروزرسانی
            $updateFile = $this->downloadUpdate();
            
            // استخراج و نصب فایل‌ها
            $this->extractAndInstall($updateFile);
            
            // بروزرسانی پایگاه داده
            $this->updateDatabase();
            
            return [
                'success' => true,
                'message' => 'بروزرسانی با موفقیت انجام شد'
            ];
            
        } catch (Exception $e) {
            error_log('خطا در نصب بروزرسانی: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * تهیه نسخه پشتیبان
     */
    private function createBackup() {
        $backupDir = ROOT_PATH . '/backups/' . date('Y-m-d_H-i-s');
        if (!mkdir($backupDir, 0755, true)) {
            throw new Exception('خطا در ایجاد پوشه پشتیبان');
        }
        
        // کپی فایل‌های برنامه
        $this->copyDirectory(ROOT_PATH, $backupDir, ['/backups', '/vendor', '/tmp']);
        
        // پشتیبان از دیتابیس
        $this->backupDatabase($backupDir . '/database.sql');
    }
    
    /**
     * دانلود فایل بروزرسانی
     */
    private function downloadUpdate() {
        $tmpFile = ROOT_PATH . '/tmp/update.zip';
        $fp = fopen($tmpFile, 'w+');
        
        $ch = curl_init($this->updateInfo['downloadUrl']);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if (curl_exec($ch) === false) {
            throw new Exception('خطا در دانلود فایل بروزرسانی');
        }
        
        curl_close($ch);
        fclose($fp);
        
        return $tmpFile;
    }
    
    /**
     * استخراج و نصب فایل‌های جدید
     */
    private function extractAndInstall($updateFile) {
        $zip = new ZipArchive;
        if ($zip->open($updateFile) !== true) {
            throw new Exception('خطا در باز کردن فایل بروزرسانی');
        }
        
        $zip->extractTo(ROOT_PATH . '/tmp/update');
        $zip->close();
        
        // کپی فایل‌های جدید
        $this->copyDirectory(
            ROOT_PATH . '/tmp/update', 
            ROOT_PATH,
            ['/config/config.php', '/config/db.php']
        );
        
        // پاکسازی فایل‌های موقت
        $this->cleanupTempFiles();
    }
    
    /**
     * بروزرسانی پایگاه داده
     */
    private function updateDatabase() {
        $updateSqlFile = ROOT_PATH . '/tmp/update/database/update.sql';
        if (file_exists($updateSqlFile)) {
            $sql = file_get_contents($updateSqlFile);
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            global $pdo;
            foreach ($queries as $query) {
                $pdo->exec($query);
            }
        }
    }
    
    /**
     * کپی دایرکتوری با قابلیت استثنا
     */
    private function copyDirectory($source, $dest, $excludes = []) {
        $dir = opendir($source);
        @mkdir($dest);
        
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $sourcePath = $source . '/' . $file;
                $destPath = $dest . '/' . $file;
                
                // بررسی استثناها
                $excluded = false;
                foreach ($excludes as $exclude) {
                    if (strpos($sourcePath, $exclude) !== false) {
                        $excluded = true;
                        break;
                    }
                }
                
                if (!$excluded) {
                    if (is_dir($sourcePath)) {
                        $this->copyDirectory($sourcePath, $destPath, $excludes);
                    } else {
                        copy($sourcePath, $destPath);
                    }
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * پاکسازی فایل‌های موقت
     */
    private function cleanupTempFiles() {
        $this->removeDirectory(ROOT_PATH . '/tmp/update');
        @unlink(ROOT_PATH . '/tmp/update.zip');
    }
    
    /**
     * حذف کامل یک دایرکتوری
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    $this->removeDirectory($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}