<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock/reason',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'	=> './manage/ip/lock/reason/remove/'.$reason->ipLockReasonId,
	'class'	=> 'btn btn-danger btn-small',
) );

$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate.' aktivieren', array(
	'href'		=> './manage/ip/lock/reason/activate/'.$reason->ipLockReasonId,
	'class'		=> 'btn btn-success btn-small',
	'title'		=> 'activate',
) );
if( $reason->status ){
	$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate.' deaktivieren', array(
		'href'	=> './manage/ip/lock/reason/deactivate/'.$reason->ipLockReasonId,
		'class'	=> 'btn btn-inverse btn-small',
		'title'	=> 'deactivate',
	) );
}

$optStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktiv',
	0		=> 'inaktiv',
), $reason->status );


$list	= '<div><em><small>Keine Filter vorhanden.</small></em></div>';
if( $reason->filters ){
	$list	= array();
	foreach( $reason->filters as $filter ){
		$link	= UI_HTML_Tag::create( 'a', $filter->title, array(
			'href'	=> './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link, array() );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}
$panelFilters	= '
<div class="content-panel">
	<h3>Filters <small>('.count( $reason->filters ).')</small></h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

$panelEdit	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock/reason">IP-Sperr-Grund:</a> '.$reason->title.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/ip/lock/reason/edit/'.$reason->ipLockReasonId.'" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title" class="required mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $reason->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span1">
					<label for="input_code"><abbr title="HTTP-Status-Code">Code</abbr></label>
					<input type="text" name="code" id="input_code" class="span12" required="required" value="'.htmlentities( $reason->code, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_duration"><abbr title="in Sekunden">Dauer</abbr></label>
					<input type="text" name="duration" id="input_duration" class="span12" value="'.$reason->duration.'"/>
				</div>
				<div class="span2">
					<label for="input_status">Status</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung <small class="muted">(erscheint als Text auf der Fehlerseite)</small></label>
					<textarea name="description" id="input_description" class="span12" rows="5">'.htmlentities( $reason->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
				'.$buttonStatus.'
				'.$buttonRemove.'
<!--				&nbsp;|&nbsp;-->
			</div>
		</form>
	</div>
</div>';

$heading	= '<h2>IP-Sperren</h2>';
$tabs		= View_Manage_Ip_Lock::renderTabs( $env, 'reason' );
return /*$heading.*/$tabs.HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		$panelEdit
	).
    HTML::DivClass( 'span4',
        $panelFilters
	)
).
HTML::DivClass( 'row-fluid', ''
);
