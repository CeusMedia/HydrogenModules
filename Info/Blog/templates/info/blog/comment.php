<?php

$w		= (object) $words['comment'];

$iconSave	= '';
if( $env->getModules()->has( 'UI_Bootstrap' ) )
	$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) ).'&nbsp;';
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'value'		=> '1',
	'class'		=> 'btn btn-primary',
) );

if( $moduleConfig->get( 'comments.ajax' ) )
	$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.$w->buttonSave, array(
		'type'		=> 'button',
		'onclick'	=> 'Blog.comment()',
		'class'		=> 'btn btn-primary',
	) );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form-info-blog-comment-add" action="./info/blog/comment/'.$post->postId.'" method="post">
			<input type="hidden" name="postId" id="input_postId" value="'.$post->postId.'"/>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_content">'.$w->labelContent.' <small class="muted">'.$w->labelContent_suffix.'</small></label>
					<textarea name="content" id="input_content" class="span12" rows="5" required="required"></textarea>
				</div>
				<div class="span4">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_username">'.$w->labelUsername.' <small class="muted">'.$w->labelUsername_suffix.'</small></label>
							<input type="text" name="username" id="input_username" class="span12" value="" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_email">'.$w->labelEmail.' <small class="muted">'.$w->labelEmail_suffix.'</small></label>
							<input type="text" name="email" id="input_email" class="span12" value=""/>
						</div>
					</div>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span4">
					<label for="input_username">'.$w->labelUsername.' <small class="muted">'.$w->labelUsername_suffix.'</small></label>
					<input type="text" name="username" id="input_username" class="span12" value="" required="required"/>
				</div>
				<div class="span8">
					<label for="input_email">'.$w->labelEmail.' <small class="muted">'.$w->labelEmail_suffix.'</small></label>
					<input type="text" name="email" id="input_email" class="span12" value=""/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.' <small class="muted">'.$w->labelContent_suffix.'</small></label>
					<textarea name="content" id="input_content" class="span12" rows="6" required="required"></textarea>
				</div>
			</div>-->
			<div class="buttonbar">
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
<script>
var Blog = {
	init: function(){
		var form = $("#form-info-blog-comment-add");
		form.find(":input[required]").bind("keyup", Blog.updateSaveButton);
		this.updateSaveButton();
	},
	updateSaveButton: function(){
		var form = $("#form-info-blog-comment-add");
		var required = form.find(":input[required]");
		var got = 0;
		required.each(function(){
			if($(this).val().length)
				got ++;
		});
		if(got == required.size())
			form.find("button").removeProp("disabled");
		else
			form.find("button").prop("disabled", "disabled");
	},
	comment: function(){
		var form = $("#form-info-blog-comment-add");
		$.ajax({
			url: "./info/blog/ajaxComment/",
			data: {
				postId: form.find("#input_postId").val(),
				username: form.find("#input_username").val(),
				email: form.find("#input_username").val(),
				content: form.find("#input_content").val(),
			},
			method: "post",
			dataType: "json",
			success: function(json){
				var container = $("<div></div>").addClass("comment-new").hide();
				container.html(json.data.html);
				$(".list-comments").append(container);
				container.fadeIn(1000);
			},
			error: function(json){
				console.log(json);
				if(typeof json.responseJSON != "undefined")
					alert(json.responseJSON.data);
				else
					alert(json);
			}
		});
	}
};
$(document).ready(function(){
	Blog.init();
});
</script>
';
