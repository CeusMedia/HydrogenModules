<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var object $customer */
/** @var int|string $customerId */

$panelMap	= '';

if( $customer->latitude || $customer->longitude  ){
	$map	= HtmlTag::create( 'div', '', [
		'id'				=> 'map-customer',
		'class'				=> 'border',
		'data-latitude'		=> $customer->latitude,
		'data-longitude'	=> $customer->longitude,
		'data-marker-title'	=> $customer->title,
	] );
	$panelMap	= '
<!--<h4>Karte</h4>-->
'.$map.'
<script>$(document).ready(function(){loadMap("map-customer")});</script>';	
}

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId, 'map/'.$customerId );

$url	= preg_replace( "/^https?:\/\/(www.)?/", "", rtrim( $customer->url, '/' ) );

return '
<h3><span class="muted">Kunde</span> '.$customer->title.'</h3>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		'.$panelMap.'
	</div>
	<div class="span4">
		<h4>Adresse</h4>
		<address>
			<span>'.$customer->title.'</span><br/>
			<span>'.$customer->street.' '.$customer->nr.'</span><br/>
			<span>'.$customer->postcode.' '.$customer->city.'</span><br/>
		</address>
		<h4>Fakten</h4>
		<dl class="dl-horizontal short">
			<dt>Webseite</dt>
			<dd><a href="'.$customer->url.'" target="_blank">'.$url.'</a>&nbsp;</dd>
			<dt>E-Mail</dt>
			<dd><a href="mailto:'.$customer->email.'">'.$customer->email.'</a>&nbsp;</dd>
			<dt>Telefon</dt>
			<dd>'.( $customer->phone ?: '-' ).'&nbsp;</dd>
			<dt>Telefax</dt>
			<dd>'.( $customer->fax ?: '-' ).'&nbsp;</dd>
			<dt>GPS</dt>
			<dd>'.( $customer->latitude ? $customer->latitude.', '.$customer->longitude : '' ).'&nbsp;</dd>
		</dl>
	</div>
</div>
<style>
#map-customer {
	height: 400px;
	}
dl.short dt {
	width: 80px;
	}
dl.short dd {
	margin-left: 100px;
	}
</style>
';