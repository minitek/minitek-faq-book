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
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;

if ($this->popular_topics_header)
{
	?><h2 class="fbContent_title">
		<?php echo Text::_('COM_FAQBOOKPRO_POPULAR_TOPICS'); ?>
	</h2><?php
}

if (count($this->popular_topics))
{
	?><div id="fb_popTopics">
		<ul class="fbContent_popItems clearfix"><?php
			foreach ($this->popular_topics as $key => $item)
			{
				?><li class="<?php echo $class; ?>"><?php
					if ($this->popular_topics_title)
					{
						?><h5>
							<a href="<?php echo Route::_(RouteHelper::getTopicRoute($item->id, false, false, $item->language)); ?>" class="<?php echo $anchor_class; ?>" id="fid<?php echo $item->id; ?>">
								<?php echo $item->title; ?>
							</a>
						</h5><?php
					}

					if ($this->popular_topics_desc && $item->description)
					{
						?><p>
							<?php echo UtilitiesHelper::getWordLimit($item->description, $this->popular_topics_desc_limit); ?>
						</p><?php
					}
				?></li><?php
			}
		?></ul>
	</div><?php
}
