<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('topic.batch');return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
