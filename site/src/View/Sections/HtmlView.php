<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
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

		// Get Sections
		$specific_sections = $this->params->get('fb_sections', '');
		$this->sections = $this->model->getSections($specific_sections);

		// Params
		$this->topic_col_class = '';
		if ($this->params->get('sections_forum_last_question', true)) {
			$this->topic_col_class = 'fb-col-8';
		}

		// Extra Section data
		foreach ($this->sections as $key => $section) {
			$section->q_count = $this->model->getSectionQuestionsCount($section->id);
			$section->topics = $sectionModel->getSectionTopics($section->id);

			foreach ($section->topics as $topic) {
				$topic->q_count = $this->model->getTopicQuestionsCount($topic->id);
				$topic->children = $this->model->getChildrenTopics($topic->id);
				$topic->lastpost = $this->model->getTopicLastQuestion($topic->id);
				if ($topic->lastpost) {
					$topic->lastpost->time_since = UtilitiesHelper::getTimeSince($topic->lastpost->created);

					// Author name
					if ($topic->lastpost->created_by) {
						// Check whether user exists
						$userExists = UtilitiesHelper::userExists($topic->lastpost->created_by);
						$topic->lastpost->created_by = $userExists ? $topic->lastpost->created_by : false;

						if ($this->params->get('questions_author_name', 'username') === 'name') {
							$topic->lastpost->author_name = Factory::getUser($topic->lastpost->created_by)->name;
						} else if ($this->params->get('questions_author_name', 'username') === 'username') {
							$topic->lastpost->author_name = Factory::getUser($topic->lastpost->created_by)->username;
						}
					}
				}

				if ($topic->children) {
					foreach ($topic->children as $child) {
						$child->q_count = $this->model->getTopicQuestionsCount($child->id);

						$childParams = json_decode($child->params, false);
						$child->icon_class = 'folder';
						if (isset($childParams->topic_icon_class)) {
							$child->icon_class = $childParams->topic_icon_class;
						}
					}
				}

				$topicParams = json_decode($topic->params, false);
				$topic->icon_class = 'folder';
				if (isset($topicParams->topic_icon_class)) {
					$topic->icon_class = $topicParams->topic_icon_class;
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
