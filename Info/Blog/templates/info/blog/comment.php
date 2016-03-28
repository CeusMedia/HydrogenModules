<?php

$w		= (object) $words['comment'];

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./info/blog/comment/'.$post->postId.'">
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
				<button type="submit" name="save" value="1" class="btn btn-primary">'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
