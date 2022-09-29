<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['editStatus'];

$optStatus	= [];
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= HtmlElements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );

$iconAccept		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconBan		= HtmlTag::create( 'i', '', ['class' => 'icon-lock icon-white'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconAccept		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );
	$iconBan		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-lock'] );
	$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-remove'] );
}

$buttons	= [];
if( $user->status != 1 ){
	$buttons[]	= HtmlElements::LinkButton(
		'./manage/user/accept/'.$userId,
		$iconAccept.'&nbsp;'.$w->buttonAccept,
		'btn btn-small btn-success',
		$w->buttonAcceptConfirm
	);
}
if( $moduleConfig->get( 'ban' ) && $user->status == 1 ){
	$buttons[]	= HtmlElements::LinkButton(
		'./manage/user/ban/'.$userId,
		$iconBan.'&nbsp;'.$w->buttonBan,
		'btn btn-small btn-warning',
		$w->buttonBanConfirm
	);
}
if( $user->status != -2 ){
	$buttons[]	= HtmlElements::LinkButton(
		'./manage/user/disable/'.$userId,
		$iconRemove.'&nbsp;'.$w->buttonDisable,
		'btn btn-small btn-danger',
		$w->buttonDisableConfirm
	);
}
$buttons	= HtmlTag::create( 'div', $buttons, ['class' => "btn-group"] );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="editUserStates" action="./manage/user/edit/'.$userId.'" method="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="status">'.$w->labelStatus.'</label><br/>
				</div>
				<div class="span7">
					'.HtmlElements::Input( 'status', $words['status'][$user->status], 'span12 user-status status'.$user->status, TRUE ).'
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
