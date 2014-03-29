<?php
$w	= (object) $words['thread-add'];
return '
<h4>'.sprintf( $w->heading, $thread->title ).'</h4>
<form action="./info/forum/addPost/'.$thread->threadId.'" method="post" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span8">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab1" data-toggle="tab">Text</a></li>
				<li><a href="#tab2" data-toggle="tab">Bild</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="tab1">
					<label for="input_content">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" rows="10" class="span12">'.$request->get( 'content' ).'</textarea>
				</div>
				<div class="tab-pane" id="tab2">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_file">'.$w->labelImageFile.'
								<small class="muted">(max. '.ini_get( 'upload_max_filesize' ).'B)</small>
							</label>
							<input type="file" name="file" id="input_file"/><br/><br/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelImageTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12">'.$request->get( 'title' ).'</textarea>
						</div>
					</div>
					<br/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./info/forum/topic/'.$thread->topicId.'" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" value="1" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</div>
	</div>
</form>';
?>
