<?php

$w	= (object) $words['view'];

$helperGravatar	= new View_Helper_Gravatar( $env );

$image	= UI_HTML_Tag::create( 'img', NULL, array(
	'src'	=> $helperGravatar->getUrl( $user->email, 160 ),
	'class'	=> 'img-polaroid',
) );

$modelRole	= new Model_Role( $env );
$role		= $modelRole->get( $user->roleId );

$data	= print_m( $user, NULL, NULL, TRUE );

$buttonCancel	= UI_HTML_Tag::create( 'a', $w->buttonCancel, array(
	'href'		=> $from ? $from : './member/search',
	'class'		=> 'btn btn-small',
) );
$buttonRequest	= '';
$buttonRelease	= '';
if( $user->userId !== $currentUserId ){
	$buttonRequest	= UI_HTML_Tag::create( 'a', $w->buttonRequest, array(
		'href'		=> './member/request/'.$user->userId,
		'class'		=> 'btn btn-small btn-primary',
	) );
	$buttonRelease	= UI_HTML_Tag::create( 'a', $w->buttonRelease, array(
		'href'		=> '#',
		'class'		=> 'btn btn-small btn-inverse',
		'disabled'	=> 'disabled'
	) );
	if( $relation ){
		$buttonRequest	= UI_HTML_Tag::create( 'a', $w->buttonRequest, array(
			'href'		=> '#',
			'class'		=> 'btn btn-small btn-primary',
			'disabled'	=> 'disabled'
		) );
		$buttonRelease	= UI_HTML_Tag::create( 'a', $w->buttonRelease, array(
			'href'		=> './member/release/'.$relation->userRelationId,
			'class'		=> 'btn btn-small btn-inverse',
		) );
	}
}

$panelInfo	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span8">
				<dl class="dl-horizontal">
					<dt>Bentzername</dt>
					<dd><big><strong>'.$user->username.'</strong></big></dd>
					<dt>Vor- und Nachname</dt>
					<dd>'.$user->firstname.' '.$user->surname.'</dd>
					<dt>Rolle</dt>
					<dd>'.$role->title.'&nbsp;<small class="muted">('.$role->roleId.')</small></dd>
					<dt>E-Mail-Adresse</dt>
					<dd><a href="mailto:'.$user->email.'">'.$user->email.'</a></dd>
					<dt>Telefon</dt>
					<dd>'.( $user->phone ? $user->phone : '-' ).'</dd>
					<dt>Telefax</dt>
					<dd>'.( $user->fax ? $user->fax : '-' ).'</dd>
					<dt>registriert am</dt>
					<dd>'.date( 'd.m.Y', $user->createdAt ).'&nbsp;<small class="muted">'.date( 'H:i:s', $user->createdAt ).'</small><dd>
					<dt>zuletzt eingeloggt am</dt>
					<dd>'.( $user->loggedAt ? date( 'd.m.Y', $user->loggedAt ) : '-' ).'&nbsp;<small class="muted">'.( $user->loggedAt ? date( 'H:i:s', $user->loggedAt ) : '' ).'</small><dd>
<!--					<dt>zuletzt aktiv am</dt>
					<dd>'.( $user->activeAt ? date( 'd.m.Y', $user->activeAt ) : '-' ).'&nbsp;<small class="muted">'.( $user->activeAt ? date( 'H:i:s', $user->activeAt ) : '' ).'</small><dd>-->
					<dt>Status</dt>
					<dd>'.$words['user-states'][$user->status].'</dd>
					<dt>Verbindung</dt>
					<dd><em class="muted">???</em></dd>
				</dl>
			</div>
			<div class="span4 pull-right">
				'.$image.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonRequest.'
			'.$buttonRelease.'
		</div>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span6">
		'.$panelInfo.'
	</div>
</div>';




