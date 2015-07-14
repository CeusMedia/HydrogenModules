<?php
class Controller_Csrf extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;

	public function __onInit(){
		$this->logic		= Logic_CSRF::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.security_csrf.', TRUE );
	}

	public function ajaxGetToken(){
		$this->checkAjaxRequest();
		$formName	= $this->env->getRequest()->get( 'formName' );
		try{
			if( !$formName )
				throw new InvalidArgumentException( 'Form name is missing' );
	        $token	= $this->logic->getToken( $formName );
			$this->handleJsonResponse( TRUE, array( 'token' => $token ) );
		}
		catch( Exception $e ){
			$this->handleJsonResponse( FALSE, array(
				'error'		=> $e->getMessage(),
				'exception'	=> $e
			) );
		}
	}

	public function checkToken( $redirectUrl = NULL ){
		$token		= $this->env->getRequest()->get( 'csrf_token' );							//  get token from request
		$formName	= $this->env->getRequest()->get( 'csrf_form_name' );						//  get form name from request
		$result		= $this->logic->verifyToken( $formName, $token );							//  check token against environment
		if( $result !== Logic_CSRF::CHECK_OK ){													//  there has been an error
			$statusCode	= 401;																	//  HTTP status: Forbidden
			$msg		= (object) $this->getWords( 'msg', 'csrf' );							//  load language
			switch( $result ){																	//  dispatch error
				case LOGIC_CSRF::CHECK_FORM_NAME_MISSING:										//  form name is missing
					$this->messenger->noteFailure( $msg->error_form_name_missing );				//  note failure
					break;
				case LOGIC_CSRF::CHECK_TOKEN_MISSING:											//  token is missing
					$this->messenger->noteFailure( $msg->error_token_missing );					//  note failure
					break;
				case LOGIC_CSRF::CHECK_TOKEN_INVALID:											//  token not found
					$this->messenger->noteFailure( $msg->error_token_invalid );					//  note failure
					break;
				case LOGIC_CSRF::CHECK_TOKEN_USED:												//  token already has been used
					$this->messenger->noteError( $msg->error_token_used );						//  note error
					break;
				case Logic_CSRF::CHECK_TOKEN_REPLACED:											//  form has been loaded again since
					$statusCode	= 409;															//  HTTP status: Conflict
                    $this->messenger->noteError( $msg->error_token_replaced );					//  note error
					break;
				case Logic_CSRF::CHECK_TOKEN_OUTDATED:											//  token is too old
					$statusCode	= 408;															//  HTTP status: Request Timeout
					$maxMinutes	= floor( $this->moduleConfig->get( 'duration' ) / 60 );			//  calculate time out minutes
					$message	= sprintf( $msg->error_token_outdated, $maxMinutes );			//  generate message
                    $this->messenger->noteError( $message );									//  note error
					break;
                case Logic_CSRF::CHECK_SESSION_MISMATCH:										//  session ID is not matching to token
					$statusCode	= 409;															//  HTTP status: Conflict
					$this->messenger->noteFailure( $msg->error_session_mismatch );				//  note failure
					break;
				case Logic_CSRF::CHECK_IP_MISMATCH:												//  IP is not matching to token
					$statusCode	= 409;															//  HTTP status: Conflict
					$this->messenger->noteFailure( $msg->error_ip_mismatch );					//  note failure
					break;
				default:																		//  all others
					$message	= sprintf( $msg->error_unhandled_result, $result );				//  generate message
					$this->messenger->noteFailure( $message );									//  note failure
					break;
			}
			if( $redirectUrl )
				$this->restart( './'.$redirectUrl, $statusCode );
			$this->restart( getEnv( 'HTTP_REFERER' ), $statusCode );
		}
		return TRUE;
	}
}
