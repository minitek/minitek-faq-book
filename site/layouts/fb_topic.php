<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;

$app = Factory::getApplication();
$user = Factory::getUser();
$this->options = [];
$this->options['tab'] = $displayData['tab'];
$this->options['sectionId'] = $displayData['sectionId'];
$this->options['topic'] = $displayData['topic'];
$this->options['topic_params'] = $displayData['topic_params'];
$this->options['questions_params'] = $displayData['questions_params'];
$canDo = ContentHelper::getActions('com_faqbookpro', 'topic', $this->options['topic']->id);
?>
<div class="fbTopic" id="fbTopic_<?php echo $this->options['topic']->id; ?>" data-topic-title="<?php echo $this->options['topic']->title; ?>">
	<?php if ($this->options['topic_params']->show_section_questions == 'active' && $this->options['topic_params']->topicid) {
		// Topic Title
		if ($this->options['topic_params']->show_topic_title || $can_ask_question) { ?>
			<h2 class="fbContent_title clearfix">
				<?php if ($this->options['topic_params']->show_topic_title) { ?>
					<a id="topicPermalink_<?php echo $this->options['topic']->id; ?>" class="fbTopic_permalink" href="<?php echo Route::_(RouteHelper::getTopicRoute($this->options['topic']->id, false, false, $this->options['topic']->language)); ?>">
						<?php echo $this->options['topic']->title; ?>
					</a>
				<?php } ?>
			</h2>
		<?php }

		// Topic Description
		if ($this->options['topic_params']->show_topic_description && $this->options['topic']->description) { ?>
			<div class="fbTopic_description"><?php echo HTMLHelper::_('content.prepare', $this->options['topic']->description); ?></div>
		<?php }

		// Topic Image
		if ($this->options['topic_params']->show_topic_image && $this->options['topic']->image) { ?>
			<div class="fbTopic_image">
				<img src="<?php echo Uri::root() . $this->options['topic']->image; ?>" alt="<?php echo $this->options['topic']->image_alt; ?>">
			</div>
		<?php }
	}

	// Loader
	if (isset($this->options['topic']->questions)) {
		$params = UtilitiesHelper::getParams('com_faqbookpro');
		$pagination_limit = $params->get('pagination_limit', 20);
		$questions_found = $this->options['topic']->questions ? count($this->options['topic']->questions) : 0;
		if ($questions_found > $pagination_limit) {
			array_pop($this->options['topic']->questions); // remove extra item from end of array
		} ?>
		<div class="topic_loader">
			<?php $list_placeholder = new FileLayout('fb_list_placeholder');
			echo $list_placeholder->render(); ?>
		</div>
		<?php

		// Questions
		if ($this->options['topic_params']->show_topic_questions && isset($this->options['topic']->questions) && $this->options['topic']->questions) { ?>
			<div class="fbTopic_content">
				<div class="fbContent_qList">
					<?php $questionsData = array();
					$questionsData['sectionId'] = $this->options['sectionId'];
					$questionsData['topic'] = $this->options['topic'];
					$questionsData['questions_params'] = $this->options['questions_params'];
					if ($questions_found > $pagination_limit) {
						$questionsData['final_page'] = false;
					} else {
						$questionsData['final_page'] = true;
					}
					$layout = new FileLayout('fb_questions_' . $this->options['questions_params']->questions_theme);
					echo $layout->render($questionsData); ?>
				</div>
			</div>
			<?php

			if ($questions_found > $pagination_limit) {
				// Section & topic
				if (isset($this->options['topic_params']->issection) || $this->options['topic']->id) {
					$paging_class = isset($this->options['topic_params']->issection) ? 'fbContent_section_paging' : '';
					$class = 'fbContent_topic_paging ' . $paging_class;
					$data_attributes = 'data-page="2" data-tab="' . $this->options['tab'] . '" data-topic="' . $this->options['topic']->id . '"';
				}
				// Profile
				else {
					$class = 'fbContent_myquestions_paging';
					$data_attributes = 'data-assigned="' .
						$this->options['topic_params']->isprofile[2] . '" data-page="2" data-tab="' .
						$this->options['tab'] . '" data-user="' . $this->options['topic_params']->isprofile[1] . '"';
				} ?>
				<div class="fbContent_paging" id="fbPaging_<?php echo $this->options['topic']->id; ?>">
					<a href="#" class="fbContent_paging_button btn <?php echo $class; ?>" <?php echo $data_attributes; ?>>
						<span class="fbContent_paging_text"><?php echo Text::_('COM_FAQBOOKPRO_LOAD_MORE'); ?></span>
						<span class="fbContent_noresults"><?php echo Text::_('COM_FAQBOOKPRO_NO_MORE_ITEMS'); ?></span>
						<div class="fbContent_paging_loader">
							<div class="spinner-border spinner-border-sm" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</a>
				</div>
			<?php }
		} else if ($this->options['topic_params']->show_topic_questions) { ?>
			<div class="fbTopic_content">
				<div class="fbContent_empty">
					<p><?php echo Text::_('COM_FAQBOOKPRO_NO_QUESTIONS_MESSAGE'); ?></p>
				</div>
			</div>
	<?php
		}
	} ?>
</div>
<?php

// Subtopics
if (
	$app->input->get('filter', '', 'STRING') != 'tab'
	&& $this->options['topic_params']->show_section_questions == 'active' && !$this->options['topic_params']->merge_topics && $this->options['topic_params']->show_subtopics && isset($this->options['topic']->subtopics) && $this->options['topic']->subtopics
) {
	foreach ($this->options['topic']->subtopics as $subtopic) {
		$data = array();
		$data['tab'] = $this->options['tab'];
		$data['sectionId'] = $this->options['sectionId'];
		$data['topic'] = $subtopic;
		$data['topic_params'] = $this->options['topic_params'];
		$data['topic_params']->show_topic_title = $this->options['topic_params']->show_subtopics_title;
		$data['topic_params']->show_topic_description = $this->options['topic_params']->show_subtopics_description;
		$data['topic_params']->show_topic_image = $this->options['topic_params']->show_subtopics_image;
		$data['topic_params']->show_topic_questions = $this->options['topic_params']->show_subtopics_questions;
		$data['questions_params'] = $this->options['questions_params'];
		$data['questions_params']->questions_opened = $subtopic->questions_opened;
		$layout = new FileLayout('fb_topic'); // recursive
		echo $layout->render($data);
	}
}
