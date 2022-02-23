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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\UCM\UCMType;
use Joomla\Database\ParameterType;
use Joomla\CMS\Event\Model\BeforeBatchEvent;
use Joomla\String\StringHelper;

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
	 * Batch copy/move command. If set to false,
	 * the batch copy/move command is not supported
	 *
	 * @var    string
	 * @since  4.1.7
	 */
	protected $batch_copymove = 'topic_id';

	/**
	 * Allowed batch commands
	 *
	 * @var    array
	 * @since  4.1.7
	 */
	protected $batch_commands = array(
		'assetgroup_id' => 'batchAccess',
		'language_id' => 'batchLanguage'
	);

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

		// If there is no parent topic, parent_id has the format 'section.section_id.1'
		// We must clean this up and set it to '1'
		if (substr( $data['parent_id'], 0, 7 ) === "section")
			$data['parent_id'] = 1;

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
	 * Batch copy topics to a new topic.
	 *
	 * @param   string   $value     The new parent value {section_id}.{topic_id}
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed    An array of new IDs on success, boolean false on failure.
	 *
	 * @since   4.1.7
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		// $value comes as {section_id}.{topic_id}
		$parts = explode('.', $value);
		
		$sectionId = (int) $parts[0];

		// We have section and topic
		if (count($parts) > 1)
		{
			$parentId = (int) $parts[1];
		}
		// We only have section, therefore the new parent_id = 1 (root topic)
		else 
		{
			$parentId = 1;
		}
		
		$type = new UCMType;
		$this->type = $type->getTypeByAlias($this->typeAlias);

		$db = $this->getDbo();
		$newIds = array();

		// Check that the parent exists
		if ($parentId)
		{
			if (!$this->table->load($parentId))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}

			// Check that user has create permission for parent topic
			if ($parentId == $this->table->getRootId())
			{
				$canCreate = $this->user->authorise('core.create', 'com_faqbookpro');
			}
			else
			{
				$canCreate = $this->user->authorise('core.create', 'com_faqbookpro.topic.' . $parentId);
			}

			if (!$canCreate)
			{
				// Error since user cannot create in parent topic
				$this->setError(Text::_('COM_FAQBOOKPRO_WARNING_BATCH_CANNOT_CREATE'));

				return false;
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId))
		{
			if (!$parentId = $this->table->getRootId())
			{
				$this->setError($this->table->getError());

				return false;
			}
			// Make sure we can create in root
			elseif (!$this->user->authorise('core.create', 'com_faqbookpro'))
			{
				$this->setError(Text::_('COM_FAQBOOKPRO_WARNING_BATCH_CANNOT_CREATE'));

				return false;
			}
		}

		// We need to log the parent ID
		$parents = array();

		// Calculate the emergency stop count as a precaution against a runaway loop bug
		$query = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName('#__minitek_faqbook_topics'));
		$db->setQuery($query);

		try
		{
			$count = $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Parent exists so let's proceed
		while (!empty($pks) && $count > 0)
		{
			// Pop the first id off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Copy is a bit tricky, because we also need to copy the children
			$lft = (int) $this->table->lft;
			$rgt = (int) $this->table->rgt;
			$query->clear()
				->select($db->quoteName('id'))
				->from($db->quoteName('#__minitek_faqbook_topics'))
				->where($db->quoteName('lft') . ' > :lft')
				->where($db->quoteName('rgt') . ' < :rgt')
				->bind(':lft', $lft, ParameterType::INTEGER)
				->bind(':rgt', $rgt, ParameterType::INTEGER);
			$db->setQuery($query);
			$childIds = $db->loadColumn();

			// Add child ID's to the array only if they aren't already there.
			foreach ($childIds as $childId)
			{
				if (!\in_array($childId, $pks))
				{
					$pks[] = $childId;
				}
			}

			// Make a copy of the old ID, Parent ID and Asset ID
			$oldId       = $this->table->id;
			$oldParentId = $this->table->parent_id;
			$oldAssetId  = $this->table->asset_id;

			// Reset the id because we are making a copy.
			$this->table->id = 0;

			// If we a copying children, the Old ID will turn up in the parents list
			// otherwise it's a new top level item
			$this->table->parent_id = $parents[$oldParentId] ?? $parentId;

			// Set the new location in the tree for the node.
			$this->table->setLocation($this->table->parent_id, 'last-child');

			// Set the new section_id
			$this->table->section_id = $sectionId;

			// @TODO: Deal with ordering?
			// $this->table->ordering = 1;
			$this->table->level = null;
			$this->table->asset_id = null;
			$this->table->lft = null;
			$this->table->rgt = null;

			// Alter the title & alias
			[$title, $alias] = $this->generateNewTitle($this->table->parent_id, $this->table->alias, $this->table->title);
			$this->table->title  = $title;
			$this->table->alias  = $alias;

			// Unpublish because we are making a copy
			$this->table->published = 0;

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;

			// Copy rules
			$query->clear()
				->update($db->quoteName('#__assets', 't'))
				->join('INNER',
					$db->quoteName('#__assets', 's'),
					$db->quoteName('s.id') . ' = :oldid'
				)
				->bind(':oldid', $oldAssetId, ParameterType::INTEGER)
				->set($db->quoteName('t.rules') . ' = ' . $db->quoteName('s.rules'))
				->where($db->quoteName('t.id') . ' = :assetid')
				->bind(':assetid', $this->table->asset_id, ParameterType::INTEGER);
			$db->setQuery($query)->execute();

			// Now we log the old 'parent' to the new 'parent'
			$parents[$oldId] = $this->table->id;
			$count--;
		}

		// Rebuild the hierarchy.
		if (!$this->table->rebuild())
		{
			$this->setError($this->table->getError());

			return false;
		}

		// Rebuild the tree path.
		if (!$this->table->rebuildPath($this->table->id))
		{
			$this->setError($this->table->getError());

			return false;
		}

		return $newIds;
	}

	/**
	 * Batch move topics to a new topic.
	 *
	 * @param   string   $value     The new parent value {section_id}.{topic_id}
	 * @param   array    $pks       An array of row IDs
	 * @param   array    $contexts  An array of item contexts
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.1.7
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		// $value comes as {section_id}.{topic_id}
		$parts = explode('.', $value);
		
		$sectionId = (int) $parts[0];

		// We have section and topic
		if (count($parts) > 1)
		{
			$parentId = (int) $parts[1];
		}
		// We only have section, therefore the new parent_id = 1 (root topic)
		else 
		{
			$parentId = 1;
		}
		
		$type = new UCMType;
		$this->type = $type->getTypeByAlias($this->typeAlias);

		$db = $this->getDbo();
		$query = $db->getQuery(true);
	
		// Check that the parent exists
		if ($parentId)
		{
			PluginHelper::importPlugin('content');
			
			if (!$this->table->load($parentId))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
	
			// Check that user has create permission for parent topic
			if ($parentId == $this->table->getRootId())
			{
				$canCreate = $this->user->authorise('core.create', 'com_faqbookpro');
			}
			else
			{
				$canCreate = $this->user->authorise('core.create', 'com_faqbookpro.topic.' . $parentId);
			}
			
			if (!$canCreate)
			{
				// Error since user cannot create in parent topic
				$this->setError(Text::_('COM_FAQBOOKPRO_WARNING_BATCH_CANNOT_CREATE'));

				return false;
			}
		
			// Check that user has edit permission for every topic being moved
			// Note that the entire batch operation fails if any topic lacks edit permission
			foreach ($pks as $pk)
			{
				if (!$this->user->authorise('core.edit', 'com_faqbookpro.topic.' . $pk))
				{
					// Error since user cannot edit this topic
					$this->setError(Text::_('COM_FAQBOOKPRO_WARNING_BATCH_CANNOT_EDIT'));

					return false;
				}
			}
		}

		// We are going to store all the children and just move the topic
		$children = array();

		// Parent exists so let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node
			$this->table->setLocation($parentId, 'last-child');

			// Set the new section_id
			$this->table->section_id = $sectionId;
			
			// Get children
			$lft = (int) $this->table->lft;
			$rgt = (int) $this->table->rgt;

			// Add the child node ids to the children array
			$query->clear()
				->select($db->quoteName('id'))
				->from($db->quoteName('#__minitek_faqbook_topics'))
				->where($db->quoteName('lft') . ' BETWEEN :lft AND :rgt')
				->bind(':lft', $lft, ParameterType::INTEGER)
				->bind(':rgt', $rgt, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$children = array_merge($children, (array) $db->loadColumn());
			}
			catch (\RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			// Store the row
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Rebuild the tree path
			if (!$this->table->rebuildPath())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Run event for each child
			foreach ($children as $id)
			{
				$this->table->reset();
				$this->table->load($id);
				
				// Set the new section_id
				$this->table->section_id = $sectionId;

				// Store the row
				if (!$this->table->store())
				{
					$this->setError($this->table->getError());

					return false;
				}

				Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_faqbookpro.topic', &$this->table, false, array()));
			}
		}

		// Process the child rows
		if (!empty($children))
		{
			// Remove any duplicates and sanitize ids
			$children = array_unique($children);
			$children = ArrayHelper::toInteger($children);
		}

		return true;
	}

	/**
	 * Batch language changes for a group of rows.
	 *
	 * @param   string  $value     The new value matching a language.
	 * @param   array   $pks       An array of row IDs.
	 * @param   array   $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   4.1.7
	 */
	protected function batchLanguage($value, $pks, $contexts)
	{
		$db = $this->getDbo();

		PluginHelper::importPlugin('content');

		// Initialize re-usable member properties, and re-usable local variables
		$this->initBatch();

		// Get all the children to change their language
		$children = array();

		foreach ($pks as $pk)
		{
			if ($this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->table->reset();
				$this->table->load($pk);
				$this->table->language = $value;

				$event = new BeforeBatchEvent(
					$this->event_before_batch,
					['src' => $this->table, 'type' => 'language']
				);
				$this->dispatchEvent($event);

				// Check the row.
				if (!$this->table->check())
				{
					$this->setError($this->table->getError());

					return false;
				}

				// Get children
				$lft = (int) $this->table->lft;
				$rgt = (int) $this->table->rgt;

				// Add the child node ids to the children array
				$query = $db->getQuery(true);
				$query->clear();
				$query->select($db->quoteName('id'))
					->from($db->quoteName('#__minitek_faqbook_topics'))
					->where($db->quoteName('lft') . ' BETWEEN :lft AND :rgt')
					->bind(':lft', $lft, ParameterType::INTEGER)
					->bind(':rgt', $rgt, ParameterType::INTEGER);
				$db->setQuery($query);

				try
				{
					$children = array_merge($children, (array) $db->loadColumn());
				}
				catch (\RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return false;
				}

				if (!$this->table->store())
				{
					$this->setError($this->table->getError());

					return false;
				}

				// Update children
				foreach ($children as $id)
				{
					$this->table->reset();
					$this->table->load($id);
					
					// Set the new language
					$this->table->language = $value;

					// Store the row
					if (!$this->table->store())
					{
						$this->setError($this->table->getError());

						return false;
					}

					Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_faqbookpro.topic', &$this->table, false, array()));
				}
			}
			else
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch access level changes for a group of rows.
	 *
	 * @param   integer  $value     The new value matching an Asset Group ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   4.1.7
	 */
	protected function batchAccess($value, $pks, $contexts)
	{
		$db = $this->getDbo();

		PluginHelper::importPlugin('content');

		// Initialize re-usable member properties, and re-usable local variables
		$this->initBatch();

		// Get all the children to change their language
		$children = array();

		foreach ($pks as $pk)
		{
			if ($this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->table->reset();
				$this->table->load($pk);
				$this->table->access = (int) $value;

				$event = new BeforeBatchEvent(
					$this->event_before_batch,
					['src' => $this->table, 'type' => 'access']
				);
				$this->dispatchEvent($event);

				// Check the row.
				if (!$this->table->check())
				{
					$this->setError($this->table->getError());

					return false;
				}

				// Get children
				$lft = (int) $this->table->lft;
				$rgt = (int) $this->table->rgt;

				// Add the child node ids to the children array
				$query = $db->getQuery(true);
				$query->clear();
				$query->select($db->quoteName('id'))
					->from($db->quoteName('#__minitek_faqbook_topics'))
					->where($db->quoteName('lft') . ' BETWEEN :lft AND :rgt')
					->bind(':lft', $lft, ParameterType::INTEGER)
					->bind(':rgt', $rgt, ParameterType::INTEGER);
				$db->setQuery($query);

				try
				{
					$children = array_merge($children, (array) $db->loadColumn());
				}
				catch (\RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return false;
				}

				if (!$this->table->store())
				{
					$this->setError($this->table->getError());

					return false;
				}

				// Update children
				foreach ($children as $id)
				{
					$this->table->reset();
					$this->table->load($id);
					
					// Set the new access
					$this->table->access = $value;

					// Store the row
					if (!$this->table->store())
					{
						$this->setError($this->table->getError());

						return false;
					}

					Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_faqbookpro.topic', &$this->table, false, array()));
				}
			}
			else
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
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
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
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

	public function getChildrenTopics($items, $id)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('t.id')
			->from('#__minitek_faqbook_topics AS t')
			->where('t.parent_id = ' . $db->quote($id) . '');
		$db->setQuery($query);
		$children = $db->loadObjectList();

		if ($children)
		{
			foreach ($children as $child)
			{
				$items[] = $child;
				$items = $this->getChildrenTopics($items, $child->id);
			}
		}

		return $items;
	}
}
