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
	<div class="row">

		<div class="col-sm-12 col-md-8">
			<div class="media mt-0">
				<div class="float-start">
					<img class="media-object" src="<?php echo URI::root(true) . '/media/com_faqbookpro/css/logo.png'; ?>">
				</div>
				<div class="media-body">
					<h2 class="media-heading"><?php echo Text::_('COM_FAQBOOKPRO'); ?> <span class="badge bg-success">Free</span></h2>
					<?php echo Text::_('COM_FAQBOOKPRO_DESC'); ?>
				</div>
			</div>

			<div class="dashboard-thumbnails">
				<div class="thumbnail card">
					<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=sections'); ?>">
						<i class="icon icon-list-2"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_SECTIONS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=topics'); ?>">
						<i class="icon icon-folder"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_TOPICS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="<?php echo Route::_('index.php?option=com_faqbookpro&view=questions'); ?>">
						<i class="icon icon-question"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_QUESTIONS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-comment"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_ANSWERS'); ?>
							<span class="badge bg-danger">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-envelope"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_EMAIL_TEMPLATES'); ?>
							<span class="badge bg-danger">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-folder-plus"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_CUSTOM_FIELDS'); ?>
							<span class="badge bg-danger">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-checkmark-2"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_CUSTOM_STATES'); ?>
							<span class="badge bg-danger">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-attachment"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_ATTACHMENTS'); ?>
							<span class="badge bg-danger">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_faqbookpro&path=&return=' . base64_encode(URI::getInstance()->toString())); ?>">
						<i class="icon icon-cog"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_CONFIGURATION'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail card">
					<a href="https://extensions.joomla.org/extensions/extension/directory-a-documentation/faq/minitek-faq-book/" target="_blank">
						<i class="icon icon-star" style="color: #ffcb52;"></i>
						<span class="thumbnail-title">
							<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_LIKE_THIS_EXTENSION'); ?><br>
							<span class="small">
								<?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_LEAVE_A_REVIEW_ON_JED'); ?>
							</span>
						</span>
					</a>
				</div>
			</div>
		</div>

		<div class="col-sm-12 col-md-4">

			<div class="dashboard-module">
				<div class="card mb-3">
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
				<div class="card mb-3">
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