<?php
/**
 * moduleディレクトリを生成します。
 * 
 * 生成ディレクトリ
 * <ul>
 *   <li>app/modules/module名</li>
 *   <li>app/modules/module名/dao</li>
 *   <li>app/modules/module名/dicon</li>
 *   <li>app/modules/module名/entity</li>
 *   <li>app/modules/module名/interceptor</li>
 *   <li>app/modules/module名/service</li>
 *   <li>test/modules/module名/dao</li>
 *   <li>test/modules/module名/service</li>
 * </ul>
 * 
 */
class ModuleCommand implements S2Base_GenerateCommand {
    protected $moduleName;
    protected $srcDirectory;
    protected $testDirectory;

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "module";
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
        $this->moduleName = S2Base_StdinManager::getValue('module name ? : ');
        $this->validate($this->moduleName);
        $this->srcDirectory = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testDirectory = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        if (!$this->finalConfirm()){
            return;
        }
        $this->createDirectory();
        $this->prepareFiles();
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name : {$this->moduleName}" . PHP_EOL;

        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function createDirectory(){
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        S2Base_CommandUtil::createDirectory($this->srcDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->srcDirectory. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        S2Base_CommandUtil::createDirectory($this->testDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testDirectory. $dir);
        }
    }

    protected function prepareFiles(){
        $this->prepareModuleIncFile();
    }

    protected function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_DS . "{$this->moduleName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . S2BASE_PHP5_DS . 'module'
                     . S2BASE_PHP5_DS . 'include.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}

