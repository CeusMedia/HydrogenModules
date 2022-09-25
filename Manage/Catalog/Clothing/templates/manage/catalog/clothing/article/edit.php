<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';

$optCategoryId	= [];
foreach( $categoryMap as $item )
	$optCategoryId[$item->categoryId]	= $item->title;
$optCategoryId	= HtmlElements::Options( $optCategoryId, $article->categoryId );

$optCurrency	= array( 'EUR' => 'EURO' );
$optCurrency	= HtmlElements::Options( $optCurrency, $article->currency );

$panelFacts	= '
<div class="content-panel">
	<h3>Produkt</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/clothing/article/edit/'.$article->articleId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">Titel</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "text",
						'id'		=> "input_title",
						'name'		=> "title",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $article->title
					) ).'
				</div>
				<div class="span4">
					<label for="input_categoryId">Kategorie</label>
					<select name="categoryId" id="input_categoryId" class="span12">'.$optCategoryId.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_form">Ausführung</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "text",
						'id'		=> "input_form",
						'name'		=> "form",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $article->form
					) ).'
				</div>
				<div class="span3">
					<label for="input_size">Größe</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "text",
						'id'		=> "input_size",
						'name'		=> "size",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $article->size
					) ).'
				</div>
				<div class="span3">
					<label for="input_color">Farbe</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "text",
						'id'		=> "input_color",
						'name'		=> "color",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $article->color
					) ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_price">Preis <small class="muted">(€€.¢¢)</small></label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "number",
						'step'		=> '0.01',
						'min'		=> '0',
						'max'		=> '1000',
						'id'		=> "input_price",
						'name'		=> "price",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $article->price
					) ).'
				</div>
				<div class="span3">
					<label for="input_price">Währung</label>
					'.HtmlTag::create( 'select', $optCurrency, array(
						'id'		=> "input_currency",
						'name'		=> "currency",
						'class'		=> "span12",
						'required'	=> "required",
					) ).'
				</div>
				<div class="span3">
					<label for="input_price">Lagerbestand</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "number",
						'step'		=> '1',
						'min'		=> '0',
						'max'		=> '10000',
						'id'		=> "input_quantity",
						'name'		=> "quantity",
						'class'		=> "span12",
						'value'		=> $article->quantity
					) ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung</label>
					<textarea name="description" id="input_description" class="span12" rows="6">'.$article->description.'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/catalog/clothing/article" class="btn btn-small">'.$iconCancel.'abbrechen</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'speichern</button>
			</div>
		</form>
	</div>
</div>';

$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconUpload		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );

$image			= '';
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.'&nbsp;entfernen', array(
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn btn-inverse'
) );

if( $article->image ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'	=> './manage/catalog/clothing/article/setImage/'.$article->articleId.'/remove',
		'class'	=> 'btn btn-inverse'
	) );
	$image	= HtmlTag::create( 'img', NULL, array(
		'src'	=> $path.$article->image,
		'class'	=> 'img-polaroid',
	) ).'<hr/>';
}
$upload		= new View_Helper_Input_File( $env );
$upload->setLabel( $iconUpload );

$buttonSave	= HtmlTag::create( 'button', $iconSave.'&nbsp;hochladen', array(
	'type'	=> "submit",
	'name'	=> "save",
 	'class'	=> "btn btn-primary",
) );

$panelImage	= '
<div class="content-panel">
	<h3>Bild</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/clothing/article/setImage/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span11">
					'.$image.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">lokale Bilddatei</label>
					'.$upload.'
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonSave.'
				'.$buttonRemove.'
			</div>
		</div>
	</div>
</div>';


return '
<div class="row-fluid">
	<div class="span8">
		'.$panelFacts.'
	</div>
	<div class="span4">
		'.$panelImage.'
	</div>
</div>';
