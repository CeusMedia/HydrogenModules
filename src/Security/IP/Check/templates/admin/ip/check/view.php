<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $entry */

$wf	= (object) $words['view'];

$backLink	= HtmlTag::create( 'a', '&larr; '.$wf->back, [
	'href'	=> './admin/ip/check',
	'class'	=> 'btn btn-default',
] );

// Decode JSON fields
$reasons	= $entry->reasons ?? '';
$reasonsArray	= json_decode( $reasons, TRUE );
if( is_array( $reasonsArray ) ){
	$reasonsHtml	= '<ul>';
	foreach( $reasonsArray as $reason )
		$reasonsHtml	.= '<li>'.htmlspecialchars( $reason ).'</li>';
	$reasonsHtml	.= '</ul>';
}
else {
	$reasonsHtml	= htmlspecialchars( $reasons );
}

$headers	= $entry->requestHeaders ?? NULL;
$headersHtml	= '';
if( NULL !== $headers && '' !== $headers ){
	$headersArray	= json_decode( $headers, TRUE );
	if( is_array( $headersArray ) ){
		$headersHtml	= '<dl class="dl-horizontal">';
		foreach( $headersArray as $key => $value )
			$headersHtml	.= '<dt>'.htmlspecialchars( $key ).'</dt><dd>'.htmlspecialchars( $value ).'</dd>';
		$headersHtml	.= '</dl>';
	}
}

$postData	= $entry->postData ?? NULL;
$postDataHtml	= '';
if( NULL !== $postData && '' !== $postData ){
	$postDataArray	= json_decode( $postData, TRUE );
	if( is_array( $postDataArray ) ){
		$postDataHtml	= '<dl class="dl-horizontal">';
		foreach( $postDataArray as $key => $value )
			$postDataHtml	.= '<dt>'.htmlspecialchars( $key ).'</dt><dd>'.htmlspecialchars( is_scalar( $value ) ? (string) $value : json_encode( $value ) ).'</dd>';
		$postDataHtml	.= '</dl>';
	}
	else {
		$postDataHtml	= '<pre>'.htmlspecialchars( $postData ).'</pre>';
	}
}

$deleteLink	= HtmlTag::create( 'a', new Icon( 'trash' ) . '&nbsp;'.$wf->delete, [
	'href'	=> './admin/ip/check/delete/'.$entry->id,
	'class'	=> 'btn btn-danger',
	'onclick'	=> 'return confirm("'.$wf->confirmDelete.'");',
] );

$actions	= '
<div class="row-fluid">
	<div class="span6">
		'.$backLink.'
	</div>
	<div class="span6 text-right">
		'.$deleteLink.'
	</div>
</div>';

$details	= '
<dl class="dl-horizontal">
	<dt>'.$wf->labelId.'</dt>
	<dd>'.$entry->id.'</dd>

	<dt>'.$wf->labelCreatedAt.'</dt>
	<dd>'.$entry->createdAt.'</dd>

	<dt>'.$wf->labelIp.'</dt>
	<dd><strong>'.$entry->ip.'</strong></dd>

	<dt>'.$wf->labelVersion.'</dt>
	<dd>IPv'.$entry->ipVersion.'</dd>

	<dt>'.$wf->labelScore.'</dt>
	<dd><strong>'.$entry->score.'</strong></dd>

	<dt>'.$wf->labelReasons.'</dt>
	<dd>'.$reasonsHtml.'</dd>

	<dt>'.$wf->labelMethod.'</dt>
	<dd>'.$entry->requestMethod.'</dd>

	<dt>'.$wf->labelUri.'</dt>
	<dd><code>'.$entry->requestUri.'</code></dd>
</dl>';

$headersSection	= '';
if( '' !== $headersHtml ){
	$headersSection	= '
<h4>'.$wf->legendHeaders.'</h4>
'.$headersHtml;
}

$postSection	= '';
if( '' !== $postDataHtml ){
	$postSection	= '
<h4>'.$wf->legendPostData.'</h4>
'.$postDataHtml;
}

return '
'.$actions.'
<h3>'.$wf->legend.'</h3>
'.$details.'
'.$headersSection.'
'.$postSection;
