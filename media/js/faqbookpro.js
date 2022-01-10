(function (document, Joomla) {
  "use strict";

  var query = document.querySelector.bind(document);
  var queryAll = document.querySelectorAll.bind(document);
  var options = Joomla.getOptions("com_faqbookpro");
  var site_path = options.site_path;
  var page_view = options.page_view;
  var sectionId = options.sectionId;
  var topicId = options.topicId;
  var leftnav = options.leftnav;
  var loadAllTopics = options.loadAllTopics;
  var active_tab = options.active_tab;
  var duration = 300;
  var ajax_request;
  var nodes = [];

  function clearNavigation() {
    clearNavigationParents();
    clearNavigationItems(["li_loading"]);

    if (nodes.fbTopNavigation_wrap)
      nodes.fbTopNavigation_wrap.classList.remove("NavTopULloading");
  }

  function clearNavigationParents() {
    if (nodes.NavLeftUL_parents) {
      nodes.NavLeftUL_parents.forEach(function (a) {
        a.classList.remove("ul_loading");
      });
    }
  }

  function clearNavigationItems(classes) {
    if (nodes.NavLeftUL_items) {
      nodes.NavLeftUL_items.forEach(function (a) {
        classes.forEach(function (c) {
          a.classList.remove(c);
        });
      });
    }
  }

  function animateLeft(current_left, new_left, duration) {
    var wrap = nodes.fbLeftNavigation_wrap;

    var animation = wrap.animate(
      [
        {
          left: current_left + "%",
        },
        {
          left: new_left + "%",
        },
      ],
      {
        duration: duration,
        easing: "ease-in-out",
        iterations: 1,
      }
    );

    animation.ready.then(function () {
      if (duration > 0) wrap.classList.add("wrap_animated");
    });

    return animation;
  }

  // Load section
  function loadHome(href, tab, url, text) {
    // Check if there is a pending ajax request
    if (typeof ajax_request !== "undefined") ajax_request.abort();

    tab = tab || active_tab;
    url =
      url ||
      site_path +
        "index.php?option=com_faqbookpro&task=section.getContent&sectionId=" +
        sectionId +
        "&tab=" +
        tab;

    if (nodes.fbContent_root) nodes.fbContent_root.style.display = "none";

    if (nodes.fb_loader) nodes.fb_loader.style.display = "block";

    ajax_request = Joomla.request({
      url: url,
      method: "POST",
      onBefore: (xhr) => {
        // Change url dynamically
        window.history.pushState({}, document.title, href);
      },
      onSuccess: (response, xhr) => {
        clearNavigationParents();
        clearNavigationItems(["li_loading", "li_selected"]);

        if (nodes.fbTopNavigation_wrap)
          nodes.fbTopNavigation_wrap.classList.remove("NavTopULloading");

        if (nodes.fbContent_root) {
          nodes.fbContent_root.innerHTML = response;
          nodes.fbContent_root.style.display = "block";
        }

        if (nodes.fb_loader) nodes.fb_loader.style.display = "none";

        // Change data-tab / data-page in pagination
        var fbContent_paging_button = query(".fbContent_paging_button");

        if (fbContent_paging_button) {
          fbContent_paging_button.setAttribute("data-type", tab);
          fbContent_paging_button.setAttribute("data-page", "2");
        }

        // Change browser title dynamically
        if (nodes.top_liid_home) {
          var section_title = nodes.top_liid_home
            .querySelector(".NavTopUL_link")
            .textContent.trim();
          document.title = section_title;
        }

        // Show left navigation
        if (nodes.fbLeftNavigation_core)
          nodes.fbLeftNavigation_core.classList.remove("fb-hidden");

        if (nodes.show_menu_btn)
          nodes.show_menu_btn.classList.add("active", "text-white");

        // Remove all li items after home in topnav
        if (nodes.fbTopNavigation_root) {
          var top_lis = nodes.fbTopNavigation_root.querySelectorAll("li");

          top_lis.forEach(function (a) {
            if (
              (a.id && a.id != "top_liid_home") ||
              a.classList.contains("NavTopUL_topics")
            )
              a.remove();
          });
        }

        if (tab == "topics") {
          if (nodes.NavLeftUL_items)
            query('.NavLeftUL_item[data-tab="topics"]').classList.add(
              "li_selected"
            );

          if (text !== undefined) {
            if (nodes.fbTopNavigation_root) {
              nodes.fbTopNavigation_root.innerHTML +=
                '<li class="NavTopUL_item NavTopUL_topics NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
                text +
                "</a></li>";

              if (nodes.top_liid_home)
                query("#top_liid_home").classList.remove("NavTopUL_lastChild");
            }
          }
        }
      },
      onError: (xhr) => {
        console.log(xhr);
      },
    });
  }

  // Reset left navigation topics
  function resetTopics(href, tab, url, text) {
    // Fix left navigation topics height
    if (nodes.fbLeftNavigation_wrap)
      nodes.fbLeftNavigation_wrap.style.height = "auto";

    if (nodes.NavLeftUL_parents) {
      nodes.NavLeftUL_parents.forEach(function (a) {
        a.classList.add("ul_loading");
      });
    }

    var top_li_count = 0;

    // Calculate leftnav left value with topnav enabled
    if (nodes.fbTopNavigation_root) {
      var NavTopUL_parents =
        nodes.fbTopNavigation_root.querySelectorAll("li.NavTopUL_parent");

      if (NavTopUL_parents) top_li_count = NavTopUL_parents.length;
    }

    // Calculate leftnav left value with topnav disabled
    if (top_li_count === 0) {
      if (nodes.fbLeftNavigation_wrap) {
        top_li_count =
          parseInt(nodes.fbLeftNavigation_wrap.style.left, 10) || "0";

        if (top_li_count < 0) top_li_count = Math.abs(top_li_count) / 100;
      }
    }

    // Leftnav is enabled
    if (nodes.fbLeftNavigation_core) {
      var slide_count = parseInt(top_li_count, 10);
      var wrap = nodes.fbLeftNavigation_wrap;
      var distance = slide_count * 100;

      if (!wrap.classList.contains("wrap_animated")) {
        var current_left = wrap.style.left ? parseInt(wrap.style.left, 10) : 0;
        var new_left = current_left + distance;
        var animation = animateLeft(current_left, new_left, duration);

        animation.onfinish = function () {
          wrap.style.left = new_left + "%";
          var NavLeftUL_parent_ul = queryAll(".NavLeftUL_parent ul");

          if (NavLeftUL_parent_ul) {
            NavLeftUL_parent_ul.forEach(function (a) {
              a.classList.remove("NavLeftUL_expanded");
            });
          }

          if (tab != "topics" && nodes.top_liid_home)
            query("#top_liid_home").classList.add("NavTopUL_lastChild");

          loadHome(href, tab, url, text);

          wrap.classList.remove("wrap_animated");
        };
      }
    }
    // Leftnav is disabled
    else {
      if (tab != "topics" && nodes.top_liid_home)
        query("#top_liid_home").classList.add("NavTopUL_lastChild");

      loadHome(href, tab, url, text);
    }
  }

  // Load topic endpoint
  function loadEndpoint(id, this_liid, href, topic_title) {
    if (
      loadAllTopics == 1 ||
      query("#" + this_liid).classList.contains("NavLeftUL_endpoint")
    ) {
      // Check if there is a pending ajax request
      if (typeof ajax_request !== "undefined") ajax_request.abort();

      if (nodes.NavLeftUL_parents) {
        nodes.NavLeftUL_parents.forEach(function (a) {
          a.classList.add("ul_loading");
        });
      }

      clearNavigationItems(["li_loading"]);

      if (nodes.fbContent_root) nodes.fbContent_root.style.display = "none";

      if (nodes.fb_loader) nodes.fb_loader.style.display = "block";

      ajax_request = Joomla.request({
        url:
          site_path +
          "index.php?option=com_faqbookpro&task=topic.getContent&id=" +
          id +
          "&tab=" +
          active_tab,
        method: "POST",
        onBefore: (xhr) => {
          query("#" + this_liid).classList.add("li_loading");
          window.history.pushState({}, document.title, href);
        },
        onSuccess: (response, xhr) => {
          query("#" + this_liid).classList.remove("li_loading");

          clearNavigationItems(["li_selected"]);

          query("#" + this_liid).classList.add("li_selected");

          if (nodes.fbContent_root) {
            nodes.fbContent_root.innerHTML = response;
            nodes.fbContent_root.style.display = "block";
          }

          if (nodes.fb_loader) nodes.fb_loader.style.display = "none";

          if (nodes.fbTopNavigation_wrap)
            nodes.fbTopNavigation_wrap.classList.remove("NavTopULloading");

          clearNavigationParents();

          // Change browser title dynamically
          document.title = topic_title;

          if (query("#" + this_liid).classList.contains("NavLeftUL_endpoint")) {
            if (nodes.show_menu_btn)
              nodes.show_menu_btn.classList.remove("active", "text-white");

            // Hide left navigation
            if (nodes.fbLeftNavigation_core)
              nodes.fbLeftNavigation_core.classList.add("fb-hidden");
          }
        },
        onError: (xhr) => {
          console.log(xhr);
        },
      });
    }
  }

  // Check whether content has loaded
  function contentLoaded() {
    var fbLeftNavigation_core = query(".fbLeftNavigation_core");
    var fbTopNavigation_wrap = query(".fbTopNavigation_wrap");
    var fbContent_paging_button = query(".fbContent_paging_button");

    var content_loaded =
      fbLeftNavigation_core.querySelectorAll(".ul_loading").length == 0 &&
      (fbTopNavigation_wrap
        ? !fbTopNavigation_wrap.classList.contains("NavTopULloading")
        : true) &&
      (fbContent_paging_button
        ? !fbContent_paging_button.classList.contains("page_loading")
        : true);

    return content_loaded;
  }

  // Get static DOM nodes
  function getStaticNodes() {
    nodes.fbTopNavigation_wrap = query(".fbTopNavigation_wrap");
    nodes.fbTopNavigation_root = query(".fbTopNavigation_root");
    nodes.top_liid_home = query("#top_liid_home");
    nodes.show_menu_btn = query("a.show_menu");
    nodes.fbLeftNavigation_core = query(".fbLeftNavigation_core");
    nodes.fbLeftNavigation_wrap = query(".fbLeftNavigation_wrap");
    nodes.NavLeftUL_parents = queryAll(".NavLeftUL_parent");
    nodes.NavLeftUL_items = queryAll(".NavLeftUL_item");
    nodes.NavLeftUL_anchors = queryAll("a.NavLeftUL_anchor");
    nodes.fbContent_core = query(".fbContent_core");
    nodes.fbContent_root = query(".fbContent_root");
    nodes.fb_loader = query(".fb_loader");
  }

  var getParents = function (elem, selector) {
    // Element.matches() polyfill
    if (!Element.prototype.matches) {
      Element.prototype.matches =
        Element.prototype.matchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector ||
        Element.prototype.oMatchesSelector ||
        function (s) {
          var matches = (this.document || this.ownerDocument).querySelectorAll(
              s
            ),
            i = matches.length;

          while (--i >= 0 && matches.item(i) !== this) {}

          return i > -1;
        };
    }

    // Set up a parent array
    var parents = [];

    // Push each parent element to the array
    for (; elem && elem !== document; elem = elem.parentNode) {
      if (selector) {
        if (elem.matches(selector)) parents.push(elem);

        continue;
      }

      parents.push(elem);
    }

    // Return our parent array
    return parents;
  };

  function insertAfter(referenceNode, newNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
  }

  // Fix leftnav height
  window.addEventListener("load", function () {
    if (leftnav && (page_view == "topic" || page_view == "question")) {
      // Show left navigation before calculating height
      if (nodes.fbLeftNavigation_core)
        nodes.fbLeftNavigation_core.classList.remove("fb-hidden");

      // Fix left navigation topics height
      var liid_topicId = query("#liid" + topicId);

      if (liid_topicId.classList.contains("NavLeftUL_endpoint"))
        var parent_ul = liid_topicId.parentNode;
      else var parent_ul = liid_topicId.querySelector("ul.NavLeftUL_sublist");

      var vheight = parseFloat(parent_ul.clientHeight);

      if (nodes.fbLeftNavigation_wrap)
        nodes.fbLeftNavigation_wrap.style.height = vheight + "px";

      // Hide left navigation
      if (nodes.fbLeftNavigation_core)
        nodes.fbLeftNavigation_core.classList.add("fb-hidden");

      if (nodes.show_menu_btn)
        nodes.show_menu_btn.classList.remove("fb-active");
    } else if (
      page_view == "section" ||
      page_view == "profile" ||
      page_view == "myquestion" ||
      page_view == "myanswer"
    ) {
      // Fix left navigation topics height
      if (nodes.fbLeftNavigation_wrap)
        nodes.fbLeftNavigation_wrap.style.height = "auto";
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    getStaticNodes();

    // Topic / Question view
    if (page_view == "topic" || page_view == "question") {
      var liid_topicId = query("#liid" + topicId);

      // Active left navigation li
      liid_topicId.classList.add("li_selected");

      // Active left navigation ul parents
      getParents(liid_topicId, "ul.NavLeftUL_sublist").forEach(function (a) {
        a.classList.add("NavLeftUL_expanded");
      });

      if (liid_topicId.querySelector("ul.NavLeftUL_sublist"))
        liid_topicId
          .querySelector("ul.NavLeftUL_sublist")
          .classList.add("NavLeftUL_expanded");

      var parent_ul_class = liid_topicId.parentNode.getAttribute("class");

      if (parent_ul_class != "NavLeftUL_parent") {
        var ul_level = parent_ul_class.split(" ")[1];
        var ul_level_num = ul_level.substring(
          ul_level.lastIndexOf("level") + 5
        );

        // Endpoint topic - we don't want to see the children topics
        if (liid_topicId.classList.contains("NavLeftUL_endpoint"))
          var move_level_num = parseInt(ul_level_num, 10);
        // We want to see the chidlren topics of selected topic, so we need one more level
        else var move_level_num = parseInt(ul_level_num, 10) + 1;

        var move_ul = parseInt(move_level_num, 10) * 100;
        nodes.fbLeftNavigation_wrap.style.left = "-" + move_ul + "%";

        // Active top navigation li
        var parents_num = parseInt(ul_level_num, 10);
        var first_parent_text =
          liid_topicId.parentNode.parentNode.querySelector(
            ".NavLeftUL_anchor span.topicTitle"
          ).textContent;
        var first_parent_id;

        if (liid_topicId.parentNode.parentNode.nodeName == "LI")
          first_parent_id = liid_topicId.parentNode.parentNode.id;

        if (nodes.fbTopNavigation_root)
          nodes.fbTopNavigation_root
            .querySelector("li.NavTopUL_firstChild")
            .classList.remove("NavTopUL_lastChild");

        if (first_parent_id) {
          // Add top navigation li's
          var $id = query("#" + first_parent_id);
          var $li = $id;

          function findParents() {
            $id = $id.parentNode.parentNode;

            if ($li.parentNode.parentNode.nodeName == "LI") {
              $li = $li.parentNode.parentNode;
              var prev_parent_text = $id.querySelector(
                ".NavLeftUL_anchor span.topicTitle"
              ).textContent;
              var prev_parent_id = $li.id;
            }

            // Add topic to breadcrumbs
            var _previous = query("li.NavTopUL_firstChild");
            var _li = document.createElement("li");
            _li.id = "top_" + prev_parent_id;
            _li.classList.add(
              "NavTopUL_item",
              "NavTopUL_topic",
              "NavTopUL_parent"
            );
            _li.innerHTML =
              '<a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
              prev_parent_text +
              "</a>";

            insertAfter(_previous, _li);
          }

          // Only for level1+ ul's
          if (ul_level_num > 0) {
            for (var i = 1; i < parents_num; i++) {
              findParents();
            }

            // Add lastChild parent li in top navigation
            // Endpoint topic - add class NavTopUL_lastChild
            if (
              nodes.fbTopNavigation_root &&
              liid_topicId.classList.contains("NavLeftUL_endpoint")
            ) {
              nodes.fbTopNavigation_root.innerHTML +=
                '<li id="top_' +
                first_parent_id +
                '" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
                first_parent_text +
                "</a></li>";
            }
            // Not endpoint topic - don't add class NavTopUL_lastChild
            else {
              nodes.fbTopNavigation_root.innerHTML +=
                '<li id="top_' +
                first_parent_id +
                '" class="NavTopUL_item NavTopUL_topic NavTopUL_parent"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
                first_parent_text +
                "</a></li>";
            }
          }
        }

        // Add lastChild li in top navigation
        var last_topic_text = liid_topicId.querySelector(
          ".NavLeftUL_anchor span.topicTitle"
        ).textContent;

        // Endpoint topic - don't add class NavTopUL_parent
        if (
          nodes.fbTopNavigation_root &&
          liid_topicId.classList.contains("NavLeftUL_endpoint")
        ) {
          nodes.fbTopNavigation_root.innerHTML +=
            '<li id="top_liid' +
            topicId +
            '" class="NavTopUL_item NavTopUL_topic NavTopUL_endpoint NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
            last_topic_text +
            "</a></li>";
        }
        // Endpoint topic - add class NavTopUL_parent
        else {
          nodes.fbTopNavigation_root.innerHTML +=
            '<li id="top_liid' +
            topicId +
            '" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
            last_topic_text +
            "</a></li>";
        }
      }
    }

    // Hide/Show menu button / Show left navigation
    if (nodes.show_menu_btn) {
      nodes.show_menu_btn.addEventListener("click", function (e) {
        e.preventDefault();

        this.classList.toggle("active");
        this.classList.toggle("text-white");

        if (nodes.fbLeftNavigation_core)
          nodes.fbLeftNavigation_core.classList.toggle("fb-hidden");
      });
    }

    // Left navigation links
    if (nodes.NavLeftUL_anchors) {
      nodes.NavLeftUL_anchors.forEach(function (a) {
        a.addEventListener("click", function (e) {
          e.preventDefault();

          if (contentLoaded()) {
            // Topic links
            if (
              e.target &&
              e.target.closest(".NavLeftUL_topic") &&
              !e.target.closest(".NavLeftUL_backItem")
            ) {
              if (
                e.target.classList.contains("topicTitle") ||
                e.target.classList.contains("NavLeftUL_navIcon")
              )
                var _this = e.target.parentNode;
              else var _this = e.target;

              var this_liid = _this.closest("li").id;
              var endpoint_liid = _this.closest("li").id;
              var endpoint_id = endpoint_liid.split("id").pop(1);
              var href = _this.href;
              var topic_title = _this.querySelector(".topicTitle").textContent;

              // Remove browse topics li from top navigation
              if (query(".NavTopUL_topics")) query(".NavTopUL_topics").remove();

              // Slide menu only if not endpoint
              if (
                !_this.closest("li").classList.contains("NavLeftUL_endpoint")
              ) {
                query("#" + this_liid)
                  .querySelector("ul")
                  .classList.add("NavLeftUL_expanded");

                // Fix left navigation topics height
                var parent_li = _this.parentNode;
                var child_ul = parent_li.querySelector("ul");

                if (child_ul) {
                  var eheight = parseFloat(child_ul.clientHeight);

                  if (nodes.fbLeftNavigation_wrap)
                    nodes.fbLeftNavigation_wrap.style.height = eheight + "px";
                }

                var wrap = nodes.fbLeftNavigation_wrap;
                var current_left = wrap.style.left
                  ? parseInt(wrap.style.left, 10)
                  : 0;
                var new_left = current_left - 100;
                var animation = animateLeft(current_left, new_left, duration);

                animation.onfinish = function () {
                  wrap.style.left = new_left + "%";

                  if (nodes.fbTopNavigation_root) {
                    nodes.fbTopNavigation_root
                      .querySelectorAll("li")
                      .forEach(function (a) {
                        // Remove last child class
                        a.classList.remove("NavTopUL_lastChild");

                        // Remove last endpoint
                        if (a.classList.contains("NavTopUL_endpoint"))
                          a.remove();
                      });

                    // Add topic to breadcrumbs
                    var this_title = query("#" + this_liid).querySelector(
                      ".topicTitle"
                    ).textContent;
                    nodes.fbTopNavigation_root.innerHTML +=
                      '<li id="top_' +
                      this_liid +
                      '" class="NavTopUL_item NavTopUL_topic NavTopUL_parent NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
                      this_title +
                      "</a></li>";
                  }

                  if (nodes.fbTopNavigation_wrap)
                    nodes.fbTopNavigation_wrap.classList.remove(
                      "NavTopULloading"
                    );

                  loadEndpoint(endpoint_id, this_liid, href, topic_title);

                  wrap.classList.remove("wrap_animated");
                };
              } else {
                var this_title = query("#" + this_liid).querySelector(
                  ".topicTitle"
                ).textContent;

                // Remove lastchild class from section li
                if (nodes.fbTopNavigation_root) {
                  nodes.fbTopNavigation_root
                    .querySelector("li.NavTopUL_section")
                    .classList.remove("NavTopUL_lastChild");

                  // Remove last endpoint
                  nodes.fbTopNavigation_root
                    .querySelectorAll("li.NavTopUL_endpoint")
                    .forEach(function (a) {
                      a.remove();
                    });

                  // Add endpoint topic to breadcrumbs
                  nodes.fbTopNavigation_root.innerHTML +=
                    '<li id="top_' +
                    this_liid +
                    '" class="NavTopUL_item NavTopUL_topic NavTopUL_endpoint NavTopUL_lastChild"><a class="NavTopUL_link" href="#" onclick="return false;"><i class="fas fa-caret-right"></i>' +
                    this_title +
                    "</a></li>";

                  if (nodes.fbTopNavigation_wrap)
                    nodes.fbTopNavigation_wrap.classList.remove(
                      "NavTopULloading"
                    );
                }

                loadEndpoint(endpoint_id, this_liid, href, topic_title);
              }
            }

            // Back link
            if (e.target && e.target.closest(".NavLeftUL_backItem")) {
              var _this = e.target;
              var endpoint_id = _this.parentNode.getAttribute("data-parent");
              let this_liid = "liid" + endpoint_id;
              var href = _this.href;
              let topic_title = _this.getAttribute("data-title");

              // Fix left navigation topics height
              var back_child_ul =
                _this.parentNode.parentNode.parentNode.parentNode;

              if (back_child_ul) {
                var wheight = parseFloat(back_child_ul.clientHeight);

                if (nodes.fbLeftNavigation_wrap)
                  nodes.fbLeftNavigation_wrap.style.height = wheight + "px";
              }

              var wrap = nodes.fbLeftNavigation_wrap;
              var current_left = wrap.style.left
                ? parseInt(wrap.style.left, 10)
                : 0;
              var new_left = current_left + 100;
              var animation = animateLeft(current_left, new_left, duration);

              animation.onfinish = function () {
                wrap.style.left = new_left + "%";
                _this.closest("ul").classList.remove("NavLeftUL_expanded");

                if (nodes.fbTopNavigation_root) {
                  // Remove last endpoint
                  nodes.fbTopNavigation_root
                    .querySelectorAll("li.NavTopUL_lastChild")
                    .forEach(function (a) {
                      a.remove();
                    });

                  if (nodes.fbTopNavigation_root.lastChild.classList) {
                    nodes.fbTopNavigation_root.lastChild.classList.add(
                      "NavTopUL_lastChild"
                    );
                  }
                }

                // Load parent topic if it exists
                if (endpoint_id > 1)
                  loadEndpoint(endpoint_id, this_liid, href, topic_title);
                else loadHome(href);

                wrap.classList.remove("wrap_animated");
              };
            }

            // Browse topics link
            if (e.target && e.target.closest(".NavLeftUL_tab")) {
              var _this = e.target.closest(".NavLeftUL_tab");
              var href = e.target.href;
              var tab = _this.getAttribute("data-tab");
              var text = _this.getAttribute("data-text");

              if (nodes.fbTopNavigation_wrap)
                nodes.fbTopNavigation_wrap.classList.remove("NavTopULloading");

              clearNavigationItems(["li_loading"]);

              _this.classList.add("li_loading");
              var url =
                site_path +
                "index.php?option=com_faqbookpro&task=section.getContent&sectionId=" +
                sectionId +
                "&tab=" +
                tab;

              resetTopics(href, tab, url, text);
            }
          }
        });
      });
    }

    // Top navigation links
    if (nodes.fbTopNavigation_root) {
      nodes.fbTopNavigation_root.addEventListener("click", function (e) {
        if (
          e.target &&
          e.target.classList.contains("NavTopUL_link") &&
          !e.target.classList.contains("NavTopUL_sections")
        ) {
          e.preventDefault();

          var _this = e.target;

          if (
            loadAllTopics == 0 &&
            _this.parentNode.classList.contains("NavTopUL_parent")
          )
            return;

          // If in question/answer form, treat the section link as a normal link
          if (page_view == "myquestion" || page_view == "myanswer") {
            if (_this.parentNode.id == "top_liid_home")
              window.location.href = _this.href;

            return;
          }

          // Topic links
          if (
            _this.parentNode.classList.contains("NavTopUL_parent") &&
            contentLoaded()
          ) {
            var NavTopUL_parents =
              nodes.fbTopNavigation_root.querySelectorAll(".NavTopUL_parent");
            var top_li_count = NavTopUL_parents.length;
            var li_index = [].slice
              .call(NavTopUL_parents)
              .indexOf(_this.parentNode);
            var slide_count =
              parseInt(top_li_count, 10) - parseInt(li_index, 10) - 1;

            // Remove li after specific index
            nodes.fbTopNavigation_root
              .querySelectorAll(".NavTopUL_topic")
              .forEach(function (a, i) {
                if (i > li_index) a.remove();
              });

            _this.parentNode.classList.add("NavTopUL_lastChild");

            var li_id = _this.parentNode.id.split("_").pop(0);
            var leftnav_li = query("#" + li_id);

            // Fix left navigation topics height
            var child_ul = leftnav_li.querySelector("ul");
            var eheight = parseFloat(child_ul.clientHeight);

            if (nodes.fbLeftNavigation_wrap)
              nodes.fbLeftNavigation_wrap.style.height = eheight + "px";

            var wrap = nodes.fbLeftNavigation_wrap;
            var distance = slide_count * 100;
            var _duration = duration; // reset duration

            if (!wrap.classList.contains("wrap_animated")) {
              // If leftnav animation is not required, don't wait for animation
              if (distance <= 0) _duration = 0;

              var current_left = wrap.style.left
                ? parseInt(wrap.style.left, 10)
                : 0;
              var new_left = current_left + distance;
              var animation = animateLeft(current_left, new_left, _duration);

              animation.onfinish = function () {
                wrap.style.left = new_left + "%";

                if (leftnav_li.querySelector("ul").querySelector("ul"))
                  leftnav_li
                    .querySelector("ul")
                    .querySelector("ul")
                    .classList.remove("NavLeftUL_expanded");

                if (nodes.fbTopNavigation_wrap)
                  nodes.fbTopNavigation_wrap.classList.add("NavTopULloading");

                var topic_id = li_id.split("id").pop(1);
                var href = leftnav_li.querySelector("a").href;
                var topic_title =
                  leftnav_li.querySelector("a span.topicTitle").textContent;

                loadEndpoint(topic_id, li_id, href, topic_title);

                wrap.classList.remove("wrap_animated");
              };
            }
          }

          // Home link
          if (
            _this.parentNode.classList.contains("NavTopUL_section") &&
            contentLoaded()
          ) {
            if (nodes.fbTopNavigation_wrap)
              nodes.fbTopNavigation_wrap.classList.add("NavTopULloading");

            resetTopics(_this.href);
          }
        }
      });
    }

    nodes.fbContent_core.addEventListener("click", function (e) {
      // Ajax pagination - Topic
      if (
        e.target &&
        (e.target.classList.contains("fbContent_topic_paging") ||
          e.target.closest(".fbContent_topic_paging"))
      ) {
        e.preventDefault();

        if (
          e.target.classList.contains("fbContent_btn_disabled") ||
          e.target.classList.contains("fbContent_noresults") ||
          e.target.classList.contains("fbContent_paging_loader")
        )
          return false;

        if (e.target.classList.contains("fbContent_topic_paging"))
          var _this = e.target;
        else if (e.target.classList.contains("fbContent_paging_text"))
          var _this = e.target.parentNode;

        if (contentLoaded()) {
          // Check if there is a pending ajax request
          if (typeof ajax_request !== "undefined") ajax_request.abort();

          _this.classList.add("page_loading", "fbContent_btn_disabled");
          _this.querySelector(".fbContent_paging_text").style.display = "none";
          _this.querySelector(".fbContent_paging_loader").style.display =
            "inline-block";

          clearNavigation();

          var page = parseInt(_this.getAttribute("data-page"), 10);
          var tab = _this.getAttribute("data-tab");
          var sectionId = options.sectionId;
          var topicId = parseInt(_this.getAttribute("data-topic"), 10);
          var url;

          if (_this.classList.contains("fbContent_section_paging"))
            url =
              site_path +
              "index.php?option=com_faqbookpro&task=section.getContent&sectionId=" +
              sectionId +
              "&topicId=" +
              topicId +
              "&tab=" +
              tab +
              "&page=" +
              page;
          else
            url =
              site_path +
              "index.php?option=com_faqbookpro&task=topic.getContent&id=" +
              topicId +
              "&tab=" +
              tab +
              "&page=" +
              page;

          ajax_request = Joomla.request({
            url: url,
            method: "POST",
            onSuccess: (response, xhr) => {
              var fbPaging_topicId = query("#fbPaging_" + topicId);
              var fbTopic_topicId = query("#fbTopic_" + topicId);

              fbPaging_topicId.querySelector(
                ".fbContent_paging_loader"
              ).style.display = "none";

              if (response) {
                fbTopic_topicId.querySelector(".fbContent_qList").innerHTML +=
                  response;
                fbPaging_topicId.querySelector(
                  ".fbContent_paging_text"
                ).style.display = "block";
                fbPaging_topicId
                  .querySelector(".fbContent_paging_button")
                  .classList.remove("fbContent_btn_disabled");
                var endofitems = fbTopic_topicId.querySelector(".endofitems");

                if (endofitems) {
                  fbPaging_topicId.querySelector(
                    ".fbContent_paging_text"
                  ).style.display = "none";
                  fbPaging_topicId.querySelector(
                    ".fbContent_noresults"
                  ).style.display = "block";
                  fbPaging_topicId
                    .querySelector(".fbContent_paging_button")
                    .classList.add("fbContent_btn_disabled");
                } else {
                  // Increment data-page
                  var new_page = page + 1;
                  fbPaging_topicId
                    .querySelector(".fbContent_paging_button")
                    .setAttribute("data-page", new_page);
                }
              } else {
                fbPaging_topicId.querySelector(
                  ".fbContent_paging_text"
                ).style.display = "none";
                fbPaging_topicId.querySelector(
                  ".fbContent_noresults"
                ).style.display = "block";
                fbPaging_topicId
                  .querySelector(".fbContent_paging_button")
                  .classList.add("fbContent_btn_disabled");
              }

              fbPaging_topicId
                .querySelector(".fbContent_paging_button")
                .classList.remove("page_loading");
            },
            onError: (xhr) => {
              console.log(xhr);
            },
          });
        }
      }

      // Toggle FAQs
      if (
        e.target &&
        (e.target.classList.contains("qFaqItem_link") ||
          e.target.closest(".qFaqItem_link"))
      ) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var _this = e.target;

        if (
          _this
            .closest(".fbContent_qFaqItemInner")
            .classList.contains("faq_open")
        ) {
          _this
            .closest(".fbContent_qFaqItemInner")
            .classList.remove("faq_open");
          _this
            .closest(".fbContent_qFaqItemInner")
            .querySelector(".qFaqItem_triangle .fas")
            .classList.replace("fa-caret-down", "fa-caret-right");
        } else {
          _this.closest(".fbContent_qFaqItemInner").classList.add("faq_open");
          _this
            .closest(".fbContent_qFaqItemInner")
            .querySelector(".qFaqItem_triangle .fas")
            .classList.replace("fa-caret-right", "fa-caret-down");
        }
      }
    });

    // Toggle left navigation
    if (nodes.fbLeftNavigation_core) {
      nodes.fbLeftNavigation_core.addEventListener("click", function (e) {
        if (
          e.target &&
          (e.target.classList.contains("NavLeftUL_toggle") ||
            e.target.closest(".NavLeftUL_toggle"))
        ) {
          e.preventDefault();

          if (contentLoaded()) {
            // Check if there is a pending ajax request
            if (typeof ajax_request !== "undefined") ajax_request.abort();

            nodes.fbLeftNavigation_core.classList.toggle("fb-minimized");

            if (
              nodes.fbLeftNavigation_core.classList.contains("fb-minimized")
            ) {
              query(".fbLeftNavigation_toggle .NavLeftUL_toggle").innerHTML =
                '<i class="fas fa-angle-double-right"></i>';
              var minimized = "on";
            } else {
              query(".fbLeftNavigation_toggle .NavLeftUL_toggle").innerHTML =
                '<i class="fas fa-angle-double-left"></i>';
              var minimized = "off";
            }

            ajax_request = Joomla.request({
              url:
                site_path +
                "index.php?option=com_faqbookpro&task=section.toggleLeftnav&minimized=" +
                minimized,
              method: "POST",
              onSuccess: (response, xhr) => {},
              onError: (xhr) => {
                console.log(xhr);
              },
            });
          }
        }
      });
    }
  });
})(document, Joomla);
