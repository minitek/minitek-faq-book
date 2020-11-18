<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;

/**
 * The topic controller
 *
 * @since  4.0.0
 */
class TopicController extends FormController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CmsApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowAdd($data = array())
	{
		$user = \JFactory::getUser();

		return ($user->authorise('core.create', 'com_faqbookpro') || count(FAQBookProHelper::getAuthorisedTopics('core.create')));
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = \JFactory::getUser();
		$userId = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_faqbookpro'))
		{
			return true;
		}

		// Check specific edit permission.
		if ($user->authorise('core.edit', 'com_faqbookpro.topic.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_faqbookpro.topic.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_user_id']) ? $data['created_user_id'] : 0;

			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_user_id;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry;
			$registry->loadArray($item->params);
			$item->params = (string) $registry;
		}

		if (isset($item->metadata) && is_array($item->metadata))
		{
			$registry = new Registry;
			$registry->loadArray($item->metadata);
			$item->metadata = (string) $registry;
		}

		// Change parent section recursively to all children topics
		$id = $item->get('id');
		$section_id = $item->get('section_id');

		$children = $this->getChildren($items = array(), $id);

		foreach ($children as $child)
		{
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('section_id') . ' = ' . $db->quote($section_id)
			);
			$conditions = array(
				$db->quoteName('id') . ' = ' . $db->quote($child->id),
			);
			$query->update($db->quoteName('#__minitek_faqbook_topics'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
		}

		return;
	}

	public function dynamicSection()
	{
		if (!Session::checkToken('get'))
		{
			echo new \JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$jinput = \JFactory::getApplication()->input;
			$topicId = $jinput->get('topicid');

			if ($topicId == 1)
			{
				jexit('root');
			}
			else
			{
				$model = $this->getModel();
				$data = $model->dynamicSection($topicId);
			}
		}
	}

	public function getChildren($items, $id)
	{
		$db = \JFactory::getDBO();
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
				$items = $this->getChildren($items, $child->id);
			}
		}

		return $items;
	}
}
