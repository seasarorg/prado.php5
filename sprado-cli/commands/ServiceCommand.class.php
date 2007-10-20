<?php
/**
 * serviceを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/service/service名.class.php</li>
 *   <li>app/modules/module名/service/service名Impl.class.php</li>
 *   <li>app/modules/module名/dicon/service名Impl.dicon</li>
 *   <li>test/modules/module名/service/service名ImplTest.class.php</li>
 * </ul>
 * 
 */
class ServiceCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $serviceClassName;
    
    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "service";
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }

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
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name : {$this->serviceClassName}Test" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareServiceClassFile();
        $this->prepareServiceTestFile();
    }
    
    protected function prepareServiceClassFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/service/service.tpl');

        $patterns = array('/@@CLASS_NAME@@/');
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/service/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
