<?php
class GenerateCoreFileTask extends Task {

    private $searchDir = null;
    private $listFile = null;
    private $coreFile = null;
    private $filterChains = array();
    private $classes = array();
    private $contents = null;
    private $included = 0;
    
    public function init(){}

    public function main(){
        $this->log("searchDir : {$this->searchDir}");
        $this->log("listFile : {$this->listFile}");
        $this->log("coreFile : {$this->coreFile}");
        $this->contents = array();
        $this->classes = $this->getClasses();
        $this->searchDirectory($this->searchDir);

        $contents = "<?php\n" . 
                    $this->getAllContents() .
                    "?>\n";
        
        file_put_contents($this->coreFile,
                          $contents,
                          LOCK_EX);

        $this->log(count($this->classes) . " classes in listFile.");
        $this->log($this->included . " classes included.");
    }

    private function getAllContents(){
        $ret = "";
        foreach($this->classes as $className){
            $ret .= $this->contents[$className];
        }
        return $ret;
    }
            
    private function getClasses(){
        $classes = file($this->listFile);
        foreach($classes as &$c){
            $c = trim($c);
        }
        return $classes;            
    }
    
    private function searchDirectory($parentPath){
        $d = dir($parentPath);
        while (false !== ($entry = $d->read())) {
            if(preg_match("/^\./",$entry)){
                continue;
            }
            $path = $parentPath . DIRECTORY_SEPARATOR . $entry;

            if(is_dir($path)){
                $this->searchDirectory($path);
            }else{
                if(preg_match('/(.+)\.php$/',$entry,$reg)){
                    if(in_array($reg[1],$this->classes)){
                        $this->getSrc($reg[1],$path);
                        $this->included++;
                    }
                }
            }
        }
        $d->close();
    }
    
    private function getSrc($className,$path){
        $lines = file($path);
        $isComment = false;
        for($i=0;$i<count($lines);$i++){
            $line = preg_replace("/\/\/.*$/","",$lines[$i]);
            if (trim($line) == '' or
                trim($line) == '<?php' or
                trim($line) == '?>'){
                continue;
            }
            if (ereg("\/\*",$line)){
                $isComment = true;
            }
            if (ereg("\*\/",$line)){
                $isComment = false;
                continue;
            }
            if (!$isComment){
                isset($this->contents[$className]) ?
                $this->contents[$className] .= $line :
                $this->contents[$className] = $line;
            }
        }
        $this->contents[$className] .= "\n";
    }

    public function setSearchDir($searchDir){
        $this->searchDir = $searchDir;
    }

    public function setListFile($listFile){
        $this->listFile = $listFile;
    }

    public function setCoreFile($coreFile){
        $this->coreFile = $coreFile;
    }

    public function createFilterChain(){
        $fc = new FilterChain($this->project);
        $this->filterChains[] = $fc;
        return $fc;
    }
}
?>
