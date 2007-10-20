<?php
class ModuleCommand implements S2Base_GenerateCommand {
    const MODEL_DIR = 'model';
    const VALIDATE_DIR = 'validate';

    protected $moduleName;
    protected $appModuleDir;
    protected $appCtlDir;
    protected $appModelDir;
    protected $appViewDir;
    protected $testModuleDir;
    protected $testCtlDir;
    protected $testModelDir;
    protected $controllerName;
    protected $controllerClassName;
    protected $controllerClassFile;
    protected $dispatcher;

    public function __construct(){
        $this->dispatcher = new S2Base_ZfDispatcherImpl();
    }

    public static function isStandardView() {
        if (defined('S2BASE_PHP5_USE_SMARTY') and S2BASE_PHP5_USE_SMARTY) {
            return false;
        }
        return true;
    }

    public static function getViewSuffixName() {
        if (self::isStandardView()) {
            return '_std';
        }
        return '';
    }

    public static function getCtlServiceInterfaceName($moduleName) {
        $dispatcher = new S2Base_ZfDispatcherImpl();
        return $dispatcher->formatName($moduleName, false) . 'Service';
    }

    public static function getActionControllerName($moduleName){
        $controllers = self::getAllControllers($moduleName);
        if(count($controllers) == 0){
            throw new Exception("Controller not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($controllers,'Controller list');
    }

    public static function getAllControllers($moduleName){
        $moduleDir = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $moduleName . S2BASE_PHP5_DS . 'models';
        if (!is_dir($moduleDir)) {
            throw new Exception("dir not exists : [ $moduleDir ]");
        }
        $entries = scandir($moduleDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $moduleDir ]");
        }

        $controllers = array();
        foreach($entries as $entry) {
            $ctlDir = $moduleDir . S2BASE_PHP5_DS . $entry;
            $ctlIncFile = $ctlDir . S2BASE_PHP5_DS . "$entry.inc.php";
            if(is_dir($ctlDir) and is_file($ctlIncFile)) {
                $controllers[] = $entry;
            }
        }
        return $controllers;
    }
    
    public function getName(){
        return "module & controller";
    }

    public function isAvailable(){
        return true;
    }

    public function execute(){
        $this->moduleName = $this->getModuleName();
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }
        $this->validate($this->moduleName);
        $this->controllerName = S2Base_StdinManager::getValue('controller name ? : ');
        $this->validate($this->controllerName);
        list($this->controllerName, $this->controllerClassName, $this->controllerClassFile) = 
            self::getControllerNames($this->dispatcher, $this->moduleName, $this->controllerName);
        $this->ctlServiceInterfaceName = self::getCtlServiceInterfaceName($this->controllerName);
        if (!$this->finalConfirm()){
            return;
        }
        $this->createDirectory();
        $this->prepareFiles();
    }

