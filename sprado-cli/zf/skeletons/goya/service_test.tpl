<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $controller = "@@CONTROLLER_NAME@@";
    private $container;
    private $service;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA() {
    }

    public function setUp() {
        print __CLASS__ . "::{$this->getName()}" . PHP_EOL;
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->service = $this->container->getComponent('@@SERVICE_CLASS@@');
    }

    public function tearDown() {
        print PHP_EOL;
        $this->container = null;
        $this->service = null;
    }
}
