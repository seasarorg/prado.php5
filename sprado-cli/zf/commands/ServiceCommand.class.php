<?php
class ServiceCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
    protected $serviceClassName;
    protected $appModuleDir;
    protected $appCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    public function getName(){
        return "service";
    }

    public function isAvailable(){
        return true;
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }
        $this->controllerName = ModuleCommand::getActionControllerName($this->moduleName);
        if(S2Base_CommandUtil::isListExitLabel($this->controllerName)){
            return;
        }
        $this->moduleServiceInterfaceName = ModuleCommand::getCtlServiceInterfaceName($this->controllerName);
        $this->serviceClassName = S2Base_StdinManager::getValue('service class name ? : ');
        $this->validate($this->serviceClassName);

        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->appModuleDir  = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir     = $this->appModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->prepareServiceClassFile();
        $this->prepareServiceTestFile();
    }
    
    protected function prepareServiceClassFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/service/service.tpl');

        $patterns = array("/@@CLASS_NAME@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/service/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
