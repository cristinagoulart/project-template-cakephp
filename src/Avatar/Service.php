<?php
namespace App\Avatar;

use App\Avatar\Type\ImageSource;
use Cake\Core\Configure;

final class Service
{
    /**
     * @var \App\Avatar\AvatarInterface
     */
    private $avatar;

    /**
     * Constructor method.
     *
     * @param \App\Avatar\AvatarInterface $avatar Avatar instance
     * @return void
     */
    public function __construct(AvatarInterface $avatar = null)
    {
        if (!$avatar) {
            $avatar = new ImageSource();
        }

        $this->avatar = $avatar;
    }

    /**
     * Set Avatar Source separately
     *
     * @param \App\Avatar\AvatarInterface $avatar object
     *
     * @return void
     */
    public function setAvatarSource(AvatarInterface $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Fetches avatar image.
     *
     * @param array $options if any present on the avatar provider
     *
     * @return string
     */
    public function getImage(array $options)
    {
        $filename = $this->getImageName($options);
        $options['filename'] = $filename;

        $order = Configure::read('Avatar.order');

        foreach ($order as $classType) {
            $defaultOptions = Configure::read('Avatar.options.' . $classType);
            $options = array_merge($defaultOptions, $options);
            $source = $this->getAvatarSource($classType, $options);
            $image = $source->get();

            if (!empty($image)) {
                return $image;
            }
        }

        // using anonymous image as the default.
        $defaultAvatar = Configure::read('Avatar.defaultImage');
        $imageSource = $this->getAvatarSource('ImageSource', ['src' => $defaultAvatar]);
        $this->setAvatarSource($imageSource);

        return $this->avatar->get();
    }

    /**
     * Initiate class object base on its name
     *
     * @param string $class name
     * @param array $options if any required
     *
     * @return \App\Avatar\AvatarInterface $instance of the class
     */
    public function getAvatarSource($class, $options)
    {
        if (class_exists($class)) {
            $instance = new $class($options);
        }

        return $instance;
    }

    /**
     * Get Image Name of the service
     *
     * @param array $options config
     * @param bool $ext whether to use file extension or not
     *
     * @return string $name of the file.
     */
    public function getImageName(array $options, $ext = true)
    {
        $extension = ($ext) ? Configure::read('Avatar.extension') : '';
        $name = $options['id'] . $extension;

        return $name;
    }

    /**
     * Saving image resource
     *
     * @param string $file path including its name
     * @param resource $resource of the file
     *
     * @return bool $status of the file save.
     */
    public function saveImage($file, $resource)
    {
        $status = $this->avatar->processAvatarResource($file, $resource);

        return $status;
    }

    /**
     * Remove image resource from memory
     *
     * @param resource $resource of the file
     *
     * @return bool whether the resource was removed from the memory
     */
    public function removeImageResource($resource)
    {
        return $this->avatar->removeAvatarResource($resource);
    }

    /**
     * is uploading resource is Image
     *
     * @param array $data from the form
     *
     * @return bool $result whether mime type is image.
     */
    public function isImage($data)
    {
        $result = false;
        list($mimeGroup, ) = explode('/', $data['type']);

        if ('image' == strtolower($mimeGroup)) {
            $result = true;
        }

        return $result;
    }

    /**
     * File size check
     *
     * @param array $data of the image from the form
     *
     * @return bool $result comparing with config/avatar.
     */
    public function isAllowedSize($data)
    {
        $result = false;
        $allowedSize = Configure::read('Avatar.fileSize');

        if ($allowedSize > $data['size']) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get Image resource stream
     *
     * @param mixed $data of the file origin (form, base64 data)
     * @param bool $isBase64 flag to check how to handle $data
     *
     * @return resource $source of the image
     */
    public function getImageResource($data, $isBase64 = false)
    {
        if (!$isBase64) {
            $extension = strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));

            if ('png' == $extension) {
                $source = imagecreatefrompng($data['tmp_name']);
            }

            if (in_array($extension, ['jpg', 'jpeg'])) {
                $source = imagecreatefromjpeg($data['tmp_name']);
            }
        } else {
            $source = imagecreatefromstring(file_get_contents($data));
        }

        return $source;
    }
}
