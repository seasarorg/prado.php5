<?php
/**
 * S2Prado bootstrap file.
 *
 * This file is intended to be included in the entry script of SPrado applications.
 * It defines SPrado class by extending SPradoBase, a static class providing globally
 * available functionalities that enable PRADO component model and error handling mechanism.
 *
 * By including this file, the PHP error and exception handlers are set as
 * PRADO handlers, and an __autoload function is provided that automatically
 * loads a class file if the class is not defined.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: prado.php 1680 2007-02-07 19:04:38Z xue $
 * @package System
 */

/**
 * S2 Container import　and autoload setting
 */
// s2container.php5
require_once('S2Container/S2Container.php');
require_once('S2ContainerSplAutoLoad.php');
S2ContainerClassLoader::import(S2CONTAINER_PHP5);		

// s2dao.php5
require_once('S2Dao/S2Dao.php');	
S2ContainerClassLoader::import(S2DAO_PHP5);


/**
 * Includes the SPradoBase class file
 */
require_once(dirname(__FILE__).'/SPradoBase.php');

/**
 * Defines Prado class if not defined.
 */
if(!class_exists('Prado',false))
{
	class Prado extends SPradoBase
	{
	}
}

/**
 * Registers the autoload function.
 * Since Prado::autoload will report a fatal error if the class file
 * cannot be found, if you have multiple autoloaders, Prado::autoload
 * should be registered in the last.
 */
spl_autoload_register(array('Prado','autoload'));

/**
 * Initializes error and exception handlers
 */
Prado::initErrorHandlers();

/**
 * Includes TApplication class file
 */
require_once(dirname(__FILE__).'/prado/framework/TApplication.php');

/**
 * Includes TShellApplication class file
 */
require_once(dirname(__FILE__).'/prado/framework/TShellApplication.php');


class SPrado extends TModule
{
	private $_feedServicePath = "feeds";
	private $_jsonServicePath = "jsons";
	private $_soapServicePath = "soaps";
	private $_ctlComponentPath = "controls";
	private $_dbComponentPath = "databases";
	
	// appBasePath Accessor
	public function setAppBasePath($value)
	{
		Prado::setAppBasePath($value);
	}
	public function getAppBasePath()
	{
		return Prado::getAppBasePath();
	}

	//  Accessor to Path of Classes for Feed Service
	public function setFeedServicePath($value)
	{
		$this->_feedServicePath = $value;
	}
	public function getFeedServicePath()
	{
		return $this->_feedServicePath;
	}

	//  Accessor to Path of Classes for Json Service
	public function setJsonServicePath($value)
	{
		$this->_jsonServicePath = $value;
	}
	public function getJsonServicePath()
	{
		return $this->_jsonServicePath;
	}

	//  Accessor to Path of Classes for Soap Service
	public function setSoapServicePath($value)
	{
		$this->_soapServicePath = $value;
	}
	public function getSoapServicePath()
	{
		return $this->_soapServicePath;
	}

	//  Accessor to Path of Classes for Control (Template)
	public function setCtlComponentPath($value)
	{
		$this->_ctlComponentPath = $value;
	}
	public function getCtlComponentPath()
	{
		return $this->_ctlComponentPath;
	}

	//  Accessor to Path of Classes for ActiveRecord
	public function setDbComponentPath($value)
	{
		$this->_dbComponentPath = $value;
	}
	public function getDbComponentPath()
	{
		return $this->_dbComponentPath;
	}

	public function init($config)
	{		
		/**
		 * SPrado App Directory setting & SPrado module import
		 */
		require_once(dirname(__FILE__) . '/Web/Services/SPageService.php');
		
		$path = $this->getApplication()->getBasePath() . "/";
		if(file_exists($path . $this->_feedServicePath)){
			Prado::using('System.Web.Services.TFeedService');
			Prado::using('Application.feeds.*');		
		}
		if(file_exists($path . $this->_jsonServicePath)){
			Prado::using('System.Web.Services.TJsonService');
			Prado::using('Application.jsons.*');		
		}
		if(file_exists($path . $this->_soapServicePath)){
			Prado::using('System.Web.Services.TSoapService');
			Prado::using('Application.soaps.*');		
		}
		if(file_exists($path . $this->_ctlComponentPath)){
			Prado::using('Application.controls.*');		
		}
		if(file_exists($path . $this->_dbComponentPath)){
			Prado::using('System.Data.ActiveRecord.TActiveRecord');
			Prado::using('Application.databases.*');		
		}
	}
}
?>