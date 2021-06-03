<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.multiselect');

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$ordering = ($listOrder == 'a.lft');
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');

if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = 'index.php?option=com_faqbookpro&task=topics.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>

<form action="<?php echo Route::_('index.php?option=com_faqbookpro&view=topics'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>

					<table class="table" id="categoryList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_FAQBOOKPRO_TOPICS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
									</th>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="min-width:100px">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="min-width:100px">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FAQBOOKPRO_HEADING_SECTION_TITLE', 'section_title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-3 d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FAQBOOKPRO_HEADING_QUESTIONS_COUNT', 'questions_count', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
								</th>
								<th scope="col" class="w-3 d-none d-lg-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false" <?php endif; ?>>
							<?php foreach ($this->items as $i => $item) :
								$item->max_ordering = 0;
								$canEdit    = $user->authorise('core.edit',       'com_faqbookpro.topic.' . $item->id);
								$canCheckin = $user->authorise('core.admin',      'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canEditOwn = $user->authorise('core.edit.own',   'com_faqbookpro.topic.' . $item->id) && $item->created_user_id == $userId;
								$canChange  = $user->authorise('core.edit.state', 'com_faqbookpro.topic.' . $item->id) && $canCheckin;

								// Get the parents of item for sorting
								if ($item->level > 1) {
									$parentsStr = "";
									$_currentParentId = $item->parent_id;
									$parentsStr = " " . $_currentParentId;
									for ($i2 = 0; $i2 < $item->level; $i2++) {
										foreach ($this->ordering as $k => $v) {
											$v = implode("-", $v);
											$v = "-" . $v . "-";
											if (strpos($v, "-" . $_currentParentId . "-") !== false) {
												$parentsStr .= " " . $k;
												$_currentParentId = $k;
												break;
											}
										}
									}
								} else {
									$parentsStr = "";
								}
							?>
								<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">

									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>

									<td class="text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';
										if (!$canChange) {
											$iconClass = ' inactive';
										} elseif (!$saveOrder) {
											$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" class="hidden" name="order[]" size="5" value="<?php echo $item->lft; ?>">
										<?php endif; ?>
									</td>

									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'topics.', $canChange, 'cb'); ?>
									</td>

									<th scope="row" class="has-context">
										<div class="break-word">
											<?php echo str_repeat('<span class="gi">&mdash;</span>', $item->level - 1) ?>
											<?php if ($item->checked_out) : ?>
												<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'topics.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit || $canEditOwn) : ?>
												<a href="<?php echo Route::_('index.php?option=com_faqbookpro&task=topic.edit&id=' . $item->id); ?>">
													<?php echo $this->escape($item->title); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->title); ?>
											<?php endif; ?>
										</div>
										</td>

									<th scope="row" class="has-context">
										<div class="break-word">
											<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_faqbookpro&task=section.edit&id=' . $item->section_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
												<?php echo $this->escape($item->section_title); ?>
											</a>
											<div class="small break-word">
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->section_alias)); ?>
											</div>
										</div>
									</th>

									<td class="btns d-none d-lg-table-cell text-center itemnumber">
										<a class="btn <?php echo ($item->questions_count > 0) ? 'btn-success' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_faqbookpro&view=questions&filter[topic_id]=' . $item->id); ?>">
											<?php echo $item->questions_count; ?>
										</a>
									</td>

									<td class="small d-none d-md-table-cell">
										<?php echo $this->escape($item->access_level); ?>
									</td>

									<td class="small d-none d-md-table-cell">
										<?php if ($item->language == '*') : ?>
											<?php echo Text::alt('JALL', 'language'); ?>
										<?php else : ?>
											<?php echo $item->language_title ? $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
										<?php endif; ?>
									</td>

									<td class="d-none d-lg-table-cell">
										<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
											<?php echo (int) $item->id; ?>
										</span>
									</td>

								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>