<?php
namespace App\Shell;

use CakeDC\Users\Shell\UsersShell as BaseShell;
use Cake\ORM\Entity;

class UsersShell extends BaseShell
{
    /**
     * Add a new superadmin user
     *
     * @return void
     */
    public function addSuperuser()
    {
        $username = $this->getUsername();
        $password = $this->getPassword();
        $email = $this->getEmail($username);

        $user = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'active' => 1,
        ];

        $userEntity = $this->Users->newEntity($user);
        $userEntity->is_superuser = true;
        $userEntity->role = 'superuser';
        $savedUser = $this->Users->save($userEntity);
        if (!empty($savedUser)) {
            $this->printUserInfo($savedUser, $password);
        } else {
            $this->printUserErrors($userEntity);
            $this->abort(__d('CakeDC/Users', 'Failed to add superuser'));
        }
    }

    /**
     * Return a username for the new user
     *
     * If the username is provided as an argument,
     * return that.  Otherwise, generate a unique
     * username for the super user.
     *
     * @return string
     */
    protected function getUsername(): string
    {
        if (!empty($this->params['username'])) {
            return $this->params['username'];
        }

        return $this->Users->generateUniqueUsername('superadmin');
    }

    /**
     * Return a password for the new user
     *
     * If the password is provided as an argument,
     * return that.  Otherwise, generate a random
     * password.
     *
     * @return string
     */
    protected function getPassword(): string
    {
        if (!empty($this->params['password'])) {
            return $this->params['password'];
        }

        return $this->_generateRandomPassword();
    }

    /**
     * Return an email for the new user
     *
     * If the email is provided as an argument,
     * return that.  Otherwise, generate an email
     * based on a given username.
     *
     * @param string $username Username
     * @return string
     */
    protected function getEmail(string $username): string
    {
        if (!empty($this->params['email'])) {
            return $this->params['email'];
        }

        return $username . '@example.com';
    }

    /**
     * Print out user information
     *
     * @param \Cake\ORM\Entity $user User entity
     * @param string $password Plain text password
     * @return void
     */
    protected function printUserInfo(Entity $user, string $password): void
    {
        $this->out('<success>' . __d('CakeDC/Users', 'Superuser added successfully.') . '</success>');
        $this->out('<info>' . __d('CakeDC/Users', 'User Id : {0}', $user->id) . '</info>');
        $this->out('<info>' . __d('CakeDC/Users', 'Username: {0}', $user->username) . '</info>');
        $this->out('<info>' . __d('CakeDC/Users', 'Email   : {0}', $user->email) . '</info>');
        $this->out('<info>' . __d('CakeDC/Users', 'Password: {0}', $password) . '</info>');
    }

    /**
     * Print out user errors
     *
     * @param \Cake\ORM\Entity $user User entity
     * @return void
     */
    protected function printUserErrors(Entity $user): void
    {
        $this->err(__d('CakeDC/Users', 'Errors while trying to add a superuser:'));

        collection($user->errors())->each(function ($error, $field) {
            $this->err(__d('CakeDC/Users', 'Field "{0}" error: {1}', $field, implode(',', $error)));
        });
    }
}
