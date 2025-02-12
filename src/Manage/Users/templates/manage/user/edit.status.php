<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Dictionary $moduleConfig */
/** @var View_Manage_User $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var array<object> $roles */
/** @var array<object> $projects */
/** @var object $user */

$w	= (object) $words['editStatus'];

$optStatus	= [];
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= HtmlElements::Option( (string) $key, $label, $key == $user->status, FALSE, 'user-status status'.$key );
$optStatus	= join( $optStatus );

$iconAccept		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );
$iconBan		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-lock'] );
$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-remove'] );

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
