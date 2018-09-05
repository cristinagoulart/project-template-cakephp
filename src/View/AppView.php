<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\View\View;

/**
 * App View class
 */
class AppView extends View
{
    /**
     * @var array Holds all the views that are currently being rendered
     */
    private $backtrace = [];

    /**
     * Initialization hook method.
     *
     * For e.g. use this method to load a helper for all views:
     * `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadHelper('Menu.Menu');
        $this->loadHelper('Form', ['className' => 'AdminLTE.Form']);
        $this->loadHelper('HtmlEmail');
        $this->loadHelper('SystemInfo');
        $this->loadHelper('CakeDC/Users.User');
    }

    /**
     * Extends render method to log backtrace information
     *
     * @param string $viewFile Filename of the view
     * @param array $data Data to include in rendered view. If empty the current
     *   View::$viewVars will be used.
     * @return string Rendered output
     * @throws \LogicException When a block is left open.
     * @triggers View.beforeRenderFile $this, [$viewFile]
     * @triggers View.afterRenderFile $this, [$viewFile, $content]
     */
    protected function _render($viewFile, $data = [])
    {
        array_push($this->backtrace, $viewFile);
        $output = parent::_render($viewFile, $data);
        array_pop($this->backtrace);

        return $output;
    }

    /**
     * Returns the backtrace for this view.
     *
     * @return array The backtrace
     */
    public function getBacktrace()
    {
        return $this->backtrace;
    }
}
