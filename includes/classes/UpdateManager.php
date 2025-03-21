<?php
/**
 * کلاس مدیریت بروزرسانی برنامه
 * تاریخ ایجاد: ۱۴۰۲/۱۲/۲۹
 */

class UpdateManager {
    private $currentVersion;
    private $updateServer;
    private $tempPath;
    private $backupPath;
    
    public function __construct() {
        $this->currentVersion = APP_VERSION;
        $this->updateServer = UPDATE_SERVER;
        $this->tempPath = UPDATE_TEMP_PATH;
        $this->backupPath = BACKUP_PATH;
        
        // ساخت پوشه‌های مورد نیاز
        if (!file_exists($this->tempPath)) {
            mkdir($this->tempPath, 0777, true);
        }
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0777, true);
        }
    }
    
    /**
     * بررسی وجود نسخه جدید
     */
    public function checkForUpdates() {
        try {
            $ch = curl_init($this->updateServer . '/check.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'version' => $this->currentVersion,
                'site' => $_SERVER['HTTP_HOST']
            ]);
            
            $result = curl_exec($ch);
            curl_close($ch);
            
            if ($result === false) {
                throw new Exception('خطا در ارتباط با سرور بروزرسانی');
            }
            
            $updateInfo = json_decode($result, true);
            return $updateInfo;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * دانلود فایل بروزرسانی
     */
    public function downloadUpdate($version) {
        try {
            $downloadUrl = $this->updateServer . '/download.php?version=' . $version;
            $savePath = $this->tempPath . '/update-' . $version . '.zip';
            
            $fp = fopen($savePath, 'w+');
            $ch = curl_init($downloadUrl);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $success = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            
            if (!$success) {
                throw new Exception('خطا در دانلود فایل بروزرسانی');
            }
            
            return $savePath;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * ایجاد نسخه پشتیبان
     */
    public function createBackup() {
        try {
            $backupFile = $this->backupPath . '/backup-' . date('Y-m-d-His') . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($backupFile, ZipArchive::CREATE) !== true) {
                throw new Exception('خطا در ایجاد فایل پشتیبان');
            }
            
            $this->addFolderToZip(BASE_PATH, $zip);
            $zip->close();
            
            return $backupFile;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * نصب بروزرسانی
     */
    public function installUpdate($updateFile) {
        try {
            // ایجاد نسخه پشتیبان قبل از نصب
            $backup = $this->createBackup();
            if (!$backup) {
                throw new Exception('خطا در ایجاد نسخه پشتیبان');
            }
            
            // استخراج فایل‌های بروزرسانی
            $zip = new ZipArchive();
            if ($zip->open($updateFile) !== true) {
                throw new Exception('خطا در باز کردن فایل بروزرسانی');
            }
            
            $zip->extractTo(BASE_PATH);
            $zip->close();
            
            // پاکسازی فایل‌های موقت
            unlink($updateFile);
            
            return true;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * افزودن پوشه به فایل زیپ
     */
    private function addFolderToZip($folder, $zip, $subfolder = '') {
        $handle = opendir($folder);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $filePath = $folder . '/' . $entry;
                $zipPath = $subfolder . ($subfolder ? '/' : '') . $entry;
                
                if (is_file($filePath)) {
                    $zip->addFile($filePath, $zipPath);
                } elseif (is_dir($filePath)) {
                    $zip->addEmptyDir($zipPath);
                    $this->addFolderToZip($filePath, $zip, $zipPath);
                }
            }
        }
        closedir($handle);
    }
}