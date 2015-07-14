<?php
class Logic_CSRF{

    const STATUS_ABUSED		= -3;
    const STATUS_OUTDATED	= -2;
    const STATUS_NOT_USED	= -1;
    const STATUS_OPEN		= 0;
    const STATUS_USED		= 1;

    const CHECK_OK					= 1;
    const CHECK_FORM_NAME_MISSING	= 2;
    const CHECK_TOKEN_MISSING		= 3;
    const CHECK_TOKEN_INVALID		= 4;
	const CHECK_TOKEN_USED			= 5;
    const CHECK_TOKEN_OUTDATED		= 6;
    const CHECK_TOKEN_REPLACED		= 7;
    const CHECK_SESSION_MISMATCH	= 8;
    const CHECK_IP_MISMATCH			= 9;

    static protected $instance;

	protected $env;
	protected $ip;
	protected $model;
	protected $moduleConfig;
	protected $sessionId;

    protected function __construct( $env ){
        $this->env          = $env;
        $this->model        = new Model_CSRF_Token( $env );
        $this->sessionId    = $env->getSession()->getSessionId();
        $this->ip           = getEnv( 'REMOTE_ADDR' );
        $this->moduleConfig	= $this->env->getConfig()->getAll( 'module.security_csrf.', TRUE );
        $this->cancelOutdatedTokens();
    }

    static public function getInstance( $env ){
        if( !self::$instance )
            self::$instance = new Logic_CSRF( $env );
        return self::$instance;
    }

    protected function cancelOutdatedTokens(){
        $outdatedTokens = $this->model->getAll( array(
            'status'    => self::STATUS_OPEN,
            'timestamp' => '<'.( time() - $this->moduleConfig->get( 'duration' ) ),
        ) );
        foreach( $outdatedTokens as $token ){
            $this->model->edit( $token->tokenId, array(
                'status'    => self::STATUS_OUTDATED
            ) );
        }
        return count( $outdatedTokens );
    }

    protected function cancelOldTokens( $formName ){
        $tokens  = $this->model->getAllByIndices( array(
            'status'    => self::STATUS_OPEN,
            'sessionId' => $this->sessionId,
            'ip'        => $this->ip,
            'formName'  => $formName,
        ) );
        foreach( $tokens as $token ){
            $this->model->edit( $token->tokenId, array(
                'status'    => self::STATUS_NOT_USED
            ) );
        }
        return count( $tokens );
    }

    public function getToken( $formName ){
        $this->cancelOldTokens( $formName );
        $token      = md5( $this->ip.$this->sessionId.$formName.microtime( TRUE ) );
        $tokenId    = $this->model->add( array(
            'status'    => self::STATUS_OPEN,
            'sessionId' => $this->sessionId,
            'ip'        => $this->ip,
            'token'     => $token,
			'formName'	=> $formName,
            'timestamp' => time(),
        ) );
        return $token;
    }

    public function verifyToken( $formName, $token ){
        if( !strlen( trim( $formName ) ) )
            return self::CHECK_FORM_NAME_MISSING;
        if( !strlen( trim( $token ) ) )
            return self::CHECK_TOKEN_MISSING;
        $entry  = $this->model->getByIndex( 'token', $token );
        if( !$entry )
            return self::CHECK_TOKEN_INVALID;
        if( $entry->status == self::STATUS_USED )
            return self::CHECK_TOKEN_USED;
        if( $entry->status == self::STATUS_NOT_USED )
            return self::CHECK_TOKEN_REPLACED;
        if( $entry->status == self::STATUS_OUTDATED )
            return self::CHECK_TOKEN_OUTDATED;
        if( $entry->sessionId !== $this->sessionId )
            return self::CHECK_SESSION_MISMATCH;
        if( $entry->ip !== $this->ip )
            return self::CHECK_IP_MISMATCH;
        $this->model->edit( $entry->tokenId, array( 'status' => self::STATUS_USED ) );
        return self::CHECK_OK;
    }
}
?>
