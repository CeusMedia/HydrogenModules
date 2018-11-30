<?php
if( !in_array( $article->status, array( -1, 0, 1 ) ) )
	return '';

$w				= (object) $words['article'];

/*  @todo extract locales to new section 'article-panel-order' */
//$w				= (object) $words['article-panel-order'];
//$labelHeading		= 'Bestellen';
$labelHeading		= '';
$labelButtonOrder	= 'bestellen';
$labelButtonCart	= 'zum Warenkorb';

$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus' ) );
$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-shopping-cart icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$labelButtonOrder	= 'in den Warenkorb';
	$labelButtonCart	= 'weiter zur Kasse';
	$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
	$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
//	$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
//	$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-money' ) );
}

$inputQuantity	= UI_HTML_Tag::create( 'input', NULL, array(
	'type'		=> 'number',
	'step'		=> 1,
	'min'		=> 1,
	'max'		=> 1000,
	'name'		=> 'quantity',
	'id'		=> 'input_quantity',
	'class'		=> 'span2 numeric',
	'required'	=> 'required',
	'value'		=> min( 1000, max( 1, (int) $request->get( 'quantity' ) ) ),
) );

$buttonOrder	= UI_HTML_Tag::create( 'button', $iconOrder.' '.$labelButtonOrder, array(
	'type'	=> 'submit',
	'name'	=> 'order',
//	'class'	=> 'btn btn-primary',
	'class'	=> 'btn not-btn-primary',
) );
$buttonCart	= UI_HTML_Tag::create( 'a', $iconCart.' '.$labelButtonCart, array(
	'href'		=> "./shop/cart",
//	'class'		=> "btn not-btn-success not-btn-small",
	'class'		=> "btn btn-success",
) );
if( !$cart )
	$buttonCart	= '';

$heading	= $labelHeading ? UI_HTML_Tag::create( 'h3', $labelHeading ) : '';
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
?>
