<?php
$w			= (object) $words['edit-info'];

$links		= array();

$linkTarget		= UI_HTML_Tag::create( 'a', $w->labelLinkTarget, array(
	'href'		=> $relocation->url,
	'target'	=> '_blank',
) );
$links[]	= UI_HTML_Tag::create( 'dd', $linkTarget );

$linkService	= UI_HTML_Tag::create( 'a', $w->labelLinkService, array(
	'href'		=> $this->env->url.'info/relocation/'.$relocation->relocationId,
	'target'	=> '_blank',
) );
$links[]	= UI_HTML_Tag::create( 'dd', $linkService );


if( $shortcut ){
	$linkServiceShort	= UI_HTML_Tag::create( 'a', $w->labelLinkShortcut, array(
		'href'		=> $this->env->url.$shortcut.'/'.$relocation->relocationId,
		'target'	=> '_blank',
	) );
	$links[]	= UI_HTML_Tag::create( 'dd', $linkServiceShort );
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
