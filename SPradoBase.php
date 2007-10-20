<?php
/**
 * SPradoBase class file.
 *
 * This is the file that establishes the PRADO component model
 * and error handling mechanism.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: PradoBase.php 1980 2007-05-30 23:27:00Z knut $
 * @package System
 */

require_once(dirname(__FILE__).'/prado/framework/Pradobase.php');

/**
 * SPradoBase class.
 *
 * PradoBase implements a few fundamental static methods.
 *
 * To use the static methods, Use Prado as the class name rather than PradoBase.
 * PradoBase is meant to serve as the base class of Prado. The latter might be
 * rewritten for customization.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: PradoBase.php 1980 2007-05-30 23:27:00Z knut $
 * @package System
 * @since 3.0
 */
class SPradoBase extends PradoBase
{
	/**
	 * File extension for Seasar class files.
	 */
	const SEASAR_CLASS_FILE_EXT='.class.php';
		
	private static $_appBasePath = "components";
	public static function setAppBasePath($value)
	{
		self::$_appBasePath = $value;
	}
	public static function getAppBasePath()
	{
		return self::$_appBasePath;
	}
	
	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string class name
	 */
	public static function autoload($className)
	{
		include_once($className.parent::CLASS_FILE_EXT);
		if(!class_exists($className,false) && !interface_exists($className,false))
		{
			include_once($className.self::SEASAR_CLASS_FILE_EXT);
			if(!class_exists($className,false) && !interface_exists($className,false))
			{
				self::fatalError("Class file for '$className' cannot be found.");
			}
						
		}
	}
	
	/**
	 * Creates a component with the specified type.
	 * A component type can be either the component class name
	 * or a namespace referring to the path of the component class file.
	 * For example, 'TButton', 'System.Web.UI.WebControls.TButton' are both
	 * valid component type.
	 * This method can also pass parameters to component constructors.
	 * All parameters passed to this method except the first one (the component type)
	 * will be supplied as component constructor parameters.
	 * @param string component type
	 * @return TComponent component instance of the specified type
	 * @throws TInvalidDataValueException if the component type is unknown
	 */
	public static function createComponent($type)
	{
		// $typeの内容に応じて、コンテナから取得するか生成するかを判定する。生成する場合は親クラスのメソッドを使用。
		// IDIRequiredComponentを実装しているクラスで引数１つならばコンテナから取得
		$n=func_num_args();
		$args=func_get_args();
		$s='$args[0]';
		for($i=1;$i<$n;++$i)
			$s.=",\$args[$i]";
		eval("\$component=parent::createComponent($s);");
		
		if($component instanceof IDIRequiredComponent){
			$container = self::createContainer($component->getFilePath());
			$container->injectDependency($component, $type);
		}
		return $component;
	}

	/**
	 * create S2Container by page class name
     * @param String $className Class Name
     * @return S2Container 
	 */
	protected static function createContainer($classPath)
	{
		if(!is_file($classPath))
			throw new TConfigurationException('page_class_file_not_exist',$classPath);

		/**
		 * S2Container Family module import
		 */

		// s2container.php5
		S2ContainerApplicationContext::init();

		// page Component
		$path = self::getApplication()->getBasePath();
		$className = basename($classPath);
		$className = basename($className,self::SEASAR_CLASS_FILE_EXT);
		S2ContainerApplicationContext::$CLASSES[$className] = $classPath;
		S2ContainerApplicationContext::setExcludePattern("/Service$/");
		S2ContainerApplicationContext::addExcludePattern("/Logic$/");

		// S2PRADO Component
		$path = $path ."/". self::$_appBasePath;
		define('SPRADO_COMMON_DICON_ROOT', $path . '/commons/dicon');
		define('SPRADO_APP_DICON_ROOT', $path . '/dicon');

		// logic Component
		S2ContainerApplicationContext::import($path . '/logic');
		S2ContainerClassLoader::import($path . '/logic');
		Prado::using('Application.'. self::$_appBasePath . '.logic.*');

		// interceptor Component
		S2ContainerApplicationContext::import($path . '/interceptor');
		S2ContainerClassLoader::import($path . '/interceptor');
		Prado::using('Application.'. self::$_appBasePath . '.interceptor.*');

		// dao Component
		S2ContainerApplicationContext::import($path . '/dao');
		S2ContainerApplicationContext::import(SPRADO_COMMON_DICON_ROOT . '/dao.dicon');	
		S2ContainerClassLoader::import($path . '/dao');
		Prado::using('Application.'. self::$_appBasePath . '.dao.*');	
		define('PDO_DICON', SPRADO_COMMON_DICON_ROOT . '/pdo.dicon');

		// Read DataSource Setting
		$dbConnection = self::getApplication()->getModule('dataSource')->DbConnection;
		define('PDO_DSN', $dbConnection->ConnectionString);
		define('PDO_USER', $dbConnection->Username);
		define('PDO_PASSWORD', $dbConnection->Password);
		
		// entity Component	
		S2ContainerApplicationContext::import($path . '/entity');
		S2ContainerClassLoader::import($path . '/entity');
		Prado::using('Application.'. self::$_appBasePath . '.entity.*');

		// test/dao Component
		//S2ContainerApplicationContext::import($path . '/test/dao');
		S2ContainerClassLoader::import($path . '/test/dao');
		Prado::using('Application.'. self::$_appBasePath . '.test.dao.*');

		// test/logic Component
		//S2ContainerApplicationContext::import($path . '/test/logic');
		S2ContainerClassLoader::import($path . '/test/logic');
		Prado::using('Application.'. self::$_appBasePath . '.test.logic.*');

		// Container create
		$container = S2ContainerApplicationContext::create();
		$container->init();
		return $container;
	}
}
?>