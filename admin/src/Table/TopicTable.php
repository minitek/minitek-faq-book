<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Nested;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Rules;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Topic Table
 *
 * @since  4.0.0
 */
class TopicTable extends Nested
{
	/**
	 * Class constructor.
	 *
	 * @param   DatabaseDriver  $db  DatabaseDriver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__minitek_faqbook_topics', 'id', $db);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_faqbookpro.topic.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 *
	 * @link    https://docs.joomla.org/Special:MyLanguage/JTable/getAssetTitle
	 * @since   4.0.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   Table    $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   4.0.0
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;

		// This is a topic under a topic.
		if ($this->parent_id > 1) {
			// Build the query to get the asset id for the parent topic.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__minitek_faqbook_topics'))
				->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}
		// This is a topic that needs to parent with the parent section.
		elseif ($assetId === null) {
			// Build the query to get the asset id for the parent section.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__minitek_faqbook_sections'))
				->where($this->_db->quoteName('id') . ' = ' . $this->section_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId) {
			return $assetId;
		} else {
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    https://docs.joomla.org/Special:MyLanguage/JTable/check
	 * @since   4.0.0
	 */
	public function check()
	{
		// Check for a title.
		if (trim($this->title) == '') {
			$this->setError(Text::_('COM_FAQBOOKPRO_ERROR_TOPIC_MUST_HAVE_A_TITLE'));

			return false;
		}

		$this->alias = trim($this->alias);

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new Rules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Stores a record.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   4.0.0
	 */
	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		if ($this->id) {
			// Existing topic
			$this->modified_time = $date->toSql();
			$this->modified_user_id = $user->get('id');
		} else {
			// New topic
			$this->created_time = $date->toSql();
			$this->created_user_id = $user->get('id');
		}

		// Verify that the alias is unique within this section and parent topic
		$table = Table::getInstance('TopicTable', __NAMESPACE__ . '\\');

		if (
			$table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id, 'section_id' => $this->section_id))
			&& ($table->id != $this->id || $this->id == 0)
		) {
			$this->setError(Text::_('COM_FAQBOOKPRO_ERROR_TOPIC_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}
}
