<?php
/**
 * TCallbackResponseAdapter and TCallbackResponseWriter class file.
 *
 * @author Wei Zhuo <weizhuo[at]gamil[dot]com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2006 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: TCallbackResponseAdapter.php 1568 2006-12-09 09:17:22Z wei $
 * @package System.Web.UI.ActiveControls
 */

/**
 * TCallbackResponseAdapter alters the THttpResponse's outputs.
 *
 * A TCallbackResponseWriter is used instead of the TTextWrite when
 * createHtmlWriter is called. Each call to createHtmlWriter will create
 * a new TCallbackResponseWriter. When flushContent() is called each
 * instance of TCallbackResponseWriter's content is flushed.
 *
 * The callback response data can be set using the {@link setResponseData ResponseData}
 * property.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version $Id: TCallbackResponseAdapter.php 1568 2006-12-09 09:17:22Z wei $
 * @package System.Web.UI.ActiveControls
 * @since 3.1
 */
class TCallbackResponseAdapter extends THttpResponseAdapter
{
	/**
	 * @TCallbackResponseWriter[] list of writers.
	 */
	private $_writers=array();
	/**
	 * @mixed callback response data.
	 */
	private $_data;

	private $_redirectUrl=null;

	/**
	 * Returns a new instance of THtmlWriter.
	 * An instance of TCallbackResponseWriter is created to hold the content.
	 * @param string writer class name.
	 * @param THttpResponse http response handler.
	 */
	public function createNewHtmlWriter($type,$response)
	{
		$writer = new TCallbackResponseWriter();
		$this->_writers[] = $writer;
		return parent::createNewHtmlWriter($type,$writer);
	}

	/**
	 * Flushes the contents in the writers.
	 */
	public function flushContent()
	{
		foreach($this->_writers as $writer)
			echo $writer->flush();
		parent::flushContent();
	}

	/**
	 * @param mixed callback response data.
	 */
	public function setResponseData($data)
	{
		$this->_data = $data;
	}

	/**
	 * @return mixed callback response data.
	 */
	public function getResponseData()
	{
		return $this->_data;
	}

	/**
	 * Delay the redirect until we process the rest of the page.
	 * @param string new url to redirect to.
	 */
	public function httpRedirect($url)
	{
		if($url[0]==='/')
			$url=$this->getRequest()->getBaseUrl().$url;
		$this->_redirectUrl=str_replace('&amp;','&',$url);
	}

	/**
	 * @return string new url for callback response to redirect to.
	 */
	public function getRedirectedUrl()
	{
		return $this->_redirectUrl;
	}
}

/**
 * TCallbackResponseWriter class.
 *
 * TCallbackResponseWriter class enclosed a chunck of content within a
 * html comment boundary. This allows multiple chuncks of content to return
 * in the callback response and update multiple HTML elements.
 *
 * The {@link setBoundary Boundary} property sets boundary identifier in the
 * HTML comment that forms the boundary. By default, the boundary identifier
 * is generated using microtime.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version $Id: TCallbackResponseAdapter.php 1568 2006-12-09 09:17:22Z wei $
 * @package System.Web.UI.ActiveControls
 * @since 3.1
 */
class TCallbackResponseWriter extends TTextWriter
{
	/**
	 * @var string boundary ID
	 */
	private $_boundary;

	/**
	 * Constructor. Generates unique boundary ID using microtime.
	 */
	public function __construct()
	{
		$this->_boundary = sprintf('%x',crc32(microtime()));
	}

	/**
	 * @return string boundary identifier.
	 */
	public function getBoundary()
	{
		return $this->_boundary;
	}

	/**
	 * @param string boundary identifier.
	 */
	public function setBoundary($value)
	{
		$this->_boundary = $value;
	}

	/**
	 * Returns the text content wrapped within a HTML comment with boundary
	 * identifier as its comment content.
	 * @return string text content chunck.
	 */
	public function flush()
	{
		$content = '<!--'.$this->getBoundary().'-->';
		$content .= parent::flush();
		$content .= '<!--//'.$this->getBoundary().'-->';
		return $content;
	}
}

?>