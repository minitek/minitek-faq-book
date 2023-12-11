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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Form\Form;

/**
 * Model for a Section.
 *
 * @since  4.0.0
 */
class SectionModel extends AdminModel
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
		if (!empty($record->id)) {
			if ($record->state != -2) {
				return;
			}

			$user = Factory::getUser();

			return $user->authorise('core.delete', 'com_faqbookpro.section.' . (int) $record->id);
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

		// Check for existing article.
		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', 'com_faqbookpro.section.' . (int) $record->id);
		}
		// Default to component settings if section unknown.
		else {
			return parent::canEditState('com_faqbookpro');
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
	public function getTable($type = 'Section', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
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
		if ($item = parent::getItem($pk)) {
			// Convert the metadata field to an array.
			if (!empty($item->metadata)) {
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			// Convert the params field to an array.
			if (!empty($item->attribs)) {
				$registry = new Registry;
				$registry->loadString($item->attribs);
				$item->attribs = $registry->toArray();
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_faqbookpro.section', 'section', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$jinput = Factory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id')) {
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else {
			$id = $jinput->get('id', 0);
		}

		// Determine correct permissions to check.
		if ($this->getState('section.id')) {
			$id = $this->getState('section.id');
		}

		$user = Factory::getUser();

		// Check for existing section.
		// Modify the form based on Edit State access controls.
		if (
			$id != 0 && (!$user->authorise('core.edit.state', 'com_faqbookpro.section.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_faqbookpro'))
		) {
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
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
		$data = $app->getUserState('com_faqbookpro.edit.section.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry) {
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_faqbookpro.section', $data);

		return $data;
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
		$app = Factory::getApplication();

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy') {
			$origTable = clone $this->getTable();
			$origTable->load($app->input->getInt('id'));

			if ($data['title'] == $origTable->title) {
				list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			} else {
				if ($data['alias'] == $origTable->alias) {
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}

		if (parent::save($data)) {
			if (isset($data['featured'])) {
				$this->featured($this->getState($this->getName() . '.id'), $data['featured']);
			}

			return true;
		}

		return false;
	}

	protected function generateNewTitle($topic_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias))) {
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Allows preprocessing of the Form object.
	 *
	 * @param   Form    $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'faqbookpro')
	{
		// Set the access control rules field component value.
		$form->setFieldAttribute('rules', 'component', 'com_faqbookpro');
		$form->setFieldAttribute('rules', 'section', 'section');

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_faqbookpro');
	}
}
