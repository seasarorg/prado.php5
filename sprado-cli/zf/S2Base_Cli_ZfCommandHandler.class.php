<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * @author klove
 */
class S2Base_Cli_ZfCommandHandler extends S2Base_Cli_AbstractCommandHandler {

    /**
     * @see S2Base_Cli_AbstractCommandHandler::projectHandler()
     */
    public function projectHandler(){
        self::validateProjectDir($this->projectDir);

        $includePattern = array('/project.build\.xml$/');
        $includePattern[] = '/project.app$/';
        $includePattern[] = '/project.app.commons/';
        $includePattern[] = '/project.app.modules/';
        $includePattern[] = '/project.config/';
        $includePattern[] = '/project.lib/';
        $includePattern[] = '/project.public$/';
        $includePattern[] = '/project.public.images/';
        $includePattern[] = '/project.public.css/';
        $includePattern[] = '/project.public.htaccess\.sample$/';
        $includePattern[] = '/project.public.z\.php$/';
        $includePattern[] = '/project.test/';
        $includePattern[] = '/project.var/';
        $includePattern[] = '/project.vendor$/';
        $includePattern[] = '/project.vendor.plugins/';

        $excludePattern = array('/dummy$/');

        $srcDir = $this->s2baseDir . DIRECTORY_SEPARATOR . 'project';
        self::dircopy($srcDir, $this->projectDir, $includePattern, $excludePattern);
        $this->modifyBuildXmlFile($this->projectDir, 'zf');
        $this->modifyEnvFile($this->projectDir, 'zf');
        $this->renameHtaccess($this->projectDir);
    }

    /**
     * @see S2Base_Cli_AbstractCommandHandler::commandHandler()
     */
    public function commandHandler(){
        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        require_once('S2Base/S2Base.phing.php');
        require_once('config/s2base_zf.inc.php');
        $pattern = $this->projectDir
                 . DIRECTORY_SEPARATOR . 'vendor'
                 . DIRECTORY_SEPARATOR . 'plugins'
                 . DIRECTORY_SEPARATOR . 'zf'
                 . DIRECTORY_SEPARATOR . 'commands'
                 . DIRECTORY_SEPARATOR . '*.php';
        $launcher = S2Base_CommandLauncherFactory::create(glob($pattern));
        $launcher->main();
    }

    /**
     * @see S2Base_Cli_AbstractCommandHandler::testHandler()
     */
    public function testHandler(){
        $testTargetPattern = '.+Test';
        if (isset($this->cmdLineArgs[2])) {
            $testTargetPattern = $this->cmdLineArgs[2];
        }
        print "[INFO ] test target       : $testTargetPattern"  . PHP_EOL . PHP_EOL;

        ini_set('include_path','lib' . PATH_SEPARATOR . ini_get('include_path'));
        require_once('config/environment.inc.php');
        require_once('S2Base/S2Base.cmd.php');
        require_once('config/s2base_zf.inc.php');
        $this->runUnitTest($this->projectDir . DIRECTORY_SEPARATOR . 'test', $testTargetPattern);
    }


    /**
     * @param string $projectDir
     * @param string $projectType default|smarty|zf
     */
    private function modifyEnvFile($projectDir, $projectType) {
        $envFile = $projectDir 
                 . DIRECTORY_SEPARATOR 
                 . 'config'
                 . DIRECTORY_SEPARATOR 
                 . 's2base.inc.php';
        if (!file_exists($envFile)) {
            print "[ERROR] file not found [$envFile] " . PHP_EOL;
            exit;
        }
        $iniContents = file_get_contents($envFile);
        if ($iniContents === false) {
            print "[ERROR] could not read file [$envFile] " . PHP_EOL;
            exit;
        }
        $pattern = "/'\.class\.php'/";
        $replacement = "'.php'";
        $iniContents = preg_replace($pattern, $replacement, $iniContents, 1);
        $result = file_put_contents($envFile, $iniContents, LOCK_EX);
        if ($result === false) {
            print "[ERROR] could not write file [$envFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $envFile" . PHP_EOL;
    }

    /**
     * @param string $projectDir
     */
    private function renameHtaccess($projectDir) {
        $htFile = $projectDir 
                . DIRECTORY_SEPARATOR 
                . 'public'
                . DIRECTORY_SEPARATOR 
                . 'htaccess.sample';
        $newFile = $projectDir 
                 . DIRECTORY_SEPARATOR 
                 . 'public'
                 . DIRECTORY_SEPARATOR 
                 . '.htaccess';
        if (!file_exists($htFile)) {
            print "[ERROR] file not found [$htFile] " . PHP_EOL;
            exit;
        }

        if (file_exists($newFile)) {
            print "[INFO ] file exist. ignore. [$newFile] " . PHP_EOL;
            return;
        }

        $result = rename($htFile, $newFile);
        if ($result === false) {
            print "[ERROR] could not rename file [$htFile to $newFile] " . PHP_EOL;
            exit;
        }
        print "[INFO ] modify : $newFile" . PHP_EOL;
    }
}
