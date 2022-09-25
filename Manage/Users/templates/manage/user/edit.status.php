<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['editStatus'];

$optStatus	= [];
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );

$iconAccept		= HtmlTag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconBan		= HtmlTag::create( 'i', '', array( 'class' => 'icon-lock icon-white' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconAccept		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconBan		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconRemove		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$buttons	= [];
if( $user->status != 1 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/accept/'.$userId,
		$iconAccept.'&nbsp;'.$w->buttonAccept,
		'btn btn-small btn-success',
		$w->buttonAcceptConfirm
	);
}
if( $moduleConfig->get( 'ban' ) && $user->status == 1 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/ban/'.$userId,
		$iconBan.'&nbsp;'.$w->buttonBan,
		'btn btn-small btn-warning',
		$w->buttonBanConfirm
	);
}
if( $user->status != -2 ){
	$buttons[]	= UI_HTML_Elements::LinkButton(
		'./manage/user/disable/'.$userId,
		$iconRemove.'&nbsp;'.$w->buttonDisable,
		'btn btn-small btn-danger',
		$w->buttonDisableConfirm
	);
}
$buttons	= HtmlTag::create( 'div', $buttons, array( 'class' => "btn-group" ) );

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
