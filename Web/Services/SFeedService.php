<?php
class SFeedService extends TFeedService
{
	/**
	 * Initializes this module.
	 * This method is required by the IModule interface.
	 * @param TXmlElement configuration for this module, can be null
	 */
	public function init($config)
	{
		// フィードディレクトリ配下のファイル群のリストを取得して、コンフィグファイルに設定する。
		//   (未実装)
		// 
		// 
//		foreach($config->getElementsByTagName('feed') as $feed)
//		{
//			if(($id=$feed->getAttributes()->remove('id'))!==null)
//				$this->_feeds[$id]=$feed;
//			else
//				throw new TConfigurationException('feedservice_id_required');
//		}
//		
		parent::init($config);
	}
	
}
?>