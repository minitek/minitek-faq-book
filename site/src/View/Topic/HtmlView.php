<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\View\Topic;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\View\GenericDataException;

/**
 * HTML Topic View class for the FAQ Book component
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $item;

	function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$app = Factory::getApplication();
		$ajax = $app->input->get('task', false);
		$this->user = Factory::getUser();
		$this->topicId = $this->item->id;
		$this->model = $this->getModel();

		// Check for no 'access_view'
		if (!$this->item->access_view) {
			if ($this->user->id) {
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);
				return;
			} else {
				$return = base64_encode(Uri::getInstance());
				$login_url_with_return = Route::_('index.php?option=com_users&view=login&return=' . $return);
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
				$app->redirect($login_url_with_return, 403);
			}
		}

		// Get component params, topic params & section params
		$this->topic = $this->item;
		$this->topic->issubtopic = false;
		$this->sectionId = $this->topic->section_id;
		$this->section = Table::getInstance('SectionTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
		$this->section->load($this->sectionId);
		$this->sectionParams = new Registry($this->section->attribs);
		$this->params = UtilitiesHelper::getParams('com_faqbookpro');
		$topicParams = new Registry($this->topic->params);
		$this->topic_params = new \stdClass(); // Store topic params
		$this->questions_params = new \stdClass(); // Store question params

		// Add hit
		$this->model->addHit($this->topic->id);

		// Get Navigation
		if (!$ajax) {
			// Get Top Navigation
			$this->topnavigation = $this->sectionParams->get('topnav', 1);

			// Get Left Navigation
			$this->leftnavigation = $this->sectionParams->get('leftnav', 1);
		}

		// Load Endpoint Topics / All Topics
		$this->loadAllTopics = $this->sectionParams->get('load_all_topics', 1);

		// Get page
		$this->page = $app->input->get('page', '1');

		// Ordering type
		$this->topic_params->ordering_type = $this->sectionParams->get('questions_ordering_type', 'static');

		// Get active tab
		$active_tab = $this->topic_params->ordering_type == 'dynamic' ? $this->sectionParams->get('active_tab', 'recent') : 'recent';
		$this->tab = $app->input->get('tab', $active_tab);

		// Ordering
		if ($this->topic_params->ordering_type == 'dynamic') {
			$ordering = $this->tab;
			$ordering_dir = 'DESC';
		} else if ($this->topic_params->ordering_type == 'static') {
			$ordering = $this->sectionParams->get('questions_ordering', 'created');
			$ordering_dir = $this->sectionParams->get('questions_ordering_dir', 'DESC');
		}

		// Group method
		$this->topic_params->merge_topics = $this->sectionParams->get('questions_group_method', 0);

		// Reset qvisibility
		if (!isset($this->topic->qvisibility)) {
			$this->topic->qvisibility = 0;
		}

		// Show topic title
		$this->topic_params->show_topic_title = $this->sectionParams->get('topic_title', 1);

		// Show topic description
		$this->topic_params->show_topic_description =  $this->sectionParams->get('topic_description', 1);

		// Show topic image
		$this->topic_params->show_topic_image = $this->sectionParams->get('topic_image', 0);
		$this->topic->image = $topicParams->get('image', 0);
		$this->topic->image_alt = $topicParams->get('image_alt', 0);

		// Topic questions
		$this->topic_params->show_section_questions = 'active';
		$this->topic_params->topicid = $this->topicId;
		$this->topic_params->show_topic_questions = $topicParams->get('enable_questions', 1);

		if ($this->topic_params->show_topic_questions) {
			$this->topic->questions = $this->model->getTopicQuestions($this->topicId, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
		}

		// Questions theme
		$this->questions_params->questions_theme = $this->sectionParams->get('questions_theme', 'faq');

		// Questions opened
		if ($topicParams->get('questions_opened', '') != '') {
			$this->questions_params->questions_opened = $topicParams->get('questions_opened', '');
		} else {
			$this->questions_params->questions_opened = $this->sectionParams->get('section_questions_opened', 0);
		}

		// Subtopics
		$this->topic_params->show_subtopics = $this->sectionParams->get('show_subtopics', 1);

		if (!$this->topic_params->merge_topics && $this->topic_params->show_subtopics) {
			// Show subtopic title
			$this->topic_params->show_subtopics_title = $this->sectionParams->get('subtopics_title', 1);

			// Show subtopic description
			$this->topic_params->show_subtopics_description = $this->sectionParams->get('subtopics_description', 1);

			// Show subtopic image
			$this->topic_params->show_subtopics_image = $this->sectionParams->get('subtopics_image', 0);

			// Show subtopic questions
			$this->topic_params->show_subtopics_questions = $this->sectionParams->get('subtopics_questions', 1);
			$this->topic->subtopics = $this->model->getTopicChildren($this->topicId);

			foreach ($this->topic->subtopics as $key => $subtopic) {
				$subtopic->issubtopic = true;
				$subtopicParams = new Registry($subtopic->params);
				$subtopic->image = $subtopicParams->get('image', 0);
				$subtopic->image_alt = $subtopicParams->get('image_alt', 0);

				// Reset qvisibility
				if (!isset($subtopic->qvisibility)) {
					$subtopic->qvisibility = 0;
				}

				// Questions opened
				$subtopic->questions_opened = $this->questions_params->questions_opened;
				if ($subtopicParams->get('questions_opened', '') != '') {
					$subtopic->questions_opened = $subtopicParams->get('questions_opened', '');
				}

				// Questions
				if ($this->topic_params->show_subtopics_questions) {
					if ($subtopicParams->get('enable_questions', 1)) {
						$subtopic->questions = $this->model->getTopicQuestions($subtopic->id, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
					}

					if (isset($subtopic->questions) && $subtopic->questions) {
						foreach ($subtopic->questions as $subquestion) {
							$this->prepareQuestion($subquestion);
						}
					}
				}
			}
		}

		// Show questions link
		$this->questions_params->questions_link = false;

		// Show answers
		$this->questions_params->questions_answers = false;

		// Show state labels
		$this->questions_params->questions_state_labels = false;

		// Show image
		$this->questions_params->questions_image = $this->sectionParams->get('questions_image', 0);

		// Show introtext
		$this->questions_params->questions_introtext = $this->sectionParams->get('questions_introtext', 1);

		// Show views
		$this->questions_params->questions_views = $this->sectionParams->get('questions_views', 0);

		// Show date
		$this->questions_params->questions_date = $this->sectionParams->get('questions_date', 1);

		// Show author
		$this->questions_params->questions_author = $this->sectionParams->get('questions_author', 1);

		// Show topic
		$this->questions_params->questions_topic = $this->sectionParams->get('questions_topic', 2);

		// Prepare questions
		if (isset($this->topic->questions) && $this->topic->questions) {
			foreach ($this->topic->questions as $key => $question) {
				$this->prepareQuestion($question, $this->sectionParams);
			}
		}

		if (!$ajax) {
			$document = Factory::getDocument();

			// Add script options 
			$document->addScriptOptions('com_faqbookpro', array(
				'site_path' => Uri::root(),
				'page_view' => $app->input->get('view', false),
				'sectionId' => $this->sectionId,
				'topicId' => $this->topicId,
				'leftnav' => $this->sectionParams->get('leftnav', 1),
				'loadAllTopics' => $this->loadAllTopics,
				'active_tab' => $active_tab
			));

			// Set title
			$this->setDocumentTitle($this->topic->title);

			// Set metadata
			if ($this->topic->metadesc) {
				$document->setDescription($this->topic->metadesc);
			} elseif ($this->params->get('menu-meta_description')) {
				$document->setDescription($this->params->get('menu-meta_description'));
			}

			if ($this->topic->metakey) {
				$document->setMetadata('keywords', $this->topic->metakey);
			} elseif ($this->params->get('menu-meta_keywords')) {
				$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			if (!is_object($this->topic->metadata)) {
				$this->topic->metadata = new Registry($this->topic->metadata);
			}

			$mdata = $this->topic->metadata->toArray();

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
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Display the view
		parent::display($tpl);
	}

	public function prepareQuestion($question)
	{
		// Image
		$images = json_decode($question->images, false);
		$question->image = isset($images->image) ? $images->image : false;
		$question->image_alt = isset($images->image_alt) ? $images->image_alt : false;

		// Introtext
		$introtext_limit = $this->sectionParams->get('questions_introtext_limit', 20);
		$question->introtext = preg_replace('/\{.*\}/', '', $question->content);
		$question->introtext = preg_replace('/\[.*\]/', '', $question->introtext);
		if ($introtext_limit) {
			$question->introtext = UtilitiesHelper::getWordLimit($question->introtext, $introtext_limit);
		}

		// Date
		$question->time_since = UtilitiesHelper::getTimeSince($question->created);

		$question->qListItem_class = '';

		// Pinned
		if ($question->pinned && $this->tab == 'recent') {
			$question->qListItem_class .= ' qListItem_pinned';
		}

		// Check if user is author
		$question->isAuthor = false;
		if ($this->user->id && $question->created_by && ($question->created_by == $this->user->id)) {
			$question->isAuthor = true;
		}

		// Author name
		if ($question->created_by) {
			// Check whether user exists
			$userExists = UtilitiesHelper::userExists($question->created_by);
			$question->created_by = $userExists ? $question->created_by : false;

			if ($this->sectionParams->get('questions_author_name', 'username') === 'name') {
				$question->author_name = Factory::getUser($question->created_by)->name;
			} else if ($this->sectionParams->get('questions_author_name', 'username') === 'username') {
				$question->author_name = Factory::getUser($question->created_by)->username;
			}
		} else if ($question->created_by_name) {
			$question->author_name = $question->created_by_name;
		}

		// Scheduled
		$nowDate = Factory::getDate()->toSql();
		if ($question->publish_up > $nowDate) {
			$question->qListItem_class .= ' qListItem_scheduled';
		}

		// Trashed
		if ($question->state == -2) {
			$question->qListItem_class .= ' qListItem_trashed';
		}

		// Unpublished
		if ($question->state == 0) {
			$question->qListItem_class .= ' qListItem_unpublished';
		}

		// Archived
		if ($question->state == 2) {
			$question->qListItem_class .= ' qListItem_archived';
		}

		// Parent topics
		if ($this->sectionParams->get('questions_topic', 2) == 2) {
			$question->topics = $this->model->getTopicParentTopics($question->topicid, $topics = array());
			$parents = array();

			if (count($question->topics)) {
				$table = $this->model->getTable();

				foreach ($question->topics as $key => $topic) {
					$table->reset();
					$table->load($topic);
					$parent = array();
					$parent['id'] = $topic;
					$parent['title'] = $table->title;
					$parents[] = $parent;
				}
			}

			$question->parents = $parents;
		}
	}
}
