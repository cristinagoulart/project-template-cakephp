<?php

namespace App\Avatar;

interface AvatarInterface
{
    /**
     * Avatar getter method.
     *
     * @return string
     */
    public function get(): string;

    /**
     * Process Avatar Resource file
     *
     * @param string $file path of avatar
     * @param resource $resource of scanned image
     *
     * @return bool if file is process
     */
    public function processAvatarResource(string $file, $resource): bool;

    /**
     * Remove image resource from the memory
     *
     * @param resource $resource of the file.
     *
     * @return bool on imagedestroy()
     */
    public function removeAvatarResource($resource): bool;
}
