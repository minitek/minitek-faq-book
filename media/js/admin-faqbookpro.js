(function (document, Joomla) {
	'use strict';

	function getUrlParameter(sParam)
	{
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		
		for (var i = 0; i < sURLVariables.length; i++)
		{
			var sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] == sParam)
				return sParameterName[1];
		}
	}

	function dynamicSection()
	{
		var options = Joomla.getOptions('com_faqbookpro');
		var token = options.token + "=1";
		var site_path = options.site_path;
		var jform_parent_id = document.querySelector('#jform_parent_id');

		if (jform_parent_id)
		{
			jform_parent_id.addEventListener('change', function () 
			{ 
				var topicId = jform_parent_id.value;
				
				Joomla.request({
					url: site_path+"index.php?option=com_faqbookpro&task=topic.dynamicSection&topicid=" + topicId + "&" + token,
					method: 'POST',
					onSuccess: (response, xhr) => {
						if (response != 'root')
						{
							var json = JSON.parse(response);
							var jform_section_id = document.querySelector('#jform_section_id');
							var jform_section_id_lbl = document.querySelector('#jform_section_id-lbl');

							if (jform_section_id)
							{
								jform_section_id.value = json.section_id;
								jform_section_id.classList.add('disabled');
								jform_section_id.classList.remove('form-control-danger', 'invalid');
								jform_section_id.style.display = 'none';

								if (jform_section_id_lbl)
								{
									jform_section_id_lbl.classList.remove('invalid');
									jform_section_id_lbl.querySelectorAll('.form-control-feedback').forEach(function(a)
									{
										a.remove()
									});
								}
							}

							var section_id_duplicate = document.querySelector('#section_id_duplicate');
							var jform_section_id_duplicate = document.querySelector('#jform_section_id_duplicate');

							if (section_id_duplicate && jform_section_id_duplicate)
							{
								section_id_duplicate.style.display = 'block';
								jform_section_id_duplicate.value = json.section_title + ' [' + json.section_alias + ']';
							}
						}
						else
						{
							var jform_section_id = document.querySelector('#jform_section_id');

							if (jform_section_id)
							{
								jform_section_id.classList.remove('disabled');
								jform_section_id.style.display = 'block';
							}

							var section_id_duplicate = document.querySelector('#section_id_duplicate');
							var jform_section_id_duplicate = document.querySelector('#jform_section_id_duplicate');

							if (section_id_duplicate && jform_section_id_duplicate)
							{
								section_id_duplicate.style.display = 'none';
								jform_section_id_duplicate.value = '';
							}
						}
					},
					onError: (xhr) => {
						console.log(xhr);
					}
				});
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () 
	{
		if (getUrlParameter('view') == 'topic')
			dynamicSection();
	});

})(document, Joomla);
