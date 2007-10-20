<?php
/**
 * PRADO Page Service for Cooperating with S2Container.PHP5
 */
class SPageService extends TPageService
{
    /**
	 * Create Page Component
     * 
     * @param String $path Page path
     * @return TPage|null 
     * 
	 *   1) with PRADO Framework and S2Container
	 *   2) with PRADO Framework
	 *   3) (Default Page)
     */
	protected function createPage($path)
	{
		$path=$this->getBasePath().'/'.strtr($path,'.','/');
		//$hasTemplateFile=is_file($path.self::PAGE_FILE_EXT);
		$hasTemplateFile=is_file($path.self::PAGE_FILE_EXT);
		$hasSeasarClassFile=is_file($path.Prado::SEASAR_CLASS_FILE_EXT);
		$hasClassFile=is_file($path.Prado::CLASS_FILE_EXT);

		$page = null;
		if($hasSeasarClassFile) {
			// 
			$className=basename($path);
			if(!class_exists($className,false))
				include_once($path.Prado::SEASAR_CLASS_FILE_EXT);
		}
		elseif($hasClassFile)
		{
			// 
			$className=basename($path);
			if(!class_exists($className,false))
				include_once($path.Prado::CLASS_FILE_EXT);
		}
		else
		{
			// 
			$className=$this->getBasePageClass();
			Prado::using($className);
		}
		
 		if(!class_exists($className,false) || ($className!=='TPage' && !is_subclass_of($className,'TPage')))
			throw new THttpException(404,'pageservice_page_unknown',$pagePath);

		$page=Prado::createComponent($className);
		$page->setPagePath($this->getRequestedPagePath());
		
		if($hasTemplateFile) {
			$page->setTemplate($this->getTemplateManager()->getTemplateByFileName($path.self::PAGE_FILE_EXT));
		}else{
			throw new THttpException(404,'pageservice_page_unknown',$this->getRequestedPagePath());			
		}

		return $page;
	}
}
?>
