<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
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
									<?php echo $this->form->getLabel('description'); ?>
									<?php echo $this->form->getInput('description'); ?>
								</fieldset>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="bg-white px-3">
							<fieldset>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('parent_id'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('parent_id'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('section_id'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('section_id'); ?>
									</div>
									<div class="controls" id="section_id_duplicate" style="display: none;">
										<?php echo $this->form->getInput('section_id_duplicate'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('published'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('published'); ?>
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
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('language'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('language'); ?>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'params', Text::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
				<div class="row">
					<div class="col-12">
						<?php echo $this->form->renderFieldset('basic'); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'questions', Text::_('COM_FAQBOOKPRO_FIELDSET_QUESTIONS', true)); ?>
				<div class="row">
					<div class="col-12">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('qvisibility'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('qvisibility'); ?>
							</div>
						</div>
						<?php echo $this->form->renderFieldset('questions'); ?>
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

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rules', Text::_('COM_FAQBOOKPRO_FIELDSET_RULES', true)); ?>
					<?php echo $this->form->getInput('rules'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
