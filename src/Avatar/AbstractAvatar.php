<?php
namespace App\Avatar;

use App\Avatar\AvatarInterface;
use Cake\Core\Configure;

abstract class AbstractAvatar implements AvatarInterface
{
    /**
     * Save Avatar Image Resource
     *
     * @param string $file path with filename
     * @param object $resource of the scanned image
     *
     * @return bool $processed if save was successful.
     */
    public function processAvatarResource($file, $resource)
    {
        $processed = false;

        imagealphablending($resource, false);
        imagetruecolortopalette($resource, false, 256);

        if (imagepng($resource, $file, 6, PNG_NO_FILTER)) {
            $processed = true;
        }

        return $processed;
    }

    /**
     * Remove image resource from the memory
     *
     * @param resource $resource of the file.
     *
     * @return bool on imagedestroy()
     */
    public function removeAvatarResource($resource)
    {
        return imagedestroy($resource);
    }

    /**
     * Return Avatar URL that will be used for img tag
     *
     * @param array $options passed from the service
     *
     * @return string URL
     */
    public function getAvatarUrl(array $options)
    {
        $directory = Configure::read('Avatar.directory');

        return $directory . $options['filename'];
    }
}
