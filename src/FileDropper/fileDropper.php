<?php

namespace App\FileDropper;
/**
 * Class fileDropper
 */
class fileDropper
{

    /**
     * Days on the files should not be deleted
     * @var array
     */
    private $protect_days = [];

    /**
     * Time on the files should not be deleted
     * @var array
     */
    private $protect_time_window = [];

    /**
     * Date on the files should not be deleted
     * @var array
     */
    private $protect_date_range = [];

    /**
     * File type the files should not be deleted
     * @var array
     */
    private $protect_file_types = [];

    /**
     * File TYPE GROUP the files should not be deleted
     * @var array
     */
    private $protect_file_group = [];

    /**
     * Count deleted files
     * @var int
     */
    private $deleted = 0;

    /**
     * Count copied files
     * @var int
     */
    private $copied = 0;

    /**
     * Unix timestamp
     * @var int
     */
    private $now = 0;

    /**
     * The dir how the file dropper work
     * @var string
     */
    private $work_dir = '';

    /**
     * Backup dir name
     * @var string
     */
    private $backup_folder = '';


    public function __construct()
    {
        $this->resetNow();
    }

    /**
     * Reset the unix timestamp
     */
    private function resetNow()
    {
        $this->now = time();
    }


    /**
     * Set the save days as seconds
     * @param int $save_days
     * @return fileDropper
     */
    public function setSaveDays($save_days)
    {
        $this->save_days = 3600 * 24 * $save_days;
        return $this;
    }

    /**
     * @param array $protect_time_window
     * @return fileDropper
     */
    public function setProtectTimeWindow($protect_time_window)
    {
        $this->protect_time_window = $protect_time_window;
        return $this;
    }


    /**
     * @param string $base_path
     * @return fileDropper
     */
    public function setBasePath($base_path)
    {
        $this->base_path = $base_path;
        return $this;
    }


    /**
     * Create a archive save dir
     * @throws Exception
     * @throws FileDropperException
     */
    private function makeFolder()
    {
        if (file_exists($this->backup_folder)) {
            // folder exist nothing to do
            return true;
        }

        if (!mkdir($this->backup_folder, 0777, true)) {
            throw new FileDropperException('Make folder function failure');
        }

        return true;
    }


    /**
     * Execute the the file dropper and remove old files
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->work_dir) {
            echo 'A save phat must be set ->setBasePath';
            return false;
        }

        $this->setBackupFolder();

        // create a base/archive folder
        if (!file_exists($this->backup_folder)) {
            $this->makeFolder();
        }

        $failed_copy_filename = null;
        $failed_copy = null;
        $failed_copy = 0;
        $delete_counter = 0;
        $delete = 0;
        $rest = 0;
        $copy_count = 0;

        if (is_dir($this->work_dir)) {
            if ($dh = opendir($this->work_dir)) {
                while (($file = readdir($dh)) !== false) {

                    // only files
                    if (is_file($this->work_dir . $file)) {
                        $rest = $delete_counter - $delete;// create file time
                        $file_time = filemtime($this->work_dir . $file);
                        $file_time_date = date("H:i", $file_time);// create file parts
                        $file_parts = pathinfo($this->work_dir . $file);
                        /*$filename = $file_parts['filename'];
                        $file_ext = $file_parts['extension'];*/

                        $diff = $this->now - $file_time;

                        // drop old files and non protected
                        // 2 = 20.02.2020 - 18.01.2020 and not protected
                        if ($diff >= $this->save_days and !in_array($file_time_date, $this->protect_time_window)) {
                            unlink($this->work_dir . $file);
                            $delete++;
                        } elseif (in_array($file_time_date, $this->protect_time_window)) {
                            // protected copy to new folder
                            $oldie = $this->work_dir . $file;
                            $name = $this->backup_folder . $file;
                            if (!copy($oldie, $name)) {
                                $failed_copy_filename[] = $file;
                                $failed_copy++;
                            } else {
                                // if file exist in backup folder drop it from base is it to old
                                if ($this->isFileInBackup($file) and $diff >= $this->save_days) {
                                    $this->dropFile($file);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }


        // reset the timestamp and Folder
        $this->warmup();

        return true;
    }

    private function isFileProtected($file)
    {

        if (isset($this->protect_time_window) and $this->isFileTimeProtected($file)) {
            return true;
        }

        if (isset($this->protect_date_range) and $this->isFileDateProtected($file)) {
            return true;
        }

        if (isset($this->protect_file_types) and $this->isFileTypeProtected($file)) {
            return true;
        }

        if (isset($this->protect_file_group) and $this->isFileTypeGroupProtected($file)) {
            return true;
        }

        return false;
    }

    private function isFileTimeProtected($file)
    {
        return false;
    }

    private function isFileDateProtected($file)
    {
        return false;
    }

    private function isFileTypeProtected($file)
    {
        return false;
    }

    private function isFileTypeGroupProtected($file)
    {
        return false;
    }

    /**
     * Check file_exist in backup folder
     * @param $file
     * @return bool
     */
    private function isFileInBackup($file)
    {
        if (file_exists($this->backup_folder . $file)) {
            return true;
        }
        return false;
    }


    /**
     * Drop: Unlink file from base folder
     * @param $file
     * @return bool
     * @throws FileDropperException
     */
    private function dropFile($file)
    {
        if (!file_exists($this->work_dir . $file)) {
            throw new FileDropperException('File: ' . $file . ' dont exists for drop');
        }

        if (unlink($this->work_dir . $file)) {
            $this->deleted++;
            return true;
        }
        throw new FileDropperException('File: ' . $file . ' wrong file permission for backup');
    }


    /**
     * @param $file
     * @return bool
     * @throws FileDropperException
     */
    private function backupFile($file)
    {
        if (!file_exists($this->work_dir . $file)) {
            throw new FileDropperException('File: ' . $file . ' dont exists for backup');
        }

        if (copy($this->work_dir . $file, $this->backup_folder . $file)) {
            $this->copied++;
            return true;
        }

        throw new FileDropperException('File: ' . $file . ' wrong file permission for backup');
    }

    /**
     * $this->setNow();
     * $this->setBackupFolder();
     */
    private function warmup()
    {
        $this->setNow();
        $this->setBackupFolder();
        $this->makeFolder();
    }

    /**
     * Set the Folder name as year 2020/
     */
    private function setBackupFolder()
    {
        $this->backup_folder = $this->work_dir . 'backups/' . date('Y', $this->now) . '/';
    }


    /**
     * @return mixed
     */
    public function getCopyCount()
    {
        return $this->copy_count;
    }

}