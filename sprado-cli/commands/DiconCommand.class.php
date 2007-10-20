<?php
/**
 * Diconファイルを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/dicon/dicon名.dicon</li>
 * </ul>
 * 
 */
class DiconCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $diconName;

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "dicon";
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
        $this->diconName = S2Base_StdinManager::getValue('dicon name ? : ');
        $this->validate($this->diconName);
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid dicon name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name     : {$this->moduleName}" . PHP_EOL;
        print "  dicon file name : {$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . S2BASE_PHP5_DS . $this->diconName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/dicon/dicon.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
