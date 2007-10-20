<?php
/**
 * THttpResponse class
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: THttpResponse.php 1980 2007-05-30 23:27:00Z knut $
 * @package System.Web
 */

/**
 * Includes the THttpResponse adapter.
 */
Prado::using('System.Web.THttpResponseAdapter');

/**
 * THttpResponse class
 *
 * THttpResponse implements the mechanism for sending output to client users.
 *
 * To output a string to client, use {@link write()}. By default, the output is
 * buffered until {@link flush()} is called or the application ends. The output in
 * the buffer can also be cleaned by {@link clear()}. To disable output buffering,
 * set BufferOutput property to false.
 *
 * To send cookies to client, use {@link getCookies()}.
 * To redirect client browser to a new URL, use {@link redirect()}.
 * To send a file to client, use {@link writeFile()}.
 *
 * By default, THttpResponse is registered with {@link TApplication} as the
 * response module. It can be accessed via {@link TApplication::getResponse()}.
 *
 * THttpResponse may be configured in application configuration file as follows
 *
 * <module id="response" class="System.Web.THttpResponse" CacheExpire="20" CacheControl="nocache" BufferOutput="true" />
 *
 * where {@link getCacheExpire CacheExpire}, {@link getCacheControl CacheControl}
 * and {@link getBufferOutput BufferOutput} are optional properties of THttpResponse.
 *
 * THttpResponse sends charset header if either {@link setCharset() Charset}
 * or {@link TGlobalization::setCharset() TGlobalization.Charset} is set.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: THttpResponse.php 1980 2007-05-30 23:27:00Z knut $
 * @package System.Web
 * @since 3.0
 */
class THttpResponse extends TModule implements ITextWriter
{
	/**
	 * @var boolean whether to buffer output
	 */
	private $_bufferOutput=true;
	/**
	 * @var boolean if the application is initialized
	 */
	private $_initialized=false;
	/**
	 * @var THttpCookieCollection list of cookies to return
	 */
	private $_cookies=null;
	/**
	 * @var integer response status code
	 */
	private $_status=200;
	/**
	 * @var string HTML writer type
	 */
	private $_htmlWriterType='System.Web.UI.THtmlWriter';
	/**
	 * @var string content type
	 */
	private $_contentType=null;
	/**
	 * @var string character set, e.g. UTF-8
	 */
	private $_charset='';
	/**
	 * @var THttpResponseAdapter adapter.
	 */
	private $_adapter;

	/**
	 * Destructor.
	 * Flushes any existing content in buffer.
	 */
	public function __destruct()
	{
		//if($this->_bufferOutput)
		//	@ob_end_flush();
	}

	/**
	 * @param THttpResponseAdapter response adapter
	 */
	public function setAdapter(THttpResponseAdapter $adapter)
	{
		$this->_adapter=$adapter;
	}

	/**
	 * @return THttpResponseAdapter response adapter, null if not exist.
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}

	/**
	 * @return boolean true if adapter exists, false otherwise.
	 */
	public function getHasAdapter()
	{
		return !is_null($this->_adapter);
	}

	/**
	 * Initializes the module.
	 * This method is required by IModule and is invoked by application.
	 * It starts output buffer if it is enabled.
	 * @param TXmlElement module configuration
	 */
	public function init($config)
	{
		if($this->_bufferOutput)
			ob_start();
		$this->_initialized=true;
		$this->getApplication()->setResponse($this);
	}

	/**
	 * @return integer time-to-live for cached session pages in minutes, this has no effect for nocache limiter. Defaults to 180.
	 */
	public function getCacheExpire()
	{
		return session_cache_expire();
	}

	/**
	 * @param integer time-to-live for cached session pages in minutes, this has no effect for nocache limiter.
	 */
	public function setCacheExpire($value)
	{
		session_cache_expire(TPropertyValue::ensureInteger($value));
	}

	/**
	 * @return string cache control method to use for session pages
	 */
	public function getCacheControl()
	{
		return session_cache_limiter();
	}

	/**
	 * @param string cache control method to use for session pages. Valid values
	 *               include none/nocache/private/private_no_expire/public
	 */
	public function setCacheControl($value)
	{
		session_cache_limiter(TPropertyValue::ensureEnum($value,array('none','nocache','private','private_no_expire','public')));
	}

	/**
	 * @return string content type, default is text/html
	 */
	public function setContentType($type)
	{
		$this->_contentType = $type;
	}

	/**
	 * @return string current content type
	 */
	public function getContentType()
	{
		return $this->_contentType;
	}

