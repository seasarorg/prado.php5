<?php
S2ContainerApplicationContext::init();
S2ContainerApplicationContext::import(dirname(__FILE__) . '/dao');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/entity');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/interceptor');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/service');
S2ContainerApplicationContext::import(dirname(__FILE__) . '/model');
S2ContainerApplicationContext::$CLASSES['@@CONTROLLER_CLASS_NAME@@'] = dirname(dirname(__FILE__)) . '/@@CONTROLLER_FILE_NAME@@' . S2BASE_PHP5_CLASS_SUFFIX;
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/app/commons/dao');
S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/app/commons/dicon/dao.dicon');
