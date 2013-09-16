<?php

$authorRelationsByAuthorId	= array();
foreach( $articleAuthors as $item )
	$authorRelationsByAuthorId[$item->author_id]	= $item;

$listAuthors	= '<small class="muted"><em>Noch kein(e) Autore(n) zugewiesen.</em></small>';
$listAuthors	= '<div class="label not-label-warning">Noch kein(e) Autore(n) zugewiesen.</div>';
$listAuthors	= '<div class="alert alert-error">Noch kein(e) Autore(n) zugewiesen.</div>';

if( $articleAuthors ){
	$listAuthors	= array();
	foreach( $articleAuthors as $item ){
		
		$optRole		= $words['authorRoles'];
		$optRole		= UI_HTML_Elements::Options( $optRole, (int) $item->editor );
		$urlRemove		= './manage/catalog/article/removeAuthor/'.$article->article_id.'/'.(int) $item->author_id;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'"><i class="icon-remove icon-white"></i></a>';
		$listAuthors[]	= '<tr>
		<td><div class="autocut">'.$item->lastname.( $item->firstname ? ', '.$item->firstname : "" ).'</div></td>
		<td><select class="span12" onchange="document.location.href=\'./manage/catalog/article/setAuthorRole/'.$article->article_id.'/'.$item->author_id.'/\'+this.value;">'.$optRole.'</select></td>
		<td><div class="pull-right">'.$buttonRemove.'</div></td>
	</tr>';
	}

	$listAuthors	= '
	<table class="table table-condensed">
		'.UI_HTML_Elements::ColumnGroup( '', '150px', '40px' ).'
		<thead>
			<tr>
				<th>Autor / Herausgeber</th>
				<th>Rolle</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			'.join( $listAuthors ).'
		</tbody>
	</table>';
}

$optAuthor	= array();
foreach( $authors as $item )
	if( !in_array( $item->author_id, array_keys( $authorRelationsByAuthorId ) ) ){
		$label	= $item->lastname . ( $item->firstname ? ', '.$item->firstname : "" );
		$optAuthor[$item->author_id]	= $label;
	}
$optAuthor	= UI_HTML_Elements::Options( $optAuthor );

$optRole	= $words['authorRoles'];
$optRole	= UI_HTML_Elements::Options( $optRole );

return '
<!--  Manage: Catalog: Article: Authors  -->
	<div class="row-fluid">
		<h4>Autoren <small class="muted">(und Herausgeber)</small></h4>
		'.$listAuthors.'
	</div>
	<div class="row-fluid">
		<h4>Autor zuweisen</h4>
		<form action="./manage/catalog/article/addAuthor/'.$article->article_id.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_authorId">'.$w->labelAuthor.'</label>
					<select class="span12" name="authorId" id="input_authorId">'.$optAuthor.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_role">'.$w->labelRole.'</label>
					<select class="span12" name="editor" id="input_role">'.$optRole.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a class="btn btn-small" href="./manage/catalog/article"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" class="btn btn-small btn-success" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
<!--  /Manage: Catalog: Article: Authors  -->
';
?>