	/**
	 * @return string output charset.
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * @param string output charset.
	 */
	public function setCharset($charset)
	{
		$this->_charset = $charset;
	}

	/**
	 * @return boolean whether to enable output buffer
	 */
	public function getBufferOutput()
	{
		return $this->_bufferOutput;
	}

	/**
	 * @param boolean whether to enable output buffer
	 * @throws TInvalidOperationException if session is started already
	 */
	public function setBufferOutput($value)
	{
		if($this->_initialized)
			throw new TInvalidOperationException('httpresponse_bufferoutput_unchangeable');
		else
			$this->_bufferOutput=TPropertyValue::ensureBoolean($value);
	}

	/**
	 * @return integer HTTP status code, defaults to 200
	 */
	public function getStatusCode()
	{
		return $this->_status;
	}

	/**
	 * @param integer HTTP status code
	 */
	public function setStatusCode($status)
	{
		$this->_status=TPropertyValue::ensureInteger($status);
	}

	/**
	 * @return THttpCookieCollection list of output cookies
	 */
	public function getCookies()
	{
		if($this->_cookies===null)
			$this->_cookies=new THttpCookieCollection($this);
		return $this->_cookies;
	}

	/**
	 * Outputs a string.
	 * It may not be sent back to user immediately if output buffer is enabled.
	 * @param string string to be output
	 */
	public function write($str)
	{
		echo $str;
	}

	/**
	 * Sends a file back to user.
	 * Make sure not to output anything else after calling this method.
	 * @param string file name
	 * @param string content to be set. If null, the content will be read from the server file pointed to by $fileName.
	 * @param string mime type of the content.
	 * @param array list of headers to be sent. Each array element represents a header string (e.g. 'Content-Type: text/plain').
	 * @throws TInvalidDataValueException if the file cannot be found
	 */
	public function writeFile($fileName,$content=null,$mimeType=null,$headers=null)
	{
		static $defaultMimeTypes=array(
			'css'=>'text/css',
			'gif'=>'image/gif',
			'jpg'=>'image/jpeg',
			'jpeg'=>'image/jpeg',
			'htm'=>'text/html',
			'html'=>'text/html',
			'js'=>'javascript/js'
		);

		if($mimeType===null)
		{
			$mimeType='text/plain';
			if(function_exists('mime_content_type'))
				$mimeType=mime_content_type($fileName);
			else if(($ext=strrchr($fileName,'.'))!==false)
			{
				$ext=substr($ext,1);
				if(isset($defaultMimeTypes[$ext]))
					$mimeType=$defaultMimeTypes[$ext];
			}
		}
		$fn=basename($fileName);
		if(is_array($headers))
		{
			foreach($headers as $h)
				header($h);
		}
		else
		{
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}
		header("Content-type: $mimeType");
		header('Content-Length: '.($content===null?filesize($fileName):strlen($content)));
		header("Content-Disposition: attachment; filename=\"$fn\"");
		header('Content-Transfer-Encoding: binary');
		if($content===null)
			readfile($fileName);
		else
			echo $content;
	}

	/**
	 * Redirects the browser to the specified URL.
	 * The current application will be terminated after this method is invoked.
	 * @param string URL to be redirected to. If the URL is a relative one, the base URL of
	 * the current request will be inserted at the beginning.
	 */
	public function redirect($url)
	{
		if($this->getHasAdapter())
			$this->_adapter->httpRedirect($url);
		else
			$this->httpRedirect($url);
	}

	/**
	 * Redirect the browser to another URL and exists the current application.
	 * This method is used internally. Please use {@link redirect} instead.
	 * @param string URL to be redirected to. If the URL is a relative one, the base URL of
	 * the current request will be inserted at the beginning.
	 */
	public function httpRedirect($url)
	{
		if(!$this->getApplication()->getRequestCompleted())
			$this->getApplication()->onEndRequest();
		if($url[0]==='/')
			$url=$this->getRequest()->getBaseUrl().$url;
		header('Location: '.str_replace('&amp;','&',$url));
		exit();
	}

	/**
	 * Reloads the current page.
	 * The effect of this method call is the same as user pressing the
	 * refresh button on his browser (without post data).
	 **/
	public function reload()
	{
		$this->redirect($this->getRequest()->getRequestUri());
	}

	/**
	 * Flush the response contents and headers.
	 */
	public function flush()
	{
		if($this->getHasAdapter())
			$this->_adapter->flushContent();
		else
			$this->flushContent();
	}

