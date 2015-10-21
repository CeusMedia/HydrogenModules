<?php

$listTags	= "";
$panelTags	= '<div class="alert alert-error">Noch kein Schlagwort vergeben.</div>';

$iconRemove	= '<i class="icon-remove icon-white"></i>';
$iconPlus	= '<i class="icon-plus icon-white"></i>';

if( $articleTags ){
	$listTags	= array();
	foreach( $articleTags as $item ){
		$urlRemove	= './manage/catalog/article/removeTag/'.$article->articleId.'/'.$item->articleTagId;
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-danger',
		) );
		$listTags[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $item->tag ),
			UI_HTML_Tag::create( 'td', '<div class="pull-right">'.$buttonRemove.'</div>' )
		) );
	}
/*
	$inputFile	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'	=> 'text',
		'class'	=> 'span12',
		'name'	=> 'tag',
		'id'	=> 'input_type',
		'placeholder'	=> 'neues Schlagwort',
	) );

	$buttonSave	= UI_HTML_Tag::create( 'button', $iconPlus.' hinzufügen', array(
		'class'		=> 'btn btn-primary',
		'type'		=> 'submit',
		'name'		=> 'save',
	) );

	$listTags[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $inputFile ),
		UI_HTML_Tag::create( 'td', '<div class="pull-right">'.$buttonSave.'</div>' )
	) );*/

	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '140px' );
	$tbody		= UI_HTML_Tag::create( 'tbody', join( $listTags ) );
	$listTags	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => "table table-condensed" ) );

	$panelTags	= '
		<div class="content-panel">
			<h4>Schlagwörter</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						'.$listTags.'
					</div>
				</div>
			</div>
		</div>';
}

$panelAdd	= '
	<div class="content-panel">
		<h4>Schlagwort vergeben</h4>
		<div class="content-panel-inner form-changes-auto">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_tag">neues Schlagwort</label>
					<input class="span12" type="text" name="tag" id="input_tag"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" class="btn btn-primary"><i class="icon-plus icon-white"></i> hinzufügen</button>
			</div>
		</div>
	</div>';

return '
<!--  Manage: Catalog: Article: Tags  -->
<form action="./manage/catalog/article/addTag/'.$article->articleId.'" method="post">
	'.$panelTags.'
	'.$panelAdd.'
</form>
<!--  Manage: Catalog: Article: Tags  -->';
?>
