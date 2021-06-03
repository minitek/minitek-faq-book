<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.filesystem.folder');

/**
 * FAQ Book Component Utilities Helper.
 *
 * @since  4.0.0
 */
abstract class UtilitiesHelper
{
	public static function getParams($option)
	{
		$application = Factory::getApplication();

		if ($application->isClient('site')) {
			$params = $application->getParams($option);
		} else {
			$params = ComponentHelper::getParams($option);
		}

		return $params;
	}

	public static function getWordLimit($text, $limit, $end_char = '&#8230;')
	{
		if (StringHelper::trim($text) == '')
			return $text;

		// always strip tags for text
		$text = strip_tags($text);
		$find = array(
			"/\r|\n/u",
			"/\t/u",
			"/\s\s+/u"
		);
		$replace = array(
			" ",
			" ",
			" "
		);
		$text = preg_replace($find, $replace, $text);

		preg_match('/\s*(?:\S*\s*){' . (int)$limit . '}/u', $text, $matches);

		if (StringHelper::strlen($matches[0]) == StringHelper::strlen($text))
			$end_char = '';

		return StringHelper::rtrim($matches[0]) . $end_char;
	}

	public static function getTimeSince($date)
	{
		$date = strtotime($date);
		$now = Factory::getDate()->format("Y-m-d H:i:s");
		$now = strtotime($now);
		$since = $now - $date;

		$chunks = array(
			array(60 * 60 * 24 * 365, Text::_('COM_FAQBOOKPRO_YEAR'), Text::_('COM_FAQBOOKPRO_YEARS')),
			array(60 * 60 * 24 * 30, Text::_('COM_FAQBOOKPRO_MONTH'), Text::_('COM_FAQBOOKPRO_MONTHS')),
			array(60 * 60 * 24 * 7, Text::_('COM_FAQBOOKPRO_WEEK'), Text::_('COM_FAQBOOKPRO_WEEKS')),
			array(60 * 60 * 24, Text::_('COM_FAQBOOKPRO_DAY'), Text::_('COM_FAQBOOKPRO_DAYS')),
			array(60 * 60, Text::_('COM_FAQBOOKPRO_HOUR'), Text::_('COM_FAQBOOKPRO_HOURS')),
			array(60, Text::_('COM_FAQBOOKPRO_MINUTE'), Text::_('COM_FAQBOOKPRO_MINUTES')),
			array(1, Text::_('COM_FAQBOOKPRO_SECOND'), Text::_('COM_FAQBOOKPRO_SECONDS'))
		);

		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i][0];
			$name_1 = $chunks[$i][1];
			$name_n = $chunks[$i][2];

			if (($count = floor($since / $seconds)) != 0) {
				break;
			}
		}

		$print = ($count == 1) ? '1 ' . $name_1 : "$count {$name_n}";

		return $print;
	}

	public static function getAuthorisedTopics($action)
	{
		// Brute force method: get all published topic rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('t.id AS id, a.name AS asset_name')
			->from('#__minitek_faqbook_topics AS t')
			->join('INNER', '#__assets AS a ON t.asset_id = a.id')
			->where('t.published = 1');
		$db->setQuery($query);
		$allTopics = $db->loadObjectList('id');
		$allowedTopics = array();

		foreach ($allTopics as $topic) {
			if (Factory::getUser()->authorise($action, $topic->asset_name)) {
				$allowedTopics[] = (int) $topic->id;
			}
		}

		return $allowedTopics;
	}

	public static function userExists($id)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id) . '');
		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}
}
