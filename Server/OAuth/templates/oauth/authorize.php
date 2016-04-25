<?php
$w	= (object) $words['authorize'];

extract( $view->populateTexts( array( 'authorize.top', 'authorize.bottom' ), 'html/oauth/', array( 'application' => $application ) ) );

return $textAuthorizeTop.'
		<div class="content-panel">
			<div class="content-panel-inner">
				<h3>'.$w->heading.'</h3>
<!--				<div class="muted"><em><small>Scopes...</small></em></div>-->
				<form action="./oauth/authorize" method="post">
					<input type="hidden" name="client_id" value="'.htmlentities( $clientId, ENT_QUOTES, 'UTF-8' ).'"/>
					<input type="hidden" name="response_type" value="'.htmlentities( $responseType, ENT_QUOTES, 'UTF-8' ).'"/>
					<input type="hidden" name="redirect_uri" value="'.htmlentities( $redirectUri, ENT_QUOTES, 'UTF-8' ).'"/>
					<input type="hidden" name="state" value="'.htmlentities( $state, ENT_QUOTES, 'UTF-8' ).'"/>
					<input type="hidden" name="scope" value="'.htmlentities( $scope, ENT_QUOTES, 'UTF-8' ).'"/>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_username">'.$w->labelUsername.'</label>
							<input type="text" id="input_username" name="username"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_password">'.$w->labelPassword.'</label>
							<input type="password" id="input_password" name="password"/>
						</div>
					</div>
					<div class="buttonbar">
						<a href="'.$application->url.'" class="btn"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" name="login" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonLogin.'</button>
					</div>
				</form>
			</div>
		</div>
'.$textAuthorizeBottom;
?>
