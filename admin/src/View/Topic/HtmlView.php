<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\View\Topic;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View to edit a topic.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_faqbookpro', 'topic', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Add script options 
		Factory::getDocument()->addScriptOptions('com_faqbookpro', array(
			'token' => \JSession::getFormToken(),
			'site_path' => \JURI::base(),
		));

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user = Factory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		ToolbarHelper::title(
			\JText::_('COM_FAQBOOKPRO_TOPIC_TITLE_'.($checkedOut ? 'VIEW_TOPIC' : ($isNew ? 'ADD_TOPIC' : 'EDIT_TOPIC'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($isNew && count(FAQBookProHelper::getAuthorisedTopics('core.create')) > 0)
		{
			ToolbarHelper::apply('topic.apply');
			ToolbarHelper::save('topic.save');
			ToolbarHelper::save2new('topic.save2new');
			ToolbarHelper::cancel('topic.cancel');
		}
		// If not checked out, can save the item.
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId))
				{
					ToolbarHelper::apply('topic.apply');
					ToolbarHelper::save('topic.save');

					if ($canDo->get('core.create'))
					{
						ToolbarHelper::save2new('topic.save2new');
					}
				}
			}

			ToolbarHelper::cancel('topic.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
