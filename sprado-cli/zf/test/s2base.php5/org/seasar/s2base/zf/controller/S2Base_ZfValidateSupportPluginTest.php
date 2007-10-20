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
// $Id: S2Base_ZfValidateSupportPluginTest.php 287 2007-04-21 04:37:46Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfValidateSupportPluginTest extends PHPUnit_Framework_TestCase {
    private $request = null;
    private $plugin  = null;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testHasError() {
        $this->assertFalse($this->plugin->hasError($this->request));
        $this->assertFalse($this->plugin->hasError($this->request, 'aaa'));

        $this->request->setParam(S2Base_ZfValidateSupportPlugin::ERR_KEY, true);
        $this->assertTrue($this->plugin->hasError($this->request));

        $this->request->setParam(S2Base_ZfValidateSupportPlugin::ERRORS_KEY, array('aa'=>''));
        $this->assertTrue($this->plugin->hasError($this->request, 'aa'));
        $this->assertFalse($this->plugin->hasError($this->request, 'bb'));
    }

    public function testGetErrors() {
        $this->assertTrue($this->plugin->getErrors($this->request) == array());
        $errors = array('aa' => array('AA'),
                        'bb' => array('BB'));
        $this->request->setParam(S2Base_ZfValidateSupportPlugin::ERRORS_KEY, $errors);
        $this->assertTrue($this->plugin->getErrors($this->request) == $errors);
        $this->assertTrue($this->plugin->getErrors($this->request, 'aa') == array('AA'));
        $this->assertTrue($this->plugin->getErrors($this->request, 'cc') == array());
    }

    public function testAddValidateFactory() {
        $this->plugin->addValidateFactory(new V_S2Base_ZfValidateSupportPluginTest());

        try {
            $this->plugin->addValidateFactory(new V_S2Base_ZfValidateSupportPluginTest());
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $this->plugin->addValidateFactory(new V_S2Base_ZfValidateSupportPluginTest(), 'yyy');

        try {
            $this->plugin->addValidateFactory(new V_S2Base_ZfValidateSupportPluginTest(), 'yyy');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $this->request = new Zend_Controller_Request_Http();
        $this->plugin = new S2Base_ZfValidateSupportPlugin();
    }

    public function tearDown() {
        print "\n";
    }
}

class V_S2Base_ZfValidateSupportPluginTest implements S2Base_ZfValidateFactory {
    public function getInstance($paramName, Zend_Config $config) {
        return new Zend_Validate_Int();
    }

    public function getId() {
        return 'xxx';
    }
}
?>
