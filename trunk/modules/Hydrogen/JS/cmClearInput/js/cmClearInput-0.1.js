cmClearInput = {
       init: function(){
                $(":input.clearable").each(function(){
                        var input = $(this);
                        input.parent().css("position", "relative");
                        var trigger = $("<span></span>").addClass("triggerClear");
                        var left = $(this).width() + parseInt($(this).css("padding-right")) - 11 - 4;
                        var topPosition = input.position().top;
                        var topMargin = parseInt(input.css("margin-top"));
                        var topHeight = ((input.height() - 11) / 2);
                        var topPadding = parseInt(input.css("padding-top"));
                        var top = Math.ceil(topPosition + topMargin + topPadding + topHeight);
/*                      console.log({
                                topPosition: topPosition,
                                topMargin: topMargin,
                                topPadding: topPadding,
                                topHeight: topHeight,
                        });
*/                      trigger.css("left", left).css("top", top).insertAfter(input);
                });
                $(".triggerClear").each(function(){
                        var input = $(this).parent().find(":input");
                        input.bind("change.updateClearTrigger",{trigger: $(this)}, function(event){
                                console.log("change.updateClearTrigger: "+$(this).val().length);
                                if($(this).val().length)
                                        event.data.trigger.show();
                                else
                                        event.data.trigger.hide();
                        }).trigger("change.updateClearTrigger");
                        $(this).bind("click", function(){
                                input.val("").trigger("change.updateClearTrigger");
                        });
                });
        }
};

