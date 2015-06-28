<?php
/**
 * This file is part of SoloProyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos-php/sys-file/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos-php/sys-file
 */
namespace soloproyectos\sys\file;
use soloproyectos\arr\Arr;
use soloproyectos\text\Text;
use soloproyectos\sys\exception\SysException;

/**
 * Class SysFile.
 *
 * This class is used to access to the file system.
 *
 * @package Sys\File
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE The MIT License (MIT)
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class SysFile
{
    /**
     * Concatenates filenames.
     *
     * This function concatenates several filenames into a new one.
     *
     * For example:
     * ```php
     * // the next command prints "dir1/dir2/test.txt"
     * echo SysFile::concat("dir1", "/dir2", "test.txt");
     * ```
     *
     * @param string $file One or more files
     *
     * @return string
     */
    public static function concat($file/*, ...*/)
    {
        $args = array();
        $len = func_num_args();

        for ($i = 0; $i < $len; $i++) {
            $value = func_get_arg($i);
            $values = is_array($value)? array_values($value) : array($value);
            $args = array_merge($args, $values);
        }

        return rtrim(preg_replace("/\/+/", "/", Text::concat("/", $args)), "/");
    }

    /**
     * Gets a human readable size.
     *
     * This function gets an human readable size.
     *
     * For example:
     * ```php
     * // human readable sizes:
     * echo SysFile::getHumanSize(13);           // prints 13 bytes
     * echo SysFile::getHumanSize(1024);         // prints 1K
     * echo SysFile::getHumanSize(4562154, 2);   // prints 4.35M (2 digits)
     * echo SysFile::getHumanSize(98543246875);  // prints 91.8G
     * ```
     *
     * @param integer $size      Size in bytes
     * @param integer $precision Digit precision (default is 1)
     *
     * @return string
     */
    public static function getHumanSize($size, $precision = 1)
    {
        $units = array(" bytes", "K", "M", "G", "T", "P", "E", "Z", "Y");
        $pow = 1024;
        $factor = 0;

        while ($size + 1 > $pow) {
            $size /= $pow;
            $factor++;
        }

        return round($size, $precision) . $units[$factor];
    }

    /**
     * Gets an available name under a given directory.
     *
     * This function returns an available name under a given directory. For
     * example, if there's a file named 'test.txt' under de directory 'dir1', the
     * following command returns 'test_1.txt':
     *
     * ```php
     * // prints 'test_1.txt' if the name 'test.txt' is taken:
     * echo SysFile::getAvailName('dir1', 'test1.txt');
     * ```
     *
     * @param string $dir     Directory
     * @param string $refname Filename used as reference (default is "")
     * @param string $refext  Extension used as reference (default is "")
     *
     * @return string
     */
    public static function getAvailName($dir, $refname = "", $refext = "")
    {
        // fixes arguments
        $dir = trim($dir);
        $refname = trim($refname);
        $refext = ltrim(trim($refext), ".");

        if (!is_dir($dir)) {
            throw new SysException("Directory not found: $dir");
        }

        // default refname
        if (Text::isEmpty($refname)) {
            $refname = "file";
        }

        // gets name and extension
        $refname = basename($refname);
        $pos = strrpos($refname, ".");
        $name = $refname;
        $ext = $refext;

        if ($pos !== false) {
            $name = substr($refname, 0, $pos);

            if (Text::isEmpty($refext)) {
                $ext = substr($refname, $pos + 1);
            }
        }

        // gets an available name
        for ($i = 0; $i < 100; $i++) {
            $basename = $i > 0
                ? Text::concat(".", $name . "_" . $i, $ext)
                : Text::concat(".", $name, $ext);
            $filename = SysFile::concat($dir, $basename);

            if (!is_file($filename)) {
                break;
            }
        }

        return $filename;
    }

    /**
     * Gets the information of a file path.
     * 
     * @param string $path File path
     *
     * @return string[]
     */
    public static function getInfo($path)
    {
        $info = pathinfo($path);
        return array(
            "dirname" => Arr::get($info, "dirname", ""),
            "basename" => Arr::get($info, "basename", ""),
            "extension" => Arr::get($info, "extension", ""),
            "filename" => Arr::get($info, "filename", "")
        );
    }

    /**
     * Gets the extension of a file path.
     * 
     * @param string $path File path
     *
     * @return string
     */
    public static function getExtension($path)
    {
        $info = SysFile::getInfo($path);
        return $info["extension"];
    }
}
