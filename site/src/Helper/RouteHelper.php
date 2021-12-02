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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;

/**
 * FAQ Book Component Route Helper.
 *
 * @since  4.0.0
 */
abstract class RouteHelper
{
	protected static $lookup = array();

	public static function getSectionsRoute($Itemid = 0, $language = 0)
	{
		$link = 'index.php?option=com_faqbookpro&view=sections';

		$needles = array(
			'sections' => 0
		);

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang='.$lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		$link .= '&Itemid='.$Itemid;

		return $link;
	}

	public static function getSectionRoute($sectionid, $tab = false, $language = 0)
	{
		$id = (int) $sectionid;

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			if ($tab)
			{
				$link = 'index.php?option=com_faqbookpro&view=section&tab='.$tab.'&id='.$id;
			}
			else
			{
				$link = 'index.php?option=com_faqbookpro&view=section&id='.$id;
			}

			$needles = array(
				'section' => (int)$id,
				'tab' => $tab
			);

			if ($language && $language != "*" && Multilanguage::isEnabled())
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('a.sef AS sef')
					->select('a.lang_code AS lang_code')
					->from('#__languages AS a');
				$db->setQuery($query);
				$langs = $db->loadObjectList();

				foreach ($langs as $lang)
				{
					if ($language == $lang->lang_code)
					{
						$link .= '&lang='.$lang->sef;
						$needles['language'] = $language;
					}
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid='.$item;
			}
		}

