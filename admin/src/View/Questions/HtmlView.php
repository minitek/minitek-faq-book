<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\View\Questions;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;

/**
 * Questions view class for FAQ Book.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
	 *
	 * @since  4.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  mixed
	 *
	 * @since  4.0.0
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->authors = $this->get('Authors');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$model = $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Get topics for batch operations
		$this->topics = $model->getBatchTopics();

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

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
		$canDo = FAQBookProHelper::getActions('com_faqbookpro', 'topic', $this->state->get('filter.topic_id'));

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_FAQBOOKPRO_QUESTIONS_TITLE'), 'question');

		if ($canDo->get('core.create') || count(FAQBookProHelper::getAuthorisedTopics('core.create')) > 0 )
		{
			$toolbar->addNew('question.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('questions.publish')->listCheck(true);
			$childBar->unpublish('questions.unpublish')->listCheck(true);
			$childBar->archive('questions.archive')->listCheck(true);
			$childBar->checkin('questions.checkin')->listCheck(true);
		}

		// Add a batch button
		if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
		{
			$childBar->popupButton('batch')
				->text('JTOOLBAR_BATCH')
				->selector('collapseModal')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('questions.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}
		elseif ($canDo->get('core.edit.state'))
		{
			$childBar->trash('questions.trash')->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_faqbookpro');
		}
	}
}
