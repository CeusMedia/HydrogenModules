<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $words */
/** @var object $article */
/** @var bool $cart */

if( !in_array( $article->status, [-1, 0, 1] ) )
	return '';

$w				= (object) $words['article'];

/*  @todo extract locales to new section 'article-panel-order' */
//$w				= (object) $words['article-panel-order'];
//$labelHeading		= 'Bestellen';
$labelHeading		= '';
$labelButtonOrder	= 'bestellen';
$labelButtonCart	= 'zum Warenkorb';

$iconOrder		= HtmlTag::create( 'i', '', ['class' => 'icon-plus'] );
$iconCart		= HtmlTag::create( 'i', '', ['class' => 'icon-shopping-cart icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$labelButtonOrder	= 'in den Warenkorb';
	$labelButtonCart	= 'weiter zur Kasse';
	$iconOrder		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
	$iconOrder		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	$iconCart		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
//	$iconOrder		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
//	$iconCart		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-money'] );
}

$inputQuantity	= HtmlTag::create( 'input', NULL, [
	'type'		=> 'number',
	'step'		=> 1,
	'min'		=> 1,
	'max'		=> 1000,
	'name'		=> 'quantity',
	'id'		=> 'input_quantity',
	'class'		=> 'span2 numeric',
	'required'	=> 'required',
	'value'		=> min( 1000, max( 1, (int) $request->get( 'quantity' ) ) ),
] );

$buttonOrder	= HtmlTag::create( 'button', $iconOrder.' '.$labelButtonOrder, [
	'type'	=> 'submit',
	'name'	=> 'order',
//	'class'	=> 'btn btn-primary',
	'class'	=> 'btn not-btn-primary',
] );
$buttonCart	= HtmlTag::create( 'a', $iconCart.' '.$labelButtonCart, [
	'href'		=> "./shop/cart",
//	'class'		=> "btn not-btn-success not-btn-small",
	'class'		=> "btn btn-success",
] );
if( !$cart )
	$buttonCart	= '';

$heading	= $labelHeading ? HtmlTag::create( 'h3', $labelHeading ) : '';
return '<div class="content-panel">
	'.$heading.'
	<div class="content-panel-inner well alert alert-success" id="panel-catalog-article-order">
		<form action="./catalog/bookstore/order" method="post" class="form-horizontal">
			<input type="hidden" name="articleId" value="'.$article->articleId.'"/>
			<input type="hidden" name="from" value="'.$from.'"/>
			<label for="input_quantity">'.$w->quantity.'</label>
			'.$inputQuantity.'
			'.$buttonOrder.'
			<div class="pull-right">
				'.$buttonCart.'
			</div>
		</form>
	</div>
</div>';
