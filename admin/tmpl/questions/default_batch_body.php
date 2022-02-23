<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Component\FAQBookPro\Administrator\Helper\BatchTopicIdField;

$published = (int) $this->state->get('filter.published');
$topics_field = new BatchTopicIdField;
$select = $topics_field->getInput();

?><div class="p-3">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls"><?php 
				echo LayoutHelper::render('joomla.html.batch.access', []); 
			?></div>
		</div>
	</div>

	<div class="row"><?php 
		if ($published >= 0) 
		{ 
			?><div class="form-group col-md-6">
				<div class="controls"><?php 
					// Create the copy/move options.
					$options = array(
						HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
						HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
					); 
					
					?><label id="batch-choose-action-lbl" for="batch-choose-action"><?php 
						echo Text::_('COM_FAQBOOKPRO_BATCH_QUESTIONS_MENU_LABEL'); 
					?></label>
					<div id="batch-choose-action" class="control-group"><?php 
						echo $select;
					?></div>

					<div id="batch-move-copy" class="control-group radio">
						<fieldset id="batch-copy-move-id">
							<legend><?php echo Text::_('COM_FAQBOOKPRO_BATCH_MOVE'); ?></legend><?php
							echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); 
						?></fieldset>
					</div>
				</div>
			</div><?php 
		} 
	?></div>
</div>