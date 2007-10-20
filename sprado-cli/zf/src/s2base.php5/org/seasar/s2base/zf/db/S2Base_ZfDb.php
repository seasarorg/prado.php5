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
// $Id: S2Base_ZfDb.php 286 2007-04-21 04:36:44Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.db
 * @author     klove
 */
class S2Base_ZfDb {
    private function __construct(){}

    public static function factory($pdoDicon) {
        $container = S2ContainerFactory::create($pdoDicon);
        $cd = $container->getComponentDef('dataSource');
        $username = null;
        $password = null;
        if ($cd->hasPropertyDef('user')) {
            $username = $cd->getPropertyDef('user')->getValue();
        }
        if ($cd->hasPropertyDef('password')) {
            $password = $cd->getPropertyDef('password')->getValue();
        }
        $dsn = $cd->getPropertyDef('dsn')->getValue();
        list($pdoType, $pdoParams)= preg_split('/:/', $dsn, 2);
        $pdoType = 'PDO_' . $pdoType;
        $params = array('username' => $username, 'password' => $password);
        if (preg_match('/;/', $pdoParams)) {
            $items = preg_split('/;/', $pdoParams, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($items as $item) {
                list($key, $val)= preg_split('/=/', $item, 0);
                $params[trim($key)] = trim($val);
            }
        } else {
            $params['dbname'] = $pdoParams;
        }
        return Zend_Db::factory($pdoType, $params);
    }

    public static function setDefaultPdoAdapter($pdoDicon = PDO_DICON) {
        Zend_Db_Table::setDefaultAdapter(self::factory($pdoDicon));
    }
}
?>
