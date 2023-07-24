<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var array $articleTags */
/** @var object $article */

$listTags	= "";
$panelTags	= '<div class="alert alert-error">Noch kein Schlagwort vergeben.</div>';

$iconRemove	= '<i class="icon-remove icon-white"></i>';
$iconPlus	= '<i class="icon-plus icon-white"></i>';

if( $articleTags ){
	$listTags	= [];
	foreach( $articleTags as $item ){
		$urlRemove	= './manage/catalog/bookstore/article/removeTag/'.$article->articleId.'/'.$item->articleTagId;
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-danger',
		] );
		$listTags[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $item->tag ),
			HtmlTag::create( 'td', '<div class="pull-right">'.$buttonRemove.'</div>' )
		) );
	}
/*
	$inputFile	= HtmlTag::create( 'input', NULL, [
		'type'	=> 'text',
		'class'	=> 'span12',
		'name'	=> 'tag',
		'id'	=> 'input_type',
		'placeholder'	=> 'neues Schlagwort',
	] );

	$buttonSave	= HtmlTag::create( 'button', $iconPlus.' hinzufügen', [
		'class'		=> 'btn btn-primary',
		'type'		=> 'submit',
		'name'		=> 'save',
	] );

	$listTags[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $inputFile ),
		HtmlTag::create( 'td', '<div class="pull-right">'.$buttonSave.'</div>' )
	) );*/

	$colgroup	= HtmlElements::ColumnGroup( '', '140px' );
	$tbody		= HtmlTag::create( 'tbody', join( $listTags ) );
	$listTags	= HtmlTag::create( 'table', $colgroup.$tbody, ['class' => "table table-condensed"] );

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
<form action="./manage/catalog/bookstore/article/addTag/'.$article->articleId.'" method="post">
	'.$panelTags.'
	'.$panelAdd.'
</form>
<!--  Manage: Catalog: Article: Tags  -->';
