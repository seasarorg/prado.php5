<?php
require_once('AbstractGoyaCommand.class.php');
class GoyaCommand extends AbstractGoyaCommand {

    public function getName(){
        return "goya";
    }

    public function isAvailable(){
        return true;
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

    protected function isUseDao() {
        return S2Base_StdinManager::isYes('use dao ?');
    }
}
?>
