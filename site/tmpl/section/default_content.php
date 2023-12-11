<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Helper\ContentHelper;

$canDo = ContentHelper::getActions('com_faqbookpro');

// Topics tree
if ($this->tab != 'topics' && isset($this->show_topics_tree) && $this->show_topics_tree) {
	echo $this->loadTemplate('topics');
}

// Section title
if ($this->show_section_title) { ?>
	<h2 class="fbContent_title"><?php echo $this->section->title; ?></h2>
<?php }

// Section description
if ($this->show_section_description && $this->section->description) { ?>
	<div class="fbSection_description"><?php echo HTMLHelper::_('content.prepare', $this->section->description); ?></div>
<?php }

// Section questions
if ($this->topic_params->show_section_questions) {
	$data = array();
	$data['tab'] = $this->tab;
	$data['sectionId'] = $this->sectionId;
	$data['topic'] = $this->topic;
	$data['topic_params'] = $this->topic_params;
	$data['questions_params'] = $this->questions_params;
	$layout = new FileLayout('fb_topic');
	echo $layout->render($data);
}

// Popular topics
if ($this->tab != 'topics' && isset($this->show_popular_topics) && $this->show_popular_topics) {
	echo $this->loadTemplate('poptopics');
}

// Popular questions
if ($this->tab != 'topics' && isset($this->show_popular_questions) && $this->show_popular_questions) {
	echo $this->loadTemplate('popquestions');
}
