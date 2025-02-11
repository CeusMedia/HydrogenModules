var WorkMissionsViewer = {

	missionId: 0,

	init: function(missionId){
		this.missionId = missionId;
		this.loadContent();
		this.initVersions();
	},

	initVersions: function(){
		$(".version-list-item").each(function(){
			$(this).on("click", function(){
				$(".version-list-item").removeClass( 'active' );
				$(this).addClass( 'active' );
				var version		= parseInt($(this).data("version"), 10);
				var missionId	= WorkMissionsViewer.missionId;
				var serviceUri	= "./ajax/work/mission/renderMissionContent/"+missionId;
				if(version > 0)
					serviceUri	+= "/"+version;
				if(version > 1)
					serviceUri	+= "/"+(version-1);
				$.ajax({
					url: serviceUri,
					dataType: "json",
					success: function(json){
						if(json.status !== "data")
							return alert(json.status+": "+json.data);
						$("#mission-content-html").html(json.data);
					}
				});
			});
		})
	},

	loadContent: function(){
		$.ajax({
			url: "./ajax/work/mission/renderMissionContent/"+this.missionId,
			dataType: "json",
			success: function(json){
				if(json.status == "data"){
					$("#mission-content-html").html(json.data);
				}
				else{
					alert(json.data);
				}
			}
		});
	}
};
