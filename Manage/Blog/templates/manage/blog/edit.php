<?php

$w			= (object) $words['edit'];

return '
<div class="content-panel content-panel-form">
	<h3><span class="muted">Eintrag: </span>'.$post->title.'</h3>
	<div class="content-panel-inner">
		'.print_m( $post, NULL, NULL, TRUE ).'
		<form action="./manage/blog/edit/'.$post->postId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/blog" class="btn btn-small">'.$w->buttonCancel.'</a>
				<button type="submit" name="save" value="1">'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
