(function($) {
	$(function(){

		var options = Joomla.getOptions('com_faqbookpro');
		var site_path = options.site_path;
		var page_view = options.page_view;
		var sectionId = options.sectionId;
		var topicId = options.topicId;
		var leftnav = options.leftnav;
		var loadAllTopics = options.loadAllTopics;
		var active_tab = options.active_tab;
		var ajax_request;
		var duration = 300; // animate duration

		// Fix leftnav height on window load
		if (leftnav)
		{
			$(window).on('load', function()
			{
				if (page_view == 'topic' || page_view == 'question')
				{
					// Show left navigation before calculating height
					$('.fbLeftNavigation_core').removeClass('fb-hidden');

					// Fix left navigation topics height
					if ($('#liid'+topicId).hasClass('NavLeftUL_endpoint'))
					{
						var parent_ul = $('#liid'+topicId).parent();
					}
					else
					{
						var parent_ul = $('#liid'+topicId).find('ul.NavLeftUL_sublist');
					}

					var vheight = $(parent_ul).height();
					$('.fbLeftNavigation_wrap').css({"height":vheight+"px"});

					// Hide left navigation
					$('.fbLeftNavigation_core').addClass('fb-hidden');
					$('.show_menu').find('a:first').removeClass('fb-active');
				}
				else if (page_view == 'section' || page_view == 'profile' || page_view == 'myquestion' || page_view == 'myanswer')
				{
					// Fix left navigation topics height
					$('.fbLeftNavigation_wrap').css({"height":"auto"});
				}
			});
		}

		// Load section
		function loadHome(event, href, tab, url, text)
		{
			event.preventDefault();
			tab = tab || active_tab;
			url = url || site_path+"index.php?option=com_faqbookpro&task=section.getContent&sectionId=" + sectionId + "&tab=" + tab;

			// Check if there is a pending ajax request
			if (typeof ajax_request !== "undefined")
				ajax_request.abort();

			$('.fbContent_root').hide();
			$('.fb_loader').show();

			ajax_request = $.ajax({
				type: "POST",
				url: url,
				beforeSend: function() {
					// Change url dynamically
					window.history.pushState({}, document.title, href);
				},
				success: function(msg) {
					$('.NavLeftUL_parent').removeClass('ul_loading');
					$('.NavLeftUL_item').removeClass('li_loading');
					$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
					$('#top_liid_home').addClass('NavTopUL_lastChild');
					$('.fbContent_root').show();
					$(".fbContent_root").html(msg);
					$('.fb_loader').hide();
					$('.NavLeftUL_item').removeClass('li_selected');

					// Change data-tab / data-page in pagination
					$('.fbContent_paging_button').attr('data-tab', tab);
					$('.fbContent_paging_button').attr('data-page', '2');

					// Change browser title dynamically
					var section_title = $('#top_liid_home .NavTopUL_link').text().trim();
					$(document).attr('title', section_title);

					// Show left navigation
					$('.fbLeftNavigation_core').removeClass('fb-hidden');
					$('.show_menu').find('a:first').addClass('fb-active');

					// Remove all li items after home in topnav
					$('#top_liid_home').nextAll('li').remove();

					if (tab == 'topics')
					{
						$('.NavLeftUL_item[data-tab="topics"]').addClass('li_selected');

						if (text !== undefined)
						{
							$('.fbTopNavigation_root')
							.append($('<li class="NavTopUL_item NavTopUL_topics NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+text+'</a></li>'));
							$('#top_liid_home').removeClass('NavTopUL_lastChild');
						}
					}
					else
					{
						$('#NavLeftUL_'+ tab +'Questions').parent().addClass('li_selected');
						$('.section_tabs a[data-tab="'+ tab +'"]').addClass('tab_selected');
					}
				}
			});
		}

		// Reset left navigation topics
		function resetTopics(event, href, tab, url, text)
		{
			// Fix left navigation topics height
			$('.fbLeftNavigation_wrap').css({"height":"auto"});
			$('.NavLeftUL_parent').addClass('ul_loading');

			// Calculate leftnav left value with topnav enabled
			var li_count = $('.fbTopNavigation_root li.NavTopUL_parent').length;

			// Calculate leftnav left value with topnav disabled
			if (li_count === 0)
			{
				if ($('.fbLeftNavigation_wrap').length)
				{
					var right_amount = parseInt($('.fbLeftNavigation_wrap')[0].style.left, 10) || '0';

					if (right_amount < 0)
					{
						right_amount = Math.abs(right_amount) / 100;
					}

					li_count = right_amount;
				}
			}

			var slide_count = parseInt(li_count);
			var righty = $('.fbLeftNavigation_wrap');
			var move_right = slide_count * 100;

			// Leftnav is enabled
			if ($('.fbLeftNavigation_core').length)
			{
				// Keep track of left navigation animation to prevent double clicks
				if ($('.fbLeftNavigation_wrap:animated').length == 0)
				{
					righty.animate(
						{left:"+="+move_right+"%"},
						{queue: false, duration: duration, complete: function(){
							$('.NavLeftUL_parent ul').removeClass('NavLeftUL_expanded');
							$('#top_liid_home').addClass('NavTopUL_lastChild');
							loadHome(event, href, tab, url, text);
						}
					});
				}
			}
			// Leftnav is disabled
			else
			{
				$('#top_liid_home').addClass('NavTopUL_lastChild');
				loadHome(event, href, tab, url, text);
			}
		}

		// On load Topic / Question view
		if (page_view == 'topic' || page_view == 'question')
		{
			// Active left navigation li
			$('#liid'+topicId).addClass('li_selected');

			// Active left navigation ul
			$('#liid'+topicId).parents('ul.NavLeftUL_sublist').addClass('NavLeftUL_expanded');
			$('#liid'+topicId).find('ul.NavLeftUL_sublist:first').addClass('NavLeftUL_expanded');
			var parent_ul_class = $('#liid'+topicId).parent('ul').attr('class');

			if (parent_ul_class != 'NavLeftUL_parent')
			{
				var ul_level = parent_ul_class.split(" ")[1];
				var ul_level_num = ul_level.substring(ul_level.lastIndexOf('level') + 5);

				// Endpoint topic - we don't want to see the children topics
				if ($('#liid'+topicId).hasClass('NavLeftUL_endpoint'))
				{
					var move_level_num = parseInt(ul_level_num, 10);
				}
				// We want to see the chidlren topics of selected topic, so we need one more level
				else
				{
					var move_level_num = parseInt(ul_level_num, 10) + 1;
				}

				var move_ul = parseInt(move_level_num, 10)*100;
				$('.fbLeftNavigation_wrap').css({"left":"-"+move_ul+"%"});

				// Active top navigation li
				var parents_num = parseInt(ul_level_num);
				var first_parent_text = $('#liid'+topicId).parent().parent().find('> .NavLeftUL_anchor span.topicTitle').text();
				var first_parent_id = $('#liid'+topicId).parent('ul').parent('li').attr('id');

				$('.fbTopNavigation_root li.NavTopUL_firstChild').removeClass('NavTopUL_lastChild');

				// Add top navigation li's
				var $id = $('#'+first_parent_id);
				var $li = $('#'+first_parent_id);

				function findParents()
				{
					$id = $id.parent().parent();
					$li = $li.parent('ul').parent('li');
					var prev_parent_text = $id.find('> .NavLeftUL_anchor span.topicTitle').text();
					var prev_parent_id = $li.attr('id');

					// Add topic to breadcrumbs
					$('<li id="top_'+prev_parent_id+'" class="NavTopUL_item NavTopUL_topic NavTopUL_parent"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+prev_parent_text+'</a></li>').insertAfter('li.NavTopUL_firstChild');
				}

				// Only for level1+ ul's
				if (ul_level_num > 0)
				{
					for (var i = 1; i < parents_num; i++)
					{
						findParents();
					}
				}

				// Only for level1+ ul's
				if (ul_level_num > 0)
				{
					// Add lastChild parent li in top navigation
					// Endpoint topic - add class NavTopUL_lastChild
					if ($('#liid'+topicId).hasClass('NavLeftUL_endpoint'))
					{
						$('.fbTopNavigation_root').append($('<li id="top_'+first_parent_id+'" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+first_parent_text+'</a></li>'));
					}
					// Not endpoint topic - don't add class NavTopUL_lastChild
					else
					{
						$('.fbTopNavigation_root').append($('<li id="top_'+first_parent_id+'" class="NavTopUL_item NavTopUL_topic NavTopUL_parent"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+first_parent_text+'</a></li>'));
					}
				}

				// Add lastChild li in top navigation
				var last_topic_text = $('#liid'+topicId).find('> .NavLeftUL_anchor span.topicTitle').text();

				// Endpoint topic - don't add class NavTopUL_parent
				if ($('#liid'+topicId).hasClass('NavLeftUL_endpoint'))
				{
					$('.fbTopNavigation_root').append($('<li id="top_liid'+topicId+'" class="NavTopUL_item NavTopUL_topic NavTopUL_endpoint NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+last_topic_text+'</a></li>'));
				}
				// Endpoint topic - add class NavTopUL_parent
				else
				{
					$('.fbTopNavigation_root').append($('<li id="top_liid'+topicId+'" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+last_topic_text+'</a></li>'));
				}
			}
		}

		// Load topic endpoint
		function loadEndpoint(id, this_liid, href_link, topic_title)
		{
			if (loadAllTopics == 1 || $('#'+this_liid).hasClass('NavLeftUL_endpoint'))
			{
				$('.NavLeftUL_parent').addClass('ul_loading');
				$('.NavLeftUL_item').removeClass('li_loading');

				$('.fbContent_root').hide();
				$('.fb_loader').show();

				// Check if there is a pending ajax request
				if (typeof ajax_request !== "undefined")
					ajax_request.abort();

				ajax_request = $.ajax({
					type: "POST",
					url: site_path+"index.php?option=com_faqbookpro&task=topic.getContent&id=" + id + "&tab=" + active_tab,
					beforeSend: function() {
						$('#'+this_liid).addClass('li_loading');
						window.history.pushState({}, document.title, href_link);
					},
					success: function(msg) {
						$('#'+this_liid).removeClass('li_loading');
						$('.NavLeftUL_item').removeClass('li_selected');
						$('#'+this_liid).addClass('li_selected');
						$('.fbContent_root').show();
						$(".fbContent_root").html(msg);
						$('.fb_loader').hide();
						$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
						$('.NavLeftUL_parent').removeClass('ul_loading');

						// Change browser title dynamically
						$(document).attr('title', topic_title);

						// Hide left navigation
						if ($('#'+this_liid).hasClass('NavLeftUL_endpoint'))
						{
							$('.show_menu').find('a:first').removeClass('fb-active');
							$('.fbLeftNavigation_core').addClass('fb-hidden');
						}
					}
				});
			}
		}

		// Left navigation topic links
		$('.NavLeftUL_parent .NavLeftUL_topic').on('click', 'a:first', function(event)
		{
			event.preventDefault();

			// Keep track of left navigation animation to prevent double clicks
			if ($('.fbLeftNavigation_wrap:animated').length == 0 && !$('.NavLeftUL_parent').hasClass('ul_loading') && !$('.fbTopNavigation_wrap').hasClass('NavTopULloading') && !$('.fbContent_paging_button').hasClass('page_loading'))
			{
				// Fix left navigation topics height
				var parent_li = $(this).parent();
				var child_ul = $(parent_li).find('ul:first');
				var eheight = $(child_ul).height();
				$('.fbLeftNavigation_wrap').css({"height":eheight+"px"});

				var this_liid = $(this).parent('li').attr('id');
				var endpoint_liid = $(this).parent('li').attr('id');
				var endpoint_id = endpoint_liid.split("id").pop(1);
				var href_link = $(this).attr('href');
				var topic_title = $(this).text();

				// Remove user li from top navigation
				$('.NavTopUL_user').remove();

				// Remove browse topics li from top navigation
				$('.NavTopUL_topics').remove();

				// Remove myquestion/myanswer li from top navigation
				$('.NavTopUL_myquestion').remove();
				$('.NavTopUL_myanswer').remove();

				// Slide menu only if not endpoint
				if (!$(this).parent('li').hasClass('NavLeftUL_endpoint'))
				{
					$('#'+this_liid).find('ul:first').addClass('NavLeftUL_expanded');
					var lefty = $('.fbLeftNavigation_wrap');

					lefty.animate(
						{left:"-=100%"},
						{queue: true, duration: duration, complete: function()
						{
							// Remove lastchild class
							$('.fbTopNavigation_root li').removeClass('NavTopUL_lastChild');

							// Remove last endpoint
							$('.fbTopNavigation_root li.NavTopUL_endpoint').remove();

							// Add topic to breadcrumbs
							var this_title = $('#'+this_liid).find('a:first').text();
							$('.fbTopNavigation_root').append($('<li id="top_'+this_liid+'" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+this_title+'</a></li>'));

							// Load endpoint
							$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
							loadEndpoint(endpoint_id, this_liid, href_link, topic_title);
						}
					});
				}
				else
				{
					var this_title = $('#'+this_liid).find('a:first').text();

					// Remove lastchild class from section li
					$('.fbTopNavigation_root li.NavTopUL_section').removeClass('NavTopUL_lastChild');

					// Remove last endpoint
					$('.fbTopNavigation_root li.NavTopUL_endpoint').remove();

					// Add endpoint topic to breadcrumbs
					$('.fbTopNavigation_root').append($('<li id="top_'+this_liid+'" class="NavTopUL_item NavTopUL_topic NavTopUL_endpoint NavTopUL_lastChild"><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<a class="NavTopUL_link" href="#" onclick="return false;">'+this_title+'</a></li>'));

					// Load endpoint
					$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
					loadEndpoint(endpoint_id, this_liid, href_link, topic_title);
				}
			}
		});

		// Topic back link - Remove class 'expanded' from 1st parent ul / Move wrap to the right
		$('.NavLeftUL_parent li.NavLeftUL_backItem').on('click', 'a:first', function(event)
		{
			event.preventDefault();

			// Keep track of left navigation animation to prevent double clicks
			if ($('.fbLeftNavigation_wrap:animated').length == 0 && !$('.NavLeftUL_parent').hasClass('ul_loading') && !$('.fbTopNavigation_wrap').hasClass('NavTopULloading') && !$('.fbContent_paging_button').hasClass('page_loading'))
			{
				var this_backliid = $(this).parent().attr('id');

				// Fix left navigation topics height
				var back_child_ul = $(this).parent().parent().parent().parent();
				var wheight = $(back_child_ul).height();
				$('.fbLeftNavigation_wrap').css({"height":wheight+"px"});
				var righty = $('.fbLeftNavigation_wrap');

				righty.animate(
					{left:"+=100%"},
					{queue: false, duration: duration, complete: function()
					{
						$('#'+this_backliid).parent('ul').removeClass('NavLeftUL_expanded');
						$('.fbTopNavigation_root li.NavTopUL_lastChild').remove();
						$('.fbTopNavigation_root li:last').addClass('NavTopUL_lastChild');
					}
				});
			}
		});

		// Top navigation
		$('.fbTopNavigation_root').on('click', 'li', function(event)
		{
			if (loadAllTopics == 0 && $(this).hasClass('NavTopUL_parent'))
			{
				return;
			}

			// If in question/answer form, treat the section link as a normal link
			if (page_view == 'myquestion' || page_view == 'myanswer')
			{
				if ($(this).attr('id') == 'top_liid_home')
				{
					window.location.href = $(this).find('a').attr('href');
				}

				return;
			}

			var _duration = duration; // Reset duration
			var this_liid = $(this).attr('id');
			var href = $(this).find('.NavTopUL_link').attr('href');

			// Topic links
			if ($(this).hasClass('NavTopUL_parent') && !$('.NavLeftUL_parent').hasClass('ul_loading') && !$('.fbTopNavigation_wrap').hasClass('NavTopULloading') && !$('.fbContent_paging_button').hasClass('page_loading'))
			{
				var li_count = $('.fbTopNavigation_root li.NavTopUL_parent').length;
				var li_index = $('.fbTopNavigation_root li.NavTopUL_parent').index(this);
				var slide_count = parseInt(li_count) - parseInt(li_index) - 1;

				// Remove li's after specific index
				$('.NavTopUL_topic').eq(li_index).nextAll('li').remove();
				$(this).addClass('NavTopUL_lastChild');

				// Move left navigation
				var righty = $('.fbLeftNavigation_wrap');
				var move_right = slide_count * 100;

				// Fix left navigation topics height
				var this_id = this_liid.split("_").pop(0);
				var parent_li = $('#'+this_id);
				var child_ul = parent_li.find('ul:first');
				var eheight = $(child_ul).height();
				$('.fbLeftNavigation_wrap').css({"height":eheight+"px"});

				// Keep track of left navigation animation to prevent double clicks
				if ($('.fbLeftNavigation_wrap:animated').length == 0)
				{
					// If no leftnav animation is required, don't wait for animation
					if (move_right <= 0)
					{
						_duration = 0;
					}

					righty.animate(
						{left:"+="+move_right+"%"},
						{queue: false, duration: _duration, complete: function()
						{
							$('#'+this_id+' ul ul').removeClass('NavLeftUL_expanded');

							// Add loader in top navigation
							$(".fbTopNavigation_wrap").addClass('NavTopULloading');

							// Load topic content
							var topic_id = this_liid.split("id").pop(1);
							var left_liid = 'liid'+topic_id;
							var href_link = $('.NavLeftUL_parent').find('#'+left_liid+' a:first').attr('href');
							var topic_title = $('.NavLeftUL_parent').find('#'+left_liid+' a:first span.topicTitle').text();

							loadEndpoint(topic_id, left_liid, href_link, topic_title);
						}
					});
				}
			}

			// Home link
			if ($(this).hasClass('NavTopUL_section') && !$('.NavLeftUL_parent').hasClass('ul_loading') && !$('.fbTopNavigation_wrap').hasClass('NavTopULloading') && !$('.fbContent_paging_button').hasClass('page_loading'))
			{
				// Add loader in top navigation
				$(".fbTopNavigation_wrap").addClass('NavTopULloading');
				resetTopics(event, href);
			}
		});

		// Hide/Show menu button / Show left navigation
		$('.fbTopNavigation_core').on('click', '.show_menu', function(event)
		{
			event.preventDefault();
			$(this).find('a:first').toggleClass('fb-active');
			$('.fbLeftNavigation_core').toggleClass('fb-hidden');
		});

		// Left navigation sorting links ('Browse topics' link)
		$('.fbLeftNavigation_core').on('click', '.NavLeftUL_tab', function(event)
		{
			event.preventDefault();

			// Keep track of left navigation animation to prevent double clicks
			if ($('.fbLeftNavigation_wrap:animated').length == 0 && !$('.NavLeftUL_parent').hasClass('ul_loading') && !$('.fbTopNavigation_wrap').hasClass('NavTopULloading') && !$('.fbContent_paging_button').hasClass('page_loading'))
			{
				var $this = $(this);
				var href = $this.find('.NavLeftUL_anchor').attr('href');
				var tab = $this.data('tab');
				var text = $this.data('text');

				$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
				$('.NavLeftUL_item').removeClass('li_loading');
				$this.addClass('li_loading');

				// Get url
				var url = site_path+"index.php?option=com_faqbookpro&task=section.getContent&sectionId=" + sectionId + "&tab=" + tab;

				// Reset topics
				resetTopics(event, href, tab, url, text);
			}
		});

		// Ajax pagination - Topic
		$('.fbContent_core').on('click', 'a.fbContent_topic_paging', function(event)
		{
			event.preventDefault();

			if (!$(this).hasClass('fbContent_btn_disabled'))
			{
				// Keep track of left navigation animation to prevent double clicks
				if (
					$('.fbLeftNavigation_wrap:animated').length == 0
					&& !$('.NavLeftUL_parent').hasClass('ul_loading')
					&& !$('.fbTopNavigation_wrap').hasClass('NavTopULloading')
					&& !$('.fbContent_paging_button').hasClass('page_loading'))
				{
					$(this).addClass('page_loading');
					$(this).find('.fbContent_paging_text').hide();
					$(this).find('.fbContent_paging_loader').css('display', 'inline-block');
					$(this).addClass('fbContent_btn_disabled');

					// Clear navigation
					clearNavigation();

					// Get page
					var page = $(this).attr('data-page');
					page = parseInt(page, 10);

					// Get tab
					var tab = $(this).attr('data-tab');

					// Get topic
					var topicId = $(this).attr('data-topic');

					// Check if there is a pending ajax request
					if (typeof ajax_request !== "undefined")
						ajax_request.abort();

					// Get url
					var url;

					if ($(this).hasClass('fbContent_section_paging'))
					{
						url = site_path+"index.php?option=com_faqbookpro&task=section.getContent&sectionId=" + sectionId + "&topicId=" + topicId + "&tab=" + tab + "&page=" + page;
					}
					else
					{
						url = site_path+"index.php?option=com_faqbookpro&task=topic.getContent&id=" + topicId + "&tab=" + tab + "&page=" + page;
					}

					ajax_request = $.ajax({
						type: "POST",
						url: url,
						success: function(msg) {
							$('#fbPaging_'+topicId).find('.fbContent_paging_loader').hide();

							if (msg)
							{
								$('#fbTopic_'+topicId).find('.fbContent_qList').append(msg);
								$('#fbPaging_'+topicId).find(".fbContent_paging_text").show();
								$('#fbPaging_'+topicId).find('.fbContent_paging_button').removeClass('fbContent_btn_disabled');

								if ($('#fbTopic_'+topicId).find('.endofitems').length)
								{
									$('#fbPaging_'+topicId).find(".fbContent_paging_text").hide();
									$('#fbPaging_'+topicId).find(".fbContent_noresults").show();
									$('#fbPaging_'+topicId).find('.fbContent_paging_button').addClass('fbContent_btn_disabled');
								}
								else
								{
									// Increment data-page
									new_page = page + 1;
									$('#fbPaging_'+topicId).find('.fbContent_paging_button').attr('data-page', new_page);
								}
							}
							else
							{
								$('#fbPaging_'+topicId).find(".fbContent_paging_text").hide();
								$('#fbPaging_'+topicId).find(".fbContent_noresults").show();
								$('#fbPaging_'+topicId).find('.fbContent_paging_button').addClass('fbContent_btn_disabled');
							}

							$('#fbPaging_'+topicId).find('.fbContent_paging_button').removeClass('page_loading');
						}
					});
				}
			}
		});

		// Clear navigation
		function clearNavigation()
		{
			$('.NavLeftUL_item').removeClass('li_loading');
			$(".fbTopNavigation_wrap").removeClass('NavTopULloading');
			$('.NavLeftUL_parent').removeClass('ul_loading');
		}

		// Toggle FAQs
		$('.fbContent_core').on('click', '.qFaqItem_link', function(event)
		{
			event.stopImmediatePropagation();
			event.preventDefault();

			var faq_id = parseInt($(this).attr('data-id'), 10);

			if ($(this).closest('.fbContent_qFaqItemInner').hasClass('faq_open'))
			{
				$(this).closest('.fbContent_qFaqItemInner').removeClass('faq_open');
				$(this).closest('.fbContent_qFaqItemInner').find('.qFaqItem_triangle .fa').removeClass('fa-caret-down').addClass('fa-caret-right');
			}
			else
			{
				$(this).closest('.fbContent_qFaqItemInner').addClass('faq_open');
				$(this).closest('.fbContent_qFaqItemInner').find('.qFaqItem_triangle .fa').removeClass('fa-caret-right').addClass('fa-caret-down');
			}
		});

		// Toggle left navigation
		$('#fbExtended').on('click', '.NavLeftUL_toggle', function(event)
		{
			event.preventDefault();

			$('.fbLeftNavigation_core').toggleClass('fb-minimized');

			// Check if there is a pending ajax request
			if (typeof ajax_request !== "undefined")
				ajax_request.abort();

			if ($('.fbLeftNavigation_core').hasClass('fb-minimized'))
			{
				$('.fbLeftNavigation_toggle').find('.NavLeftUL_toggle').html('<i class="fas fa-angle-double-right"></i>');

				ajax_request = $.ajax({
					type: "POST",
					url: site_path+"index.php?option=com_faqbookpro&task=section.toggleLeftnav&minimized=on",
					success: function(msg) {
					}
				});
			}

			if (!$('.fbLeftNavigation_core').hasClass('fb-minimized'))
			{
				$('.fbLeftNavigation_toggle').find('.NavLeftUL_toggle').html('<i class="fas fa-angle-double-left"></i>');

				ajax_request = $.ajax({
					type: "POST",
					url: site_path+"index.php?option=com_faqbookpro&task=section.toggleLeftnav&minimized=off",
					success: function(msg) {
					}
				});
			}
		});

	})
})(jQuery);
