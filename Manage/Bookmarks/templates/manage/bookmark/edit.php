<?php

return '
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				'.$this->renderList( $bookmark->bookmarkId ).'
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>Lesezeichen verändern</h3>
			<div class="content-panel-inner">
				<form action="./manage/bookmark/edit/'.$bookmark->bookmarkId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">Internet-Adresse <small class="muted">(vollständige URL)</small></label>
							<input class="span12" type="text" name="url" id="input_url" value="'.htmlentities( $bookmark->url, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							<input class="span12" type="text" name="title" id="input_title" value="'.htmlentities( $bookmark->title, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="buttonbar">
						<a class="btn btn-small" href="./manage/bookmark"><i class="icon-arrow-left"></i> zurück</a>
						<button type="submit" name="save" class="btn not-btn-small btn-primary"><i class="icon-ok icon-white"></i> speichern</button>
						<a class="btn btn-small btn-danger" href="./manage/bookmark/remove/'.$bookmark->bookmarkId.'"><i class="icon-remove icon-white"></i> entfernen</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
?>
