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
 * @version    Release: 1.0.3
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.3
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfDispatcherImpl extends S2Base_ZfAbstractDispatcher {
    const PARAM_MAX_LEN = 50;
    protected $disableS2Container = false;

    public function setDisableS2Container($val = true) {
        $this->disableS2Container = $val;
    }

    /**
     * @see Zend_Controller_Dispatcher_Abstrac::_formatName()
     */
    public function formatName($unformatted, $isAction = false) {
        return $this->_formatName($unformatted, $isAction);
    }

    /**
     * @see S2Base_ZfAbstractDispatcher::instantiateController()
     */
    public function instantiateController(Zend_Controller_Request_Abstract $request, $moduleName, $controllerClassName) {
        if ($this->disableS2Container) {
            return new $controllerClassName($request, $this->getResponse(), $this->getParams()); 
        }

        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $this->validateModule($moduleName);
        $this->validateController($controllerName);
        $this->validateAction($actionName);

        $formatedActionName = $this->formatName($actionName);
        $actionMethodName = $this->getActionMethod($request);
        $controllerDir = "/app/modules/$moduleName/models/$controllerName";
        $actionDicon   = S2BASE_PHP5_ROOT . $controllerDir . "/dicon/$actionMethodName.dicon";
        $moduleIncFile = S2BASE_PHP5_ROOT . $controllerDir . "/$controllerName.inc.php";
        $actionIncFile = S2BASE_PHP5_ROOT . $controllerDir . "/$actionMethodName.inc.php";

        require_once($moduleIncFile);
        if (is_file($actionIncFile)) {
            require_once($actionIncFile);
        }

        $container = null;
        $cd = null;
        if (is_file($actionDicon)) {
            $container = S2ContainerFactory::create($actionDicon);
            $cd = $container->getComponentDef($controllerClassName);
        } else {
            $container = S2ContainerApplicationContext::create();
            $cd = $container->getComponentDef($controllerClassName);
        }

        if ($cd->getArgDefSize() == 0) {
            $cd->addArgDef(new S2Container_ArgDefImpl($request));
            $cd->addArgDef(new S2Container_ArgDefImpl($this->getResponse()));
            $cd->addArgDef(new S2Container_ArgDefImpl($this->getParams()));
        }

        return $container->getComponent($controllerClassName);
    }

    private function validateModule($value) {
        if (!preg_match('/^[_a-zA-Z0-9]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid module. [$value]");
        }
    }

    private function validateController($value) {
        if (!preg_match('/^[a-zA-Z0-9]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid controller. [$value]");
        }
    }

    private function validateAction($value) {
        if (!preg_match('/^[_a-zA-Z0-9\.\-]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid action. [$value]");
        }
    }
}