	/**
	 * Outputs the buffered content, sends content-type and charset header.
	 * This method is used internally. Please use {@link flush} instead.
	 */
	public function flushContent()
	{
		Prado::trace("Flushing output",'System.Web.THttpResponse');
		$this->sendContentTypeHeader();
		if($this->_bufferOutput)
			ob_flush();
	}

	/**
	 * Sends content type header if charset is not empty.
	 */
	protected function sendContentTypeHeader()
	{
		$charset=$this->getCharset();
		if($charset==='' && ($globalization=$this->getApplication()->getGlobalization(false))!==null)
			$charset=$globalization->getCharset();
		if($charset!=='')
		{
			$contentType=$this->_contentType===null?'text/html':$this->_contentType;
			$this->appendHeader('Content-Type: '.$contentType.';charset='.$charset);
		}
		else if($this->_contentType!==null)
			$this->appendHeader('Content-Type: '.$this->_contentType.';charset=UTF-8');
	}

	/**
	 * Returns the content in the output buffer.
	 * The buffer will NOT be cleared after calling this method.
	 * Use {@link clear()} is you want to clear the buffer.
	 * @return string output that is in the buffer.
	 */
	public function getContents()
	{
		Prado::trace("Retrieving output",'System.Web.THttpResponse');
		return $this->_bufferOutput?ob_get_contents():'';
	}

	/**
	 * Clears any existing buffered content.
	 */
	public function clear()
	{
		if($this->_bufferOutput)
			ob_clean();
		Prado::trace("Clearing output",'System.Web.THttpResponse');
	}

	/**
	 * Sends a header.
	 * @param string header
	 */
	public function appendHeader($value)
	{
		Prado::trace("Sending header '$value'",'System.Web.THttpResponse');
		header($value);
	}

	/**
	 * Writes a log message into error log.
	 * This method is simple wrapper of PHP function error_log.
	 * @param string The error message that should be logged
	 * @param integer where the error should go
	 * @param string The destination. Its meaning depends on the message parameter as described above
	 * @param string The extra headers. It's used when the message parameter is set to 1. This message type uses the same internal function as mail() does.
	 * @see http://us2.php.net/manual/en/function.error-log.php
	 */
	public function appendLog($message,$messageType=0,$destination='',$extraHeaders='')
	{
		error_log($message,$messageType,$destination,$extraHeaders);
	}

	/**
	 * Sends a cookie.
	 * Do not call this method directly. Operate with the result of {@link getCookies} instead.
	 * @param THttpCookie cook to be sent
	 */
	public function addCookie($cookie)
	{
		$request=$this->getRequest();
		if($request->getEnableCookieValidation())
		{
			$value=$this->getApplication()->getSecurityManager()->hashData($cookie->getValue());
			setcookie($cookie->getName(),$value,$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$cookie->getSecure());
		}
		else
			setcookie($cookie->getName(),$cookie->getValue(),$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$cookie->getSecure());
	}

	/**
	 * Deletes a cookie.
	 * Do not call this method directly. Operate with the result of {@link getCookies} instead.
	 * @param THttpCookie cook to be deleted
	 */
	public function removeCookie($cookie)
	{
		setcookie($cookie->getName(),null,0,$cookie->getPath(),$cookie->getDomain(),$cookie->getSecure());
	}

	/**
	 * @return string the type of HTML writer to be used, defaults to THtmlWriter
	 */
	public function getHtmlWriterType()
	{
		return $this->_htmlWriterType;
	}

	/**
	 * @param string the type of HTML writer to be used, may be the class name or the namespace
	 */
	public function setHtmlWriterType($value)
	{
		$this->_htmlWriterType=$value;
	}

	/**
	 * Creates a new instance of HTML writer.
	 * If the type of the HTML writer is not supplied, {@link getHtmlWriterType HtmlWriterType} will be assumed.
	 * @param string type of the HTML writer to be created. If null, {@link getHtmlWriterType HtmlWriterType} will be assumed.
	 */
	public function createHtmlWriter($type=null)
	{
		if($type===null)
			$type=$this->getHtmlWriterType();
		if($this->getHasAdapter())
			return $this->_adapter->createNewHtmlWriter($type, $this);
		else
		 	return $this->createNewHtmlWriter($type, $this);
	}

	/**
	 * Create a new html writer instance.
	 * This method is used internally. Please use {@link createHtmlWriter} instead.
	 * @param string type of HTML writer to be created.
	 * @param ITextWriter text writer holding the contents.
	 */
	public function createNewHtmlWriter($type, $writer)
	{
		return Prado::createComponent($type, $writer);
	}
}

?>