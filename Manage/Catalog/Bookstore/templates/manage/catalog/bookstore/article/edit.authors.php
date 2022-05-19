<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );

$authorRelationsByAuthorId	= [];
foreach( $articleAuthors as $item )
	$authorRelationsByAuthorId[$item->authorId]	= $item;

$panelAuthors	= '<div class="alert alert-error">Noch keine Autoren/Herausgeber zugewiesen.</div>';

if( $articleAuthors ){
	$listAuthors	= [];
	foreach( $articleAuthors as $item ){

		$optRole		= $words['authorRoles'];
		$optRole		= UI_HTML_Elements::Options( $optRole, (int) $item->editor );
		$urlRemove		= './manage/catalog/bookstore/article/removeAuthor/'.$article->articleId.'/'.(int) $item->authorId;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'"><i class="icon-remove icon-white"></i></a>';
		$label			= $item->lastname.( $item->firstname ? ', '.$item->firstname : "" );
		$label			= '<a href="./manage/catalog/bookstore/author/edit/'.$item->authorId.'">'.$label.'</a>';
		$listAuthors[]	= '<tr>
		<td><div class="autocut">'.$label.'</div></td>
		<td><select class="span12" onchange="document.location.href=\'./manage/catalog/bookstore/article/setAuthorRole/'.$article->articleId.'/'.$item->authorId.'/\'+this.value;">'.$optRole.'</select></td>
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

	$panelAuthors	= '
<div class="content-panel">
	<h4>Autoren <small class="muted">(und Herausgeber)</small></h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			'.$listAuthors.'
		</div>
	</div>
</div>
<hr/>';
}

$optAuthor	= [];
foreach( $authors as $item )
	if( !in_array( $item->authorId, array_keys( $authorRelationsByAuthorId ) ) ){
		$label	= $item->lastname . ( $item->firstname ? ', '.$item->firstname : "" );
		$optAuthor[$item->authorId]	= $label;
	}
$optAuthor	= UI_HTML_Elements::Options( $optAuthor );

$optRole	= $words['authorRoles'];
$optRole	= UI_HTML_Elements::Options( $optRole );

$panelAdd	= '
<div class="content-panel">
	<h4>Autor zuweisen</h4>
	<div class="content-panel-inner form-changes-auto">
		<form action="./manage/catalog/bookstore/article/addAuthor/'.$article->articleId.'" method="post">
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
				'.UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
//					'title'	=> htmlentities( $w->buttonSave, ENT_QUOTES, 'UTF-8' ),
				) ).'
			</div>
		</form>
	</div>
</div>
';
return '
<!--  Manage: Catalog: Article: Authors  -->
'.$panelAuthors.'
'.$panelAdd.'
<!--  /Manage: Catalog: Article: Authors  -->';
?>
