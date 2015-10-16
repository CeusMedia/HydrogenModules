<?php

$listDocuments	= '<small class="muted"><em>Noch keine Dokumente gespeichert.</em></small>';
$listDocuments	= '<div class="label not-label-warning">Noch keine Dokumente gespeichert.</div>';
$listDocuments	= '<div class="alert alert-error">Noch keine Dokumente gespeichert.</div>';

$iconRemove		= '<i class="icon-remove icon-white"></i>';

if( $articleDocuments ){
	$listDocuments	= array();
	foreach( $articleDocuments as $item ){
		$urlRemove		= './manage/catalog/article/removeDocument/'.$article->articleId.'/'.$item->articleDocumentId;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'" title="Dokument entfernen">'.$iconRemove.'</a>';
		$listDocuments[]	= '<tr>
	<td>'.$item->title.'</td>
	<td><div class="pull-right">'.$buttonRemove.'</div></td>
</tr>';
	}

	$listDocuments	= '<table class="table table-condensed">
	'.UI_HTML_Elements::ColumnGroup( '', '70px' ).'
	<thead>
		<tr>
			<th>Dokument</th>
			<th>entfernen</th>
		</tr>
	</thead>
	<tbody>
		'.join( $listDocuments ).'
	</tbody>
</table>';
}


$configKey	= 'module.manage_catalog.article.document.maxSize';
$maxSize	= Logic_Upload::getMaxUploadSize( $env, $configKey, 'M' );
$maxSize	= Alg_UnitFormater::formatBytes( $maxSize );

$configKey	= 'module.manage_catalog.article.document.extensions';
$types		 = Logic_Upload::getTypes( $env, $configKey );
natcasesort( $types );
$types		= join( ", ", $types );

return '
<!--  Manage: Catalog: Article: Documents  -->
<div class="content-panel">
	<h4>Dokumente</h4>
	<div class="content-panel-inner">
		'.$listDocuments.'
	</div>
</div>
<hr/>
<div class="content-panel">
	<h4>Dokumente hinzufügen</h4>
	<div class="content-panel-inner">
		<div class="alert">
			<b>Dateitypen: </b>
			<span>'.$types.'</span><br/>
			<b>Größe: </b>
			<span>max. '.$maxSize.'</span>
		</div>
		<form action="./manage/catalog/article/addDocument/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_document">lokale Dokumentdatei <small class="muted">(max. '.$maxSize.')</small></label>
					<input class="span12" type="file" name="document" id="input_document"/><br/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">unter dem Titel</label>
					<input class="span12" type="text" name="title" id="input_title"/><br/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" class="btn btn-primary" name="save"><i class="icon-plus icon-white"></i> hinzufügen</button>
			</div>
		</form>
	</div>
</div>
<!--  /Manage: Catalog: Article: Documents  -->';
?>
