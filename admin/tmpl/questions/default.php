<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;

HTMLHelper::_('behavior.multiselect');

$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_faqbookpro&task=questions.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>

<form action="<?php echo Route::_('index.php?option=com_faqbookpro&view=questions'); ?>" method="post" name="adminForm" id="adminForm">
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

					<table class="table itemList" id="articleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_FAQBOOKPRO_QUESTIONS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="min-width:100px">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-1 d-none d-lg-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>

						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$item->max_ordering = 0;
							$ordering   = ($listOrder == 'a.ordering');
							$canCreate  = $user->authorise('core.create',     'com_faqbookpro.topic.' . $item->topicid);
							$canEdit    = $user->authorise('core.edit',       'com_faqbookpro.question.' . $item->id);
							$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canEditOwn = $user->authorise('core.edit.own',   'com_faqbookpro.question.' . $item->id) && $item->created_by == $userId;
							$canChange  = $user->authorise('core.edit.state', 'com_faqbookpro.question.' . $item->id) && $canCheckin;
							?>
							<tr class="row<?php echo $i % 2; ?>"
								data-dragable-group="<?php echo $item->topicid; ?>">

								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>

								<td class="article-status text-center">
								<?php
									$options = [
										'task_prefix' => 'questions.',
										'disabled' => !$canChange
									];

									echo (new PublishedButton)->render((int) $item->state, $i, $options, $item->publish_up, $item->publish_down);
								?>
								</td>

								<th scope="row" class="has-context question-title">
									<div class="break-word">
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'questions.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($item->language == '*'):?>
											<?php $language = Text::alt('JALL', 'language'); ?>
										<?php else:?>
											<?php $language = $item->language_title ? $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
										<?php endif;?>

										<?php if ($item->pinned) { ?>
											<span class="text-info question-icon"><span class="fas fa-thumbtack" title="<?php echo Text::_('COM_FAQBOOKPRO_PINNED'); ?>"></span></span>
										<?php } ?>

										<?php if ($canEdit || $canEditOwn) : ?>
											<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_faqbookpro&task=question.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
												<?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<span><?php echo $this->escape($item->title); ?></span>
										<?php endif; ?>
									</div>
									<div class="small">
										<?php echo Text::_('COM_FAQBOOKPRO_TOPIC').": "; ?>
										<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_faqbookpro&task=topic.edit&id=' . $item->topicid); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->topic_title); ?>
										</a>
									</div>
								</th>

								<td class="small d-none d-md-table-cell">
									<?php if ($item->created_by_alias) : ?>
										<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo Text::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
										<p class="smallsub"> <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></p>
									<?php else : ?>
										<?php if ($item->created_by) { ?>
											<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo Text::_('JAUTHOR'); ?>">
												<?php echo $this->escape($item->author_name); ?>
											</a>
										<?php } else { ?>
											<?php if (isset($item->created_by_name)) { ?>
												<div><?php echo $this->escape($item->created_by_name); ?></div>
												<?php if (isset($item->created_by_email)) { ?>
													<?php echo $this->escape($item->created_by_email); ?>
												<?php } ?>
											<?php } else { ?>
												<?php echo Text::_('COM_FAQBOOKPRO_GUEST'); ?>
											<?php } ?>
										<?php } ?>
									<?php endif; ?>
								</td>

								<td class="small d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
								</td>

								<td class="d-none d-lg-table-cell text-center">
									<span class="badge badge-info">
										<?php echo (int) $item->hits; ?>
									</span>
								</td>

								<td class="d-none d-lg-table-cell">
									<?php echo (int) $item->id; ?>
								</td>

							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_faqbookpro')
						&& $user->authorise('core.edit', 'com_faqbookpro')
						&& $user->authorise('core.edit.state', 'com_faqbookpro')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title' => Text::_('COM_FAQBOOKPRO_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer')
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif;?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo HTMLHelper::_('form.token'); ?>

			</div>
		</div>
	</div>
</form>
