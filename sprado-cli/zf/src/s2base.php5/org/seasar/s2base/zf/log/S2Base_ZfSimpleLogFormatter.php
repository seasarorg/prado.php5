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
 * @version    Release: 1.2.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.2.0
 * @package    org.seasar.s2base.zf.log
 * @author     klove
 */
class S2Base_ZfSimpleLogFormatter implements Zend_Log_Formatter_Interface {

    /**
     * @param array $event
     *        array('timestamp'    => date('c'),
     *              'message'      => $message,
     *              'priority'     => $priority,
     *              'priorityName' => $this->_priorities[$priority]),
     *              $this->_extras);
     * @return string formatted messages.
     */
    public function format($event) {
        $timestamp = str_replace('T', ' ', $event['timestamp']);
        $timestamp = preg_replace('/\+.+$/', '', $timestamp);
        $priorityName = sprintf('[%-6s]', $event['priorityName']);
        return "$timestamp $priorityName {$event['message']}" . PHP_EOL;
    }
}
