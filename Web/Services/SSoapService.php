<?php
class SSoapService extends TSoapService
{
	/**
	 * Loads configuration from an XML element
	 * @param TXmlElement configuration node
	 * @throws TConfigurationException if soap server id is not specified or duplicated
	 */
	private function loadConfig($xml)
	{
		// JSONディレクトリ配下のファイル群のリストを取得して、コンフィグファイルに設定する。
		//   (未実装)
		// 
		// 
//		foreach($xml->getElementsByTagName('soap') as $serverXML)
//		{
//			$properties=$serverXML->getAttributes();
//			if(($id=$properties->remove('id'))===null)
//				throw new TConfigurationException('soapservice_serverid_required');
//			if(isset($this->_servers[$id]))
//				throw new TConfigurationException('soapservice_serverid_duplicated',$id);
//			$this->_servers[$id]=$properties;
//		}
		prado::loadConfig($xml);
	}
}

?>