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
 * @version    Release: 2.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 2.0.0
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfAnaSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    private $moduleName     = null;
    private $controllerName = 'index';
    const LOGIN_ACTION      = 'login';
    const ERROR_NONE = 1;
    const ERROR_AUTH = 2;
    const ERROR_ROLE = 3;
    const ERROR_KEY = __CLASS__;

    public function setModuleName($val) {
        $this->moduleName = $val;
        return $this;
    }

    public function setControllerName($val) {
        $this->controllerName = $val;
        return $this;
    }

    public static function hasError(Zend_Controller_Request_Abstract $request) {
        return $request->has(self::ERROR_KEY);
    }

    public static function getError(Zend_Controller_Request_Abstract $request) {
        return $request->getParam(self::ERROR_KEY);
    }

    public static function getErrorCode(Zend_Controller_Request_Abstract $request) {
        if ($request->has(self::ERROR_KEY)) {
            $errors = $request->getParam(self::ERROR_KEY);
            return $errors['code'];
        }
        return self::ERROR_NONE;
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if ($request->getModuleName() === $this->moduleName) {
            S2Container_S2Logger::getLogger(__CLASS__)->info("module [{$this->moduleName}] is unsecured.", __METHOD__);
            return;
        }

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $msg = 'not authorized.';
            S2Container_S2Logger::getLogger(__CLASS__)->info($msg, __METHOD__);
            if ($this->moduleName !== null) {
                $this->setupRequest($request, self::ERROR_AUTH, $msg);
            } else {
                throw new Exception($msg);
            }
            return;
        }

        $identity = Zend_Auth::getInstance()->getIdentity();
        if (!S2Base_ZfAclFactory::create()->isAllowed($identity, $request->getModuleName())) {
            $msg = "role [{$identity}] denied.";
            S2Container_S2Logger::getLogger(__CLASS__)->info($msg, __METHOD__);
            if ($this->moduleName !== null) {
                $this->setupRequest($request, self::ERROR_ROLE, $msg);
            } else {
                throw new Exception($msg);
            }
            return;
        }
    }

    private function setupRequest(Zend_Controller_Request_Abstract $request, $errorCode, $message) {
        $request->setParam(self::ERROR_KEY,
                           array('code'       => $errorCode,
                                 'message'    => $message,
                                 'module'     => $request->getModuleName(),
                                 'controller' => $request->getControllerName(),
                                 'action'     => $request->getActionName()));
        $request->setModuleName($this->moduleName)
                ->setControllerName($this->controllerName)
                ->setActionName(self::LOGIN_ACTION);
    }
}
