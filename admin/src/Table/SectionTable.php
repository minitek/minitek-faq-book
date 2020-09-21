<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Rules;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Table\Table;

/**
 * Section Table
 *
 * @since  4.0.0
 */
class SectionTable extends Table
{
	/**
	 * Class constructor.
	 *
	 * @param   \JDatabaseDriver  $db  \JDatabaseDriver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = 'com_faqbookpro.section';

		parent::__construct('#__minitek_faqbook_sections', 'id', $db);

		$this->setColumnAlias('published', 'state');
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

		return 'com_faqbookpro.section.' . (int) $this->$k;
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

		if ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('id'))
				->from($this->_db->quoteName('#__assets'))
				->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_faqbookpro'));

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
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
		if (trim($this->title) == '')
		{
			$this->setError(Text::_('COM_FAQBOOKPRO_WARNING_PROVIDE_VALID_TITLE'));
			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->description)) == '')
		{
			$this->description = '';
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey))
		{
			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", "<", ">");

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, "", $this->metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);

			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(", ", $clean_keys);
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
		// Bind the attribs
		if (isset($array['attribs']) && is_array($array['attribs']))
		{
			$registry = new Registry;
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string) $registry;
		}

		// Bind the metadata
		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
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

		if (!$this->id)
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}

			if (empty($this->created_user_id))
			{
				$this->created_user_id = $user->get('id');
			}
		}

		// Verify that the alias is unique
		$table = Table::getInstance('SectionTable', __NAMESPACE__ . '\\');

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(Text::_('COM_FAQBOOKPRO_ERROR_SECTION_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}
}
