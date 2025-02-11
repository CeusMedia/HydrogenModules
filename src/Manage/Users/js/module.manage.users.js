let ModuleManageUsers = {
	countries: [],
	roleId: 0,
	init: function(){
		let body = jQuery("body");
		if(this.roleId && body.hasClass("site-manage-role-edit")){
			let container = $("#role-edit-rights").parent();
			container.find("td.column-actions li.changable").on("mousedown", ModuleManageUsers.onChangeRightToggle );
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
		let toggle = $(this);
		let showAll = toggle.is(":checked");
		let items = $("li.acl-module").not(".changable");
		showAll ? items.slideDown(250) : items.slideUp(250);
		let rows = $("#role-edit-rights tbody tr");
		rows.each(function(){
			let row = $(this);
			if(showAll){
				row.fadeIn({duration: 0, queue: false});
				row.find("li.action").show();
				row.find(".label-module,.label-controller").show();
			}
			else{
				row.find(".label-module,.label-controller").hide();
				let hasChangables = $(this).find("li.action.changable").length;
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
		let toggle = $(this);
		let action = toggle.data("action");
		let controller = toggle.data("controller");
		toggle.addClass("yellow");
		$.ajax({
			url: "./ajax/manage/role/changeRight",
			method: "POST",
			data: {
				roleId: ModuleManageUsers.roleId,
				controller: controller,
				action: action
			},
			dataType: "json",
			context: toggle,
			success: function(response){
				if(response.data.current)
					$(this).removeClass("red").addClass("green");
				else
					$(this).removeClass("green").addClass("red");
				$(this).removeClass("yellow");
			}
		});
	},
	setRoleId: function(roleId){
		this.roleId = roleId;
		return this;
	},
	setCountries: function(countries){
		this.countries = countries;
		return this;
	}
};
