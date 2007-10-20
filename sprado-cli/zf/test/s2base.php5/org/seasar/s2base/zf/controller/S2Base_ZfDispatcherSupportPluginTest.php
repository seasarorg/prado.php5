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
// $Id: S2Base_ZfDispatcherSupportPluginTest.php 287 2007-04-21 04:37:46Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfDispatcherSupportPluginTest extends PHPUnit_Framework_TestCase {
    private $request = null;
    private $plugin  = null;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testValidateModule(){
        $moduleName = '';
        $this->request->setModuleName($moduleName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $moduleName = '.';
        $this->request->setModuleName($moduleName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $moduleName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherSupportPlugin::PARAM_MAX_LEN ;$i++) {
            $moduleName .= 'a';
        }
        $this->request->setModuleName($moduleName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }
    }

    public function testValidateController(){
        $moduleName = 'default';
        $this->request->setModuleName($moduleName);

        $controllerName = '';
        $this->request->setControllerName($controllerName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $controllerName = '_';
        $this->request->setModuleName($moduleName);
        $this->request->setControllerName($controllerName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $controllerName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherSupportPlugin::PARAM_MAX_LEN ;$i++) {
            $controllerName .= 'a';
        }
        $this->request->setControllerName($controllerName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }
    }

    public function testValidateAction(){
        $moduleName = 'default';
        $this->request->setModuleName($moduleName);
        $controllerName = 'index';
        $this->request->setControllerName($controllerName);

        $actionName = '-';
        $this->request->setActionName($actionName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->assertTrue(true);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $actionName = '.';
        $this->request->setActionName($actionName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->assertTrue(true);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $actionName = '';
        $this->request->setActionName($actionName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $actionName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherSupportPlugin::PARAM_MAX_LEN ;$i++) {
            $actionName .= 'a';
        }
        $this->request->setActionName($actionName);
        try {
            $this->plugin->routeShutdown($this->request);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $this->request = new Zend_Controller_Request_Http();
        $this->plugin = new S2Base_ZfDispatcherSupportPlugin();
    }

    public function tearDown() {
        print "\n";
    }
}
?>
