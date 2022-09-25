<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w  = (object) $words['view-content'];

$phraser    = new View_Helper_TimePhraser( $env );

/*
function renderUserLabel( $user ){
	if( !$user )
		return "-";
	$iconUser	= HtmlTag::create( 'i', '', array( 'class' => 'icon-user' ) );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= HtmlTag::create( 'abbr', $user->username, array( 'title' => $fullname ) );
	$label		= $iconUser.'&nbsp;'.$username;
	return HtmlTag::create( 'span', $label, array( 'class' => $spanClass ) );
}
*/

$panelVersions		= '';
if( 1 || $mission->versions ){
	$list	= [];
	foreach( $mission->versions as $version ){
		$date	= date( 'Y-m-d H:i:s', $version->timestamp );
		$date	= $phraser->convert( $version->timestamp, TRUE );
		$label	= '#'.$version->version.' <small class="muted">('.$date.') von '.$version->user->username.'</small>';
		$link	= HtmlTag::create( 'a', $label, array(
			'href'	=> './work/mission/view/'.$mission->missionId.'#version-'.$version->version,
		) );
		$list[]	= new UI_HTML_Tag( 'li', $link, array(
			'class'				=> 'version-list-item',
			'data-version'		=> $version->version,
		) );
	}
	$label	= '<strong>aktuell</strong>';
	$link	= HtmlTag::create( 'a', $label, array(
		'href'		=> './work/mission/view/'.$mission->missionId,
		'onclick'	=> 'return false'
	) );
	$list[]	= new UI_HTML_Tag( 'li', $link, array(
		'class'				=> 'version-list-item active',
//		'data-version'		=> 'current',
	) );
	$list	= array_reverse( $list );
	$list	= new UI_HTML_Tag( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	$panelVersions		= '
		<div class="content-panel">
			<h4>'.$w->headingVersions.'</h4>
			<div class="content-panel-inner">
				<div style="max-height: 510px; overflow-y: auto">
					'.$list.'
				</div>
			</div>
		</div>';
}

if( !strlen( trim( $mission->content ) ) )
	return '';

return '
<!--<hr/>-->
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h4>'.$w->headingContent.'</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<div id="mission-content-html" class="mission-content">
							<div id="work-missions-loader" style=""><em class="muted">... lade Inhalte ...</em></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span3">
		'.$panelVersions.'
	</div>
</div>';
?>
