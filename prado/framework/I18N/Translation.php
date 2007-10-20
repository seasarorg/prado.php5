<?php
/**
 * Translation, static.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @version $Id: Translation.php 1444 2006-09-24 02:20:54Z xue $
 * @package System.I18N
 */

 /**
 * Get the MessageFormat class.
 */
Prado::using('System.I18N.core.MessageFormat');


/**
 * Translation class.
 *
 * Provides translation using a static MessageFormatter.
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version v1.0, last update on Tue Dec 28 11:54:48 EST 2004
 * @package System.I18N
 */
class Translation extends TComponent
{
	/**
	 * The string formatter. This is a class static variable.
	 * @var MessageFormat
	 */
	protected static $formatter;

	/**
	 * Initialize the TTranslate translation components
	 */
	public static function init()
	{
		//initialized the default class wide formatter
		if(is_null(self::$formatter))
		{
			$app = Prado::getApplication()->getGlobalization();
			$config = $app->getTranslationConfiguration();
			$source = MessageSource::factory($config['type'],
											$config['source'],
											$config['filename']);

			$source->setCulture($app->getCulture());

			if($config['cache'])
				$source->setCache(new MessageCache($config['cache']));

			self::$formatter = new MessageFormat($source, $app->getCharset());

			//mark untranslated text
			if($ps=$config['marker'])
				self::$formatter->setUntranslatedPS(array($ps,$ps));

			//save the message on end request
			Prado::getApplication()->attachEventHandler(
				'OnEndRequest', array('Translation', 'saveMessages'));
		}
	}

	/**
	 * Get the static formatter from this component.
	 * @return MessageFormat formattter.
	 * @see localize()
	 */
	public static function formatter()
	{
		return self::$formatter;
	}

	/**
	 * Save untranslated messages to the catalogue.
	 */
	public static function saveMessages()
	{
		static $onceonly = true;

		if($onceonly && !is_null($formatter = self::$formatter))
		{
			$app = Prado::getApplication()->getGlobalization();
			$config = $app->getTranslationConfiguration();
			if(isset($config['autosave']))
			{
				$formatter->getSource()->setCulture($app->getCulture());
				$formatter->getSource()->save($config['catalogue']);
			}
			$onceonly = false;
		}
	}
}

?>