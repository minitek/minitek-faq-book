<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
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
	if ($this->popular_topics_cols > 1)
	{
		$class = 'fbContent_gridItem';
		$anchor_class = 'clearfix';
	}
	else
	{
		$class = 'fbContent_gridItem onecolgrid';
		$anchor_class = '';
	}

	?><div id="fb_popTopics">
		<ul class="fbContent_grid clearfix"><?php
			$i = 0;
			foreach ($this->popular_topics as $key => $item)
			{
				?><li class="<?php echo $class; ?>" style="width:<?php echo number_format(100 / $this->popular_topics_cols, 1); ?>%;">
					<div class="fbContent_gridItemContainer"><?php
						$topic_params = json_decode($item->params, false);
						$title_class = 'text-left';

						if ($this->popular_topics_image && isset($topic_params->image) && $topic_params->image)
						{
							$title_class = 'text-center';
							?><a href="<?php echo Route::_(RouteHelper::getTopicRoute($item->id)); ?>" class="pop-item-img <?php echo $anchor_class; ?>">
								<img src="<?php echo Uri::root().$topic_params->image; ?>" alt="<?php echo $item->title; ?>">
							</a><?php
						}

						if ($this->popular_topics_title)
						{
							?><h4 class="<?php echo $title_class; ?>">
								<a href="<?php echo Route::_(RouteHelper::getTopicRoute($item->id)); ?>" class="pop-item <?php echo $anchor_class; ?>" id="fid<?php echo $item->id; ?>">
									<?php echo $item->title; ?>
								</a>
							</h4><?php
						}

						if ($this->popular_topics_desc)
						{
							?><div class="pop-item-desc">
								<?php echo UtilitiesHelper::getWordLimit($item->description, $this->popular_topics_desc_limit); ?>
							</div><?php
						}
					?></div>
				</li><?php
				$i++;
			}
		?></ul>
	</div><?php
}
