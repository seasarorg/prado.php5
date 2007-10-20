<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module     = '@@MODULE_NAME@@';
    private $controller = '@@CONTROLLER_NAME@@';

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testIndexAction(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $request->setRequestUri("/{$this->module}/{$this->controller}/index");
        try {
           //$response = Zend_Controller_Front::getInstance()->dispatch();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}" . PHP_EOL;
        $fc = Zend_Controller_Front::getInstance();
        $fc->resetInstance();
        S2Base_ZfInitialize::initTest();
        $fc->returnResponse(true);
    }

    public function tearDown() {
        print PHP_EOL;
    }
}
