<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of question records.
 *
 * @since  4.0.0
 */
class QuestionsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   4.0.0
	 * @see     \Joomla\CMS\MVC\Controller\BaseController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'topic', 'a.topic', 'topic_title',
				'access', 'a.access', 'access_level',
				'pinned', 'a.pinned',
				'created', 'a.created',
				'created_by', 'a.created_by', 'author_name',
				'created_by_name', 'a.created_by_name',
				'created_by_email', 'a.created_by_email',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'hits', 'a.hits',
				'published', 'a.published',
				'author_id',
				'topic_id',
				'level',
				'c.section_id', 'sectionid'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$pinned = $this->getUserStateFromRequest($this->context . '.filter.pinned', 'filter_pinned', '');
		$this->setState('filter.pinned', $pinned);

		$sectionId = $this->getUserStateFromRequest($this->context . '.filter.sectionid', 'filter_sectionid');
		$this->setState('filter.sectionid', $sectionId);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$topicId = $this->getUserStateFromRequest($this->context . '.filter.topic_id', 'filter_topic_id');

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$topicId = $app->input->post->get('topic_id');
			$this->setState('filter.topic_id', $topicId);
		}

		// List state information.
		parent::populateState('a.id', 'desc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   4.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.pinned');
		$id .= ':' . serialize($this->getState('filter.topic_id'));
		$id .= ':' . $this->getState('filter.sectionid');
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . serialize($this->getState('filter.access'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a database query to list topics.
	 *
	 * @return  JDatabaseQuery object.
	 *
	 * @since   4.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$app = Factory::getApplication();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.topicid' .
					', a.state, a.pinned, a.access, a.created, a.created_by, a.created_by_name, a.created_by_email' .
					', a.created_by_alias, a.ordering, a.language, a.hits, a.publish_up, a.publish_down'
			)
		);
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the language
		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the topics.
		$query->select('c.title AS topic_title')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the sections.
		$query->select('s.id AS sectionid')
			->join('LEFT', '#__minitek_faqbook_sections AS s ON s.id = c.section_id');

		// Join over the users for the author.
		$query->select('CASE WHEN a.created_by = 0 THEN a.created_by_name ELSE ua.name END as author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by access level
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$access = (int) $access;
			$query->where($db->quoteName('a.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('a.access'), $access);
		}

		// Filter by access level on topics
		if (!$user->authorise('core.admin'))
		{
			$groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->quoteName('a.access'), $groups);
			$query->whereIn($db->quoteName('c.access'), $groups);
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by pinned state
		$pinned = $this->getState('filter.pinned');

		if ($pinned != '')
		{
			$query->where('a.pinned = ' . $db->quote($pinned));
		}

		// Filter by topics and by level
		$topicId = $this->getState('filter.topic_id', array());
		$level = $this->getState('filter.level');

		if (!is_array($topicId))
		{
			$topicId = $topicId ? array($topicId) : array();
		}

		// Case: Using both topics filter and by level filter
		if (count($topicId))
		{
			$topicId = ArrayHelper::toInteger($topicId);
			$topicTable = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
			$subTopicItemsWhere = array();

			foreach ($topicId as $key => $filter_topicid)
			{
				$topicTable->load($filter_topicid);
				$topicWhere = '';

				if ($level)
				{
					$topicLevel = (int) $level + (int) $topicTable->level - 1;
					$topicWhere = $db->quoteName('c.level') . ' <= :level' . $key . ' AND ';
					$query->bind(':level' . $key, $topicLevel, ParameterType::INTEGER);
				}

				$topicWhere .= $db->quoteName('c.lft') . ' >= :lft' . $key . ' AND ' . $db->quoteName('c.rgt') . ' <= :rgt' . $key;
				$query->bind(':lft' . $key, $topicTable->lft, ParameterType::INTEGER)
					->bind(':rgt' . $key, $topicTable->rgt, ParameterType::INTEGER);

				$subTopicItemsWhere[] = '(' . $topicWhere . ')';
			}

			$query->where('(' . implode(' OR ', $subTopicItemsWhere) . ')');
		}

		// Case: Using only the by level filter
		elseif ($level = (int) $level)
		{
			$query->where($db->quoteName('c.level') . ' <= :level')
				->bind(':level', $level, ParameterType::INTEGER);
		}

		// Filter by section
		$sectionId = $this->getState('filter.sectionid');
		if (is_numeric($sectionId))
		{
			$query->where('s.id = ' . $db->quote($sectionId));
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol == 'a.ordering' || $orderCol == 'topic_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of items.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (Factory::getApplication()->isClient('site'))
		{
			$user = Factory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
}
