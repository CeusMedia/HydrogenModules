$(document).ready(function(){
    $("button.btn-cache-remove").bind("click",function(){
        var row = $(this).parent().parent();
        $.ajax({
            url: "./admin/cache/ajaxRemove",
            data: {key: row.data("key")},
            type: "post",
            context: row,
            dataType: "json",
            success: function(response){
                $(this).remove();
            }
        });
    });
});
		
