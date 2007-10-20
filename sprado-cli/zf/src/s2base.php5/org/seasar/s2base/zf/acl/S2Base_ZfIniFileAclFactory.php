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
class S2Base_ZfIniFileAclFactory {
    protected $acl = null;
    protected $resources = array();
    protected $roles     = array();
    private $modulesDir    = null;
    private $aclIniFile    = null;
    private $groupIniFile  = null;
    private $passwdIniFile = null;

    public function setAcl(Zend_Acl $val = null) {
        $this->modulesDir = $val;
    }

    public function setModulesDir($val) {
        $this->modulesDir = $val;
    }

    public function setAclIniFile($val) {
        $this->aclIniFile = $val;
    }

    public function setGroupIniFile($val) {
        $this->groupIniFile = $val;
    }

    public function setPasswdIniFile($val) {
        $this->passwdIniFile = $val;
    }

    public function __construct() {
        if (defined('S2BASE_PHP5_ROOT')) {
            $this->modulesDir    = S2BASE_PHP5_ROOT . '/app/modules';
            $this->aclIniFile    = S2BASE_PHP5_ROOT . '/config/acl.ini';
            $this->groupIniFile  = S2BASE_PHP5_ROOT . '/config/group.ini';
            $this->passwdIniFile = S2BASE_PHP5_ROOT . '/config/passwd.ini';
        }
    }

    public function create() {
        if ($this->acl instanceof Zend_Acl) {
            return $this->acl;
        }
        $this->acl = new Zend_Acl();
        $this->resources = array();
        $this->roles = array();
        $this->setupResource();
        $this->setupRole();
        $this->setupAcl();
        return $this->acl;
    }

    protected function setupResource() {
        $items = scandir($this->modulesDir);
        foreach($items as $item) {
            if (!is_dir($this->modulesDir . DIRECTORY_SEPARATOR . $item) or
                preg_match('/^\./', $item)) {
                continue;
            }
            $this->acl->add(new Zend_Acl_Resource($item));
            $this->resources[] = $item;
        }
    }

    protected function setupRole() {
        $this->setupGroup();
        $this->setupUser();
    }

    protected function setupGroup() {
        $configs = new Zend_Config_Ini($this->groupIniFile, null);
        while ($configs->valid()) {
            $config = $configs->current();
            if (isset($config->group)) {
                $this->roles[] = $config->group;
                $this->acl->addRole(new Zend_Acl_Role($config->group));
            } else {
                throw new Exception("group not found.[id : {$configs->key()}]");
            }
            $configs->next();
        }
    }

    protected function setupUser() {
        $configs = new Zend_Config_Ini($this->passwdIniFile, null);
        while ($configs->valid()) {
            $config = $configs->current();
            if (isset($config->user)) {
                $this->roles[] = $config->user;
                if (isset($config->group)) {
                    $this->acl->addRole(new Zend_Acl_Role($config->user),
                        explode(',', preg_replace('/\s*/', '', $config->group)));
                } else {
                    $this->acl->addRole(new Zend_Acl_Role($config->user));
                }
            } else {
                throw new Exception("user not found.[id : {$configs->key()}]");
            }
            $configs->next();
        }
    }

    protected function setupAcl() {
        $configs = new Zend_Config_Ini($this->aclIniFile, null);
        $resourceCount = count($this->resources);
        $roleCount = count($this->roles);
        while ($configs->valid()) {
            $config = $configs->current();
            if (!isset($config->role) or
                !isset($config->module) or
                !isset($config->access)) {
                throw new Exception("invalid acl config found.[id : {$configs->key()}]");
            }
            for ($i=0; $i<$roleCount; $i++) {
                if (!preg_match($config->role, $this->roles[$i])) {
                    continue;
                }
                for ($j=0; $j<$resourceCount; $j++) {
                    if (!preg_match($config->module, $this->resources[$j])) {
                        continue;
                    }
                    $access = $config->access;
                    $this->acl->$access($this->roles[$i], $this->resources[$j]);
                }
            }
            $configs->next();
        }
    }
}

