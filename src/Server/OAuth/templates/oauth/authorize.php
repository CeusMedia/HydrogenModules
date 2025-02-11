<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var object $application */
/** @var array<string,array<string|int,string>> $words */

$w	= (object) $words['authorize'];

extract( $view->populateTexts( ['authorize.top', 'authorize.bottom'], 'html/oauth/', ['application' => $application] ) );

$iconCancel		= HtmlTag::create( 'i', '', ['fa fa-fw fa-arrow-left'] );
$iconLogin		= HtmlTag::create( 'i', '', ['fa fa-fw fa-sign-in'] );

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
						<a href="'.$application->url.'" class="btn">'.$iconCancel.' '.$w->buttonCancel.'</a>
						<button type="submit" name="login" class="btn btn-primary">'.$iconLogin.' '.$w->buttonLogin.'</button>
					</div>
				</form>
			</div>
		</div>
'.$textAuthorizeBottom;
