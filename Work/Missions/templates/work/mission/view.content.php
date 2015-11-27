<?php

$w  = (object) $words['view'];

$phraser    = new View_Helper_TimePhraser( $env );

/*
function renderUserLabel( $user ){
	if( !$user )
		return "-";
	$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= UI_HTML_Tag::create( 'abbr', $user->username, array( 'title' => $fullname ) );
	$label		= $iconUser.'&nbsp;'.$username;
	return UI_HTML_Tag::create( 'span', $label, array( 'class' => $spanClass ) );
}
*/

$panelVersions		= '';
if( $mission->versions ){
	$list	= array();
	foreach( $mission->versions as $version ){
		$date	= date( 'Y-m-d H:i:s', $version->timestamp );
		$date	= $phraser->convert( $version->timestamp, TRUE );
		$label	= '#'.$version->version.' <small class="muted">('.$date.')</small>';
		$link	= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> './work/mission/view/'.$mission->missionId.'#version-'.$version->version,
		) );
		$list[]	= new UI_HTML_Tag( 'li', $link, array(
			'class'				=> 'version-list-item',
			'data-version'		=> $version->version,
		) );
	}
	$label	= '<strong>aktuell</strong>';
	$link	= UI_HTML_Tag::create( 'a', $label, array(
		'href'		=> './work/mission/view/'.$mission->missionId,
		'onclick'	=> 'return false'
	) );
	$list[]	= new UI_HTML_Tag( 'li', $link, array(
		'class'				=> 'version-list-item',
//		'data-version'		=> 'current',
	) );
	$list	= new UI_HTML_Tag( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	$panelVersions		= '
		<div class="content-panel">
			<h4>Versionen</h4>
			<div class="content-panel-inner">
				'.$list.'
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
			<h4>'.$w->legend.'</h4>
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