		return $link;
	}

	public static function getTopicRoute($topicid, $tab = false, $language = 0)
	{
		$id = (int) $topicid;

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			if ($tab)
			{
				$link = 'index.php?option=com_faqbookpro&view=topic&tab='.$tab.'&id='.$id;
			}
			else
			{
				$link = 'index.php?option=com_faqbookpro&view=topic&id='.$id;
			}

			$needles = array(
				'topic' => (int)$id,
				'tab' => $tab
			);

			if ($language && $language != "*" && Multilanguage::isEnabled())
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('a.sef AS sef')
					->select('a.lang_code AS lang_code')
					->from('#__languages AS a');
				$db->setQuery($query);
				$langs = $db->loadObjectList();

				foreach ($langs as $lang)
				{
					if ($language == $lang->lang_code)
					{
						$link .= '&lang='.$lang->sef;
						$needles['language'] = $language;
					}
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid='.$item;
			}
		}

		return $link;
	}

	public static function newQuestionRoute($sectionId, $topicId = 0, $language = 0)
	{
		if ($topicId)
		{
			$link = 'index.php?option=com_faqbookpro&view=myquestion&layout=edit&section='.$sectionId.'&topicid='.$topicId;
		}
		else
		{
			$link = 'index.php?option=com_faqbookpro&view=myquestion&layout=edit&section='.$sectionId;
		}

		$needles = array(
			'myquestion' => (int)$sectionId
		);

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang='.$lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function editQuestionRoute($id, $sectionId, $hash = 0, $language = 0)
	{
		$link = 'index.php?option=com_faqbookpro&view=myquestion&layout=edit&a_id='.$id.'&section='.$sectionId;

		if ($hash)
		{
			$link .= '&hash='.$hash;
		}

		$needles = array(
			'id' => (int)$id,
			'section' => (int)$sectionId
		);

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang='.$lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function newAnswerRoute($questionid, $hash = 0, $language = 0)
	{
		$link = 'index.php?option=com_faqbookpro&view=myanswer&layout=edit&question='.$questionid;

		if ($hash)
		{
			$link .= '&hash='.$hash;
		}

		$needles = array(
			'question' => (int)$questionid
		);

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang='.$lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function editAnswerRoute($id, $questionid = 0, $hash = 0, $language = 0)
	{
		$link = 'index.php?option=com_faqbookpro&view=myanswer&layout=edit&a_id='.$id;

		if ($hash)
		{
			$link .= '&hash='.$hash;
		}

		$needles = array(
			'id' => (int)$id,
			'question' => (int)$questionid
		);

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang='.$lang->sef;
					$needles['language'] = $language;
				}
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	private static $tree = null;

	public static function _findItem($needles)
	{
		$component = ComponentHelper::getComponent('com_faqbookpro');
		$params = ComponentHelper::getParams('com_faqbookpro');
		$application = Factory::getApplication();
		$menus = $application->getMenu('site', array());
		$language = isset($needles['language']) ? $needles['language'] : '*';
		$items = $menus->getItems('component_id', $component->id);
		$match = null;

		foreach ($needles as $needle => $id)
		{
			if (count($items))
			{
				foreach ($items as $item)
				{
					if ($needle == 'myquestion')
					{
						if ((@$item->query['view'] == $needle) && (@$item->query['section'] == $id))
						{
							$match = $item;
							$match_id = $match->id;
							break;
						}
					}

					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id))
					{
						$match = $item;
						$match_id = $match->id;
						break;
					}

					if (!is_null($match))
					{
						break;
					}
				}
			}

			if (!is_null($match))
			{
				break;
			}

			if (is_null($match))
			{
				// Try to detect any parent topic menu item for children topics without menu items
				if ($needle == 'topic')
				{
					if (is_null(self::$tree))
					{
						self::$tree = self::getAllTopics();
					}

					$parents = self::getTreePath(self::$tree, $id);

					if (is_array($parents))
					{
						foreach ($parents as $topicID)
						{
							if ($topicID != $id)
							{
								$match = self::_findItem(array('topic' => $topicID));

								if (!is_null($match))
								{
									$match_id = $match;
									break;
								}
							}
						}
					}
					// Try to detect any parent section menu item for topics without menu items
					if (is_null($match))
					{
						$topicSection = self::getTopic($id)->section_id;
						$match = self::_findItem(array('section' => $topicSection));
						$match_id = $match;
					}
				}

				// Try to detect any parent topic menu item for questions without menu items
				if ($needle == 'question')
				{
					$questionTopic = self::getQuestion($id)->topicid;
					$match = self::_findItem(array('topic' => $questionTopic));
					$match_id = $match;
				}
			}
		}

		if (isset($match_id))
		{
			return $match_id;
		}
		else
		{
			// Check if the active menuitem matches the requested language
			$active = $menus->getActive();

			if ($active
				&& $active->component == 'com_faqbookpro'
				&& ($language == '*' || in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
			{
				return $active->id;
			}

			// If not found, return language specific home link
			$default = $menus->getDefault($language);

			return !empty($default->id) ? $default->id : null;
		}
	}

	private static function getQuestion($id)
	{
		$db = Factory::getDBO();
		$query = 'SELECT * FROM '. $db->quoteName( '#__minitek_faqbook_questions' );
		$query .= ' WHERE ' . $db->quoteName( 'id' ) . ' = '. $db->quote($id).' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			return $row;
		}
		else
		{
			return false;
		}
	}

	private static function getTopic($id)
	{
		$db = Factory::getDBO();
		$query = 'SELECT * FROM '. $db->quoteName( '#__minitek_faqbook_topics' );
		$query .= ' WHERE ' . $db->quoteName( 'id' ) . ' = '. $db->quote($id).' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			return $row;
		}
		else
		{
			return false;
		}
	}

	private static function getAllTopics()
	{
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$user = Factory::getUser();
		$query = "SELECT id, title, parent_id	FROM #__minitek_faqbook_topics";

		if ($app->isClient('site'))
		{
			$query .= " WHERE published=1 AND level>0 ";
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";

			if ($app->getLanguageFilter())
			{
				$query .= " AND language IN(".$db->Quote(Factory::getLanguage()->getTag()).", ".$db->Quote('*').")";
			}
		}

		$query .= " ORDER BY parent_id ";
		$db->setQuery($query);
		$topics = $db->loadObjectList();
		$tree = array();

		return self::buildTree($topics);
	}

	private static function buildTree(array &$topics, $parent = 1)
	{
		$branch = array();

		foreach ($topics as &$topic)
		{
			if ($topic->parent_id == $parent)
			{
				$children = self::buildTree($topics, $topic->id);

				if ($children)
				{
					$topic->children = $children;
				}

				$branch[$topic->id] = $topic;
			}
		}

		return $branch;
	}

	private static function getTreePath($tree, $id)
	{
		if (array_key_exists($id, $tree))
		{
			return array($id);
		}
		else
		{
			foreach ($tree as $key => $root)
			{
				if (isset($root->children) && is_array($root->children))
				{
					$retry = self::getTreePath($root->children, $id);

					if ($retry)
					{
						$retry[] = $key;
						return $retry;
					}
				}
			}
		}

		return null;
	}
}
