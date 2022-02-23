(function (document, Joomla) {
  "use strict";

  function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split("&");

    for (var i = 0; i < sURLVariables.length; i++) {
      var sParameterName = sURLVariables[i].split("=");

      if (sParameterName[0] == sParam) return sParameterName[1];
    }
  }

  function updateParent() {
    var jform_parent_id = document.querySelector("#jform_parent_id");

    if (jform_parent_id) {
      jform_parent_id.addEventListener("change", function () {
        if (this.options[this.selectedIndex].closest("optgroup")) {
          // Get section
          let section_id = parseInt(
            this.options[this.selectedIndex].closest("optgroup").id,
            10
          );

          // Change section id
          var jform_section_id = document.querySelector("#jform_section_id");

          if (jform_section_id) jform_section_id.value = section_id;
        }
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (getUrlParameter("view") == "topic") updateParent();
  });
})(document, Joomla);
