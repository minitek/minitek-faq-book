<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<button type="button" class="btn btn-secondary" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>

<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('question.batch');">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
