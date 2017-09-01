<?php
$w			= (object) $words['add'];

$tabsMain	= $this->renderMainTabs();

$optStatus	= $words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, $article->status );

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, $article->series );

$optMark	= $words['marks'];
$optMark	= UI_HTML_Elements::Options( $optMark, $article->new );

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/bookstore/article/filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/catalog/bookstore/article/list.php' );

return '
'.$tabsMain.'
<div class="row-fluid">
	<div class="span2">
		'.$panelFilter.'
	</div>
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span7">
		<div class="content-panel">
			<h4>'.$w->heading.'</h4>
			<div class="content-panel-inner form-changes-auto">
				<form action="./manage/catalog/bookstore/article/add" method="post">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input class="span12" type="text" name="title" id="input_title" value="'.htmlentities( $article->title, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_subtitle">'.$w->labelSubtitle.'</label>
					<input class="span12 small" type="text" name="subtitle" id="input_subtitle" value="'.htmlentities( $article->subtitle, ENT_QUOTES, 'UTF-8' ).'"/>
					<div class="row-fluid">
						<label for="input_description">'.$w->labelDescription.'</label>
						<textarea class="span12" name="description" id="input_description" rows="4">'.htmlentities( $article->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
					</div>
					<div class="row-fluid">
						<label for="input_recension">'.$w->labelRecension.'</label>
						<textarea class="span12" name="recension" id="input_recension" rows="2">'.htmlentities( $article->recension, ENT_QUOTES, 'UTF-8' ).'</textarea>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_type">'.$w->labelType.'</label>
							<select class="span12" name="series" id="input_type">'.$optType.'</select>
						</div>
						<div class="span5">
							<label for="input_isn">'.$w->labelIsn.'</label>
							<input class="span12" type="text" name="isn" id="input_isn" value="'.htmlentities( $article->isn, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span3">
							<label for="input_language">'.$w->labelLanguages.'</label>
							<input class="span12" type="text" name="language" id="input_language" value="'.htmlentities( $article->language, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span2">
							<label for="input_price">'.$w->labelPrice.'</label>
							<input class="span12" type="text" name="price" id="input_price" value="'.htmlentities( $article->price, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_digestion">'.$w->labelDigestion.'</label>
							<input class="span12" type="text" name="digestion" id="input_digestion" value="'.htmlentities( $article->digestion, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span6">
							<label for="input_size">'.$w->labelSize.'</label>
							<input class="span12" type="text" name="size" id="input_size" value="'.htmlentities( $article->size, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_publication"><abbr title="'.$w->labelPublication_title.'">'.$w->labelPublication.'</abbr></label>
							<input class="span12" type="text" name="publication" id="input_publication" value="'.htmlentities( $article->publication, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select class="span12" name="status" id="input_status">'.$optStatus.'</select>
						</div>
						<div class="span4">
							<label for="input_mark">'.$w->labelMark.'</label>
							<select class="span12" name="new" id="input_mark">'.$optMark.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<a class="btn btn-small" href="./manage/catalog/bookstore/article"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" class="btn btn-primary" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
?>
