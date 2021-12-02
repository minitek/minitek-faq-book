<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$published = (int) $this->state->get('filter.published');
?>

<div class="p-3">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
			</div>
		</div>
	</div>

	<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="form-group col-md-6">
				<div class="controls">
					<?php // Create the copy/move options.
					$options = array(
						HTMLHelper::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
						HTMLHelper::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
					); ?>

					<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_FAQBOOKPRO_BATCH_MENU_LABEL'); ?></label>
					<div id="batch-choose-action" class="control-group">
						<select name="batch[topic_id]" class="form-select" id="batch-topic-id">
							<option value=""><?php echo JText::_('JSELECT'); ?></option>
							<?php foreach ($this->topics as $topic) { ?>
								<option value="<?php echo $topic->value; ?>"><?php echo $topic->text; ?></option>
							<?php } ?>
						</select>
					</div>

					<div id="batch-move-copy" class="control-group radio">
						<?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
