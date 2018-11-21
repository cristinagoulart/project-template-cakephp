<?php
namespace App\Error;

use Cake\Core\Configure;
use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends ExceptionRenderer
{
    /**
     * Render Error Page.
     *
     * We overwrite the render function just to remain
     * on the same layout in case the user got an error
     * and still logged in to the sytem.
     *
     * @return mixed
     */
    public function render()
    {
        $isDebug = Configure::read('debug');
        $this->controller->loadComponent('CakeDC/Users.UsersAuth');
        $this->controller->Auth->setConfig('authorize', false);
        $currentUser = $this->controller->Auth->user();

        if (empty($currentUser) || $isDebug) {
            return parent::render();
        }

        $exception = $this->error;
        $code = $this->_code($exception);
        $message = $this->_message($this->error, $this->error->getCode());

        $data = [
            'code' => $code,
            'message' => $message,
            'url' => $this->controller->request->getRequestTarget(),
            'error' => $this->_unwrap($exception),
            '_serialize' => ['message', 'code', 'url'],
        ];

        // adding generated error info into custom session variable for system/error page.
        $this->controller->request->session()->write('currentError', json_encode($data));

        return $this->controller->redirect('/system/error');
    }
}
