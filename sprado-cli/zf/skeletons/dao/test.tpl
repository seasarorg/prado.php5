<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $controller = "@@CONTROLLER_NAME@@";
    private $container;
    private $dao;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA() {
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent("@@DAO_CLASS@@");
    }

    public function tearDown() {
        print "\n";
        $this->container = null;
        $this->dao = null;
    }
}
