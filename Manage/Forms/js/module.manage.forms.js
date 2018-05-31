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
	selects: [],
	init: function(formId){
		this.formId = formId;
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
		var modal = jQuery("#rule-add");
		for(var i=0; i<3; i++){
			var selectKey = modal.find("select#input_ruleKey_"+i);
			var selectValue = modal.find("select#input_ruleValue_"+i);
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
				selectKey.append(option);
				selectKey.data("current", RuleManager.selects[j]);
			}
		}
	},
	onRuleKeyChange: function(i){
		console.log("onRuleKeyChange:"+i);
		var modal = jQuery("#rule-add");
		var selectKey = modal.find("select#input_ruleKey_"+i);
		var selectValue = modal.find("select#input_ruleValue_"+i);
		var current = selectKey.data("current");
		modal.find("input#input_ruleKeyLabel_"+i).val(current.label);
		for(var i=0; i<current.values.length; i++){
			var option = jQuery("<option></option>");
			option.attr("value", current.values[i].value);
			option.html(current.values[i].label);
			selectValue.append(option);
		}
	},

	onRuleValueChange: function(i){
		var modal = jQuery("#rule-add");
		var selectKey = modal.find("select#input_ruleKey_"+i);
		var selectValue = modal.find("select#input_ruleValue_"+i);
		var current = selectKey.data("current");
		for(var j=0; j<current.values.length; j++)
			if(current.values[j].value == selectValue.val())
				modal.find("input#input_ruleValueLabel_"+i).val(current.values[j].label);
	},
/*	noteRule: function(){
		var modal = jQuery("#rule-add");
		var selectKey = modal.find("select#input_ruleKey");
		var selectValue = modal.find("select#input_ruleValue");
		var current = selectKey.data("current");
		for(var i=0; i<current.values.length; i++){
			if(current.values[i].value == selectValue.val()){
				current.valueLabel = current.values[i].label;
			}
		}
		var data = {
			name: current.name,
			value: selectValue.val(),
			nameLabel: current.label,
			valueLabel: current.valueLabel,
		};
		console.log(data);
	},*/
/*	renderRule: function(){

	},*/
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
