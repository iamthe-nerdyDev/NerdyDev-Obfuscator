<?php
/**
 * ZipArchiver
 */
class ZipArchiver
{

    /**
     * zipDir
     *
     * @param  mixed $sourcePath
     * @param  mixed $outZipPath
     * @return bool
     */
    public static function zipDir($sourcePath, $outZipPath): bool
    {
        $pathInfo = pathinfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new ZipArchive();
        $z->open($outZipPath, ZipArchive::CREATE);
        $z->addEmptyDir($dirName);

        if ($sourcePath == $dirName) {
            self::dirToZip($sourcePath, $z, 0);
        } else {
            self::dirToZip($sourcePath, $z, strlen("$parentPath/"));
        }

        $z->close();

        return true;
    }


    /**
     * dirToZip: Add files and sub-directories in a folder to zip file.
     *
     * @param  mixed $folder
     * @param  mixed $zipFile
     * @param  mixed $exclusiveLength
     * @return void
     */
    private static function dirToZip($folder, &$zipFile, $exclusiveLength): void
    {
        $handle = opendir($folder);

        while (FALSE !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..' && $f != basename(__FILE__)) { // Check for local/parent path or zipping file itself and skip

                $filePath = "$folder/$f";

                $localPath = substr($filePath, $exclusiveLength); // Remove prefix from file path before add to zip

                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) { // Add sub-directory
                    $zipFile->addEmptyDir($localPath);

                    self::dirToZip($filePath, $zipFile, $exclusiveLength);
                }

            }

        }

        closedir($handle);
    }

}
?>