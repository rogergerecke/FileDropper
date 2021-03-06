<?php

namespace Redkitty\FileDropper;

use DateInterval;
use DatePeriod;
use DateTime;

/**
 * Class fileDropper
 */
class FileDropper
{

    /**
     * Days on the files should not be deleted
     * @var int
     */
    private $protect_days = 0;

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
     * @return int
     */
    public function getProtectDays(): int
    {
        return $this->protect_days;
    }

    /**
     * Calculate the given int to days in sec.
     * @param int $protect_days * 3600 * 24 = unix sec.
     * @return fileDropper
     */
    public function setProtectDays(int $protect_days): fileDropper
    {
        $this->protect_days = 3600 * 24 * $protect_days;
        return $this;
    }

    /**
     * @return array
     */
    public function getProtectDateRange(): array
    {
        return $this->protect_date_range;
    }

    /**
     * @return array
     */
    public function getProtectTimeWindow(): array
    {
        return $this->protect_time_window;
    }


    /**
     * Set the time window were the files no delete
     * Input format allowed single time or time range in array
     * see example input: ['12:00-13:03','15:33','17:59']
     *
     * @param array $protect_time_window
     * @return fileDropper
     */
    public function setProtectTimeWindow($protect_time_window)
    {
        foreach ($protect_time_window as $time_window) {
            if (strlen($time_window) > 5) {

                $date_parts = explode('-', $time_window, 2);
                $start = strtotime($date_parts[0]);
                $end = strtotime($date_parts[1]);

                // anonymous function add + minute
                $addMinute = function ($time) {
                    return $time + 60;
                };

                for ($time = $start; $time < $end; $time = $addMinute($time)) {
                    $this->protect_time_window[] = date('H:i', $time);
                }
                $this->protect_time_window[] = date('H:i', $end);

            } else {
                $this->protect_time_window[] = $time_window;
            }
        }

        return $this;
    }


