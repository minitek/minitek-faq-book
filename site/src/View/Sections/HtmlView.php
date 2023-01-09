<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\View\Sections;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use Joomla\Component\FAQBookPro\Site\Model\SectionModel;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\MVC\View\GenericDataException;

/**
 * HTML Sections View class for the FAQ Book component
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	function display($tpl = null)
	{
		$document = Factory::getDocument();
		$app = Factory::getApplication();
		$this->model = $this->getModel();
		$sectionModel = new SectionModel;
		$activeMenu = $app->getMenu()->getActive();
		$this->home_title = $activeMenu->title;
		$this->home_itemid = $activeMenu->id;

		// Get Params & Attribs
		$this->params = UtilitiesHelper::getParams('com_faqbookpro');
		$layout = $this->params->get('sections_layout', 'columns');

		// Get Sections
		$specific_sections = $this->params->get('fb_sections', '');
		$this->sections = $this->model->getSections($specific_sections);

		// Params
		$this->topic_col_class = '';
		if ($this->params->get('sections_forum_last_question', true)) {
			$this->topic_col_class = 'fb-col-8';
		}

		// Extra Section data
		foreach ($this->sections as $key => $section) 
		{
			if (($layout == 'columns' && $this->params->get('sections_section_count', 1))
				|| ($layout == 'forum' && $this->params->get('sections_forum_section_count', 1))
			)
				$section->q_count = $this->model->getSectionQuestionsCount($section->id);

			if (($layout == 'columns' && $this->params->get('sections_topics', 1))
			|| ($layout == 'forum' && $this->params->get('sections_forum_topics', 1))
			)
			{
				$section->topics = $sectionModel->getSectionTopics($section->id);

				foreach ($section->topics as $topic) 
				{
					if (($layout == 'columns' && $this->params->get('sections_topics_count', 1))
						|| ($layout == 'forum' && $this->params->get('sections_forum_topics_count', 1))
					)
						$topic->q_count = $this->model->getTopicQuestionsCount($topic->id);
				
					if ($layout == 'forum' && $this->params->get('sections_forum_topics_children', 1))
					{
						$topic->children = $this->model->getChildrenTopics($topic);

						if ($topic->children) 
						{
							foreach ($topic->children as $child) 
							{
								$child->q_count = $this->model->getTopicQuestionsCount($child->id);
								$childParams = new Registry($child->params);
								$child->icon_class = $childParams->get('topic_icon_class', '');
							}
						}
					}

					if ($layout == 'forum' && $this->params->get('sections_forum_last_question', 1))
					{
						$topic->lastpost = $this->model->getTopicLastQuestion($topic->id);

						if ($topic->lastpost) 
						{
							$topic->lastpost->time_since = UtilitiesHelper::getTimeSince($topic->lastpost->created);

							// Author name
							if ($topic->lastpost->created_by) 
							{
								// Check whether user exists
								$userExists = UtilitiesHelper::userExists($topic->lastpost->created_by);
								$topic->lastpost->created_by = $userExists ? $topic->lastpost->created_by : false;

								if ($this->params->get('questions_author_name', 'username') === 'name')
									$topic->lastpost->author_name = Factory::getUser($topic->lastpost->created_by)->name;
								else if ($this->params->get('questions_author_name', 'username') === 'username')
									$topic->lastpost->author_name = Factory::getUser($topic->lastpost->created_by)->username;
							}
						}
					}

					$topicParams = new Registry($topic->params);
					$topic->icon_class = $topicParams->get('topic_icon_class', '');
				}
			}
		}

		// Set metadata
		$this->setDocumentTitle($this->params->get('page_title'));

		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}

		if (!is_object($this->params->get('metadata'))) {
			$metadata = new Registry($this->params->get('metadata'));
		}

		$mdata = $metadata->toArray();

		foreach ($mdata as $k => $v) {
			if ($v) {
				$document->setMetadata($k, $v);
			}
		}

		// Menu page display options
		if ($this->params->get('page_heading')) {
			$this->params->set('page_title', $this->params->get('page_heading'));
		}

		$this->params->set('show_page_title', $this->params->get('show_page_heading'));

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Display the view
		parent::display($tpl);
	}
}
