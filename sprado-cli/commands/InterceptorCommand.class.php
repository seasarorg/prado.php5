<?php
/**
 * interceptorを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/interceptor/interceptor名.class.php</li>
 * </ul>
 * 
 */
class InterceptorCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $interceptorClassName;
    
    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "interceptor";
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
         $this->interceptorClassName = S2Base_StdinManager::getValue('interceptor class name ? : ');
         $this->validate($this->interceptorClassName);
         if (!$this->finalConfirm()){
            return;
         }
        $this->prepareFiles();
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid interceptor name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name            : {$this->moduleName}" . PHP_EOL;
        print "  interceptor class name : {$this->interceptorClassName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    protected function prepareFiles(){
        $this->prepareInterceptorFile();
    }

    protected function prepareInterceptorFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_INTERCEPTOR_DIR
                 . S2BASE_PHP5_DS . $this->interceptorClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . "/interceptor/default.tpl");
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}

