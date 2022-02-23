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
use Joomla\CMS\Router\Route;
use Joomla\CMS\URI\URI;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;

$localVersion = FAQBookProHelper::localVersion();
?>

<div class="minitek-dashboard mt-3">
	<div class="row g-3">
		<div class="col-12 col-lg-8">
			<div class="brand card mb-3 p-3">
				<div class="d-flex">
					<div class="me-4">
						<img src="<?php echo URI::root(true) . '/media/com_faqbookpro/css/logo.png'; ?>">
					</div>
					<div class="py-3">
						<h2 class="mb-3"><?php echo Text::_('COM_FAQBOOKPRO'); ?> <span class="badge bg-success">Free</span></h2>
						<p class="m-0"><?php echo Text::_('COM_FAQBOOKPRO_DESC'); ?></p>
					</div>
				</div>
			</div>

			<div class="dashboard-cards row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3 mb-3 text-center">
				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=sections'); ?>">
								<div class="pt-1 pb-3"><i class="icon icon-list-2"></i></div>
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SECTIONS'); ?></div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=topics'); ?>">
								<div class="pt-1 pb-3"><i class="icon icon-folder"></i></div>
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_TOPICS'); ?></div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=questions'); ?>">
								<div class="pt-1 pb-3"><i class="icon icon-question-circle"></i></div>
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_QUESTIONS'); ?></div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-comment"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_ANSWERS'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-comment"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_ANSWER_TEMPLATES'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-envelope"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_EMAIL_TEMPLATES'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-edit"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_CUSTOM_FIELDS'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-bookmark"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_QUESTION_TYPES'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="#" class="disabled" onclick="return: false;">
								<div class="pt-1 pb-3"><i class="icon icon-attachment"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_ATTACHMENTS'); ?>
									<span class="badge bg-danger">Pro</span>
								</div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_faqbookpro&path=&return=' . base64_encode(URI::getInstance()->toString())); ?>">
								<div class="pt-1 pb-3"><i class="icon icon-cog"></i></div>
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_CONFIGURATION'); ?></div>
							</a>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card border h-100 py-2">
						<div class="card-body">
							<a href="https://extensions.joomla.org/extensions/extension/directory-a-documentation/faq/minitek-faq-book/" target="_blank">
								<div class="pt-1 pb-3"><i class="icon icon-thumbs-up"></i></div>
								<div>
									<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_LIKE_THIS_EXTENSION'); ?><br>
									<div class="small">
										<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_LEAVE_A_REVIEW_ON_JED'); ?>
									</div>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-4">
			<div class="dashboard-module">
				<div class="card border mb-3 overflow-hidden">
					<div class="card-header">
						<h4 class="m-0"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_ABOUT'); ?></h4>
					</div>
					<div class="card-body p-0">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_EXTENSION'); ?></div>
								<div><a href="https://www.minitek.gr/joomla/extensions/minitek-faq-book" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO'); ?></a></div>
							</li>
							<li class="list-group-item">
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_VERSION'); ?></div>
								<div>
									<span class="badge bg-success"><?php echo $localVersion; ?></span> <span class="badge bg-success">Free</span>
									<a id="check-version" href="#" class="btn btn-info btn-sm float-end">
										<i class="fas fa-sync-alt"></i>&nbsp;&nbsp;<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_CHECK_VERSION'); ?>
									</a>
								</div>
							</li>
							<li class="list-group-item">
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_DEVELOPER'); ?></div>
								<div><a href="https://www.minitek.gr/" target="_blank">Minitek</a></div>
							</li>
							<li class="list-group-item">
								<div><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_LICENSE'); ?></div>
								<div><a href="https://www.minitek.gr/terms-of-service" target="_blank">GNU GPLv3 Commercial</a></div>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="dashboard-module">
				<div class="card border mb-3 overflow-hidden">
					<div class="card-header">
						<h4 class="m-0"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_QUICK_LINKS'); ?></h4>
					</div>
					<div class="card-body p-0">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<span class="me-2 icon-book icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_DOCUMENTATION'); ?></a>
								</span>
							</li>
							<li class="list-group-item">
								<span class="me-2 icon-list icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/support/changelogs/joomla/minitek-faq-book" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_CHANGELOG'); ?></a>
								</span>
							</li>
							<li class="list-group-item">
								<span class="me-2 icon-support icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/support/forum/joomla/minitek-faq-book" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_TECHNICAL_SUPPORT'); ?></a>
								</span>
							</li>
							<li class="list-group-item">
								<span class="me-2 icon-help icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book/faq" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_FAQ'); ?></a>
								</span>
							</li>
							<li class="list-group-item">
								<span class="me-2 icon-question icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book/free-vs-pro" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_FREE_VS_PRO'); ?></a>
								</span>
							</li>
							<li class="list-group-item">
								<span class="me-2 icon-lock icon-fw" aria-hidden="true"></span>&nbsp;
								<span>
									<a href="https://www.minitek.gr/joomla/extensions/minitek-faq-book#subscriptionPlans" target="_blank"><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_UPGRADE_TO_PRO'); ?></a>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>