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
 * @package    org.seasar.s2base.zf.acl
 * @author     klove
 */
class S2Base_ZfDbAclFactory extends S2Base_ZfIniFileAclFactory {

    private $tableName      = 'users';
    private $identityColumn = 'username';
    private $adapter        = null;

    public function __construct() {
        parent::__construct();
    }

    public function setTableName($val) {
        $this->tableName = $val;
        return $this;
    }

    public function setIdentityColumn($val) {
        $this->identityColumn = $val;
        return $this;
    }

    public function setAdapter($val) {
        $this->Adapter = $val;
        return $this;
    }

    protected function setupRole() {
        if ($this->adapter === null) {
            $this->adapter = Zend_Db_Table::getDefaultAdapter();
        }

        $sql = "SELECT {$this->identityColumn} FROM {$this->tableName}";
        $this->adapter->setFetchMode(Zend_Db::FETCH_OBJ);
        $rows = $this->adapter->fetchAll($sql);
        $column = $this->identityColumn;
        foreach($rows as $row) {
            $this->roles[] = $row->$column;
            $this->acl->addRole(new Zend_Acl_Role($row->$column));
        }
    }
}
