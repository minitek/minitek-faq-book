<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$input = Factory::getApplication()->input;
$params = $this->state->get('params');
?>

<form action="<?php echo Route::_('index.php?option=com_faqbookpro&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_FAQBOOKPRO_FIELDSET_GENERAL', true)); ?>
				<div class="row">
					<div class="col-lg-9">
						<div>
							<div class="card-body">
								<fieldset class="adminform">
									<?php echo $this->form->getInput('content'); ?>
								</fieldset>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="bg-white px-3">
							<fieldset>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('topicid'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('topicid'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('state'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('state'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('pinned'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('pinned'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('access'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('access'); ?>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
				<div class="row">
					<div class="col-12">
						<?php echo $this->form->renderFieldset('images'); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_FAQBOOKPRO_FIELDSET_PUBLISHING', true)); ?>
				<div class="row">
					<div class="col-12 col-lg-6">
						<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
					<div class="col-12 col-lg-6">
						<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="jform[language]" value="*" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
