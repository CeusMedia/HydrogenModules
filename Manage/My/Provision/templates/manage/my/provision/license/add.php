<?php

$w		= (object) $words['add'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
//$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );

if( !$productId ){
	$list	= 'Keine Produkte vorhanden.';
	if( $products ){
		$list	= array();
		foreach( $products as $itemProduct ){
			$link		= UI_HTML_Tag::create( 'a', array(
				UI_HTML_Tag::create( 'h5', $itemProduct->title ),
				UI_HTML_Tag::create( 'div', $itemProduct->description ),
			), array(
				'href'	=> './manage/my/provision/license/add/'.$itemProduct->productId,
				'class'	=> 'btn btn-large'
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'list-products-item' ) );
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled list-products' ) );
	}

	$position	= '
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<span>Neue Lizenz erwerben</span>
	<i class="fa fa-fw fa-chevron-right"></i>
	<span>Produktliste</span>
	<hr/>
</div>';

	$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>Produkte</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
else if( !$productLicenseId ){
	$list	= array();
	foreach( $productLicenses as $itemProductLicense ){
		if( $itemProductLicense->productId != $productId )
			continue;
		$link		= UI_HTML_Tag::create( 'a', array(
			UI_HTML_Tag::create( 'h5', $itemProductLicense->title ),
			UI_HTML_Tag::create( 'div', $itemProductLicense->description ),
			$view->renderLicenseFacts( $itemProductLicense, array( 'users', 'duration', 'price' ) ),
		), array(
			'href'	=> './manage/my/provision/license/add/'.$productId.'/'.$itemProductLicense->productLicenseId,
			'class'	=> 'btn btn-large'
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'list-licenses-item' ) );
	}
	if( $list )
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled list-licenses' ) );
	else
		$list	= 'Keine Lizenzen für dieses Produkt vorhanden.';

	$position	= '
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<span>Neue Lizenz erwerben</span>
	<i class="fa fa-fw fa-chevron-right"></i>
	<a href="./manage/my/provision/license/add">Produktliste</a>
	<i class="fa fa-fw fa-chevron-right"></i>
	<strong>'.$product->title.'</strong>
	<hr/>
</div>';

	$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3><span class="muted">Lizenzen für </span>'.$product->title.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<a href="./manage/my/provision/license/add" class="btn btn-small">'.$iconCancel.'&nbsp;Produktliste</a>
		</div>
	</div>
</div>';
}
else{
	$optProduct	= array( '' => $w->optionEmpty );
	foreach( $products as $item )
		$optProduct[$item->productId]	= $item->title;
	$optProduct	= UI_HTML_Elements::Options( $optProduct, $productId );

	$optLicense	= array( '' => $w->optionEmpty );
	foreach( $productLicenses as $item )
		$optLicense[$item->productLicenseId]	= $item->title;
	$optLicense	= UI_HTML_Elements::Options( $optLicense, $productLicenseId );

	$optPayment	= UI_HTML_Elements::Options( $words['paymentTypes'] );

	$buttonOrder	= UI_HTML_Tag::create( 'button', $iconOrder.'&nbsp;'.$w->buttonOrder, array(
		'type'		=> "submit",
		'name'		=> "save",
		'class'		=> "btn btn-primary not-btn-large",
		'disabled'	=> $productId && $productLicenseId ? NULL : 'disabled',
	) );

	$fieldPayment	= '';
/*
	if( $productLicense ){
		if( $productLicense->price > 0 )
			$fieldPayment	= '
	<div class="row-fluid">
		<div class="span6">
			<label for="input_payment">'.$w->labelPayment.'</label>
			<select name="payment" id="input_payment" class="span12">'.$optPayment.'</select>
		</div>
	</div>';
*/
	$licenseInfo	= $view->renderLicenseFacts( $productLicense );

	$position	= '
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<span>Neue Lizenz erwerben</span>
	<i class="fa fa-fw fa-chevron-right"></i>
	<a href="./manage/my/provision/license/add">Produktliste</a>
	<i class="fa fa-fw fa-chevron-right"></i>
	<strong><a href="./manage/my/provision/license/add/'.$product->productId.'">'.$product->title.'</a></strong>
	<i class="fa fa-fw fa-chevron-right"></i>
	<span>'.$productLicense->title.'</span>
	<hr/>
</div>';

	$panelAdd		= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/provision/license/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_productId">'.$w->labelProductId.'</label>
					<select name="productId" id="input_productId" class="span12" onchange="selectProduct(this)">'.$optProduct.'</select>
				</div>
				<div class="span8">
					<label for="input_productLicenseId">'.$w->labelProductLicenseId.'</label>
					<select name="productLicenseId" id="input_productLicenseId" class="span12" onchange="selectProductLicense(this)">'.$optLicense.'</select>
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span12">
					<div class="license-description">
						'.$productLicense->description.'
					</div>
					<hr/>
					'.$licenseInfo.'
				</div>
			</div>
			<hr/>
			'.$fieldPayment.'
			<div class="row-fluid">
				<div class="span6">
					<label for="input_password">'.$w->labelPassword.'</label>
					<input type="password" name="password" id="input_password" class="span12"value="" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/provision/license/add/'.$productLicense->productId.'" class="btn btn-small">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary" value="1">'.$iconOrder.'&nbsp;bestellen</button>
			</div>
		</form>
	</div>
</div>
<script>
var productId = "'.$productId.'";
function selectProduct(elem){
	document.location.href = "./manage/my/provision/license/add/"+$(elem).val();
}
function selectProductLicense(elem){
	document.location.href = "./manage/my/provision/license/add/"+productId+"/"+$(elem).val();
}
</script>
';
}


$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/my/provision/license/add/' ) );

$tabs	= View_Manage_My_License::renderTabs( $env, 'add' );

return $tabs.$textTop.'
'.$position.'
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
		<br/>
	</div>
	<div class="span3">
		<div class="content-panel content-panel-info">
			<h3>Steps</h3>
			<div class="content-panel-inner">
				<h4>Step 1</h4>
				<p>
					1. First, select the license you need.
				</p>
				<p>
					2. You will see all details of the license.
				</p>
				<p>
					3. Select a payment type, if needed.
				</p>
			</div>
		</div>
	</div>
</div>
<style>
ul.list-products {

}
ul.list-products li.list-products-item {
	padding: 0.5em 0;
}

ul.list-products li.list-products-item .btn-large {
	font-size: 1em;
	text-align: left;
	width: 90%;
	padding: 0.5em 2em;
}
ul.list-products li.list-products-item .btn-large h5 {
	font-size: 1.5em;
	font-style: italic;
	line-height: 1.5em;
	text-align: center;
	padding: 0em 0em 0.25em 0em;
	border-bottom: 1px solid rgba(0,0,0,0.15);
}


ul.list-products ul.list-licenses {

}
ul.list-licenses li.list-licenses-item {
	padding: 0.5em 0;
}
ul.list-licenses li.list-licenses-item .btn-large {
	font-size: 1em;
	text-align: left;
	width: 90%;
	padding: 0.5em 2em;
}
ul.list-licenses li.list-licenses-item .btn-large h5 {
	font-size: 1.5em;
	font-style: italic;
	line-height: 1.5em;
	text-align: center;
	padding: 0em 0em 0.25em 0em;
	border-bottom: 1px solid rgba(0,0,0,0.15);
}
ul.list-products ul.list-licenses li.list-licenses-item .btn-large div {
	margin: 0em 1em;
}
</style>';
