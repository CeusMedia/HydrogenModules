var ModuleInfoDownloads = {
	baseUrl: "./info/download/",
	changeFolderName: function(folderId, currentName){
		var value = prompt("Neuer Name:", currentName);
		if(value.length){
			$.ajax({
				url: ModuleInfoDownloads.baseUrl + "ajaxRenameFolder/",
				dataType: 'json',
				method: 'post',
				data: {
					folderId: folderId,
					name: value
				},
				success: function(folder){
					document.location.href = ModuleInfoDownloads.baseUrl + "index/"+folder.parentId;
				}
			});
		}
	},
};
