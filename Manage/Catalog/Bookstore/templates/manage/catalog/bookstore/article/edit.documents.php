<?php

$panelDocuments	= '<div class="alert alert-error">Noch keine Dokumente gespeichert.</div>';

$iconRemove		= '<i class="icon-remove icon-white"></i>';

if( $articleDocuments ){
	$listDocuments	= array();
	foreach( $articleDocuments as $item ){
		$idPrefix		= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT ).'_';
		$urlRemove		= './manage/catalog/bookstore/article/removeDocument/'.$article->articleId.'/'.$item->articleDocumentId;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'" title="Dokument entfernen">'.$iconRemove.'</a>';
		$link			= UI_HTML_Tag::create( 'a', $item->title, array(
			'href'		=> 'file/bookstore/document/'.$item->url,
			'target'	=> '_blank'
		) );
		$listDocuments[]	= '<tr>
	<td>'.$link.'</td>
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
	$panelDocuments	= '
<div class="content-panel">
	<h4>Dokumente</h4>
	<div class="content-panel-inner">
		'.$listDocuments.'
	</div>
</div>
<hr/>';
}

$documentMaxSize	= Alg_UnitParser::parse( $moduleConfig->get( 'article.document.size' ), "M" );
$documentMaxSize	= Logic_Upload::getMaxUploadSize( array( 'config' => $documentMaxSize ) );
$documentMaxSize	= Alg_UnitFormater::formatBytes( $documentMaxSize );

$list				= array();
$documentExtensions	= $moduleConfig->get( 'article.document.extensions' );
foreach( explode( ",", $documentExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), array( "jpe", "jpeg" ) ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$documentExtensions	= join( ", ", $list );

$panelAdd	= '
<div class="content-panel">
	<h4>Dokumente hinzufügen</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>Dateitypen: </b>
			<span>'.$documentExtensions.'</span><br/>
			<b>Größe: </b>
			<span>max. '.$documentMaxSize.'</span>
		</div>
		<form action="./manage/catalog/bookstore/article/addDocument/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_document">lokale Dokumentdatei <small class="muted"></small></label>
					'.View_Helper_Input_File::render( 'document', '<i class="icon-folder-open icon-white"></i>', 'Datei auswählen...' ).'
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
</div>';

return '
<!--  Manage: Catalog: Article: Documents  -->
'.$panelDocuments.'
'.$panelAdd.'
<!--  /Manage: Catalog: Article: Documents  -->';
?>
