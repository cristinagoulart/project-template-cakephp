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
     * @param string $name of the class
     * @param array $options if any required
     *
     * @return \App\Avatar\AvatarInterface $instance of the class
     */
    public function getAvatarSource($name, $options)
    {
        $class = __NAMESPACE__ . '\\Type\\' . $name;

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
}
