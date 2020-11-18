<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\URI\URI;

class FAQBookProHelper
{
	public static $extension = 'com_faqbookpro';

	/**
	 * Get authorised topics.
	 *
	 * @return  Version number
	 *
	 * @since   4.0.0
	 */
	public static function getAuthorisedTopics($action)
	{
		// Brute force method: get all published topic rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('t.id AS id, a.name AS asset_name')
			->from('#__minitek_faqbook_topics AS t')
			->join('INNER', '#__assets AS a ON t.asset_id = a.id')
			->where('t.published = 1');
		$db->setQuery($query);
		$allTopics = $db->loadObjectList('id');
		$allowedTopics = array();

		foreach ($allTopics as $topic)
		{
			if (\JFactory::getUser()->authorise($action, $topic->asset_name))
			{
				$allowedTopics[] = (int) $topic->id;
			}
		}

		return $allowedTopics;
	}

	/**
	 * Get latest version.
	 *
	 * @return  Version number
	 *
	 * @since   4.0.0
	 */
	public static function latestVersion()
	{
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$version = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '4.*')
				{
					$version = (string)$update->version;
					
					break;
				}
			}
		}

		return $version;
	}

	/**
	 * Get local version.
	 *
	 * @return  Version number
	 *
	 * @since   4.0.0
	 */
	public static function localVersion()
	{
		$xml = simplexml_load_file(JPATH_ADMINISTRATOR .'/components/com_faqbookpro/faqbookpro.xml');
		$version = (string)$xml->version;

		return $version;
	}

	/**
	 * Get update message.
	 *
	 * @return  Version number
	 *
	 * @since   4.0.0
	 */
	public static function updateMessage()
	{
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$message = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '4.*')
				{
					$message = (string)$update->message;

					break;
				}
			}
		}

		return $message;
	}

	/**
	 * Get update message version.
	 *
	 * @return  Version number
	 *
	 * @since   4.0.0
	 */
	public static function updateMessageVersion()
	{
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$version = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '4.*')
				{
					$version = (string)$update->showmessage;

					break;
				}
			}
		}

		return $version;
	}
}
