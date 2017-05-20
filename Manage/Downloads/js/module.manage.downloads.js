var ModuleManageDownloads = {
	baseUrl: "./manage/download/",
	changeFolderName: function(folderId, currentName){
		var value = prompt("Neuer Name:", currentName);
		if(value.length){
			$.ajax({
				url: ModuleManageDownloads.baseUrl + "ajaxRenameFolder/",
				dataType: 'json',
				method: 'post',
				data: {
					folderId: folderId,
					name: value
				},
				success: function(folder){
					document.location.href = ModuleManageDownloads.baseUrl + "index/"+folder.parentId;
				}
			});
		}
	},
};
