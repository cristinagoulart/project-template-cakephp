<?php
namespace App\Model\Entity;

use App\Avatar\Service as AvatarService;
use CakeDC\Users\Model\Entity\User as BaseUser;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 */
class User extends BaseUser
{
    /**
     * @var string[] $_virtual virtual fields visible to export to JSON or array
     */
    protected $_virtual = ['name', 'image_src', 'is_admin'];

    /**
     * Virtual Field: name
     *
     * Try to use first name and last name together, but
     * if it produces an empty result, fallback onto the
     * username.
     *
     * @return string
     */
    protected function _getName(): string
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
    protected function _getImageSrc(): string
    {
        $type = Configure::read('Avatar.default');
        $options = (array)Configure::read('Avatar.options.' . $type);

        $options = array_merge($options, [
            'id' => $this->get('id'),
            'email' => $this->get('email'),
            'name' => $this->get('name')
        ]);

        $service = new AvatarService();

        return $service->getImage($options);
    }

    /**
     * Virtual Field: is_admin
     *
     * Returns true only and only if this user
     * a) is a superuser
     * b) belongs to Admins role
     *
     * @return bool
     */
    protected function _getIsAdmin(): bool
    {
        if ($this->get('is_superuser')) {
            return true;
        }

        $roleName = Configure::read('RolesCapabilities.Roles.Admin.name');
        if (! is_string($roleName)) {
            return false;
        }

        /** @var \RolesCapabilities\Model\Table\CapabilitiesTable $capabilities */
        $capabilities = TableRegistry::get('RolesCapabilities.Capabilities');
        $userGroups = $capabilities->getUserGroups($this->get('id'));
        $userRoles = $capabilities->getGroupsRoles($userGroups);
        $isAdmin = in_array($roleName, $userRoles);

        return $isAdmin;
    }
}
