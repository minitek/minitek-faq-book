<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * FAQ Book Component Sections Model
 *
 * @since  4.0.0
 */
class SectionsModel extends BaseDatabaseModel
{
	public static function getSections($sections = false)
	{
		$params = UtilitiesHelper::getParams('com_faqbookpro');
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__minitek_faqbook_sections');

		if ($sections)
		{
			$query->where('id IN (' . implode(',', $sections) . ')');
		}

		$query->where('state = 1');
		$query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		// Filter by language
		$query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		// Ordering 
		$ordering = $params->get('sections_ordering', 'ordering');
		$ordering_dir = $params->get('sections_ordering_dir', 'ASC');
		$query->order($ordering.' '.$ordering_dir);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($rows)
		{
			return $rows;
		}
		else
		{
			return false;
		}
	}

	public static function getChildrenTopics($topic)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__minitek_faqbook_topics');
		$query->where('published = 1');
		$query->where('parent_id = '.$db->quote($topic->id));
		$query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		// Ordering
		$section_table = Table::getInstance('SectionTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
		$section_table->load($topic->section_id);
		$sectionParams = new Registry($section_table->attribs);
		$ordering = $sectionParams->get('topics_ordering', 'lft');
		$ordering_dir = $sectionParams->get('topics_ordering_dir', 'ASC');
		$query->order($ordering.' '.$ordering_dir);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($rows)
		{
			return $rows;
		}
		else
		{
			return false;
		}
	}

	public function getTopicLastQuestion($topicId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.alias, a.content, a.checked_out, a.checked_out_time, a.state,
			a.topicid, a.created, a.created_by, a.created_by_alias, a.created_by_name, ' .
			// Use created if modified is 0
			'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
			'a.modified_by, a.created,' .
			'a.images, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
			'a.hits, a.featured, a.locked, a.pinned, a.private'
		);
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')');

	 	// Filter by state
		$query->where('a.state = 1')
			->where('c.published = 1');

		// Don't show private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = Factory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by topic (including children topics)
		$topic_tbl = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
		$topic_tbl->load($topicId);
		$rgt = $topic_tbl->rgt;
		$lft = $topic_tbl->lft;
		$baselevel = (int) $topic_tbl->level;
		$query->where('c.lft >= ' . (int) $lft)
			->where('c.rgt <= ' . (int) $rgt);
		$query->order('a.created DESC');
		$db->setQuery($query);

		// Get the results
		$question = $db->loadObject();

		return $question;
	}

	public function getSectionQuestionsCount($sectionId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the sections
		$query->select('section.title as section_title, section.id as section_id')
			->join('LEFT', '#__minitek_faqbook_sections as section ON section.id = c.section_id');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')')
			->where('section.access IN (' . $groups . ')');

		// Filter by state
		$query->where('a.state = 1')
			->where('c.published = 1')
			->where('section.state = 1');

		// Don't count private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = Factory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by section
		$query->where('section.id = ' . $db->quote($sectionId));
		$query->order('a.created DESC');
		$db->setQuery($query);

		// Get the results
		$count = $db->loadResult();

		return $count;
	}

	public function getTopicQuestionsCount($topicId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')');

		// Filter by state
		$query->where('a.state = 1')
			->where('c.published = 1');

		// Don't count private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = Factory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by topic (including children topics)
		$topic_tbl = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
		$topic_tbl->load($topicId);
		$rgt = $topic_tbl->rgt;
		$lft = $topic_tbl->lft;
		$baselevel = (int) $topic_tbl->level;
		$query->where('c.lft >= ' . (int) $lft)
			->where('c.rgt <= ' . (int) $rgt);
		$query->order('a.created DESC');
		$db->setQuery($query);

		// Get the results
		$count = $db->loadResult();

		return $count;
	}
}
