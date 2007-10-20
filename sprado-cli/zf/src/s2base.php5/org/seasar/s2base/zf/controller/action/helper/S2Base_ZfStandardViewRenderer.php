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
class S2Base_ZfStandardViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer {

    private static $errors   = array();

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
     * @see Zend_Controller_Action_Helper_Abstract::getName()
     */
    public function getName() {
        return 'ViewRenderer';
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::postDispatch()
     */
    public function postDispatch()
    {
        if (Zend_Controller_Front::getInstance()->getParam('noViewRenderer')) {
            return;
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $moduleName = $request->getModuleName();
        $this->putError('validate', S2Base_ZfValidateSupportPlugin::getErrors($request));
        $this->view->assign('request', $request);
        $this->view->assign('errors', self::$errors);
        $this->view->assign('module', $moduleName);
        $this->view->assign('controller', $request->getControllerName());
        $this->view->assign('action', $request->getActionName());
        $this->view->assign('base_url', $request->getBaseUrl());
        $mod_url = $moduleName === S2BASE_PHP5_ZF_DEFAULT_MODULE ?
                   $request->getBaseUrl() :
                   $request->getBaseUrl() . '/' . $moduleName;
        $ctl_url = $mod_url . '/' . $request->getControllerName();
        $act_url = $ctl_url . '/' . $request->getActionName();
        $this->view->assign('mod_url', $mod_url);
        $this->view->assign('ctl_url', $ctl_url);
        $this->view->assign('act_url', $act_url);

        parent::postDispatch();
    }
}
