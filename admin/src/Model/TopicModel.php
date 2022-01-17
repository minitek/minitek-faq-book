<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Access\Rules;

/**
 * Model for a Topic.
 *
 * @since  4.0.0
 */
class TopicModel extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $text_prefix = 'COM_FAQBOOKPRO';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   4.0.0
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}

			$user = Factory::getUser();

			return $user->authorise('core.delete', 'com_faqbookpro.topic.' . (int) $record->id);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   4.0.0
	 */
	protected function canEditState($record)
	{
		$user = Factory::getUser();

		// Check for existing topic.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_faqbookpro.topic.' . (int) $record->id);
		}
		// New topic, so check against the parent.
		elseif (!empty($record->parent_id))
		{
			return $user->authorise('core.edit.state', 'com_faqbookpro.topic.' . (int) $record->parent_id);
		}
		// Default to component settings if neither topic nor parent known.
		else
		{
			return $user->authorise('core.edit.state', 'com_faqbookpro');
		}
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\Table\Table  A JTable object
	 *
	 * @since   4.0.0
	 */
	public function getTable($type = 'Topic', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	protected function populateState()
	{
		$app = Factory::getApplication('administrator');

		$parentId = $app->input->getInt('parent_id');
		$this->setState('topic.parent_id', $parentId);

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->id))
			{
				$result->parent_id = $this->getState('topic.parent_id');
			}

			// Convert the metadata field to an array.
			$registry = new Registry;
			$registry->loadString($result->metadata);
			$result->metadata = $registry->toArray();

			// Convert the created and modified dates to local user time for display in the form.
			$tz = new \DateTimeZone(Factory::getApplication()->get('offset'));

			if ((int) $result->created_time)
			{
				$date = new \JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toSql(true);
			}
			else
			{
				$result->created_time = null;
			}

			if ((int) $result->modified_time)
			{
				$date = new \JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toSql(true);
			}
			else
			{
				$result->modified_time = null;
			}

		}

		return $result;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$jinput = Factory::getApplication()->input;

		// Get the form.
		$form = $this->loadForm('com_faqbookpro.topic', 'topic', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$user = Factory::getUser();

		if (!$user->authorise('core.edit.state', 'com_faqbookpro.topic.' . $jinput->get('id')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   4.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		$data = $app->getUserState('com_faqbookpro.edit.' . $this->getName() . '.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Topics
			if (!$data->id)
			{
				// Check for selected fields
				$filters = (array) $app->getUserState('com_faqbookpro.topics.' . 'faqbookpro' . '.filter');

				$data->set(
					'published',
					$app->input->getInt(
						'published',
						((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
					)
				);
				$data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
				$data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : Factory::getConfig()->get('access'))));
			}
		}

		$this->preprocessData('com_faqbookpro.topic', $data);

		return $data;
	}

	/**
	 * Allows preprocessing of the \JForm object.
	 *
	 * @param   \JForm  $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'faqbookpro')
	{
		// Set the access control rules field component value.
		$form->setFieldAttribute('rules', 'component', 'com_faqbookpro');
		$form->setFieldAttribute('rules', 'section', 'topic');

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 */
	public function save($data)
	{
		$table      = $this->getTable();
		$input      = Factory::getApplication()->input;
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		// Load the row if saving an existing topic.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['published'] = 0;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Bind the rules.
		if (isset($data['rules']))
		{
			$rules = new Rules($data['rules']);
			$table->setRules($rules);
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before save event.
		$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($context, &$table, $isNew, $data));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the after save event.
		Factory::getApplication()->triggerEvent($this->event_after_save, array($context, &$table, $isNew, $data));

		// Rebuild the path for the topic:
		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the paths of the topic's children:
		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		if (parent::publish($pks, $value))
		{
			$extension = 'com_faqbookpro';

			// Include the content plugins for the change of topic state event.
			\JPluginHelper::importPlugin('content');

			// Trigger the onCategoryChangeState event.
			Factory::getApplication()->triggerEvent('onCategoryChangeState', array($extension, $pks, $value));

			return true;
		}
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @param   array    $idArray    An array of primary key ids.
	 * @param   integer  $lft_array  The lft value
	 *
	 * @return  boolean  False on failure or error, True otherwise
	 *
	 * @since   1.6
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Custom clean the cache of com_faqbookpro and faqbookpro modules
	 *
	 * @param   string   $group      Cache group name.
	 * @param   integer  $client_id  Application client id.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_faqbookpro');
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array    Contains the modified title and alias.
	 *
	 * @since   1.7
	 */
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
		{
			$title = \JString::increment($title);
			$alias = \JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	public function dynamicSection($topicId)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('t.id, t.title, t.section_id, s.id as section_id, s.title as section_title, s.alias as section_alias')
			->from('#__minitek_faqbook_topics AS t')
			->where('t.id = ' . $db->quote($topicId) . '')
			->join('LEFT', $db->quoteName('#__minitek_faqbook_sections') . ' AS s ON s.id = t.section_id');
		$db->setQuery($query);

		$row = $db->loadObject();
		$row = json_encode($row);

		jexit($row);
	}

	public static function getTopic($id)
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

	public function getTopicsTree()
	{
		$app = Factory::getApplication();
		$clientID = $app->getClientId();
		$db = Factory::getDBO();
		$user = Factory::getUser();
		$aid = (int)$user->get('aid');

		$query = "SELECT id, title,  parent_id	FROM #__minitek_faqbook_topics";

		if ($app->isSite())
		{
			$query .= " WHERE published=1 AND level>0 ";
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
			
			if ($app->getLanguageFilter())
			{
				$query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
			}
		}

		$query .= " ORDER BY parent_id ";
		$db->setQuery($query);

		$topics = $db->loadObjectList();
		$tree = array();

		return $this->buildTree($topics);
	}

	public function buildTree(array &$topics, $parent = 1)
	{
		$branch = array();

		foreach ($topics as &$topic)
		{
			if ($topic->parent_id == $parent)
			{
				$children = $this->buildTree($topics, $topic->id);

				if ($children)
				{
					$topic->children = $children;
				}

				$branch[$topic->id] = $topic;
			}
		}

		return $branch;
	}

	public function getTreePath($tree, $id)
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
					$retry = $this->getTreePath($root->children, $id);

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