    public static function getControllerNames($dispatcher, $moduleName, $controllerName) {
        $controllerName = self::formatModuleName($controllerName);
        $controllerClassName = $dispatcher->formatControllerName($controllerName);
        $controllerClassFile = $controllerClassName;
        if ($moduleName != S2BASE_PHP5_ZF_DEFAULT_MODULE) {
            $controllerClassName = $dispatcher->formatModuleName($moduleName)
                                 . '_' . $controllerClassName;
        }
        return array($controllerName, $controllerClassName, $controllerClassFile);
    }
    
    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  controller class name     : {$this->controllerClassName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    public function createDirectory(){
        $this->srcDefaultModuleDir = S2BASE_PHP5_MODULES_DIR
                                   . S2BASE_PHP5_DS . S2BASE_PHP5_ZF_DEFAULT_MODULE;
        $this->appModuleDir = S2BASE_PHP5_MODULES_DIR
                            . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir = $this->appModuleDir
                         . S2BASE_PHP5_DS . 'controllers';
        $this->appModelDir = $this->appModuleDir
                           . S2BASE_PHP5_DS . 'models';
        $this->appViewDir = $this->appModuleDir
                          . S2BASE_PHP5_DS . 'views';

        $this->testDefaultModuleDir = S2BASE_PHP5_TEST_MODULES_DIR
                                    . S2BASE_PHP5_DS . S2BASE_PHP5_ZF_DEFAULT_MODULE;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR
                             . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir = $this->testModuleDir
                          . S2BASE_PHP5_DS . 'controllers';
        $this->testModelDir = $this->testModuleDir
                            . S2BASE_PHP5_DS . 'models';
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_DS . self::MODEL_DIR,
            S2BASE_PHP5_DS . self::VALIDATE_DIR);

        S2Base_CommandUtil::createDirectory($this->appModuleDir);
        S2Base_CommandUtil::createDirectory($this->srcDefaultModuleDir);
        S2Base_CommandUtil::createDirectory($this->appCtlDir);
        S2Base_CommandUtil::createDirectory($this->appModelDir);
        S2Base_CommandUtil::createDirectory($this->appModelDir . S2BASE_PHP5_DS . $this->controllerName);
        S2Base_CommandUtil::createDirectory($this->appViewDir);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->appModelDir . S2BASE_PHP5_DS . $this->controllerName. S2BASE_PHP5_DS . $dir);
        }

        if (self::isStandardView()) {
            S2Base_CommandUtil::createDirectory($this->appViewDir. S2BASE_PHP5_DS . 'scripts');
            S2Base_CommandUtil::createDirectory($this->appViewDir. S2BASE_PHP5_DS . 'scripts' . S2BASE_PHP5_DS . $this->controllerName);
            S2Base_CommandUtil::createDirectory($this->appViewDir. S2BASE_PHP5_DS . 'helpers');
            S2Base_CommandUtil::createDirectory($this->appViewDir. S2BASE_PHP5_DS . 'filters');
            $commonsViewDir = S2BASE_PHP5_ROOT
                            . S2BASE_PHP5_DS . 'app'
                            . S2BASE_PHP5_DS . 'commons'
                            . S2BASE_PHP5_DS . 'view';
            S2Base_CommandUtil::createDirectory($commonsViewDir . S2BASE_PHP5_DS . 'scripts');
            S2Base_CommandUtil::createDirectory($commonsViewDir . S2BASE_PHP5_DS . 'helpers');
            S2Base_CommandUtil::createDirectory($commonsViewDir . S2BASE_PHP5_DS . 'filters');
        } else {
            S2Base_CommandUtil::createDirectory($this->appViewDir. S2BASE_PHP5_DS . $this->controllerName);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_DS . self::MODEL_DIR);
        S2Base_CommandUtil::createDirectory($this->testModuleDir);
        S2Base_CommandUtil::createDirectory($this->testDefaultModuleDir);
        S2Base_CommandUtil::createDirectory($this->testCtlDir);
        S2Base_CommandUtil::createDirectory($this->testModelDir);
        S2Base_CommandUtil::createDirectory($this->testModelDir . S2BASE_PHP5_DS . $this->controllerName);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testModelDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS . $dir);
        }
    }

    public function prepareFiles(){
        $this->prepareActionControllerClassFile();
        $this->prepareActionControllerTestFile();
        $this->prepareModuleIncFile();
        $this->prepareHtmlFile();
    }

    public function prepareActionControllerClassFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/module/controller.tpl");
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/",
                      "/@@SERVICE_CLASS_NAME@@/");
        $reps = array($this->controllerClassName,
                      $this->ctlServiceInterfaceName);
        $tempContent = preg_replace($keys, $reps, $tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareActionControllerTestFile(){
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_DS
                 . $this->controllerClassName . 'Test'
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/module/test.tpl");
        $keys = array("/@@CLASS_NAME@@/",
                      "/@@MODULE_NAME@@/",
                      "/@@CONTROLLER_NAME@@/",
                      "/@@CONTROLLER_CLASS@@/");
        $reps = array($this->controllerClassName . 'Test',
                      $this->moduleName,
                      $this->controllerName,
                      $this->controllerClassName);
        $tempContent = preg_replace($keys, $reps, $tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareModuleIncFile(){
        $srcFile = $this->appModelDir
                 . S2BASE_PHP5_DS . $this->controllerName
                 . S2BASE_PHP5_DS . "{$this->controllerName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/module/include.tpl');
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/",
                      "/@@CONTROLLER_FILE_NAME@@/");
        $reps = array($this->controllerClassName,
                      $this->controllerClassFile);
        $tempContent = preg_replace($keys, $reps, $tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareHtmlFile(){
        if (self::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'index.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'index.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $viewSuffix = self::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeletons/module/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public static function formatModuleName($name){
        if (trim($name) == '') {
            throw new Exception('invalid name. [empty]');
        }
        $name = preg_replace("/^_+/","",$name);
        $name = preg_replace("/_+$/","",$name);
        if (preg_match("/_/",$name)){
            $words = explode('_', $name);
            $fw = array_shift($words);
            $name = implode(' ', $words);
            $name = ucwords($name);
            $name = preg_replace("/\s/", '', $name);
            $name = $fw . $name;
        }
        return $name;
    }

    protected function getModuleName() {
        $createLabel = '(new module)';

        $modules = S2Base_CommandUtil::getAllModules();

        if (count($modules) == 0) {
            array_unshift($modules, $createLabel, S2BASE_PHP5_ZF_DEFAULT_MODULE);
        } else {
            array_unshift($modules, $createLabel);
        }
        
        $result = S2Base_StdinManager::getValueFromArray($modules,"Module list");
        if ($result == $createLabel) {
            return S2Base_StdinManager::getValue('module name ? : ');
        }
        return $result;
    }

    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    public function getSrcModuleDir() {
        return $this->appModuleDir;
    }
    public function setSrcModuleDir($appModuleDir) {
        $this->appModuleDir = $appModuleDir;
    }

    public function getSrcCtlDir() {
        return $this->appCtlDir;
    }
    public function setSrcCtlDir($appCtlDir) {
        $this->appCtlDir = $appCtlDir;
    }

    public function getTestModuleDir() {
        return $this->testModuleDir;
    }
    public function setTestModuleDir($testModuleDir) {
        $this->testModuleDir = $testModuleDir;
    }

    public function getTestCtlDir() {
        return $this->testCtlDir;
    }
    public function setTestCtlDir($testCtlDir) {
        $this->testCtlDir = $testCtlDir;
    }

    public function getControllerName() {
        return $this->controllerName;
    }
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function getControllerClassName() {
        return $this->controllerClassName;
    }
    public function setControllerClassName($controllerClassName) {
        $this->controllerClassName = $controllerClassName;
    }

    public function getControllerClassFile() {
        return $this->controllerClassFile;
    }
    public function setControllerClassFile($controllerClassFile) {
        $this->controllerClassFile = $controllerClassFile;
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function setCtlServiceInterfaceName($ctlServiceInterfaceName) {
        $this->ctlServiceInterfaceName = $ctlServiceInterfaceName;
    }

}
