<?php

return '
<div class="row-fluid">
	<div class="span6">
		<div class="row-fluid">
			<div class="span12">
				<label for="input_keywords">Schlagwörter <small class="muted">(kommagetrennt)</small></label>
				<input class="span12" type="text" name="keywords" id="input_keywords" value="'.htmlentities( $page->keywords, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_description">Beschreibung</label>
				<textarea class="span12" rows="3" name="description" id="input_description">'.htmlentities( $page->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label for="input_author">Autor</label>
				<input class="span12" type="text" name="author" id="input_author" value="'.htmlentities( ""/*$page->author*/, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span6">
				<label for="input_publisher">Herausgeber</label>
				<input class="span12" type="text" name="publisher" id="input_publisher" value="'.htmlentities( ""/*$page->publisher*/, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
	<div class="span6">
		<h4>Standard-Werte</h4>
		<dl class="dl-horizontal">
			<dt>Beschreibung</dt>
			<dd>'.$meta['description'].'</dd>
			<dt>Schlüsselwörter</dt>
			<dd>'.$meta['keywords'].'</dd>
			<dt>Autor</dt>
			<dd>'.$meta['author'].'</dd>
			<dt>Herausgeber</dt>
			<dd>'.$meta['publisher'].'</dd>
		</dl>
	</div>
</div>';
?>
