var InfoFile = {
	baseUrl: "./info/file",
	changeFolderName: function(folderId, currentName){
		var value = prompt("Neuer Name:", currentName);
		if(value.length){
			$.ajax({
				url: InfoFile.baseUrl + "/ajaxRenameFolder/",
				dataType: 'json',
				method: 'post',
				data: {
					folderId: folderId,
					name: value
				},
				success: function(folder){
					document.location.href = InfoFile.baseUrl + "/index/"+folder.parentId;
				}
			});
		}
	},
};