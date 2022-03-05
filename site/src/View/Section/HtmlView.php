<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\View\Section;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\Component\FAQBookPro\Site\Model\TopicModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\CMS\MVC\View\GenericDataException;

/**
 * HTML Section View class for the FAQ Book component
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
		$this->sectionId = $this->item->id;
		$this->topicId = $app->input->get('topicId', false);
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

		// Check for section menu item (mandatory)
		if (!$this->model->getSectionMenuItem($this->sectionId)) {
			$app->enqueueMessage(Text::_('COM_FAQBOOKPRO_ERROR_SECTION_MENU_ITEM'), 'error');
			return false;
		}

		// Get component params & section params
		$this->section = $this->item;
		$this->params = UtilitiesHelper::getParams('com_faqbookpro');
		$this->sectionParams = new Registry($this->section->attribs);
		$this->topic_params = new \stdClass(); // Store topic params
		$this->topic_params->issection = true; // flag for section (used in javascript pagination)
		$this->questions_params = new \stdClass(); // Store question params

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
		$this->tab = $app->input->get('tab', $this->sectionParams->get('active_tab', 'recent'));

		// Topics tree
		if ($this->sectionParams->get('show_topics_tree', 0) || $this->tab == 'topics') {
			$this->show_topics_tree = true;
			$this->first_level_topics = $this->model->getSectionTopics($this->sectionId);
			$this->browse_topics_header = $this->sectionParams->get('browse_topics_header', 1);
			$this->browse_topics_levels = $this->sectionParams->get('browse_topics_levels', 4);
			$this->browse_topics_cols = $this->sectionParams->get('browse_topics_cols', 3);
			$this->browse_topics_icons = $this->sectionParams->get('browse_topics_icons', 1);
			$this->browse_topics_images = $this->sectionParams->get('browse_topics_images', 0);
		}

		// Section title
		$this->show_section_title = $this->sectionParams->get('section_title', 0);

		// Section description
		$this->show_section_description = $this->sectionParams->get('section_description', 0);

		// Show questions
		$this->topic_params->show_section_questions = $this->sectionParams->get('show_section_questions', 0);

		// Questions
		if ($this->topic_params->show_section_questions && $this->tab != 'topics') {
			$topicModel = new TopicModel;
			
			// Questions theme
			$this->questions_params->questions_theme = $this->sectionParams->get('questions_theme', 'faq');

			// Questions opened
			$this->questions_params->questions_opened = $this->sectionParams->get('section_questions_opened', 0);

			// Ordering
			if ($this->topic_params->ordering_type == 'dynamic') {
				$ordering = $this->tab;
				$ordering_dir = 'DESC';
			} else if ($this->topic_params->ordering_type == 'static') {
				$ordering = $this->sectionParams->get('questions_ordering', 'created');
				$ordering_dir = $this->sectionParams->get('questions_ordering_dir', 'DESC');
			}

			// Active topic id
			$this->topic_params->topicid = $this->sectionParams->get('topicid', 0);

			// All questions
			if ($this->topic_params->show_section_questions == 'all') {
				$this->topic_params->show_topic_questions = true;
				$this->topic = new \stdClass();
				$this->topic->id = 0;
				$this->topic->questions = $this->model->getSectionQuestions($this->sectionId, $ordering, $ordering_dir, $this->page);
				$this->topic->issubtopic = false;
				$this->topic->qvisibility = 0;
				$this->topic->language = $this->section->language;
			}
			// Active topic
			else if ($this->topic_params->show_section_questions == 'active' && $this->sectionParams->get('topicid', 0)) {
				// Group method
				$this->topic_params->merge_topics = $this->sectionParams->get('questions_group_method', 0);

				// Is this an ajax request? (pagination)
				if ($this->topicId) {
					$this->sectionParams->set('topicid', $this->topicId);
				}

				// Get topic params
				$this->topic = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
				$this->topic->load($this->sectionParams->get('topicid', 0));
				$this->topic->issubtopic = false;
				$topicParams = new Registry($this->topic->params);

				// Show custom content if user can't create in this topic
				$this->topic_params->create_denied_content = $topicParams->get('create_denied_content', 0);

				// Reset qvisibility
				if (!isset($this->topic->qvisibility)) {
					$this->topic->qvisibility = 0;
				}

				// Show topic title
				$this->topic_params->show_topic_title = $this->sectionParams->get('topic_title', 0);

				// Show topic description
				$this->topic_params->show_topic_description = $this->sectionParams->get('topic_description', 0);

				// Show topic image
				$this->topic_params->show_topic_image = $this->sectionParams->get('topic_image', 0);
				$this->topic->image = $topicParams->get('image', 0);
				$this->topic->image_alt = $topicParams->get('image_alt', 0);

				// Questions opened
				if ($topicParams->get('questions_opened', '') != '') {
					$this->questions_params->questions_opened = $topicParams->get('questions_opened', '');
				} else {
					$this->questions_params->questions_opened = $this->sectionParams->get('section_questions_opened', 0);
				}

				// Topic questions
				$this->topic_params->show_topic_questions = $topicParams->get('enable_questions', 1);

				if ($this->topic_params->show_topic_questions) {
					$this->topic->questions = $topicModel->getTopicQuestions(
						$this->sectionParams->get('topicid', 0), $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics
					);
				}

				// Subtopics
				$this->topic_params->show_subtopics = $this->sectionParams->get('show_subtopics', 0);

				if (!$this->topic_params->merge_topics && $this->topic_params->show_subtopics) {
					// Show subtopic title
					$this->topic_params->show_subtopics_title = $this->sectionParams->get('subtopics_title', 0);

					// Show subtopic description
					$this->topic_params->show_subtopics_description = $this->sectionParams->get('subtopics_description', 0);

					// Show subtopic image
					$this->topic_params->show_subtopics_image = $this->sectionParams->get('subtopics_image', 0);

					// Show subtopic questions
					$this->topic_params->show_subtopics_questions = $this->sectionParams->get('subtopics_questions', 0);
					$this->topic->subtopics = $topicModel->getTopicChildren($this->sectionParams->get('topicid', 0));

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
								$subtopic->questions = $topicModel->getTopicQuestions($subtopic->id, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
							}

							if (isset($subtopic->questions) && $subtopic->questions) {
								foreach ($subtopic->questions as $subquestion) {
									$this->prepareQuestion($subquestion);
								}
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
		}

		// Popular topics
		if ($this->sectionParams->get('show_popular_topics', 0)) {
			$this->show_popular_topics = true;
			$this->popular_topics_header = $this->sectionParams->get('popular_topics_header', 1);
			$this->popular_topics_count = $this->sectionParams->get('popular_topics_count', 5);
			$this->popular_topics_title = $this->sectionParams->get('popular_topics_title', 1);
			$this->popular_topics_desc = $this->sectionParams->get('popular_topics_description', 1);
			$this->popular_topics_desc_limit = $this->sectionParams->get('popular_topics_description_limit', 15);
			$this->popular_topics = $this->model->getPopularTopics($this->sectionId, $this->popular_topics_count);
		}

		// Popular questions
		if ($this->sectionParams->get('show_popular_questions', 0)) {
			$this->show_popular_questions = true;
			$this->popular_questions_header = $this->sectionParams->get('popular_questions_header', 1);
			$this->popular_questions_count = $this->sectionParams->get('popular_questions_count', 5);
			$this->popular_questions_description = $this->sectionParams->get('popular_questions_description', 1);
			$this->popular_questions_description_limit = $this->sectionParams->get('popular_questions_description_limit', 15);
			$this->popular_questions = $this->model->getPopularQuestions($this->sectionId, $this->popular_questions_count);
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
				'active_tab' => $this->sectionParams->get('active_tab', 'recent'),
			));

			// Set title
			$this->setDocumentTitle($this->section->title);

			// Set metadata
			if ($this->section->metadesc) {
				$document->setDescription($this->section->metadesc);
			} elseif ($this->params->get('menu-meta_description')) {
				$document->setDescription($this->params->get('menu-meta_description'));
			}

			if ($this->section->metakey) {
				$document->setMetadata('keywords', $this->section->metakey);
			} elseif ($this->params->get('menu-meta_keywords')) {
				$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			if (!is_object($this->section->metadata)) {
				$this->section->metadata = new Registry($this->section->metadata);
			}

			$mdata = $this->section->metadata->toArray();

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

	public static function getTopicsIndex($item, $maxLevels, $cols, $level, $icons, $images)
	{
		$topicModel = new TopicModel;
		$topicParams = new Registry($item->params);
		
		$output = '';
		$subitems = $topicModel->getTopicChildren($item->id);

		$style = '';
		if ($level == 1) {
			$style = 'style="width:' . number_format(100 / $cols, 1) . '%;"';
		}

		$output .= '<li ' . $style . '>';
		$output .= '<a href="' . Route::_(RouteHelper::getTopicRoute($item->id, false, false, $item->language)) . '">';
		if ($level < 2) {
			if ($images && $topic_image = $topicParams->get('image', 0)) {
				$topic_image_alt = $topicParams->get('image_alt', 0);
				$output .= '<img src="' . Uri::root() . $topic_image . '" alt="' . $topic_image_alt . '">';
			} else if ($icons && $topicParams->get('topic_icon_class', '')) {
				$output .= '<i class="' . $topicParams->get('topic_icon_class', '') . '"></i>&nbsp;&nbsp;';
			}
		}
		if ($level > 1) {
			if ($icons && $topicParams->get('topic_icon_class', '')) {
				$output .= '<i class="' . $topicParams->get('topic_icon_class', '') . '"></i>&nbsp;&nbsp;';
			} else {
				// $output .= '&#45; ';
			}
		}
		$output .= $item->title;
		$output .= '</a>';

		if (count($subitems) && $level < $maxLevels) {
			$output .= '<ul class="level' . $level . '">';
			foreach ($subitems as $subitem) {
				$output .= self::getTopicsIndex($subitem, $maxLevels, $cols, $level + 1, $icons, $images);
			}
			$output .= '</ul>';
		}
		$output .= '</li>';

		return $output;
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
			$topicModel = new TopicModel;
			$question->topics = $topicModel->getTopicParentTopics($question->topicid, $topics = array());
			$parents = array();

			if (count($question->topics)) {
				$table = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');

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
