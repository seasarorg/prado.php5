<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $container;
    private $dao;

    function __construct($name) {
        parent::__construct($name);
    }

    function testA() {
    }

    function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $moduleDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}";
        require_once($moduleDir . "/{$this->module}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent("@@DAO_CLASS@@");
    }

    function tearDown() {
        print "\n";
        $this->container = null;
        $this->dao = null;
    }

}
