/*jslint browser: true */
var jQuery;																		//  define jQuery to be sure
if (!jQuery) {throw "LocalServerFrontendController is missing jQuery";}		//  throw exception if jQuery is missing
var LocalServerFrontendController = {											//  define controller object
	idInputForm: "form_browser",												//  ID if form
	idInputController: "input_controller",										//  ID if controller input field
	idInputAction: "input_action",												//  ID if action input field
	idInputPath: "input_path",													//  ID if path input field
	idInputParam: "input_parameters",											//  ID if parameters input field
	idInputToken: "input_token",												//  ID if token input field
	idInputPost: "input_post",													//  ID if post input field
	init: function () {
		"use strict";															//  use strict mode
		var	browser = this,														//  shortcut browser object
			form = jQuery("#" + this.idInputForm),								//  find form
			link = $("<a></a>").attr('href',document.location.href);			//  link to requested URL
		form.bind("submit", function () {return browser.request();});			//  call request on form submit, prevent default form submit event if request uses GET
		form.find("#" + browser.idInputController).bind("change", function () {	//  if controller value has been changed
			form.find("#" + browser.idInputAction).val("index");				//  clear action value
			form.find("#" + browser.idInputPath).val("");						//  clear path value
			browser.request();													//  request form using GET
		});
		form.find("#" + browser.idInputAction).bind("change", function () {		//  if action value has been changed
			form.find("#" + browser.idInputPath).val("");						//  clear path value
			browser.request();													//  request form using GET
		});
		$("#data-url").html(link.html($("#data-url").html()));					//  link self
	},
	request: function () {
		"use strict";															//  use strict mode
		var	i, input,															//  declare variables
			form = jQuery("#" + this.idInputForm),								//  find form
			controller = form.find("#" + this.idInputController).val(),
			uri = './',
			action = form.find("#" + this.idInputAction).val(),					//  get action value
			path = form.find("#" + this.idInputPath).val(),						//  get path value
			token = form.find("#" + this.idInputToken).val(),					//  get token string
			post = jQuery.deparam(form.find("#" + this.idInputPost).val()),		//  get POST pairs
			param = token ? {token: token} : {};								//  @todo implement "parameters" support = several input fields for each parameter
		uri += controller;//.toLowerCase().replace(/_/, '/');
		uri += action ? '/' + action : '';										//  append action to URL if selected
		uri += path ? '/' + path : '';											//  append path to URL if defined
		param	 = jQuery.param(param);											//  decode POST pairs string to pairs object
		if (!post.length) {															//  no POST pairs -> do GET request @todo implement GET/POST switch, maybe by paying attention to pressed button (top=GET, bottom=POST)
			document.location.href = uri + (param ? '?' + param : '');			//  realize GET request
			return false;														//  avoid submit of HTML form
		}
		form.find("input.hidden").remove();										//  remove hidden fields generated on last request
		form.attr("action", uri + (param ? '?' + param : ''));					//  update form action URL
		for (i in post) {														//  iterate POST pairs parsed from POST pairs string
			if (post.hasOwnProperty(i)) {
				input = jQuery("<input></input>").attr("name", i).val(post[i]);	//  create an input field with name and value from POST pair
				input.addClass("hidden").attr("type", "hidden");				//  hide input field and mark for removal on next request
				form.append(input);												//  insert new hidden field in form
			}
		}
		return true;
	}
};
