<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $project */
/** @var array $missions */

$w		= (object) $words['edit-info'];
$facts	= [];
if( isset( $missions ) && count( $missions ) ){
	$url	= './work/mission/filter?projects[]='.$project->projectId;
	$label	= HtmlTag::create( 'a', count( $missions ), ['href' => $url] );
	$facts[]	= HtmlTag::create( 'dt', $w->labelMissions ).HtmlTag::create( 'dd', $label );
}

if( isset( $issues ) ){
	$url	= './work/issue/filter?projects[]='.$project->projectId;
	$button	= HtmlElements::LinkButton( $url, $w->buttonView, 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$facts[]	= HtmlTag::create( 'dt', $w->labelIssues ).HtmlTag::create( 'dd', $label );
}

if( !$facts )
	return '';
	
return '
<div class="content-panel content-panel-info">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<dl>
			'.join( $facts ).'
		</dl>
	</div>
</div>';