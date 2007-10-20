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
// $Id: S2Base_ZfDbTest.php 287 2007-04-21 04:37:46Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.db
 * @author     klove
 */
class S2Base_ZfDbTest extends PHPUnit_Framework_TestCase {
    public function __construct($name) {
        parent::__construct($name);
    }

    public function testMySqlPdo(){
        $adaptor = S2Base_ZfDb::factory(dirname(__FILE__) . '/dicons/mysql.dicon');
        $this->assertTrue($adaptor instanceof Zend_Db_Adapter_Pdo_Mysql);
    }

    public function testSqlitePdo(){
        $adaptor = S2Base_ZfDb::factory(dirname(__FILE__) . '/dicons/sqlite.dicon');
        $this->assertTrue($adaptor instanceof Zend_Db_Adapter_Pdo_Sqlite);
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
    }

    public function tearDown() {
        print "\n";
    }
}
?>
