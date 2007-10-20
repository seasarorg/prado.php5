<?php
class CopyFilesToOneDirTask extends Task {

    private $incFileSets = array();
    private $filterChains = array();
    private $toDir;

    public function init(){}

    public function main(){
        if( !$this->isValidToDir() ){
            return;
        }

        $includefiles = array();
        foreach($this->incFileSets as $fileset){
            $includefiles[] = $this->getFileList($fileset);
        }

        foreach($includefiles as $files){
            $c = count($files);
            for($i = 0; $i < $c; $i++){
                $toFile = realpath($this->toDir) . DIRECTORY_SEPARATOR . $files[$i]["name"];
                $fromFile = $files[$i]["absolute"];
                if ( $this->isCopyNeed($fromFile,$toFile) ){
                    if(!copy($fromFile,$toFile)){
                        $this->log("[ERROR] copy fail. from [ $fromFile ] to [ $toFile ]");
                    }
                    $this->log("copy : $fromFile");
                }
            }
        }
    }

    public function setToDir($toDir){
        $this->toDir = $toDir;
    }

    public function createFileSet(){
        $fs = new FileSet();
        $this->incFileSets[] = $fs;
        return $fs;
    }

    public function createFilterChain(){
        $fc = new FilterChain($this->project);
        $this->filterChains[] = $fc;
        return $fc;
    }

    private function getFileList(FileSet $fileset){
        $ds = $fileset->getDirectoryScanner($this->project);
        $files = $ds->getIncludedFiles();
        foreach($files as &$file){
            $relativePath = $ds->getBaseDir() . DIRECTORY_SEPARATOR . $file;
            $file = array(
                        "key" => $file,
                        "relative" => $relativePath, 
                        "absolute" => realpath($relativePath), 
                        "name" => basename($file)
                    );
        }
        return $files;
    }

    private function isCopyNeed($fromFile,$toFile){
        if (!is_file($toFile) or
            filemtime($fromFile) > filemtime($toFile)){
            return true;
        }
        return false;
    }

    private function isValidToDir(){
        if( !is_dir($this->toDir) ){
            $this->log("[ERROR] toDir not found. [ $this->toDir ] ");
            return false;
        }

        if( !is_writable($this->toDir) ){
            $this->log("[ERROR] toDir not writable. [ $this->toDir ] ");
            return false;
        }

        return true;
    }

}
?>
