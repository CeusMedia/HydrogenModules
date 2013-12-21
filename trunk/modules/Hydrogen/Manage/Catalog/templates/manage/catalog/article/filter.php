<?php
$filterTerm		= !empty( $filters['term'] ) ? $filters['term'] : "";
$filterAuthor	= !empty( $filters['author'] ) ? $filters['author'] : "";
$filterNew		= !empty( $filters['new'] ) ? ' checked="checked"' : "";
$filterCover	= !empty( $filters['cover'] ) ? ' checked="checked"' : "";
$filterIsn		= !empty( $filters['isn'] ) ? $filters['isn'] : "";
$filterOrder	= !empty( $filters['order'] ) ? $filters['order'] : "timestamp:DESC";

$filterStatus	= strlen( isset( $filters['status'] ) && $filters['status'] ) ? $filters['status'] : "";

$optStatus	= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= UI_HTML_Elements::Options( $optStatus, (string) $filterStatus );

$optOrder	= array(
	'title:ASC'			=> 'Titel aufsteigend',
	'title:DESC'		=> 'Titel absteigend',
	'createdAt:ASC'		=> 'Erstellung aufsteigend',
	'createdAt:DESC'	=> 'Erstellung absteigend',
);
$optOrder	= UI_HTML_Elements::Options( $optOrder, $filterOrder );

return '
<form action="./manage/catalog/article/filter" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_term">Suchtext</label>
			<input class="span12" type="text" name="term" id="input_term" value="'.$filterTerm.'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_author">Autor</label>
			<input class="span12" type="text" name="author" id="input_author" value="'.$filterAuthor.'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_isn">ISBN/ISSN</label>
			<input class="span12" type="text" name="isn" id="input_isn" value="'.$filterIsn.'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_status">Status</label>
			<select class="span12" name="status" id="input_status">'.$optStatus.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_new" class="checkbox">Neuerscheinung
				<input type="checkbox" name="new" id="input_new" value="1"'.$filterNew.'/>
			</label>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_cover" class="checkbox">nur mit Bild
				<input type="checkbox" name="cover" id="input_cover" value="1"'.$filterCover.'/>
			</label>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_order">Sortierung nach Spalte</label>
			<select class="span12" name="order" id="input_order">'.$optOrder.'</select>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" class="btn btn-small btn-info"><i class="icon-search icon-white"></i> filtern</button>
		<a href="./manage/catalog/article/filter/reset" class="btn btn-small btn-inverse"><i class="icon-remove-circle icon-white"></i> zurücksetzen</a>
	</div>
</form>
';
?>
