<?php
class SJsonService extends TJsonService
{
	/**
	 * Load the service definitions.
	 * @param TXmlElement configuration for this module, can be null
	 */
	protected function loadJsonServices($xml)
	{

		// JSONディレクトリ配下のファイル群のリストを取得して、コンフィグファイルに設定する。
		//   (未実装)
		// 
		// 
//		foreach($xml->getElementsByTagName('json') as $config)
//		{
//			if(($id=$config->getAttribute('id'))!==null)
//				$this->_services[$id]=$config;
//			else
//				throw new TConfigurationException('jsonservice_id_required');
//		}
		parent::loadJsonServices($xml);
	}
}
?>