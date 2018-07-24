<?php
namespace App\Model\Entity;

use App\Avatar\Service as AvatarService;
use App\Avatar\Type\ImageSource;
use CakeDC\Users\Model\Entity\User as BaseUser;
use Cake\Core\Configure;

class User extends BaseUser
{
    /**
     * @var $_virtual - make virtual fields visible to export to JSON or array
     */
    protected $_virtual = ['name', 'image_src'];

    /**
     * Virtual Field: name
     *
     * Try to use first name and last name together, but
     * if it produces an empty result, fallback onto the
     * username.
     *
     * @return string
     */
    protected function _getName()
    {
        $result = trim($this->first_name . ' ' . $this->last_name);
        if (empty($result)) {
            $result = $this->username;
        }

        return $result;
    }

    /**
     * Virtual field image_src accessor.
     *
     * @return string
     */
    protected function _getImageSrc()
    {
        if ($this->get('image')) {
            $service = new AvatarService(new ImageSource(['src' => $this->get('image')]));

            return $service->getImage();
        }

        $type = Configure::read('Avatar.default');
        $options = (array)Configure::read('Avatar.options.' . $type);
        $options['email'] = (string)$this->get('email');
        $options['name'] = $this->get('name');

        $service = new AvatarService(new $type($options));

        return $service->getImage();
    }
}
