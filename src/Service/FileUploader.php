<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 20/08/18
 * Time: 22:01
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader
 * @package App\Service
 */
class FileUploader
{
    /**
     * @param UploadedFile $file
     * @param string $targetRep
     * @return string
     */
    public function upload(UploadedFile $file, string $targetRep)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($targetRep, $fileName);
        return $fileName;
    }
}