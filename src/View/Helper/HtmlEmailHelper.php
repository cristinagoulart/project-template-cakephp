<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Exception\MissingElementException;
use Cake\View\Helper;
use Pelago\Emogrifier;

/**
 * HtmlEmailHelper
 *
 *  includes functionlity to merge HTML and CSS in the email
 */
class HtmlEmailHelper extends Helper
{
    /**
     * @var string $templateHeader Header element
     */
    protected $templateHeader = null;

    /**
     * @var string $templateFooter Footer element
     */
    protected $templateFooter = null;

    /**
     * @var string $templateCss CSS element
     */
    protected $templateCss = null;

    /**
     * @var array $helpers Helpers to load
     */
    public $helpers = ['SystemInfo'];

    /**
     * initialize method
     *
     * @param array $config for the helper
     * @return void
     */
    public function initialize(array $config)
    {
        $this->templateHeader = Configure::read('EmailTemplates.header');
        $this->templateFooter = Configure::read('EmailTemplates.footer');
        $this->templateCss = Configure::read('EmailTemplates.css');
    }

    /**
     *  buildHtmlEmailBody method
     *
     * @param string $elementName for email
     * @param mixed[] $args optional params for element
     * @return string merged HTML and CSS
     */
    public function buildHtmlEmailBody(string $elementName, array $args = []): string
    {
        if (!$this->_View->elementExists($elementName)) {
            throw new MissingElementException("Cannot find element [$elementName]");
        }

        $content = $this->_View->elementExists($this->templateHeader) ?
                                $this->_View->element($this->templateHeader, $args) : '';
        $content .= $this->_View->element($elementName, $args);
        $content .= $this->_View->elementExists($this->templateHeader) ?
                                $this->_View->element($this->templateFooter, $args) : '';

        $css = $this->_View->elementExists($this->templateCss) ?
                                $this->_View->element($this->templateCss) : '';

        if (!empty($content) && !empty($css)) {
            $emogrifier = new Emogrifier($content, $css);
            $content = $emogrifier->emogrify();
        }

        return $content;
    }

    /**
     * getRecepientName method
     *
     * @return string recepient name
     */
    public function getRecepientName(): string
    {
        $name = $this->_View->get('first_name');
        if (empty($name)) {
            $name = $this->_View->get('username');
        }

        return $name;
    }

    /**
     * getFooterInfo method
     *
     * @return string additional footer info
     */
    public function getFooterInfo(): string
    {
        return '';
    }
}
