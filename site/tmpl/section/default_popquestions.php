<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;

if ($this->popular_questions_header) {
	?><h2 class="fbContent_title">
		<?php echo Text::_('COM_FAQBOOKPRO_POPULAR_QUESTIONS'); ?>
	</h2><?php
}

if (count($this->popular_questions))
{
	?><div id="fb_popQuestions">
		<ul class="fbContent_popQuestions clearfix"><?php
			foreach ($this->popular_questions as $key => $question)
			{
				?><li>
					<a href="<?php echo Route::_(RouteHelper::getQuestionRoute($question->id, $question->topicid)); ?>">
						<?php echo $question->title; ?>
					</a><?php

					if ($this->popular_questions_description && $question->content)
					{
						?><p>
							<?php echo UtilitiesHelper::getWordLimit($question->content, $this->popular_questions_description_limit); ?>
						</p><?php
					}
				?></li><?php
			}
		?></ul>
	</div><?php
}
