<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\Alg\UnitParser;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wd	= (object) $words['edit.documents'];

$panelDocuments	= '<div class="alert alert-error">'.$wd->noEntries.'</div>';

$iconRemove		= '<i class="icon-remove icon-white"></i>';

if( $articleDocuments ){
	$listDocuments	= [];
	foreach( $articleDocuments as $item ){
		$idPrefix		= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT ).'_';
		$urlRemove		= './manage/catalog/article/removeDocument/'.$article->articleId.'/'.$item->articleDocumentId;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'" title="'.htmlentities( $wd->buttonRemove, ENT_QUOTES, 'UTF-8' ).'">'.$iconRemove.'</a>';
		$link			= HtmlTag::create( 'a', $item->title, [
			'href'		=> $pathDocuments.$idPrefix.$item->url,
			'target'	=> '_blank'
		] );
		$listDocuments[]	= '<tr>
	<td>'.$link.'</td>
	<td><div class="pull-right">'.$buttonRemove.'</div></td>
</tr>';
	}

	$listDocuments	= '<table class="table table-condensed">
	'.HtmlElements::ColumnGroup( '', '70px' ).'
	<thead>
		<tr>
			<th>'.$wd->headDocument.'</th>
			<th>'.$wd->headRemove.'</th>
		</tr>
	</thead>
	<tbody>
		'.join( $listDocuments ).'
	</tbody>
</table>';
	$panelDocuments	= '
<div class="content-panel">
	<h4>'.$wd->heading.'</h4>
	<div class="content-panel-inner">
		'.$listDocuments.'
	</div>
</div>
<hr/>';
}

$documentMaxSize	= $moduleConfig->get( 'article.document.maxSize' );
$limits				= ['document' => UnitParser::parse( $documentMaxSize, "M" )];
$documentMaxSize	= UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize( $limits ) );

$list				= [];
$documentExtensions	= $moduleConfig->get( 'article.document.extensions' );
foreach( explode( ",", $documentExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), ["jpe", "jpeg"] ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$documentExtensions	= join( ", ", $list );

$panelAdd	= '
<div class="content-panel">
	<h4>'.$wd->headingAdd.'</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>'.$wd->labelFileTypes.' </b>
			<span>'.$documentExtensions.'</span><br/>
			<b>'.$wd->labelFileSize.' </b>
			<span>'.sprintf( $wd->valueFileSizeMax, $documentMaxSize ).'</span>
		</div>
		<form action="./manage/catalog/article/addDocument/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_document">'.$wd->labelFile.' <small class="muted"></small></label>
					'.View_Helper_Input_File::renderStatic( $env, 'document', '<i class="icon-folder-open icon-white"></i>', $wd->buttonSelect ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$wd->labelTitle.'</label>
					<input class="span12" type="text" name="title" id="input_title"/><br/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" class="btn btn-primary" name="save"><i class="icon-plus icon-white"></i> '.$wd->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

return '
<!--  Manage: Catalog: Article: Documents  -->
'.$panelDocuments.'
'.$panelAdd.'
<!--  /Manage: Catalog: Article: Documents  -->';
