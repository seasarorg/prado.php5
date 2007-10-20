<?php
/**
 * Entityを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/entity/entity名.class.php</li>
 * </ul>
 * 
 */
class EntityCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $cols;
    protected $useDB;
    protected $tableName;
    protected $tableNames;

    /**
     * カラムのsetter、getterソースを生成します。
     * 
     * @param array $cols カラム名の配列
     * @return string setter、getterソース
     */
    public static function getAccessorSrc($cols){
        $tempContent  = '    protected $@@PROP_NAME@@;' . PHP_EOL .
                        '    const @@PROP_NAME@@_COLUMN = "@@COL_NAME@@";'  . PHP_EOL .
                        '    public function set@@UC_PROP_NAME@@($val){$this->@@PROP_NAME@@ = $val;}' . PHP_EOL . 
                        '    public function get@@UC_PROP_NAME@@(){return $this->@@PROP_NAME@@;}' . PHP_EOL . PHP_EOL;
        $retSrc = "";
        foreach($cols as $col){
            $prop = self::getPropertyNameFromCol($col);
            
            $patterns = array("/@@UC_PROP_NAME@@/",
                              "/@@PROP_NAME@@/",
                              "/@@COL_NAME@@/");
            $replacements = array(ucfirst($prop),
                                  $prop,
                                  $col);
            $retSrc .= preg_replace($patterns,$replacements,$tempContent);
        }
        return $retSrc;
    }

    /**
     * カラム名からプロパティ名を導出します。
     * 
     * user_id  : userId  <br>
     * user_id_ : userId_ <br>
     * _user_id : _UserId <br>
     */
    public static function getPropertyNameFromCol($col){
        $prop = strtolower($col);
        if (preg_match("/_/",$col)){
            $prop = preg_replace("/_/"," ",$prop);
            $prop = ucwords($prop);
            $matches = array();
            if (preg_match("/^(\s+)/", $prop, $matches)) {
                $preSpace  = $matches[1];
            }
            $matches = array();
            if (preg_match("/(\s+)$/", $prop, $matches)) {
                $postSpace = $matches[1];
            }
            $prop = preg_replace("/\s/","",$prop);
            $prop = preg_replace("/\s/","_",$preSpace . $prop . $postSpace);
            $prop = strtolower(substr($prop,0,1)) . substr($prop,1);
        }
        return $prop;
    }

    /**
     * __toStringメソッドのソースをカラム名を用いて生成します。
     * 
     * @param array $cols カラム名の配列
     * @return string __toStringメソッドのソース
     */
    public static function getToStringSrc($cols){
        
        if (count($cols) == 0){
            return "";
        }
        
        $src      = '    public function __toString() {' . PHP_EOL;
        $src     .= '        $buf = array();' . PHP_EOL;
        foreach($cols as $col){
            $prop = self::getPropertyNameFromCol($col);
            $getter = '\' . $this->get' . ucfirst($prop) . '();';            
            $src .= '        $buf[] = \'' . "$prop => " . $getter . PHP_EOL;
        }
        $src     .= '        return \'{\' . implode(\', \',$buf) . \'}\';' . PHP_EOL;
        $src     .= '    }' . PHP_EOL;
        return $src;
    }

    /**
     * Entity名からテーブル名を導出します。
     * 
     * @param string $name entity名
     * @return string テーブル名
     */
    public static function guessTableName($name){
        $patterns = array("/Entity$/","/Dto$/","/Bean$/");
        $replacements = array('','','');
        $guess = strtoupper(preg_replace($patterns,$replacements,$name));
        return $guess == $name ? strtoupper($name) : $guess;
    }

    /**
     * カラム名の検証を行います
     * 
     * @param array $colsStr カンマ区切りのカラム名
     * @return array 検証済みのカラム名の配列
     */
    public static function validateCols($colsStr){
        $colsTmp = array_unique(explode(',',$colsStr));
        $cols = array();
        foreach($colsTmp as $col){
            $col = trim($col);
            if(preg_match("/^\w+$/",$col)){
                $cols[] = $col;
            }
        }
        return $cols;
    }

    /**
     * app/commons/daoのentityが使用可能かどうかを調べます。
     * 使用可能な場合は、使用するかどうかを確認します。
     * 
     * @return boolean
     */
    public static function isCommonsEntityAvailable() {
        $entitys = self::getAllEntityFromCommonsDao();
        if(count($entitys) > 0){
            return S2Base_StdinManager::isYes('extends commons entity ?');
        } else {
            return false;
        }
    }

    /**
     * app/commons/daoにあるentityクラスをすべて取得します。
     * 
     * @return array entityクラス名の配列
     */
    public static function getAllEntityFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $entitys = array();
        foreach($entries as $entry){
            $maches = array();
            if(preg_match("/(\w+Entity)\.class\.php$/",$entry,$maches)){
                $entitys[] = $maches[1];
            }
        }
        return $entitys;
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "entity";
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

        $this->useDB = $this->isUseDB();
        if ($this->useDB) {
            if ($this->getEntityInfoFromDB() and 
                $this->finalConfirm()){
                $this->prepareFiles();
            }
        } else {
            if ($this->getEntityInfoInteractive() and
                $this->finalConfirm()){
                $this->prepareFiles();
            }
        }
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function getEntityInfoFromDB() {
        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $this->tableNames = S2Base_StdinManager::getValuesFromArray($dbms->getTables(),
                                                                  "table list");
        $this->tableName = $this->tableNames[0];
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = self::getColumnsFromTables($dbms, $this->tableNames);

        $this->entityClassName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->extendsEntityClassName = "none";

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);
        return true;
    }

    public static function getColumnsFromTables($dbms, $tableNames) {
        $cols = array();
        foreach ($tableNames as $tableName) {
            $cols = array_merge($cols, $dbms->getColumns($tableName));
        }

        return array_unique($cols);
    }

    protected function getEntityInfoInteractive() {
        $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
        $this->validate($this->entityClassName);

        $this->entityExtends = $this->isEntityExtends();
        $this->extendsEntityClassName = "none";
        if ($this->entityExtends) {
            $entitys = self::getAllEntityFromCommonsDao();
            $this->extendsEntityClassName = S2Base_StdinManager::getValueFromArray($entitys,
                                        "entity list");
            if (S2Base_CommandUtil::isListExitLabel($this->extendsEntityClassName)){
                return false;
            }
            $this->tableName = "extended";
        } else {
            $tableNameTmp = self::guessTableName($this->entityClassName);
            $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
            if(trim($this->tableName) == ''){
                $this->tableName = $tableNameTmp;
            }
            $this->validate($this->tableName);
        }
        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,) : ");
        $this->cols = self::validateCols($cols);

        return true;
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name          : {$this->moduleName}" . PHP_EOL;
        print "  entity class name    : {$this->entityClassName}" . PHP_EOL;
        if (!$this->useDB) {
            print "  entity class extends : {$this->extendsEntityClassName}" . PHP_EOL;
        }
        print "  table name           : {$this->tableName}" . PHP_EOL;
        print '  columns              : ' . implode(', ',$this->cols) . PHP_EOL;

        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareEntityFile();
    }
    
    protected function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR 
                 . S2BASE_PHP5_DS . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . S2BASE_PHP5_DS . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = self::getAccessorSrc($this->cols);
        $toStringSrc = self::getToStringSrc($this->cols);
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
