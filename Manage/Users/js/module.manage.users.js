var ModuleManageUsers = {
	countries: [],
	init: function(){
		var body = jQuery("body");
		if(body.hasClass("site-manage-role-edit")){
			var container = $("#role-edit-rights").parent();
			container.find("li.changable").on("mousedown", ModuleManageUsers.onChangeRightToggle );
			container.find("#input-toggle-rights-all").on("change", ModuleManageUsers.onChangeVisibleRightsToggle);
			container.find("#button-toggle").on("click", function(e){jQuery(this).children("label").trigger("click");});
			container.find("#button-toggle>label").on("click", function(e){e.stopPropagation();});
		}
		if(this.countries.length){
			jQuery(".typeahead").each(function(){
				jQuery(this).typeahead({
					source: this.countries,
					items: 4
				});
			});
		}
	},
	onChangeVisibleRightsToggle: function(event){
		var toggle = $(this);
		var showAll = toggle.is(":checked");

		if(showAll){
			$("li.acl-module").not(".changable").slideDown(250);
		}
		else{
			$("li.acl-module").not(".changable").slideUp(250);
		}


		var rows = $("#role-edit-rights tbody tr");
		rows.each(function(){
			var row = $(this);
			if(showAll){
				row.fadeIn({duration: 0, queue: false});
				row.find("li.action").show();
				row.find(".label-module,.label-controller").show();
			}
			else{
				row.find(".label-module,.label-controller").hide();
				var hasChangables = $(this).find("li.action.changable").length;
				if(!hasChangables)
					row.fadeOut({duration: 0, queue: false});
				else{
					row.fadeIn({duration: 0, queue: false});
					row.find("li.action").not(".changable").hide();
				}
			}
		});
	},
	onChangeRightToggle: function(event){
		if(event.button != 0)
			return;
		var toggle = $(this);
		var id = toggle.attr("id");
		var parts = id.split(/-/);
		var action = parts.pop();
		var controller = parts.pop();
		toggle.addClass("yellow");
		$.ajax({
			url: "./manage/role/ajaxChangeRight/'.$roleId.'/"+controller+"/"+action,
			dataType: "json",
			context: toggle,
			success: function(data){
				if(data)
					$(this).removeClass("red").addClass("green");
				else
					$(this).removeClass("green").addClass("red");
				$(this).removeClass("yellow");
			}
		});
	},
	setCountries: function(countries){
		this.countries = countries;
		return this;
	}
};
