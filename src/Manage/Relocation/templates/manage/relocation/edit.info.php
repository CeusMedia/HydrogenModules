<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit-info'];

$links		= [];

$linkTarget		= HtmlTag::create( 'a', $w->labelLinkTarget, [
	'href'		=> $relocation->url,
	'target'	=> '_blank',
] );
$links[]	= HtmlTag::create( 'dd', $linkTarget );

$linkService	= HtmlTag::create( 'a', $w->labelLinkService, [
	'href'		=> $this->env->url.'info/relocation/'.$relocation->relocationId,
	'target'	=> '_blank',
] );
$links[]	= HtmlTag::create( 'dd', $linkService );


if( $shortcut ){
	$linkServiceShort	= HtmlTag::create( 'a', $w->labelLinkShortcut, [
		'href'		=> $this->env->url.$shortcut.'/'.$relocation->relocationId,
		'target'	=> '_blank',
	] );
	$links[]	= HtmlTag::create( 'dd', $linkServiceShort );
}

$links		= join( "\n", $links );

$helper		= new View_Helper_TimePhraser( $env );
$createdAt	= $helper->convert( $relocation->createdAt, TRUE, $w->prefixTimePhraser, $w->suffixTimePhraser );
$usedAt		= $helper->convert( $relocation->usedAt, TRUE, $w->prefixTimePhraser, $w->suffixTimePhraser );

return '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<dl class="dl-horizontal">
							<dt>'.$w->labelId.'</dt>
							<dd>'.$relocation->relocationId.'</dd>
							<dt>'.$w->labelLinks.'</dt>
							'.$links.'
							<dt>'.$w->labelViews.'</dt>
							<dd>'.$relocation->views.'</dd>
							<dt>'.$w->labelCreatedAt.'</dt>
							<dd>'.$createdAt.'</dd>
							<dt>'.$w->labelUsedAt.'</dt>
							<dd>'.$usedAt.'</dd>
						</dl>
					</div>
				</div>
			</div>
		</div>
';
