<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;

if ($this->popular_questions_header) { ?>
	<h2 class="fbContent_title">
		<?php echo Text::_('COM_FAQBOOKPRO_POPULAR_QUESTIONS'); ?>
	</h2>
<?php }

if (count($this->popular_questions)) { ?>
	<div id="fb_popQuestions">
		<ul class="fbContent_popItems clearfix">
			<?php foreach ($this->popular_questions as $key => $question) { ?>
				<li>
					<h5><?php echo $question->title; ?></h5>
					<?php if ($this->popular_questions_description && $question->content) { ?>
						<p>
							<?php echo UtilitiesHelper::getWordLimit($question->content, $this->popular_questions_description_limit); ?>
						</p>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php }
