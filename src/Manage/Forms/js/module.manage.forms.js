// noinspection JSUnusedGlobalSymbols

let FormEditor = {
	applyAceEditor: function(selector, options){
		options = jQuery.extend({
			options: {
				lineHeight: 1.2,
				maxLines: 30
			},
			flags: {
				fontSize: 14
			}
		}, options);
		ModuleAce.applyTo(selector, options);
/*		var options = jQuery.extend({
			minLines: 15,
			maxLines: 35,
		}, options);
		var textarea = jQuery(selector).hide();
		var editor = ace.edit("content_editor", options);
		editor.getSession().setValue(textarea.val());
		editor.setFontSize(14);
		editor.session.on("change", function(){
			var value = editor.getSession().getValue();
			textarea.val(value);
		});
		textarea.data('ace-editor', editor);*/
	},
	initTabs: function(){
		jQuery("#tabs-form>li>a").on("click", function(){
			let tabId = jQuery(this).attr("href").replace(/^#/, "");
			jQuery.ajax({
				url: "./manage/form/setTab/'.$form->formId.'/"+tabId
			});
		})
	}
};

let RuleManager = {
	formId: 0,
	modal: null,
	selects: [],
	init: function(formId){
		this.formId = formId;
		this.modalManager = jQuery("#rule-manager-add");
		this.modalCustomer = jQuery("#rule-customer-add");
		this.modalAttachment = jQuery("#rule-attachment-add");
	},
	loadFormView: function(){
		let shadowForm = jQuery("#shadow-form");
		jQuery.ajax({
			url: "./manage/form/view/"+RuleManager.formId+"/extended",
			success: function(html){
				shadowForm.html(html);
				shadowForm.find("button[type=submit]").parent().remove();
				RuleManager.connectBlocksWithinFormView();
				RuleManager.readFormSelects();
				RuleManager.onReady();
			}
		});
		jQuery("#show-blocks").on("change", function(){
			shadowForm.find(".form-view-block").removeClass("show");
			if(jQuery(this).is(":checked"))
				jQuery("#shadow-form .form-view-block").addClass("show");
		});
	},
	connectBlocksWithinFormView: function(){
		jQuery("#shadow-form .form-view-block").on("mouseenter", function(event){
			let block		= jQuery(this);
//			let identifier	= block.data('identifier');
			let link		= jQuery("<a></a>")
				.html(block.data('identifier'))
				.html(block.data('title'))
				.addClass("form-block-link")
				.attr("href", "./manage/form/block/edit/"+block.data('blockId'));
			block.prepend(link);
		}).on("mouseleave", function(event){
			jQuery(this).children(".form-block-link").remove();
		});
		jQuery("#list-blocks-within").find("a").on("mouseenter", function(){
			let link = jQuery(this);
			let identifier	= link.data('identifier');
			jQuery("#shadow-form .form-view-block[data-identifier='"+identifier+"']")
				.addClass("focus")
				.trigger("mouseenter");
		}).on("mouseleave", function(event){
			jQuery("#shadow-form .form-view-block").removeClass("focus").trigger("mouseleave");
		});
	},
	onReady: function(){
		let i, j, type, option, selectKey, selectValue;
		for(type=0; type<=2; type++){
			for(i=0; i<3; i++){
				if(type === 2){
					selectKey = RuleManager.modalAttachment.find("select#input_attachment_ruleKey_"+i);
					selectValue = RuleManager.modalAttachment.find("select#input_attachment_ruleValue_"+i);
				}
				else if(type === 1){
					selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
					selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
				}
				else if(type === 0){
					selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
					selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
				}
				selectKey.on("change", {type: type, i: i}, function(event){
					RuleManager.onRuleKeyChange(event.data.type, event.data.i);
				});
				selectValue.on("change", {type: type, i: i}, function(event){
					RuleManager.onRuleValueChange(event.data.type, event.data.i);
				});
				for(j=0; j<RuleManager.selects.length; j++){
					option = jQuery("<option></option>");
					option.attr("value", RuleManager.selects[j].name);
					option.html(RuleManager.selects[j].label);
	//				option.data("current", RuleManager.selects[j]);
					selectKey.append(option);
				}
			}
		}
	},
	getCurrentSelect: function(type, i){
		let j, selectKey;
		if(type === 2){
			selectKey = RuleManager.modalAttachment.find("select#input_attachment_ruleKey_"+i).val();
		}
		else if(type === 1){
			selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i).val();
		}
		else if(type === 0){
			selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i).val();
		}
//		console.log({"type": type, "row": i, "selectKey": selectKey});
		for(j=0; j<RuleManager.selects.length; j++){
			if(RuleManager.selects[j].name === selectKey){
				return RuleManager.selects[j];
			}
		}
		return null;
	},
	onRuleKeyChange: function(type, i){
		let current, selectValue;
//		console.log({on: 'onRuleKeyChange', type: type, row: i});
		current = RuleManager.getCurrentSelect(type, i);
		if(type === 2){
//			selectKey = RuleManager.modalAttachment.find("select#input_attachment_ruleKey_"+i);
			selectValue = RuleManager.modalAttachment.find("select#input_attachment_ruleValue_"+i);
			RuleManager.modalAttachment.find("input#input_attachment_ruleKeyLabel_"+i).val(current.label);
		}
		else if(type === 1){
//			selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
			selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
			RuleManager.modalManager.find("input#input_manager_ruleKeyLabel_"+i).val(current.label);
		}
		else if(type === 0){
//			selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
			selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
			RuleManager.modalCustomer.find("input#input_customer_ruleKeyLabel_"+i).val(current.label);
		}
		selectValue.html("");
		let j, option;
		for(j=0; i<current.values.length; i++){
			option = jQuery("<option></option>");
			option.attr("value", current.values[i].value);
			option.html(current.values[i].label);
			selectValue.append(option);
		}
		selectValue.trigger("change");
	},
	onRuleValueChange: function(type, i){
		let current, selectValue, j;
//		console.log({on: 'onRuleValueChange', type: type, row: i});
		current = RuleManager.getCurrentSelect(type, i);
		if(type === 2){
//			selectKey = RuleManager.modalAttachment.find("select#input_attachment_ruleKey_"+i);
			selectValue = RuleManager.modalAttachment.find("select#input_attachment_ruleValue_"+i);
			for(j=0; j<current.values.length; j++)
				if(current.values[j].value === selectValue.val())
					RuleManager.modalAttachment.find("input#input_attachment_ruleValueLabel_"+i).val(current.values[j].label);
		}
		else if(type === 1){
//			selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
			selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
			for(j=0; j<current.values.length; j++)
				if(current.values[j].value === selectValue.val())
					RuleManager.modalManager.find("input#input_manager_ruleValueLabel_"+i).val(current.values[j].label);
		}
		else if(type === 0){
//			selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
			selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
			for(j=0; j<current.values.length; j++)
				if(current.values[j].value === selectValue.val())
					RuleManager.modalCustomer.find("input#input_customer_ruleValueLabel_"+i).val(current.values[j].label);
		}
	},
	readFormSelects: function(){
		let i, j, select, map;
		RuleManager.selects	= [];
		jQuery("#shadow-form select").each(function(){
			let input = jQuery(this);
			let options = [];
			input.children("option").each(function(){
				if(jQuery(this).attr("value")){
					options.push({
						value: jQuery(this).attr("value"),
						label: jQuery(this).html(),
					});
				}
			});
			for( i=0; i<RuleManager.selects.length; i++){
				select = RuleManager.selects[i];
				if(select.id === input.attr("id")){
					map = select.values.map(function(option){return option.value});
					for(j=0; j<options.length; j++){
						if(jQuery.inArray(options[j].value, map) === -1){
							select.values.push(options[j]);
						}
					}
					return;
				}
			}
			RuleManager.selects.push({
				label: input.prev().html(),
				name: input.attr("name"),
				id: input.attr("id"),
				values: options,
			});
		});
	}
};

