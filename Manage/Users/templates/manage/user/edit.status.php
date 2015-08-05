 <?php

//$w	= (object) $words['editStatus'];

$optStatus	= array();
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );

return '
<div class="content-panel">
	<h4>'.$words['editStatus']['heading'].'</h4>
	<div class="content-panel-inner">
		<form name="editUserStates" action="./user/edit/'.$userId.'" method="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="status">'.$words['editStatus']['labelStatus'].'</label><br/>
				</div>
				<div class="span7">
					'.UI_HTML_Elements::Input( 'status', $words['status'][$user->status], 'span12 user-status status'.$user->status, TRUE ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12 buttonbar btn-group">
					'.UI_HTML_Elements::LinkButton(
						'./user/disable/'.$userId,
						'<i class="icon-remove icon-white"></i> '.$words['editStatus']['buttonDisable'],
						'btn btn-small btn-danger',
						$words['editStatus']['buttonDisableConfirm'],
						$user->status == -2
					).'
					'.UI_HTML_Elements::LinkButton(
						'./user/ban/'.$userId,
						'<i class="icon-lock icon-white"></i> '.$words['editStatus']['buttonBan'],
						'btn btn-small btn-warning',
						$words['editStatus']['buttonBanConfirm'],
						$user->status != 1
					).'
					'.UI_HTML_Elements::LinkButton(
						'./user/accept/'.$userId,
						'<i class="icon-ok icon-white"></i> '.$words['editStatus']['buttonAccept'],
						'btn btn-small btn-success',
						$words['editStatus']['buttonAcceptConfirm'],
						$user->status == 1
					).'
				</div>
			</div>
		</form>
	</div>
</div>
';
?>
