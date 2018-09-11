<?php
namespace App\Controller;

use App\Avatar\Service as AvatarService;
use App\Controller\AppController;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Exception;

/**
 * Users Controller
 */
class UsersController extends AppController
{
    // Use any of traits provided by CakeDC to extend the provided functionality
    use LoginTrait;
    use ProfileTrait;
    use RegisterTrait;
    use SimpleCrudTrait {
        SimpleCrudTrait::delete as defaultDelete;
    }

    /**
     * changeUserPassword method
     *
     * change user passwords by the superusers
     *
     * @param mixed $id user id
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     */
    public function changeUserPassword($id)
    {
        $user = $this->getUsersTable()->newEntity();
        $user->id = $id;
        $redirect = ['controller' => 'Users', 'action' => 'index'];

        if ($this->request->is('post')) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                $user = $this->getUsersTable()->patchEntity($user, $this->request->data(), ['validate' => $validator]);

                if ($user->errors()) {
                    $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                } else {
                    $user = $this->getUsersTable()->changePassword($user);
                    if ($user) {
                        $this->Flash->success(__d('CakeDC/Users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error(__d('CakeDC/Users', '{0}', $wpe->getMessage()));
            } catch (Exception $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Upload user image
     *
     * Converts and stores user image in base64 scheme.
     *
     * @param string $id User id
     * @return \Cake\Network\Response
     */
    public function uploadImage($id)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);

        $user = $this->Users->get($id);
        $data = $this->request->data('Users.image');

        if (! $data) {
            $this->Flash->error(__('Failed to upload image, please try again.'));

            return $this->redirect($this->request->referer());
        }

        $avatarService = new AvatarService();

        if (! $avatarService->isAllowedSize($data)) {
            $this->Flash->error(__('Image is too large. Max size 512kb.'));

            return $this->redirect($this->request->referer());
        }

        if (! $avatarService->isImage($data)) {
            $this->Flash->error(__('Unsupported image type.'));

            return $this->redirect($this->request->referer());
        }

        $resource = $avatarService->getImageResource($data);
        $processed = $this->Users->saveCustomAvatar($user, $resource);

        if ($processed) {
            $this->Flash->success(__('The image has been uploaded.'));
        } else {
            $this->Flash->error(__('Couldn\'t upload the image'));
        }

        return $this->redirect($this->request->referer());
    }

    /**
     * editProfile method
     *
     *  Overide CakeDC edit suer record method to give ability
     * separate user record update by admin and editing profile
     * by logged in user
     *
     * @return \Cake\Network\Response
     * @throws \CakeDC\Users\Exception\UserNotFoundException When user not found.
     * @throws \Cake\Network\Exception\UnauthorizedException When user is not authorized.
     */
    public function editProfile()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['patch', 'post', 'put']);

        $userId = $this->Auth->user('id');
        if (empty($userId)) {
            throw new UnauthorizedException('You have to login to complete this action!');
        }

        $user = $this->Users->get($userId);
        if (empty($user)) {
            throw new UserNotFoundException('User not found!');
        }

        $user = $this->Users->patchEntity($user, $this->request->data);

        if ($this->Users->save($user)) {
            $this->Flash->success(__('Profile successfully updated'));
            $this->Auth->setUser($user->toArray());
        } else {
            $this->Flash->error(__('Failed to update profile data, please try again.'));
        }

        return $this->redirect($this->request->referer());
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $users = $table->find()->all();
        $this->set($tableAlias, $users);
        $this->set('lockedUsers', $this->getLockedUsers());
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->alias();
        $entity = $table->get($id, [
            'contain' => []
        ]);

        $groupsTable = TableRegistry::get('Groups.Groups');
        $userGroups = $groupsTable->getUserGroupsAll($id, [
            'fields' => ['id', 'name', 'description'],
            'contain' => [],
        ]);

        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('userGroups', $userGroups);
        $this->set('subordinates', $this->Users->find('all')->where(['reports_to' => $id])->toArray());
        $this->set('_serialize', [$tableAlias, $userGroups, 'tableAlias']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\ForbiddenException When user is locked.
     */
    public function delete($id = null)
    {
        $table = $this->loadModel();
        $entity = $table->get($id, [
            'contain' => []
        ]);

        $username = $entity->get('username');
        if (in_array($username, $this->getLockedUsers())) {
            throw new ForbiddenException();
        }

        $this->defaultDelete($id);
    }

    /**
     * Allow/Prevent page rendering in iframe. In case of embed query param exists we allow iframe
     *
     * @return void
     */
    protected function _setIframeRendering()
    {
        if (empty($this->request->query['embed'])) {
            parent::_setIframeRendering();
        }
    }

    /**
     * Returns an array including the usernames of the currently locked users.
     *
     * @return array List of locked users
     */
    private function getLockedUsers()
    {
        return [
            $this->Auth->user('username'),
            getenv('DEV_USER')
        ];
    }
}
