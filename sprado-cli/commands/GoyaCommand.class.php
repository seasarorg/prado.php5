<?php
/**
 * service, dao, entityを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/service/service名.class.php</li>
 *   <li>app/modules/module名/service/service名Impl.class.php</li>
 *   <li>app/modules/module名/dicon/service名Impl.dicon</li>
 *   <li>test/modules/module名/service/service名ImplTest.class.php</li>
 *   <li>app/modules/module名/dao/dao名.class.php</li>
 *   <li>app/modules/module名/dicon/dao名.dicon</li>
 *   <li>test/modules/module名/dao/dao名Test.class.php</li>
 *   <li>app/modules/module名/entity/entity名.dicon</li>
 * </ul>
 * 
 */
class GoyaCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $serviceName;
    protected $serviceClassName;
    protected $daoInterfaceName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $tableName;
    protected $tableNames;
    protected $cols;
    protected $useCommonsDao;
    protected $useDB;

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "goya";
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

        $serviceName = S2Base_StdinManager::getValue('service name ? : ');
        $this->validate($serviceName);

        $this->useCommonsDao = $this->isUseCommonsDao();
        if($this->useCommonsDao){
            if ($this->getGoyaInfoWithCommonsDao($serviceName) and
                $this->finalConfirm()){
                $this->prepareFiles();
            }
        }else{
            $this->useDB = $this->isUseDB();
            if ($this->useDB) {
                if ($this->getGoyaInfowithDB($serviceName) and
                    $this->finalConfirm()) {
                    $this->prepareFiles();
                }
            } else {
                if ($this->getGoyaInfoInteractive($serviceName) and
                    $this->finalConfirm()) {
                    $this->prepareFiles();
                }
            }
        }
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function getGoyaInfoWithDB($serviceName) {
        $this->setupPropertyFromServiceName($serviceName);

        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $this->tableNames = S2Base_StdinManager::getValuesFromArray($dbms->getTables(),
                                                                  "table list");
        $this->tableName = $this->tableNames[0];
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = EntityCommand::getColumnsFromTables($dbms, $this->tableNames);

        $this->daoInterfaceName = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName  = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->extendsEntityClassName = "none";

        $daoInterfaceNameTmp = S2Base_StdinManager::getValue("dao interface name [{$this->daoInterfaceName}]? : ");
        $this->daoInterfaceName = trim($daoInterfaceNameTmp) == '' ? $this->daoInterfaceName : $daoInterfaceNameTmp;
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);
        return true;
    }

    protected function getGoyaInfoInteractive($serviceName) {
        $this->setupPropertyFromServiceName($serviceName);

        $this->entityExtends = $this->isEntityExtends();
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

    protected function getGoyaInfoWithCommonsDao($serviceName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(S2Base_CommandUtil::isListExitLabel($daoName)){
            return false;
        }

        $this->setupPropertyFromServiceName($serviceName);
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->extendsEntityClassName = "none";
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
        return true;
    }

    protected function setupPropertyFromServiceName($serviceName){
        $this->serviceName = $serviceName;
        $name = ucfirst($serviceName);
        $this->serviceClassName = $name . "Service";
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
        $this->extendsEntityClassName = "none";
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  service name            : {$this->serviceName}" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name : {$this->serviceClassName}Test" . PHP_EOL;
        print "  dao interface name      : {$this->daoInterfaceName}" . PHP_EOL;
        print "  dao test class name     : {$this->daoInterfaceName}Test" . PHP_EOL;
        print "  entity class name       : {$this->entityClassName}" . PHP_EOL;
        if (!$this->useCommonsDao) {
            if (!$this->useDB) {
                print "  entity class extends    : {$this->extendsEntityClassName}" . PHP_EOL;
            }
            print "  table name              : {$this->tableName}" . PHP_EOL;
            print '  columns                 : ' . implode(', ',$this->cols) . PHP_EOL;
        }
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareServiceClassFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoTestFile();

        if (!$this->useCommonsDao) {
            $this->prepareDaoFile();
            $this->prepareEntityFile();
        }
    }

    protected function prepareServiceClassFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/goya/service.tpl');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1))
                 . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,
                              $this->daoInterfaceName,
                              $daoProp);
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
                     . '/goya/service_test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@DAO_INTERFACE@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->serviceClassName,
                              $this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
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
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . '/dao/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@DAO_CLASS@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,
                              $this->moduleName,
                              $this->daoInterfaceName,
                              $this->serviceClassName);
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
            $patterns = array("/@@CLASS_NAME@@/",
                              "/@@ACCESSOR@@/",
                              "/@@EXTENDS_CLASS@@/",
                              "/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,
                                  $accessorSrc,
                                  $this->extendsEntityClassName,
                                  $toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                         . '/entity/entity.tpl');
            $patterns = array("/@@CLASS_NAME@@/",
                              "/@@TABLE_NAME@@/",
                              "/@@ACCESSOR@@/",
                              "/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,
                                  $this->tableName,
                                  $accessorSrc,
                                  $toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
