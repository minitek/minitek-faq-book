<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;

/**
 * Methods supporting a list of section records.
 *
 * @since  4.0.0
 */
class SectionsModel extends ListModel
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
				'description', 'a.description',
				'state', 'a.state',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'access', 'a.access', 'access_level',
				'attribs', 'a.attribs',
				'ordering', 'a.ordering',
				'metadesc', 'a.metadesc',
				'metakey', 'a.metakey',
				'metadata', 'a.metadata',
				'created_user_id', 'a.created_user_id',
				'created_time', 'a.created_time',
				'hits', 'a.hits',
				'language', 'a.language',
				'topics_count'
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

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int');

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);
		}

		// List state information.
		parent::populateState('a.id', 'desc');

		// force a language
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
		$id .= ':' . serialize($this->getState('filter.access'));
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
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
				'a.id,
				 a.asset_id,
				 a.title,
				 a.alias,
				 a.description,
				 a.state,
				 a.checked_out,
				 a.checked_out_time,
				 a.access,
				 a.attribs,
				 a.ordering,
				 a.metadesc,
				 a.metakey,
				 a.metadata,
				 a.created_user_id,
				 a.created_time,
				 a.hits,
				 a.language'
			)
		);
		$query->from('#__minitek_faqbook_sections AS a');

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

		// Count topics in section
		$query->select('COUNT(t.id) as topics_count')
			->join('LEFT', '#__minitek_faqbook_topics AS t ON t.section_id = a.id AND t.published=1')
			->group('a.id');

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

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
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
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.ordering');
		$orderDirn = $db->escape($this->state->get('list.direction', 'DESC'));

		//sqlsrv change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
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
	 * @since   4.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$app = Factory::getApplication();

		if ($app->isClient('site'))
		{
			$user = Factory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				//Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
}
