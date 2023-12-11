<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseQuery;

/**
 * Methods supporting a list of topic records.
 *
 * @since  4.0.0
 */
class TopicsModel extends ListModel
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
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'section_id', 'a.section_id', 'section_title',
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'alias', 'a.alias',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'language', 'a.language',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_time', 'a.created_time',
				'created_user_id', 'a.created_user_id',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'level', 'a.level',
				'path', 'a.path',
				'tag',
				'questions_count'
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

		$search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$section_id = $this->getUserStateFromRequest($this->context . '.filter.section_id', 'filter_section_id', '');
		$this->setState('filter.section_id', $section_id);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int');

		if ($formSubmited) {
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);
		}

		// List state information.
		parent::populateState('a.lft', 'asc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage)) {
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
		$id .= ':' . $this->getState('filter.section_id');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.access'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a database query to list topics.
	 *
	 * @return  DatabaseQuery object.
	 *
	 * @since   4.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.section_id, a.parent_id, a.title, a.alias, a.published, a.access' .
					', a.checked_out, a.checked_out_time, a.created_user_id' .
					', a.path, a.parent_id, a.level, a.lft, a.rgt' .
					', a.language'
			)
		);

		$query->from('#__minitek_faqbook_topics AS a');

		// Join over the section
		$query->select('s.title AS section_title, s.alias as section_alias')
			->join('LEFT', $db->quoteName('#__minitek_faqbook_sections') . ' AS s ON s.id = a.section_id');

		// Join over the language
		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

		// Count questions in topic
		$query->select('COUNT(q.id) as questions_count')
			->join('LEFT', '#__minitek_faqbook_questions AS q ON q.topicid = a.id AND q.state=1')
			->group('a.id');

		// Filter by access level
		$access = $this->getState('filter.access');

		if (is_numeric($access)) {
			$access = (int) $access;
			$query->where($db->quoteName('a.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		} elseif (is_array($access)) {
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('a.access'), $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin')) {
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by section
		if ($section_id = $this->getState('filter.section_id')) {
			$query->where('a.section_id = ' . $db->quote($section_id));
		}

		// Exclude ROOT category
		$query->where('a.id > 1');

		// Filter on the level.
		if ($level = $this->getState('filter.level')) {
			$query->where('a.level <= ' . (int) $level);
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'author:') === 0) {
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			} else {
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = ' . $db->quote($language));
		}

		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'a.lft');
		$listDirn = $db->escape($this->getState('list.direction', 'ASC'));

		if ($listOrdering == 'a.access') {
			$query->order('a.access ' . $listDirn . ', a.lft ' . $listDirn);
		} else {
			$query->order($db->escape($listOrdering) . ' ' . $listDirn);
		}

		return $query;
	}
}
