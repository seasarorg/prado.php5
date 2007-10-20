<?php
/**
 * Daoを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/dao/dao名.class.php</li>
 *   <li>app/modules/module名/dicon/dao名.dicon</li>
 *   <li>test/modules/module名/dao/dao名Test.class.php</li>
 *   <li>app/modules/module名/entity/entity名.dicon</li>
 * </ul>
 * 
 */
class DaoCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $daoInterfaceName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $cols;
    protected $useCommonsDao;
    protected $useDB;
    protected $tableName;
    protected $tableNames;

    /**
     * daoインターフェイス名からentityクラス名を導出します。
     * 
     * @param string $daoInterfaceName daoインターフェイス名
     * @return string entityクラス名
     */
    public static function guessEntityName($daoInterfaceName){
        $patterns = array("/Dao$/");
        $replacements = array('Entity');
        $guess = preg_replace($patterns,$replacements,$daoInterfaceName);
        return $guess == $daoInterfaceName ? 
                         $daoInterfaceName . 'Entity' : 
                         $guess;
    }

    /**
     * app/commons/daoにdaoが存在するかどうかを確認します。
     * 存在する場合は、使用するかどうかを確認します。
     * 
     * @return boolean 
     */
    public static function isCommonsDaoAvailable() {
        $daos = self::getAllDaoFromCommonsDao();
        if(count($daos) > 0){
            return S2Base_StdinManager::isYes('use commons dao ?');
        } else {
            return false;
        }
    }

    /**
     * app/commons/daoにあるdaoをすべて取得します。
     * 
     * @return array dao名が格納された配列
     */
    public static function getAllDaoFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $daos = array();
        foreach($entries as $entry){
            $maches = array();
            if(preg_match("/(\w+Dao)\.class\.php$/",$entry,$maches)){
                $daos[] = $maches[1];
            }
        }
        return $daos;
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "dao";
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

        $this->useCommonsDao = $this->isUseCommonsDao();
        if($this->useCommonsDao){
            if ($this->getDaoInfoFromCommonsDao(self::getAllDaoFromCommonsDao()) and
                $this->finalConfirm()){
                $this->prepareFiles();
            }
        } else {
            $this->useDB = $this->isUseDB();
            if ($this->useDB) {
                if ($this->getDaoInfoFromDB() and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            } else {
                if ($this->getDaoInfoInteractive() and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        }
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function getDaoInfoFromCommonsDao($daos){
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if (S2Base_CommandUtil::isListExitLabel($daoName)){
            return false;
        }
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->extendsEntityClassName = "none";
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
        return true;
    }

    protected function getDaoInfoFromDB(){
        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $this->tableNames = S2Base_StdinManager::getValuesFromArray($dbms->getTables(),
                                                                  "table list");
        $this->tableName = $this->tableNames[0];
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = EntityCommand::getColumnsFromTables($dbms, $this->tableNames);

        $this->daoInterfaceName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->extendsEntityClassName = "none";

        $daoInterfaceNameTmp = S2Base_StdinManager::getValue("dao interface name [{$this->daoInterfaceName}]? : ");
        $this->daoInterfaceName = trim($daoInterfaceNameTmp) == '' ? $this->daoInterfaceName : $daoInterfaceNameTmp;
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);
        return true;
    }

    protected function getDaoInfoInteractive(){
        $this->daoInterfaceName = S2Base_StdinManager::getValue('dao interface name ? : ');
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = self::guessEntityName($this->daoInterfaceName);
        $this->entityClassName = S2Base_StdinManager::getValue("entity class name ? [$entityClassNameTmp] : ");
        if(trim($this->entityClassName) == ''){
            $this->entityClassName = $entityClassNameTmp;
        }
        $this->validate($this->entityClassName);

        $this->entityExtends = $this->isEntityExtends();
        $this->extendsEntityClassName = "none";
        if ($this->entityExtends) {
            $entitys = EntityCommand::getAllEntityFromCommonsDao();
            $this->extendsEntityClassName = S2Base_StdinManager::getValueFromArray($entitys,
                                            "entity list");
            if (S2Base_CommandUtil::isListExitLabel($this->extendsEntityClassName)){
                return false;
            }
            $this->tableName = "extended";
        } else {
            $tableNameTmp = EntityCommand::guessTableName($this->entityClassName);
            $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
            if(trim($this->tableName) == ''){
                $this->tableName = $tableNameTmp;
            }
            $this->validate($this->tableName);
        }

        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,,) : ");
        $this->cols = EntityCommand::validateCols($cols);

        return true;
    }

    protected function getCmdMessage(){
        if ($this->entityClassName == null){
            return 'entity class name ? : ';
        }else{
            return "entity class name ? [$this->entityClassName] : ";
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name          : {$this->moduleName} " . PHP_EOL;
        print "  dao interface name   : {$this->daoInterfaceName} " . PHP_EOL;
        print "  dao test class name  : {$this->daoInterfaceName}Test " . PHP_EOL;
        print "  entity class name    : {$this->entityClassName} " . PHP_EOL;
        if (!$this->useDB and !$this->useCommonsDao) {
            print "  entity class extends : {$this->extendsEntityClassName} " . PHP_EOL;
        }
        if (!$this->useCommonsDao) {
            print "  table name           : {$this->tableName} " . PHP_EOL;
            print '  columns              : ' . implode(', ',$this->cols) . PHP_EOL;
        }
        print "  dao dicon file name  : {$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        if (!$this->useCommonsDao) {
            $this->prepareDaoFile();
            $this->prepareEntityFile();
        }
        $this->prepareDaoTestFile();
    }

    protected function prepareDaoFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName 
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/dao/dao.tpl');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName 
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/dao/test.tpl');
        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareEntityFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . S2BASE_PHP5_DS . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = EntityCommand::getAccessorSrc($this->cols);
        $toStringSrc = EntityCommand::getToStringSrc($this->cols);
        if ($this->entityExtends) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                         . '/entity/entity_extends.tpl');
            $patterns = array("/@@CLASS_NAME@@/","/@@ACCESSOR@@/","/@@EXTENDS_CLASS@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$accessorSrc,$this->extendsEntityClassName,$toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                         . '/entity/entity.tpl');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}

