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
// $Id: S2Base_ZfRegexValidateFactoryTest.php 287 2007-04-21 04:37:46Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.validate.vactory.impl
 * @author     klove
 */
class S2Base_ZfRegexValidateFactoryTest extends PHPUnit_Framework_TestCase {
    public function __construct($name) {
        parent::__construct($name);
    }

    public function testGetInstance(){

        $config = new Zend_Config(array(), true);
        try {
            $this->factory->getInstance('aa', $config);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $config->regex = array('xxx' => '/.*/');
        try {
            $this->factory->getInstance('bb', $config);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $config->regex = array('Pattern' => '/.*/');
        try {
            $this->factory->getInstance('cc', $config);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $config->regex = array('pattern' => '.*');
        $validate = $this->factory->getInstance('aa', $config);
        $this->assertTrue($validate instanceof Zend_Validate_Regex);
        $this->assertTrue($validate->getPattern() === '.*');

        $config->regex = array('pattern' => '.+');
        $validate2 = $this->factory->getInstance('aa', $config);
        $this->assertTrue($validate instanceof Zend_Validate_Regex);

        $this->assertTrue($validate === $validate2);
        $this->assertTrue($validate->getPattern() === '.+');
        $this->assertTrue($validate2->getPattern() === '.+');
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $this->factory = new S2Base_ZfRegexValidateFactory();
    }

    public function tearDown() {
        print "\n";
    }
}
?>
