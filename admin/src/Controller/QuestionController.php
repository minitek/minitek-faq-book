<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;

/**
 * The question controller
 *
 * @since  4.0.0
 */
class QuestionController extends FormController
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
		$user = Factory::getUser();
		$topicId = ArrayHelper::getValue($data, 'topicid', $this->input->getInt('filter_topic_id'), 'int');
		$allow = null;

		if ($topicId)
		{
			// If the topic has been passed in the data or URL check it.
			$allow = $user->authorise('core.create', 'com_faqbookpro.topic.' . $topicId);
		}

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
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
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();
		$userId = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_faqbookpro.question.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_faqbookpro.question.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   4.0.0
	 */
	public function batch($model = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Question', '', array());

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_faqbookpro&view=questions' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	public function dynamicQVisibility()
	{
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$jinput = Factory::getApplication()->input;
		$topicId = $jinput->get('topicid');

		if ($topicId > 1)
		{
			$model = $this->getModel();
			$data = $model->dynamicQVisibility($topicId);

			if (is_numeric($data))
			{
				echo $data;
				Factory::getApplication()->close();
			}
			else
			{
				jexit('error-qvisibility-1');
			}
		}
		else
		{
			jexit('error-qvisibility-2');
		}
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   12.2
	 */
	public function edit($key = null, $urlVar = null)
	{
		$app   = Factory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$cid   = $this->input->post->get('cid', array(), 'array');
		$context = "$this->option.edit.$this->context";
		$user = Factory::getUser();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->input->getInt($urlVar));
		$checkin = property_exists($table, 'checked_out');

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// See Private check
		if ($recordId)
		{
			$recordAuthor = $model->getQuestion($recordId)->created_by;
			$recordIsPrivate = $model->getQuestion($recordId)->private;

			if ($recordIsPrivate && !$user->authorise('core.private.see', 'com_faqbookpro.question.' . $recordId) && $user->id != $recordAuthor)
			{
				$this->setError(Text::_('COM_FAQBOOKPRO_ERROR_NOT_ALLOWED_TO_SEE_PRIVATE_QUESTIONS'));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					Route::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);

				return false;
			}
		}

		// Attempt to check-out the new record for editing and redirect.
		if ($checkin && !$model->checkout($recordId))
		{
			// Check-out failed, display a notice but allow the user to see the record.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}
		else
		{
			// Check-out succeeded, push the new record id into the session.
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return true;
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		$this->checkToken();

		$model = $this->getModel();
		$table = $model->getTable();
		$context = "$this->option.edit.$this->context";

		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $this->input->getInt($key);

		// Attempt to check-in the current record.
		if ($recordId && property_exists($table, 'checked_out') && $model->checkin($recordId) === false)
		{
			// Check-in failed, go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $key), false
				)
			);

			return false;
		}

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		Factory::getApplication()->setUserState($context . '.data', null);

		$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list
			. $this->getRedirectToListAppend();

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && \JUri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(Route::_($url, false));

		return true;
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
	}
}
