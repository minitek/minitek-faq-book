<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

$data = array();
$data['tab'] = $this->tab;
$data['sectionId'] = $this->sectionId;
$data['topic'] = $this->topic;
$data['topic_params'] = $this->topic_params;
$data['questions_params'] = $this->questions_params;
$layout = new JLayoutFile('fb_topic');
echo $layout->render($data);
