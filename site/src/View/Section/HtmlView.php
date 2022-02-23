<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
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
		$this->sectionParams = json_decode($this->section->attribs, false);
		$this->topic_params = new \stdClass(); // Store topic params
		$this->topic_params->issection = true; // flag for section (used in javascript pagination)
		$this->questions_params = new \stdClass(); // Store question params

		// Get Navigation
		if (!$ajax) {
			// Get Top Navigation
			if (isset($this->sectionParams->topnav) && $this->sectionParams->topnav) {
				$this->topnavigation = true;
			} else {
				$this->topnavigation = false;
			}

			// Get Left Navigation
			$leftnav = $this->sectionParams->leftnav;
			$this->leftnavigation = false;
			if ($leftnav) {
				$this->leftnavigation = true;
			}
		}

		// Load Endpoint Topics / All Topics
		$this->loadAllTopics = 1;
		if (isset($this->sectionParams->load_all_topics)) {
			$this->loadAllTopics = $this->sectionParams->load_all_topics;
		}

		// Get page
		$this->page = $app->input->get('page', '1');

		// Ordering type
		$this->topic_params->ordering_type = 'static';

		// Get active tab
		$active_tab = 'recent';
		$this->tab = $app->input->get('tab', $active_tab);

		// Topics tree
		if (!isset($this->sectionParams->show_topics_tree)) {
			$this->sectionParams->show_topics_tree = false;
		}
		if ($this->sectionParams->show_topics_tree || $this->tab == 'topics') {
			$this->show_topics_tree = true;
			$this->first_level_topics = $this->model->getSectionTopics($this->sectionId);
			$this->browse_topics_header = $this->sectionParams->browse_topics_header;
			$this->browse_topics_levels = $this->sectionParams->browse_topics_levels;
			$this->browse_topics_cols = $this->sectionParams->browse_topics_cols;
			$this->browse_topics_icons = $this->sectionParams->browse_topics_icons;
			$this->browse_topics_images = isset($this->sectionParams->browse_topics_images) ? $this->sectionParams->browse_topics_images : false;
		}

		// Section title
		$this->show_section_title = false;
		if (isset($this->sectionParams->section_title) && $this->sectionParams->section_title) {
			$this->show_section_title = $this->sectionParams->section_title;
		}

		// Section description
		$this->show_section_description = false;
		if (isset($this->sectionParams->section_description) && $this->sectionParams->section_description) {
			$this->show_section_description = $this->sectionParams->section_description;
		}

		// Show questions
		$this->topic_params->show_section_questions = false;
		if (isset($this->sectionParams->show_section_questions)) {
			$this->topic_params->show_section_questions = $this->sectionParams->show_section_questions;
		}

		// Questions
		if ($this->topic_params->show_section_questions && $this->tab != 'topics') {
			$topicModel = new TopicModel;
			
			// Questions theme
			$this->questions_params->questions_theme = 'faq';

			// Questions opened
			$this->questions_params->questions_opened = false;
			if (isset($this->sectionParams->section_questions_opened)) {
				$this->questions_params->questions_opened = $this->sectionParams->section_questions_opened;
			}

			// Ordering
			if ($this->topic_params->ordering_type == 'dynamic') {
				$ordering = $this->tab;
				$ordering_dir = 'DESC';
			} else if ($this->topic_params->ordering_type == 'static') {
				$ordering = 'created';
				$ordering_dir = 'DESC';
				if (isset($this->sectionParams->questions_ordering)) {
					$ordering = $this->sectionParams->questions_ordering;
				}
				if (isset($this->sectionParams->questions_ordering_dir)) {
					$ordering_dir = $this->sectionParams->questions_ordering_dir;
				}
			}

			// Active topic id
			$this->topic_params->topicid = false;
			if (isset($this->sectionParams->topicid)) {
				$this->topic_params->topicid = $this->sectionParams->topicid;
			}

			// All questions
			if ($this->topic_params->show_section_questions == 'all') {
				$this->topic_params->show_topic_questions = true;
				$this->topic = new \stdClass();
				$this->topic->id = 0;
				$this->topic->questions = $this->model->getSectionQuestions($this->sectionId, $ordering, $ordering_dir, $this->page);
				$this->topic->issubtopic = false;
				$this->topic->qvisibility = 0;
			}
			// Active topic
			else if ($this->topic_params->show_section_questions == 'active' && $this->sectionParams->topicid) {
				// Group method
				$this->topic_params->merge_topics = false;

				// Is this an ajax request? (pagination)
				if ($this->topicId) {
					$this->sectionParams->topicid = $this->topicId;
				}

				// Get topic params
				$this->topic = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
				$this->topic->load($this->sectionParams->topicid);
				$this->topic->issubtopic = false;
				$topicParams = json_decode($this->topic->params, false);

				// Show custom content if user can't create in this topic
				$this->topic_params->create_denied_content = false;

				// Reset qvisibility
				if (!isset($this->topic->qvisibility)) {
					$this->topic->qvisibility = 0;
				}

				// Show topic title
				$this->topic_params->show_topic_title = false;
				if (isset($this->sectionParams->topic_title)) {
					$this->topic_params->show_topic_title = $this->sectionParams->topic_title;
				}

				// Show topic description
				$this->topic_params->show_topic_description = false;
				if (isset($this->sectionParams->topic_description)) {
					$this->topic_params->show_topic_description = $this->sectionParams->topic_description;
				}

				// Show topic image
				$this->topic_params->show_topic_image = false;
				if (isset($this->sectionParams->topic_image)) {
					$this->topic_params->show_topic_image = $this->sectionParams->topic_image;
					$this->topic->image = isset($topicParams->image) ? $topicParams->image : false;
					$this->topic->image_alt = isset($topicParams->image_alt) ? $topicParams->image_alt : false;
				}

				// Questions opened
				$this->questions_params->questions_opened = false;
				if (isset($topicParams->questions_opened) && $topicParams->questions_opened != '') {
					$this->questions_params->questions_opened = $topicParams->questions_opened;
				} else {
					if (!isset($this->sectionParams->section_questions_opened)) {
						$this->questions_params->questions_opened = $this->params->get('questions_opened', false); // old value
					} else {
						$this->questions_params->questions_opened = $this->sectionParams->section_questions_opened;
					}
				}

				// Topic questions
				if (!isset($topicParams->enable_questions)) {
					$this->topic_params->show_topic_questions = true;
				} else {
					$this->topic_params->show_topic_questions = $topicParams->enable_questions;
				}

				if ($this->topic_params->show_topic_questions) {
					$this->topic->questions = $topicModel->getTopicQuestions($this->sectionParams->topicid, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
				}

				// Subtopics
				$this->topic_params->show_subtopics = false;
				if (isset($this->sectionParams->show_subtopics)) {
					$this->topic_params->show_subtopics = $this->sectionParams->show_subtopics;
				}

				if (!$this->topic_params->merge_topics && $this->topic_params->show_subtopics) {
					// Show subtopic title
					$this->topic_params->show_subtopics_title = false;
					if (isset($this->sectionParams->subtopics_title)) {
						$this->topic_params->show_subtopics_title = $this->sectionParams->subtopics_title;
					}

					// Show subtopic description
					$this->topic_params->show_subtopics_description = false;
					if (isset($this->sectionParams->subtopics_description)) {
						$this->topic_params->show_subtopics_description = $this->sectionParams->subtopics_description;
					}

					// Show subtopic image
					$this->topic_params->show_subtopics_image = false;
					if (isset($this->sectionParams->subtopics_image)) {
						$this->topic_params->show_subtopics_image = $this->sectionParams->subtopics_image;
					}

					// Show subtopic questions
					$this->topic_params->show_subtopics_questions = false;
					if (isset($this->sectionParams->subtopics_questions)) {
						$this->topic_params->show_subtopics_questions = $this->sectionParams->subtopics_questions;
					}

					$this->topic->subtopics = $topicModel->getTopicChildren($this->sectionParams->topicid);

					foreach ($this->topic->subtopics as $key => $subtopic) {
						$subtopic->issubtopic = true;
						$subtopicParams = json_decode($subtopic->params, false);
						$subtopic->image = isset($subtopicParams->image) ? $subtopicParams->image : false;
						$subtopic->image_alt = isset($subtopicParams->image_alt) ? $subtopicParams->image_alt : false;

						// Reset qvisibility
						if (!isset($subtopic->qvisibility)) {
							$subtopic->qvisibility = 0;
						}

						// Questions opened
						$subtopic->questions_opened = $this->questions_params->questions_opened;
						if (isset($subtopicParams->questions_opened) && $subtopicParams->questions_opened != '') {
							$subtopic->questions_opened = $subtopicParams->questions_opened;
						}

						// Questions
						if ($this->topic_params->show_subtopics_questions) {
							if (!isset($subtopicParams->enable_questions)) {
								$subtopicParams->enable_questions = true;
							}

							if ($subtopicParams->enable_questions) {
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
			$this->questions_params->questions_link = true;
			if (isset($this->sectionParams->questions_link)) {
				$this->questions_params->questions_link = $this->sectionParams->questions_link;
			}

			// Show answers
			$this->questions_params->questions_answers = false;

			// Show state labels
			$this->questions_params->questions_state_labels = false;

			// Show image
			$this->questions_params->questions_image = false;
			if (isset($this->sectionParams->questions_image)) {
				$this->questions_params->questions_image = $this->sectionParams->questions_image;
			}

			// Show introtext
			$this->questions_params->questions_introtext = false;
			if (isset($this->sectionParams->questions_introtext)) {
				$this->questions_params->questions_introtext = $this->sectionParams->questions_introtext;
			}

			// Show views
			$this->questions_params->questions_views = false;
			if (isset($this->sectionParams->questions_views)) {
				$this->questions_params->questions_views = $this->sectionParams->questions_views;
			}

			// Show date
			$this->questions_params->questions_date = false;
			if (isset($this->sectionParams->questions_date)) {
				$this->questions_params->questions_date = $this->sectionParams->questions_date;
			}

			// Show author
			$this->questions_params->questions_author = false;
			if (isset($this->sectionParams->questions_author)) {
				$this->questions_params->questions_author = $this->sectionParams->questions_author;
			}

			// Show topic
			$this->questions_params->questions_topic = false;
			if (isset($this->sectionParams->questions_topic)) {
				$this->questions_params->questions_topic = $this->sectionParams->questions_topic;
			}

			// Prepare questions
			if (isset($this->topic->questions) && $this->topic->questions) {
				foreach ($this->topic->questions as $key => $question) {
					$this->prepareQuestion($question, $this->sectionParams);
				}
			}
		}

		// Popular topics
		if (!isset($this->sectionParams->show_popular_topics)) {
			$this->sectionParams->show_popular_topics = false;
		}

		if ($this->sectionParams->show_popular_topics) {
			$this->show_popular_topics = true;
			$this->popular_topics_header = $this->sectionParams->popular_topics_header;
			$this->popular_topics_count = $this->sectionParams->popular_topics_count;
			$this->popular_topics_title = $this->sectionParams->popular_topics_title;
			$this->popular_topics_desc = $this->sectionParams->popular_topics_description;
			$this->popular_topics_desc_limit = $this->sectionParams->popular_topics_description_limit;
			$this->popular_topics = $this->model->getPopularTopics($this->sectionId, $this->popular_topics_count);
		}

		// Popular questions
		if (!isset($this->sectionParams->show_popular_questions)) {
			$this->sectionParams->show_popular_questions = false;
		}

		if ($this->sectionParams->show_popular_questions) {
			$this->show_popular_questions = true;
			$this->popular_questions_header = $this->sectionParams->popular_questions_header;
			$this->popular_questions_count = $this->sectionParams->popular_questions_count;
			$this->popular_questions_description = $this->sectionParams->popular_questions_description;
			$this->popular_questions_description_limit = $this->sectionParams->popular_questions_description_limit;
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
				'leftnav' => $leftnav,
				'loadAllTopics' => $this->loadAllTopics,
				'active_tab' => $active_tab
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
		$topicParams = json_decode($item->params, false);
		
		$output = '';
		$subitems = $topicModel->getTopicChildren($item->id);

		$style = '';
		if ($level == 1) {
			$style = 'style="width:' . number_format(100 / $cols, 1) . '%;"';
		}

		$output .= '<li ' . $style . '>';
		$output .= '<a href="' . Route::_(RouteHelper::getTopicRoute($item->id)) . '">';
		if ($level < 2) {
			if ($images && $topic_image = $topicParams->image) {
				$topic_image_alt = $topicParams->image_alt;
				$output .= '<img src="' . Uri::root() . $topic_image . '" alt="' . $topic_image_alt . '">';
			} else if ($icons && isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class) {
				$output .= '<i class="fas fa-' . $topicParams->topic_icon_class . '"></i>&nbsp;&nbsp;';
			}
		}
		if ($level > 1) {
			if ($icons && isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class) {
				$output .= '<i class="fas fa-' . $topicParams->topic_icon_class . '"></i>&nbsp;&nbsp;';
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
		$introtext_limit = $this->sectionParams->questions_introtext_limit;
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

			if ($this->sectionParams->questions_author_name === 'name') {
				$question->author_name = Factory::getUser($question->created_by)->name;
			} else if ($this->sectionParams->questions_author_name === 'username') {
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
		if ($this->sectionParams->questions_topic == 2) {
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
