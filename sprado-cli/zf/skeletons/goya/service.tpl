<?php
class @@CLASS_NAME@@ {
    private $@@DAO_PROPERTY@@;

    public function __construct(){}

    public function getWithLimit($limit) {
        $arrayobject = $this->@@DAO_PROPERTY@@->findAllList();
        if ($limit < 0 or $limit > $arrayobject->count()) {
            return $arrayobject;
        }
        $dtos = new ArrayObject();
        for ($i = 0; $i < $limit; $i++) {
            $dtos->append($arrayobject->offsetGet($i));
        }
        return $dtos;
    }

    public function set@@DAO_NAME@@(@@DAO_NAME@@ $dao){
        $this->@@DAO_PROPERTY@@ = $dao;
    } 
}