    /**
     * Input the single dates or date range to exclude from dropping
     *
     * Create a set of date range into array
     * Input array ['09.12.2020-22.12.2020']
     * Output array ['09.12.2020','10.12.2020','11.12.2020'....]
     *
     * Valid input range or single date ['09.12.2020-22.12.2020','07.12.2025','03.05.1999']
     * @param array $protect_date_range
     * @return fileDropper
     * @throws \Exception
     */
    public function setProtectDateRange(array $protect_date_range): fileDropper
    {
        foreach ($protect_date_range as $date_range) {
            if (strlen($date_range) > 10) {
                $date_parts = explode('-', $date_range, 2);
                $period = new DatePeriod(
                    new DateTime($date_parts[0]),
                    new DateInterval('P1D'),
                    new DateTime($date_parts[1])
                );
                foreach ($period as $date) $this->protect_date_range[] = $date->format('d.m.Y');
            } else {
                $this->protect_date_range[] = $date_range;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getProtectFileTypes(): array
    {
        return $this->protect_file_types;
    }

    /**
     * Can input a array of file types ['jpg','gif','mp3']
     * file types if you wont to exclude from dropping.
     *
     * @param array $protect_file_types
     * @return fileDropper
     */
    public function setProtectFileTypes(array $protect_file_types): fileDropper
    {
        $this->protect_file_types = $protect_file_types;
        return $this;
    }

    /**
     * @return array
     */
    public function getProtectFileGroup(): array
    {
        return $this->protect_file_group;
    }

    /**
     * Exclude a group of file types
     * available groups are
     * - Images
     * - Audio
     * - Fonts
     * - Movies
     *
     * Input array ['images']
     *
     * @param array $protect_file_group
     * @return fileDropper
     */
    public function setProtectFileGroup(array $protect_file_group): fileDropper
    {
        foreach ($protect_file_group as $group) {
            if (strtolower($group) == 'images') $groups[] = $this->getMediaGroupImages();
            if (strtolower($group) == 'audios') $groups[] = $this->getMediaGroupAudios();
            if (strtolower($group) == 'fonts') $groups[] = $this->getMediaGroupFonts();
            if (strtolower($group) == 'movies') $groups[] = $this->getMediaGroupMovies();
        }
        $this->protect_file_group = $protect_file_group;
        return $this;
    }

    #
    # Other way to detect formats is magic numbers but its work not fine on corrupted file or false header
    #

    public function getMediaGroupImages()
    {
        return [
            'gif',
            'jpg',
            'png',
            'swf',
            'psd',
            'bmp',
            'tiff',
            'jpc',
            'jp2',
            'jpx',
            'jb2',
            'swc',
            'iff',
            'wbmp',
            'jp2',
            'xbm',
            'ico',
            'ai',
        ];
    }

    public function getMediaGroupAudios()
    {
        return [
            'aac',
            'aif',
            'aiff',
            'iff',
            'm3u',
            'm4a',
            'mid',
            'mp3',
            'mpa',
            'oga',
            'ra',
            'wav',
            'wma',
            'aa',
            'aa3'
        ];
    }

    public function getMediaGroupMovies()
    {
        return [
            'mpeg',
            'amv',
            'asf',
            'ogv',
            'ogg',
            'vob',
            'avi',
            'mov',
            'm4v',
            '3gp',
        ];
    }

    public function getMediaGroupFonts()
    {
        return [
            'amfm',
            'mf',
            'fnt',
            'ttf',
            'vnf',
            'xfn',
            'woff',
            'pfa',
            'sfd',
            'vlw',
            'fot',
            'gxf',
            'otf',
            'pfb',
            'etx',
            'odttf',
            'svg'

        ];
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return fileDropper
     */
    public function setDeleted(int $deleted): fileDropper
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return int
     */
    public function getCopied(): int
    {
        return $this->copied;
    }

    /**
     * @param int $copied
     * @return fileDropper
     */
    public function setCopied(int $copied): fileDropper
    {
        $this->copied = $copied;
        return $this;
    }

    /**
     * @return int
     */
    public function getNow(): int
    {
        return $this->now;
    }

    /**
     * @param int $now
     * @return fileDropper
     */
    private function setNow(int $now): fileDropper
    {
        $this->now = $now;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkDir(): string
    {
        return $this->work_dir;
    }

    /**
     * @param string $work_dir
     * @return fileDropper
     */
    public function setWorkDir(string $work_dir): fileDropper
    {
        $this->work_dir = $work_dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackupFolder(): string
    {
        return $this->backup_folder;
    }

    /**
     * @param string $backup_folder
     * @return fileDropper
     */
    public function setBackupFolder(string $backup_folder): fileDropper
    {
        $this->backup_folder = $backup_folder;
        return $this;
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
     * @throws FileDropperException
     */
    public function execute()
    {
        if (!$this->work_dir) {
            echo 'A save phat must be set ->setBasePath';
            return false;
        }


        // is backup dir set creat it if not exist
        if ($this->backup_folder) {
            if (!file_exists($this->work_dir.$this->backup_folder)) {
                $this->makeFolder();
            }
        }

        if (is_dir($this->work_dir)) {
            $files = scandir($this->work_dir);

            foreach ($files as $file) {
                if (is_file($this->work_dir . $file)) {
                    if (!$this->isFileProtected($file)) {
                        $this->dropFile($file);
                    }
                }
            }
        }

//
//        if (is_dir($this->work_dir)) {
//            if ($dh = opendir($this->work_dir)) {
//                while (($file = readdir($dh)) !== false) {
//
//                    if (!$this->isFileProtected($file)) {
//                        $this->dropFile($file);
//                    }
        /*
                            // only files
                            if (is_file($this->work_dir . $file)) {
                                //$rest = $delete_counter - $delete;// create file time
                                $file_time = filemtime($this->work_dir . $file);
                                $file_time_date = date("H:i", $file_time);// create file parts
                                $file_parts = pathinfo($this->work_dir . $file);
                                $filename = $file_parts['filename'];
                                $file_ext = $file_parts['extension'];

                                $diff = $this->now - $file_time;

                                // drop old files and non protected
                                // 2 = 20.02.2020 - 18.01.2020 and not protected
                                if ($diff >= $this->save_days and !in_array($file_time_date, $this->protect_time_window)) {
                                    unlink($this->work_dir . $file);
                                    $this->deleted++;
                                } elseif (in_array($file_time_date, $this->protect_time_window)) {
                                    // protected copy to new folder
                                    $oldie = $this->work_dir . $file;
                                    $name = $this->backup_folder . $file;
                                    if (!copy($oldie, $name)) {
                                      // error = $file;

                                    } else {
                                        // if file exist in backup folder drop it from base is it to old
                                        if ($this->isFileInBackup($file) and $diff >= $this->save_days) {
                                            $this->dropFile($file);
                                        }
                                    }
                                }
//                                        }*/
//                }
//                closedir($dh);
//            }
//        }


        // reset the timestamp and Folder
        //$this->warmup();

        return true;
    }

    private function isFileProtected($file)
    {
        if ($this->protect_days and $this->isFileDayProtected($file)) {
            return true;
        }

        if ($this->protect_time_window and $this->isFileTimeProtected($file)) {
            return true;
        }

        if ($this->protect_date_range and $this->isFileDateProtected($file)) {
            return true;
        }

        if ($this->protect_file_types and $this->isFileTypeProtected($file)) {
            return true;
        }

        if ($this->protect_file_group and $this->isFileTypeGroupProtected($file)) {
            return true;
        }

        return false;
    }


    private function isFileDayProtected($file)
    {
        $file_seconds = filemtime($this->work_dir . $file);
        $diff = $this->now - $file_seconds;
        // 2days >= 30
        if ($diff >= $this->protect_days) return false;

        return true;
    }

    private function isFileTimeProtected($file)
    {
        $file_time = date("H:i", filemtime($this->work_dir . $file));

        foreach ($this->protect_time_window as $time) {
            if ($file_time == $time) return true;
        }

        return false;
    }

    private function isFileDateProtected($file)
    {
        $file_date = date("d.m.Y", filemtime($this->work_dir . $file));

        foreach ($this->protect_date_range as $date) {
            if ($file_date == $date) return true;
        }

        return false;
    }

    private function isFileTypeProtected($file)
    {
        $file_ext = pathinfo($this->work_dir . $file, PATHINFO_EXTENSION);
        foreach ($this->protect_file_types as $ext) {
            if ($file_ext == $ext) return true;
        }

        return false;
    }

    private function isFileTypeGroupProtected($file)
    {
        $file_ext = pathinfo($this->work_dir . $file, PATHINFO_EXTENSION);
        foreach ($this->protect_file_group as $ext) {
            if ($file_ext == $ext) return true;
        }

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
    private function setBackupFolder_test()
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