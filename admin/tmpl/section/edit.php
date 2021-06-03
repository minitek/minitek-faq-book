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

$input = JFactory::getApplication()->input;
?>

<form action="<?php echo Route::_('index.php?option=com_faqbookpro&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', JText::_('COM_FAQBOOKPRO_FIELDSET_GENERAL', true)); ?>
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
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'navigation', JText::_('COM_FAQBOOKPRO_FIELDSET_NAVIGATION', true)); ?>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('navigation'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', JText::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('options'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'topic', JText::_('COM_FAQBOOKPRO_FIELDSET_TOPIC', true)); ?>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('section_topics'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('separate_topic'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('topic_ordering'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'questions', JText::_('COM_FAQBOOKPRO_FIELDSET_QUESTIONS', true)); ?>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('section_questions'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', JText::_('COM_FAQBOOKPRO_FIELDSET_PUBLISHING', true)); ?>
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
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rules', JText::_('COM_FAQBOOKPRO_FIELDSET_RULES', true)); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>