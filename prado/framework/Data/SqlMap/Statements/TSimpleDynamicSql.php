<?php
/**
 * TSimpleDynamicSql class file.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005-2007 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: TSimpleDynamicSql.php 1568 2006-12-09 09:17:22Z wei $
 * @package System.Data.SqlMap.Statements
 */

/**
 * TSimpleDynamicSql class.
 *
 * @author Wei Zhuo <weizho[at]gmail[dot]com>
 * @version $Id: TSimpleDynamicSql.php 1568 2006-12-09 09:17:22Z wei $
 * @package System.Data.SqlMap.Statements
 * @since 3.1
 */
class TSimpleDynamicSql extends TStaticSql
{
	private $_mappings=array();

	public function __construct($mappings)
	{
		$this->_mappings = $mappings;
	}

	public function getPreparedStatement($parameter=null)
	{
		$statement = parent::getPreparedStatement($parameter);
		if($parameter !== null)
			$this->mapDynamicParameter($statement, $parameter);
		return $statement;
	}

	protected function mapDynamicParameter($statement, $parameter)
	{
		$sql = $statement->getPreparedSql();
		foreach($this->_mappings as $property)
		{
			$value = TPropertyAccess::get($parameter, $property);
			$sql = preg_replace('/'.TSimpleDynamicParser::DYNAMIC_TOKEN.'/', $value, $sql, 1);
		}
		$statement->setPreparedSql($sql);
	}
}

?>