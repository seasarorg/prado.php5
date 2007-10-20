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
// $Id: S2Base_ZfValidateSupportPlugin.php 286 2007-04-21 04:36:44Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfValidateSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const VALIDATE_DIR = 'validate';
    const DEFAULT_KEY  = 'default';
    const ERRORS_KEY   = 's2base_validate_errors';
    const ERR_KEY      = 's2base_validate_error';

    const ALNUM_KEY  = 'alnum';
    const ALPHA_KEY  = 'alpha';
    const DATE_KEY   = 'date';
    const FLOAT_KEY  = 'float';
    const INT_KEY    = 'int';
    const IP_KEY     = 'ip';

    private static $VALIDATE_CLASSES = array(self::ALNUM_KEY  => 'Zend_Validate_Alnum',
                                             self::ALPHA_KEY  => 'Zend_Validate_Alpha',
                                             self::DATE_KEY   => 'Zend_Validate_Date',
                                             self::FLOAT_KEY  => 'Zend_Validate_Float',
                                             self::INT_KEY    => 'Zend_Validate_Int',
                                             self::IP_KEY     => 'Zend_Validate_Ip');

    private $validators = array();
    private $validateFactories = array();

    public static function hasError(Zend_Controller_Request_Abstract $request, $paramName = null) {
        if ($request->has(self::ERR_KEY)) {
            if ($paramName === null) {
                return $request->getParam(self::ERR_KEY);
            } else {
                $errors = $request->getParam(self::ERRORS_KEY);
                return isset($errors[$paramName]);
            }
        }
        return false;
    }

    public static function getErrors(Zend_Controller_Request_Abstract $request, $paramName = null) {
        if ($request->has(self::ERRORS_KEY)) {
            if ($paramName === null) {
                return $request->getParam(self::ERRORS_KEY);
            } else {
                $errors = $request->getParam(self::ERRORS_KEY);
                if (isset($errors[$paramName])) {
                    return $errors[$paramName];
                }
            }
        }
        return array();
    }

    public function addValidateFactory(S2Base_ZfValidateFactory $validateFactory, $key = null) {
        if ($key === null) {
            $key = $validateFactory->getId();
        }
        if (isset(self::$VALIDATE_CLASSES[$key]) or
            isset($this->validateFactories[$key])) {
            throw new S2Base_ZfException("can not add " . get_class($validateFactory) . ". key [$key] exists.");
        }
        $this->validateFactories[$key] = $validateFactory;
        return $this;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $validateIni = S2BASE_PHP5_ROOT
                     . "/app/modules/$moduleName/$controllerName/"
                     . self::VALIDATE_DIR
                     . "/$actionName.ini";
        if (!is_file($validateIni)) {
            return;
        }
        $validateConfig = new Zend_Config_Ini($validateIni, null);
        $forceBreak = false;
        $errors = array();
        while ($validateConfig->valid()) {
            $paramName = strtolower($validateConfig->key());
            if ($paramName == self::DEFAULT_KEY) {
                $validateConfig->next();
                continue;
            }
            $paramConfig = $validateConfig->current();

            $vals = preg_split('/,/', $paramConfig->validate, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($vals as $valKey) {
                $valKey = strtolower(trim($valKey));
                $validator = $this->getValidatorInstance($valKey, $paramConfig, $paramName);
                $paramValue = $request->getParam($paramName);
                if (!$validator->isValid($paramValue)){
                    if($paramConfig->exception != null) {
                        throw new S2Base_ZfException($paramConfig->exception);
                    }

                    if ($paramConfig->$valKey !== null and $paramConfig->$valKey->msg !== null) {
                        $msg = $paramConfig->$valKey->msg;
                    } else { 
                        $msg = implode('. ', $validator->getMessages());
                    }
                    $errors[$paramName] = array('value'   => $paramValue,
                                                'msg'     => $msg,
                                                'pre_mod' => $moduleName,
                                                'pre_ctl' => $controllerName,
                                                'pre_act' => $actionName);

                    if($paramConfig->module != null) {
                        $request->setModuleName($paramConfig->module);
                    }
                    if($paramConfig->controller != null) {
                        $request->setControllerName($paramConfig->controller);
                    }
                    if($paramConfig->action != null) {
                        $request->setActionName($paramConfig->action);
                    }
                    if (strtolower($paramConfig->break) == 'true') {
                        $forceBreak = true;
                        break;
                    }
                }
            }
            if ($forceBreak) {
                break;
            }
            $validateConfig->next();
        }

        if (count($errors) > 0) {
            $request->setParam(self::ERR_KEY, true);
            $request->setParam(self::ERRORS_KEY, $errors);
        } else {
            $request->setParam(self::ERR_KEY, false);
        }
    }

    private function getValidatorInstance($valKey, $paramConfig, $paramName) {
        $validator = null;
        switch($valKey) {
            case self::ALNUM_KEY:
            case self::ALPHA_KEY:
            case self::DATE_KEY:
            case self::FLOAT_KEY:
            case self::INT_KEY:
            case self::IP_KEY:
                if (isset($this->validators[$valKey])) {
                    $validator = $this->validators[$valKey];
                } else {
                    $valClassName = self::$VALIDATE_CLASSES[$valKey];
                    Zend_Loader::loadClass($valClassName);
                    $validator = new $valClassName();
                    $this->validators[$valKey] = $validator;
                }
                break;
             default:
                if (isset($this->validateFactories[$valKey])) {
                    $validator =  $this->validateFactories[$valKey]->getInstance($paramName, $paramConfig);
                    if (! $validator instanceof Zend_Validate_Interface) {
                        throw new S2Base_ZfException("$valKey validate not implements Zend_Validate_Interface");
                    }
                } else {
                    throw new S2Base_ZfException("unsupported validate [$valKey]");
                }
                break;
        }
        return $validator;
    }
}
?>
