var ModuleManageJobSchedule = {
	valuesSelected: {
		"minute": [],
		"hour": [],
		"day": [],
		"month": [],
		"weekday": []
	},
	applyCronExpressionToInputs: function(exp){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		var format = form.find(":input#input_format").val();

		if(format !== "cron-month" && format !== "cron-week")
			return;
		exp = exp.split(/ /);
		if(exp.length !== 5)
			return;

		var valueMinute		= exp[0],
			valueHour		= exp[1],
			valueDay		= exp[2],
			valueMonth		= exp[3],
			valueWeekDay	= exp[4];
		var range;
		var value;

		//  minuteOfHour
		if(jQuery.inArray(valueMinute, this.valuesSelected.minute) >= 0){
			form.find("#input_minuteOfHour").val(valueMinute);
		}
		else if(value = valueMinute.match(/^(\d+)$/)){
			console.log(value);
			form.find("#input_minuteOfHour_value").show().val(value);
			form.find("#input_minuteOfHour").val("value").trigger("change");
		}
		else if(range = valueMinute.match(/^(\d+)-(\d+)$/)){
			form.find("#input_minuteOfHour_rangeFrom").val(range[1]);
			form.find("#input_minuteOfHour_rangeTo").val(range[2]);
			form.find("#input_minuteOfHour").val("range").trigger("change");
		}
		else {
			form.find("#input_minuteOfHour_values").val(valueMinute);
			form.find("#input_minuteOfHour").val("values").trigger("change");
		}

		//  hourOfDay
		if(jQuery.inArray(valueHour, this.valuesSelected.hour) >= 0){
			form.find("#input_hourOfDay").val(valueHour);
		}
		else if(valueHour.match(/^\d+$/)){
			form.find("#input_hourOfDay_value").val(valueHour);
			form.find("#input_hourOfDay").val("value").trigger("change");
		}
		else if(range = valueHour.match(/^(\d+)-(\d+)$/)){
			form.find("#input_hourOfDay_rangeFrom").val(range[1]);
			form.find("#input_hourOfDay_rangeTo").val(range[2]);
			form.find("#input_hourOfDay").val("range").trigger("change");
		}
		else {
			form.find("#input_hourOfDay_values").val(valueHour);
			form.find("#input_hourOfDay").val("values").trigger("change");
		}

		//  dayOfMonth
		if(jQuery.inArray(valueDay, this.valuesSelected.day) >= 0){
			form.find("#input_dayOfMonth").val(valueDay);
		}
		else if(valueDay.match(/^\d+$/)){
			form.find("#input_dayOfMonth_value").val(valueDay);
			form.find("#input_dayOfMonth").val("value").trigger("change");
		}
		else if(range = valueDay.match(/^(\d+)-(\d+)$/)){
			form.find("#input_dayOfMonth_rangeFrom").val(range[1]);
			form.find("#input_dayOfMonth_rangeTo").val(range[2]);
			form.find("#input_dayOfMonth").val("range").trigger("change");
		}
		else {
			form.find("#input_dayOfMonth_values").val(valueDay);
			form.find("#input_dayOfMonth").val("values").trigger("change");
		}

		//  monthOfYear
		if(jQuery.inArray(valueMonth, this.valuesSelected.month) >= 0){
			form.find("#input_monthOfYear").val(valueMonth);
		}
		else if(valueMonth.match(/^\d+$/)){
			form.find("#input_monthOfYear_value").val(valueMonth);
			form.find("#input_monthOfYear").val("value").trigger("change");
		}
		else if(range = valueMonth.match(/^(\d+)-(\d+)$/)){
			form.find("#input_monthOfYear_rangeFrom").val(range[1]);
			form.find("#input_monthOfYear_rangeTo").val(range[2]);
			form.find("#input_monthOfYear").val("range").trigger("change");
		}
		else {
			form.find("#input_monthOfYear_values").val(valueMonth);
			form.find("#input_monthOfYear").val("values").trigger("change");
		}

		//  dayOfWeek
		if(jQuery.inArray(valueWeekDay, this.valuesSelected.weekday) >= 0){
			form.find("#input_dayOfWeek").val(valueWeekDay);
		}
		else if(valueWeekDay.match(/^\d+$/)){
			form.find("#input_dayOfWeek_value").val(valueWeekDay);
			form.find("#input_dayOfWeek").val("value").trigger("change");
		}
		else if(range = valueWeekDay.match(/^(\d+)-(\d+)$/)){
			form.find("#input_dayOfWeek_rangeFrom").val(range[1]);
			form.find("#input_dayOfWeek_rangeTo").val(range[2]);
			form.find("#input_dayOfWeek").val("range").trigger("change");
		}
		else {
			form.find("#input_dayOfWeek_values").val(valueWeekDay);
			form.find("#input_dayOfWeek").val("values").trigger("change");
		}
	},
	applyIntervalExpressionToInputs: function(exp){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		if(exp.length){
			var regex = /P((?<years>\d+)Y)?((?<months>\d+)M)?((?<days>\d+)D)?T?((?<hours>\d+)H)?((?<minutes>\d+)M)?/;
			var parts = exp.match(regex);
			if(parts.groups.years !== undefined)
				form.find("#input_years").val(parts.groups.years).trigger("change");
			if(parts.groups.months !== undefined)
				form.find("#input_months").val(parts.groups.months).trigger("change");
			if(parts.groups.days !== undefined)
				form.find("#input_days").val(parts.groups.days).trigger("change");
			if(parts.groups.hours !== undefined)
				form.find("#input_hours").val(parts.groups.hours).trigger("change");
			if(parts.groups.minutes !== undefined)
				form.find("#input_minutes").val(parts.groups.minutes).trigger("change");
		}
	},
	applyDatetimeExpressionToInputs: function(exp){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");

		var parts = exp.split(/ /);
		if(parts.length === 2){
			form.find("#input_date").val(parts[0]).trigger("change");
			form.find("#input_time").val(parts[1]).trigger("change");
		}
	},
	getCronExpressionFromInputs: function(){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		var format = form.find(":input#input_format").val();
		var exp = "";

		if(format !== "cron-month" && format !== "cron-week")
			return "";

		//  minuteOfHour
		var valueMinute = form.find("#input_minuteOfHour").val();
		if(valueMinute === "value")
			valueMinute	= form.find("#input_minuteOfHour_value").val();
		else if(valueMinute === "values")
			valueMinute	= form.find("#input_minuteOfHour_values").val();
		else if(valueMinute === "range"){
			var minuteRangeFrom	= form.find("#input_minuteOfHour_rangeFrom").val();
			var minuteRangeTo	= form.find("#input_minuteOfHour_rangeTo").val();
			valueMinute	= minuteRangeFrom + "-" + minuteRangeTo;
		}

		//  hourOfDay
		var valueHour = form.find("#input_hourOfDay").val();
		if(valueHour === "value")
			valueHour	= form.find("#input_hourOfDay_value").val();
		else if(valueHour === "values")
			valueHour	= form.find("#input_hourOfDay_values").val();
		else if(valueHour === "range"){
			var hourRangeFrom	= form.find("#input_hourOfDay_rangeFrom").val();
			var hourRangeTo		= form.find("#input_hourOfDay_rangeTo").val();
			valueHour	= hourRangeFrom + "-" + hourRangeTo;
		}

		var valueWeekDay ="*";
		var valueMonthDay ="*";
		if(format === "cron-month"){
			//  dayOfMonth
			var valueMonthDay = form.find("#input_dayOfMonth").val();
			if(valueMonthDay === "value")
				valueMonthDay	= form.find("#input_dayOfMonth_value").val();
			else if(valueMonthDay === "values")
				valueMonthDay	= form.find("#input_dayOfMonth_values").val();
			else if(valueMonthDay === "range"){
				var dayRangeFrom	= form.find("#input_dayOfMonth_rangeFrom").val();
				var dayRangeTo	= form.find("#input_dayOfMonth_rangeTo").val();
				valueMonthDay	= dayRangeFrom + "-" + dayRangeTo;
			}
		}
		else if(format === "cron-week"){
			//  dayOfWeek
			var valueWeekDay = form.find("#input_dayOfWeek").val();
			if(valueWeekDay === "value")
				valueWeekDay	= form.find("#input_dayOfWeek_value").val();
			else if(valueWeekDay === "values")
				valueWeekDay	= form.find("#input_dayOfWeek_values").val();
			else if(valueWeekDay === "range"){
				var weekdayRangeFrom	= form.find("#input_dayOfWeek_rangeFrom").val();
				var weekdayRangeTo	= form.find("#input_dayOfWeek_rangeTo").val();
				valueWeekDay	= weekdayRangeFrom + "-" + weekdayRangeTo;
			}
		}

		//  monthOfYear
		valueMonth = form.find("#input_monthOfYear").val();
		if(valueMonth === "value")
			valueMonth	= form.find("#input_monthOfYear_value").val();
		else if(valueMonth === "values")
			valueMonth	= form.find("#input_monthOfYear_values").val();
		else if(valueMonth === "range"){
			var monthRangeFrom	= form.find("#input_monthOfYear_rangeFrom").val();
			var monthRangeTo	= form.find("#input_monthOfYear_rangeTo").val();
			valueMonth	= monthRangeFrom + "-" + monthRangeTo;
		}

/*		console.log({
			format: format,
			valueMinute: valueMinute,
			valueHour: valueHour,
			valueMonthDay: valueMonthDay,
			valueMonth: valueMonth,
			valueWeekDay: valueWeekDay
		});*/
		if(valueMinute.length
			&& valueHour.length
			&& valueMonth.length
			&& (valueMonthDay.length || valueWeekDay.length)
		){
			return valueMinute + " " + valueHour + " " + valueMonthDay + " " + valueMonth + " " + valueWeekDay;
		}
		return "";
	},
	getIntervalExpressionFromInputs: function(){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		var exp	= '';
		var valueYears		= form.find("#input_years").val();
		var valueMonths		= form.find("#input_months").val();
		var valueDays		= form.find("#input_days").val();
		var valueHours		= form.find("#input_hours").val();
		var valueMinutes	= form.find("#input_minutes").val();
		if( valueYears > 0 )
			exp += valueYears + 'Y';
		if( valueMonths > 0 )
			exp += valueMonths + 'M';
		if( valueDays > 0 )
			exp += valueDays + 'D';
		if( valueHours > 0 || valueMinutes > 0 ){
			exp	+= 'T';
			if( valueHours > 0 )
				exp += valueHours + 'H';
			if( valueMinutes > 0 )
				exp += valueMinutes + 'M';
		}
		return exp ? 'P' + exp : exp;
	},
	getDatetimeExpressionFromInputs: function(){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		var exp	= '';
		var valueDate	= form.find("#input_date").val();
		var valueTime	= form.find("#input_time").val();
		if(valueDate.length && valueTime.length)
			exp = valueDate + ' ' + valueTime;
		return exp;
	},
	init: function(){
		var form = jQuery("#formManageJobScheduleAdd,#formManageJobScheduleEdit");
		form.find(".modifiesCronExpression").change(function(){
			var exp = ModuleManageJobSchedule.getCronExpressionFromInputs();
			form.find("#container_expression_cron").html(exp);
			form.find("#input_expressionCron").val(exp);
		}).eq(0).trigger("change");

		form.find(".modifiesIntervalExpression").change(function(){
			var exp = ModuleManageJobSchedule.getIntervalExpressionFromInputs();
			form.find("#container_expression_interval").html(exp);
			form.find("#input_expressionInterval").val(exp);
		}).eq(0).trigger("change");

		form.find(".modifiesDatetimeExpression").change(function(){
			var exp = ModuleManageJobSchedule.getDatetimeExpressionFromInputs();
			form.find("#container_expression_datetime").html(exp);
			form.find("#input_expressionDatetime").val(exp);
		}).eq(0).trigger("change");



		jQuery(":input.canHaveValue").change(function(){
			var input = jQuery(this),
				parent = input.parent().parent(),
				value = input.val(),
				oldValue = input.data("oldValue");
			if(value == oldValue)
				return;
			var spanValue = parent.find("div.span_input_value");
			value == "value" ? spanValue.show() : spanValue.hide();
		});
		jQuery(":input.canHaveValues").change(function(){
			var input = jQuery(this),
				parent = input.parent().parent(),
				value = input.val(),
				oldValue = input.data("oldValue");
			if(value == oldValue)
				return;
			var spanValue = parent.find("div.span_input_values");
			value == "values" ? spanValue.show() : spanValue.hide();
		});
		jQuery(":input.canHaveRange").change(function(){
			var input = jQuery(this),
				parent = input.parent().parent(),
				value = input.val(),
				oldValue = input.data("oldValue");
			if(value == oldValue)
				return;
			var spanRange = parent.find("div.span_input_range");
			value == "range" ? spanRange.show() : spanRange.hide();
		});

	}
};
