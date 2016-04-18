 <?php

//$w	= (object) $words['editStatus'];

$optStatus	= array();
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );

$iconAccept		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconBan		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-lock icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconAccept		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconBan		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconRemove		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$buttons	= array();
if( $user->status != 1 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/accept/'.$userId,
		$iconAccept.'&nbsp;'.$words['editStatus']['buttonAccept'],
		'btn btn-small btn-success',
		$words['editStatus']['buttonAcceptConfirm']
	);
}
if( $moduleConfig->get( 'ban' ) && $user->status == 1 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/ban/'.$userId,
		$iconBan.'&nbsp;'.$words['editStatus']['buttonBan'],
		'btn btn-small btn-warning',
		$words['editStatus']['buttonBanConfirm']
	);
}
if( $user->status != -2 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/disable/'.$userId,
		$iconRemove.'&nbsp;'.$words['editStatus']['buttonDisable'],
		'btn btn-small btn-danger',
		$words['editStatus']['buttonDisableConfirm']
	);
}
$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => "btn-group" ) );

return '
<div class="content-panel">
	<h3>'.$words['editStatus']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="editUserStates" action="./manage/user/edit/'.$userId.'" method="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="status">'.$words['editStatus']['labelStatus'].'</label><br/>
				</div>
				<div class="span7">
					'.UI_HTML_Elements::Input( 'status', $words['status'][$user->status], 'span12 user-status status'.$user->status, TRUE ).'
				</div>
			</div>
			<div class="buttonbar">
				'.$buttons.'
			</div>
		</form>
	</div>
</div>
';
?>
