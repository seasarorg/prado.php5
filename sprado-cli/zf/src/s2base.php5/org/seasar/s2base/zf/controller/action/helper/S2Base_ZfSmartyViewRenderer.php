<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.2.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.controller.action.helper
 * @author     klove
 */
class S2Base_ZfSmartyViewRenderer extends Zend_Controller_Action_Helper_Abstract {

    public static $config   = array();
    private static $errors   = array();
    private $layout     = null;
    private $template   = null;
    private $_noRender  = false;

    public function setTemplateDir($templateDir) {
        $this->templateDir = $templateDir;
    }

    public function getTemplateDir() {
        return $this->templateDir;
    }

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function setTpl($tpl) {
        $this->setTemplate($tpl);
    }

    public function getTpl() {
        return $this->getTemplate();
    }

    public function setLayout($layout){
        $this->layout = $layout;
    }

    public function getLayout(){
        return $this->layout;
    }

    public function putError($key,$val){
        self::$errors[$key] = $val;
    }

    public function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }

    public function getErrors(){
        return self::$errors;
    }

    /**
     * Set the noRender flag (i.e., whether or not to autorender)
     * 
     * @param  boolean $flag 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setNoRender($flag = true)
    {
        $this->_noRender = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve noRender flag value
     * 
     * @return boolean
     */
    public function getNoRender()
    {
        return $this->_noRender;
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::getName()
     */
    public function getName() {
        return 'ViewRenderer';
    }

    public function __construct() {
        $this->view = new Smarty();

        foreach(self::$config as $key=>$val){
            $this->view->$key = $val;
        }

        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }

        $this->templateDir = S2BASE_PHP5_ROOT . '/app/modules';
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::init()
     */
    public function init()
    {
        if (Zend_Controller_Front::getInstance()->getParam('noViewRenderer')) {
            return;
        }

        if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
            $this->_actionController->view = $this->view;
        }
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::postDispatch()
     */
    public function postDispatch()
    {
        if (Zend_Controller_Front::getInstance()->getParam('noViewRenderer')) {
            return;
        }

        if (!$this->_noRender 
            && (null !== $this->_actionController)
            && $this->getRequest()->isDispatched()
            && !$this->getResponse()->isRedirect())
        {
            $this->render($this->getRequest()->getActionName());
        }
    }

    /**
     * @see Zend_View_Interface::render()
     */
    public function render($script) {
        $this->putError('validate', S2Base_ZfValidateSupportPlugin::getErrors($this->getRequest()));
        $moduleName = $this->getRequest()->getModuleName();
        $this->view->assign('request',$this->getRequest());
        $this->view->assign('errors',self::$errors);
        $this->view->assign('module', $moduleName);
        $this->view->assign('controller', $this->getRequest()->getControllerName());
        $this->view->assign('action', $this->getRequest()->getActionName());
        $this->view->assign('base_url', $this->getRequest()->getBaseUrl());
        $mod_url = $moduleName === S2BASE_PHP5_ZF_DEFAULT_MODULE ?
                   $this->getRequest()->getBaseUrl() :
                   $this->getRequest()->getBaseUrl() . '/' . $moduleName;
        $ctl_url = $mod_url . '/' . $this->getRequest()->getControllerName();
        $act_url = $ctl_url . '/' . $this->getRequest()->getActionName();
        $this->view->assign('mod_url', $mod_url);
        $this->view->assign('ctl_url', $ctl_url);
        $this->view->assign('act_url', $act_url);
        $ctlViewDir = $moduleName
                    . DIRECTORY_SEPARATOR . 'views'
                    . DIRECTORY_SEPARATOR . $this->getRequest()->getControllerName();
        $this->view->assign('ctl_view_dir', $this->templateDir . DIRECTORY_SEPARATOR . $ctlViewDir);
        $this->view->assign('commons_view_dir', S2BASE_PHP5_ROOT . '/app/commons/view');

        if ($this->template === null) {
            if (!preg_match('/\.' . S2BASE_PHP5_ZF_TPL_SUFFIX . '$/', $script)) {
                $script .= '.' . S2BASE_PHP5_ZF_TPL_SUFFIX;
            }
            $viewFile = $ctlViewDir . DIRECTORY_SEPARATOR . $script;
            if (!file_exists($this->templateDir . DIRECTORY_SEPARATOR . $viewFile)) {
                throw new S2Base_ZfException('template file not found. [' . 
                     $this->templateDir . DIRECTORY_SEPARATOR . $viewFile . ']');
            }
        } else {
            if (preg_match('/^file:/',$this->template)){
                $viewFile = $this->template;
            } else {
                if (!preg_match('/\.' . S2BASE_PHP5_ZF_TPL_SUFFIX . '$/', $this->template)) {
                    $this->template .= '.' . S2BASE_PHP5_ZF_TPL_SUFFIX;
                }
                $viewFile = $ctlViewDir . DIRECTORY_SEPARATOR . $this->template;
                if (!file_exists($this->templateDir . DIRECTORY_SEPARATOR . $viewFile)) {
                    throw new S2Base_ZfException('template file not found. [' . 
                         $this->templateDir . DIRECTORY_SEPARATOR . $viewFile . ']');
                }
            }
        }
        $this->view->template_dir = $this->templateDir;
        if($this->layout === null){
            $this->getResponse()->appendBody($this->view->fetch($viewFile));
        }else{
            $this->assign('content_for_layout', $viewFile);
            $this->getResponse()->appendBody($this->view->fetch($this->layout));
        }
        $this->setNoRender();
    }

    /**
     * Use this helper as a method; proxies to setRender()
     * 
     * @param  string $action 
     * @param  string $name 
     * @param  boolean $noController 
     * @return void
     */
    public function direct($template)
    {
        $this->setTemplate($template);
    }
}