let FormsTransferRuleTest = {
	init: function(){
		jQuery(".button-test-rules").bind("click", function(){
			let button = jQuery(this);
			let ruleId = button.data("rule-id");
			let modal = jQuery("#rule-transfer-edit-"+ruleId);
			let rules = modal.find("#input_rules-"+ruleId).val();
			FormsTransferRuleTest.updateTransferRulesTestTrigger(ruleId, rules);
		});
	},
	testTransferRules: function(ruleId, rules, callback){
		jQuery.ajax({
			url: "./ajax/manage/form/testTransferRules",
			method: "POST",
			dataType: "json",
			data: {
				ruleId: ruleId,
				rules: rules
			},
			success: callback
		});
	},
	updateTransferRulesTestTrigger: function(ruleId, rules){
		let callback = function(json){
			let button = jQuery("#button-test-"+ruleId);
			button.prop("title", null);
			button.removeClass("btn-info btn-success btn-danger")
			if(json.status === 'data' && json.data.status !== "empty"){
				if(json.data.status === "exception" || json.data.status === "error"){
					button.addClass("btn-danger");
					button.prop("title", json.data.message);
				}
				else if(json.data.status === "success" || json.data.status === "parsed"){
					button.addClass("btn-success");
				}
			}
			button.blur();
		}
		FormsTransferRuleTest.testTransferRules(ruleId, rules, callback);
	}
}

let FormsImportRuleTest = {
	init: function(){
		jQuery(".button-test-rules").bind("click", function(){
			let button = jQuery(this);
			let ruleId = button.data("rule-id");
			let modal = jQuery("#rule-import-edit-"+ruleId);
			let rules = modal.find("#input_rules-"+ruleId).val();
			FormsImportRuleTest.updateImportRulesTestTrigger(ruleId, rules);
		});
	},
	testImportRules: function(ruleId, rules, callback){
		jQuery.ajax({
			url: "./ajax/manage/form/import/testRules",
			method: "POST",
			dataType: "json",
			data: {
				ruleId: ruleId,
				rules: rules
			},
			success: callback
		});
	},
	updateImportRulesTestTrigger: function(ruleId, rules){
		let callback = function(json){
			let button = jQuery("#button-test-"+ruleId);
			button.prop("title", null);
			button.removeClass("btn-info btn-success btn-danger")
			if(json.status !== "empty"){
				if(json.status === "exception" || json.status === "error"){
					button.addClass("btn-danger");
					button.prop("title", json.message);
				}
				else if(json.status === "success" || json.status === "parsed"){
					button.addClass("btn-success");
				}
			}
			button.blur();
		}
		FormsImportRuleTest.testImportRules(ruleId, rules, callback);
	}
}
