(function($) {
	$(function(){

		function getUrlParameter(sParam)
		{
			var sPageURL = window.location.search.substring(1);
			var sURLVariables = sPageURL.split('&');

			for (var i = 0; i < sURLVariables.length; i++)
			{
				var sParameterName = sURLVariables[i].split('=');

				if (sParameterName[0] == sParam)
				{
					return sParameterName[1];
				}
			}
		}

		$(function()
		{
			if (getUrlParameter('view') == 'topic')
			{
				dynamicSection();
			}
		});

		function dynamicSection()
		{
			// Global variables
			var token = window.mfbvars.token + "=1";
			var site_path = window.mfbvars.site_path;

			$('select[name="jform\[parent_id\]"]').change(function(){
				topicId = jQuery(this).val();

				jQuery.ajax({
					type: "POST",
					url: site_path+"index.php?option=com_faqbookpro&task=topic.dynamicSection&topicid=" + topicId + "&" + token,
					success: function(data)
					{
						if (data != 'root')
						{
							var json = jQuery.parseJSON(data);
							$('select[name="jform\[section_id\]"]').val(json.section_id);

							$('#jform_section_id')
								.addClass('disabled')
								.hide()
								.end()
							;

							$('.section_id_duplicate').show();
							$('#jform_section_id_duplicate').val(json.section_title + ' [' + json.section_alias + ']');
						}
						else
						{
							$('#jform_section_id')
								.removeClass('disabled')
								.show()
								.end()
							;

							$('.section_id_duplicate').hide();
							$('#jform_section_id_duplicate').val('');
						}
					}
				});
			});
		}
	})
})(jQuery);
