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
	}
};

var RuleManager = {
	formId: 0,
	modal: null,
	selects: [],
	init: function(formId){
		this.formId = formId;
		this.modal = jQuery("#rule-add");
	},
	loadFormView: function(){
		jQuery.ajax({
			url: "./manage/form/view/"+RuleManager.formId,
			success: function(html){
				jQuery("#shadow-form").html(html);
				RuleManager.readFormSelects();
				RuleManager.onReady();
			}
		});
	},
	onReady: function(){
		for(var i=0; i<3; i++){
			var selectKey = RuleManager.modal.find("select#input_ruleKey_"+i);
			var selectValue = RuleManager.modal.find("select#input_ruleValue_"+i);
			selectKey.bind("change", {i: i}, function(event){
				RuleManager.onRuleKeyChange(event.data.i);
			});
			selectValue.bind("change", {i: i}, function(event){
				RuleManager.onRuleValueChange(event.data.i);
			});
			for(var j=0; j<RuleManager.selects.length; j++){
				var option = jQuery("<option></option>");
				option.attr("value", RuleManager.selects[j].name);
				option.html(RuleManager.selects[j].label);
//				option.data("current", RuleManager.selects[j]);
				selectKey.append(option);
			}
		}
	},
	getCurrentSelect: function(i){
		var selectKey = RuleManager.modal.find("select#input_ruleKey_"+i).val();
		console.log("SEARCH: " + selectKey);
		for(var j=0; j<RuleManager.selects.length; j++){
			if(RuleManager.selects[j].name == selectKey){
				console.log("FOUND: " + selectKey);
				return RuleManager.selects[j];		
			}
		}
		return null;
	},
	onRuleKeyChange: function(i){
		console.log("onRuleKeyChange:"+i);
		var selectKey = RuleManager.modal.find("select#input_ruleKey_"+i);
		var selectValue = RuleManager.modal.find("select#input_ruleValue_"+i);
		var current = RuleManager.getCurrentSelect(i);
		RuleManager.modal.find("input#input_ruleKeyLabel_"+i).val(current.label);
		for(var i=0; i<current.values.length; i++){
			var option = jQuery("<option></option>");
			option.attr("value", current.values[i].value);
			option.html(current.values[i].label);
			selectValue.append(option);
		}
	},
	onRuleValueChange: function(i){
		var selectKey = RuleManager.modal.find("select#input_ruleKey_"+i);
		var selectValue = RuleManager.modal.find("select#input_ruleValue_"+i);
		var current = RuleManager.getCurrentSelect(i);
		for(var j=0; j<current.values.length; j++)
			if(current.values[j].value == selectValue.val())
				RuleManager.modal.find("input#input_ruleValueLabel_"+i).val(current.values[j].label);
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
