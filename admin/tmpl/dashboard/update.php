<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Administrator\Helper\FAQBookProHelper;

defined('_JEXEC') or die;

$localVersion = FAQBookProHelper::localVersion();
$latestVersion = FAQBookProHelper::latestVersion();
$message = FAQBookProHelper::updateMessage();
$message_version = FAQBookProHelper::updateMessageVersion();
$type = Factory::getApplication()->input->get('type', 'auto');

if ($latestVersion && version_compare($latestVersion, $localVersion, '>')) { ?>
  <joomla-alert type="success" dismiss="true" class="p-3 text-center" style="animation-name: joomla-alert-fade-in;" role="alert">
    <div class="alert-heading"><span class="success"></span><span class="visually-hidden">success</span></div>
    <div class="alert-wrapper">
      <div class="alert-message">
        <span><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_A_NEW_VERSION_IS_AVAILABLE'); ?></span>
        <span class="badge bg-success"><?php echo $latestVersion; ?></span> <span class="badge bg-success">Free</span>
        <a class="button-info btn btn-sm btn-info" href="<?php echo Route::_('index.php?option=com_installer&view=update'); ?>">
          <span class="icon-refresh" aria-hidden="true"></span>&nbsp;
          <?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_UPDATE_NOW'); ?>
        </a>
      </div>
    </div>
  </joomla-alert>
  <?php if ($message && version_compare($localVersion, $message_version, '<')) { ?>
    <joomla-alert type="danger" dismiss="true" class="p-3" style="animation-name: joomla-alert-fade-in;" role="alert">
      <div class="alert-heading"><span class="danger"><b>WARNING</b></span><span class="visually-hidden">error</span></div>
      <div class="alert-wrapper">
        <div class="alert-message">
          <div class="update-info">
            <p><?php echo $message; ?></p>
            <p><small><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_DISABLE_MESSAGE'); ?></small></p>
          </div>
        </div>
      </div>
    </joomla-alert>
  <?php } ?>
<?php } else if ($type == 'check') { ?>
  <?php if ($latestVersion) { ?>
    <joomla-alert type="success" dismiss="true" class="p-3 text-center" style="animation-name: joomla-alert-fade-in;" role="alert">
      <div class="alert-heading"><span class="success"></span><span class="visually-hidden">success</span></div>
      <div class="alert-wrapper">
        <div class="alert-message">
          <?php if ($latestVersion == $localVersion) { ?>
            <span><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_YOU_HAVE_THE_LATEST_VERSION'); ?></span>
          <?php } else { ?>
            <span><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_YOU_HAVE_A_DEVELOPMENT_VERSION'); ?></span>
          <?php } ?>
          <span class="badge bg-success"><?php echo $latestVersion; ?></span> <span class="badge bg-success">Free</span>
        </div>
      </div>
    </joomla-alert>
  <?php } else { ?>
    <joomla-alert type="danger" dismiss="true" class="p-3 text-center" style="animation-name: joomla-alert-fade-in;" role="alert">
      <div class="alert-heading"><span class="danger"></span><span class="visually-hidden">error</span></div>
      <div class="alert-wrapper">
        <div class="alert-message">
          <span><?php echo Text::_('COM_FAQBOOKPRO_DASHBOARD_COULD_NOT_FETCH_UPDATE_INFO'); ?></span>
        </div>
      </div>
    </joomla-alert>
  <?php } ?>
<?php }
