var FormEditor = {
	applyAceEditor: function(selector, options){
		var options = jQuery.extend({
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
		textarea.data('ace-editor', editor);
	},
	initTabs: function(){
		jQuery("#tabs-form>li>a").on("click", function(){
			var tabId = jQuery(this).attr("href").replace(/^#/, "");
			jQuery.ajax({
				url: "./manage/form/setTab/'.$form->formId.'/"+tabId
			});
		})
	}
};

var RuleManager = {
	formId: 0,
	modal: null,
	selects: [],
	init: function(formId){
		this.formId = formId;
		this.modalManager = jQuery("#rule-manager-add");
		this.modalCustomer = jQuery("#rule-customer-add");
	},
	loadFormView: function(){
		jQuery.ajax({
			url: "./manage/form/view/"+RuleManager.formId,
			success: function(html){
				jQuery("#shadow-form").html(html);
				jQuery("#shadow-form").find("button[type=submit]").parent().remove();
				RuleManager.readFormSelects();
				RuleManager.onReady();
			}
		});
	},
	onReady: function(){
		for(var type=0; type<2; type++){
			for(var i=0; i<3; i++){
				if(type === 1){
					var selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
					var selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
				}
				else if(type === 0){
					var selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
					var selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
				}
				selectKey.on("change", {type: type, i: i}, function(event){
					RuleManager.onRuleKeyChange(event.data.type, event.data.i);
				});
				selectValue.on("change", {type: type, i: i}, function(event){
					RuleManager.onRuleValueChange(event.data.type, event.data.i);
				});
				for(var j=0; j<RuleManager.selects.length; j++){
					var option = jQuery("<option></option>");
					option.attr("value", RuleManager.selects[j].name);
					option.html(RuleManager.selects[j].label);
	//				option.data("current", RuleManager.selects[j]);
					selectKey.append(option);
				}
			}
		}
	},
	getCurrentSelect: function(type, i){
		if(type === 1){
			var selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i).val();
		}
		else if(type === 0){
			var selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i).val();
		}
		console.log("SEARCH: " + selectKey);
		for(var j=0; j<RuleManager.selects.length; j++){
			if(RuleManager.selects[j].name == selectKey){
				console.log("FOUND: " + selectKey);
				return RuleManager.selects[j];
			}
		}
		return null;
	},
	onRuleKeyChange: function(type, i){
		console.log({on: 'onRuleKeyChange', type: type, row: i});
		var current = RuleManager.getCurrentSelect(type, i);
		if(type === 1){
			var selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
			var selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
			RuleManager.modalManager.find("input#input_manager_ruleKeyLabel_"+i).val(current.label);
		}
		else if(type === 0){
			var selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
			var selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
			RuleManager.modalCustomer.find("input#input_customer_ruleKeyLabel_"+i).val(current.label);
		}
		for(var i=0; i<current.values.length; i++){
			var option = jQuery("<option></option>");
			option.attr("value", current.values[i].value);
			option.html(current.values[i].label);
			selectValue.append(option);
		}
	},
	onRuleValueChange: function(type, i){
		console.log({on: 'onRuleValueChange', type: type, row: i});
		var current = RuleManager.getCurrentSelect(type, i);
		if(type === 1){
			var selectKey = RuleManager.modalManager.find("select#input_manager_ruleKey_"+i);
			var selectValue = RuleManager.modalManager.find("select#input_manager_ruleValue_"+i);
			for(var j=0; j<current.values.length; j++)
				if(current.values[j].value == selectValue.val())
					RuleManager.modalManager.find("input#input_manager_ruleValueLabel_"+i).val(current.values[j].label);
		}
		else if(type === 0){
			var selectKey = RuleManager.modalCustomer.find("select#input_customer_ruleKey_"+i);
			var selectValue = RuleManager.modalCustomer.find("select#input_customer_ruleValue_"+i);
			for(var j=0; j<current.values.length; j++)
				if(current.values[j].value == selectValue.val())
					RuleManager.modalCustomer.find("input#input_customer_ruleValueLabel_"+i).val(current.values[j].label);
		}
	},
	readFormSelects: function(){
		RuleManager.selects	= [];
		jQuery("#shadow-form select").each(function(){
			var input = jQuery(this);
			var options = [];
			input.children("option").each(function(){
				options.push({
					value: jQuery(this).attr("value"),
					label: jQuery(this).html(),
				});
			});
			RuleManager.selects.push({
				label: input.prev().html(),
				name: input.attr("name"),
				id: input.attr("id"),
				values: options,
			});
		});
	}
};
