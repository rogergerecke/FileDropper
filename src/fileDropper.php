<?php


/**
 * Class fileDropper
 */
class fileDropper
{
    /**
     * @var string
     */
    private $backup_folder;
    /**
     * @var
     */
    private $cam_name;
    /**
     * @var
     */
    private $save_days;
    /**
     * @var array
     */
    private $protect_time_window;
    /**
     * @var
     */
    private $now;
    /**
     * @var string
     */
    private $base_path;

    /**
     * @var
     */
    private $output;

    /**
     * @var
     */
    private $failed_copy;

    private $copy_count;
    private $delete_counter;

    /**
     * @return mixed
     */
    public function getFailedCopy()
    {
        return $this->failed_copy;
    }


    /**
     * fileDropper constructor.
     * @param string $base_path
     * @param int $save_days
     * @param array $protect_time_window
     * @throws Exception
     */
    public function __construct($base_path = '', $save_days = 30, $protect_time_window = [])
    {
        if (!empty($base_path)) {
            $this->setBasePath($base_path);
        }

        // calculate days to seconds
        if ($save_days) {
            $this->setSaveDays($save_days);
        }

        $this->now = time();

        if (!empty($protect_time_window)) {
            $this->setProtectTimeWindow($protect_time_window);
        }
    }

    /**
     * Reset now
     * set default unix timestamp
     */
    private function setNow()
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
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }


    /**
     * Create a archive save dir
     * @throws Exception
     */
    private function makeFolder()
    {
        if (file_exists($this->backup_folder)) {
            // folder exist nothing to do
            return true;
        }

        if (!mkdir($this->backup_folder, 0777, true)) {
            throw new Exception('Make folder function failure');
        }

        return true;
    }


    /**
     * Execute the the file dropper and remove old files
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->base_path) {
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

        if (is_dir($this->base_path)) {
            if ($dh = opendir($this->base_path)) {
                while (($file = readdir($dh)) !== false) {

                    // only files
                    if (is_file($this->base_path . $file)) {
                        $rest = $delete_counter - $delete;// create file time
                        $file_time = filemtime($this->base_path . $file);
                        $file_time_date = date("H:i", $file_time);// create file parts
                        $file_parts = pathinfo($this->base_path . $file);
                        /*$filename = $file_parts['filename'];
                        $file_ext = $file_parts['extension'];*/

                        $diff = $this->now - $file_time;

                        // drop old files and non protected
                        // 2 = 20.02.2020 - 18.01.2020 and not protected
                        if ($diff >= $this->save_days and !in_array($file_time_date, $this->protect_time_window)) {
                            unlink($this->base_path . $file);
                            $delete++;
                        } elseif (in_array($file_time_date, $this->protect_time_window)) {
                            // protected copy to new folder
                            $oldie = $this->base_path . $file;
                            $name = $this->backup_folder . $file;
                            if (!copy($oldie, $name)) {
                                $failed_copy_filename[] = $file;
                                $failed_copy++;
                            } else {
                                // if file exist in backup folder drop it from base is it to old
                                if ($this->isFileInBackup($file) and $diff >= $this->save_days) {
                                    $this->dropFile($file);
                                }
                                $copy_count++;
                            }

                        }
                        $delete_counter++;
                    }
                }
                closedir($dh);
            }
        }

        // set output
        $this->output = $output = [
            'cam_name' => $this->cam_name,
            'delete' => $delete,
            'rest' => $rest,
            'failed_copy' => $failed_copy,
            'failed_copy_filename' => $failed_copy_filename
        ];

        $this->failed_copy = $failed_copy;
        $this->copy_count = $copy_count;

        // reset the timestamp and Folder
        $this->warmup();

        return true;
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
     */
    private function dropFile($file)
    {
        if (unlink($this->base_path . $file)) {
            $this->delete_counter++;
            return true;
        }
        return false;
    }

    /**
     * $this->setNow();
     * $this->setBackupFolder();
     * @throws Exception
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
        $this->backup_folder = $this->base_path . 'backups/' . date('Y', $this->now) . '/';
    }

    /**
     * @param mixed $cam_name
     * @return fileDropper
     */
    public function setCamName($cam_name)
    {
        $this->cam_name = $cam_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCopyCount()
    {
        return $this->copy_count;
    }

}