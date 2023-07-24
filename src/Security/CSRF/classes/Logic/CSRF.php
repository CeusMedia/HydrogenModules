<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_CSRF extends Logic
{
	public const STATUS_ABUSED		= -3;
	public const STATUS_OUTDATED	= -2;
	public const STATUS_NOT_USED	= -1;
	public const STATUS_OPEN		= 0;
	public const STATUS_USED		= 1;

	public const STATUSES			= [
		self::STATUS_ABUSED,
		self::STATUS_OUTDATED,
		self::STATUS_NOT_USED,
		self::STATUS_OPEN,
		self::STATUS_USED,
	];

	public const CHECK_OK					= 1;
	public const CHECK_FORM_NAME_MISSING	= 2;
	public const CHECK_TOKEN_MISSING		= 3;
	public const CHECK_TOKEN_INVALID		= 4;
	public const CHECK_TOKEN_USED			= 5;
	public const CHECK_TOKEN_OUTDATED		= 6;
	public const CHECK_TOKEN_REPLACED		= 7;
	public const CHECK_SESSION_MISMATCH		= 8;
	public const CHECK_IP_MISMATCH			= 9;

	public const CHECKS						= [
		self::CHECK_OK,
		self::CHECK_FORM_NAME_MISSING,
		self::CHECK_TOKEN_MISSING,
		self::CHECK_TOKEN_INVALID,
		self::CHECK_TOKEN_USED,
		self::CHECK_TOKEN_OUTDATED,
		self::CHECK_TOKEN_REPLACED,
		self::CHECK_SESSION_MISMATCH,
		self::CHECK_IP_MISMATCH,
	];

	/**	@var		string|NULL				$ip */
	protected ?string $ip;

	/**	@var		Model_CSRF_Token		$model */
	protected Model_CSRF_Token $model;

	/** @var		Dictionary				$moduleConfig */
	protected Dictionary $moduleConfig;

	/** @var		string					$sessionId */
	protected string $sessionId;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit()
	{
		$this->model		= new Model_CSRF_Token( $this->env );
		$this->sessionId	= $this->env->getSession()->getSessionId();
		$this->ip			= getEnv( 'REMOTE_ADDR' );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.security_csrf.', TRUE );
		$this->cancelOutdatedTokens();
	}

	/**
	 *	@param		string		$formName
	 *	@return		string
	 */
	public function getToken( string $formName ): string
	{
		$this->cancelOldTokens( $formName );
		$token		= md5( $this->ip.$this->sessionId.$formName.microtime( TRUE ) );
		$tokenId	= $this->model->add( [
			'status'	=> self::STATUS_OPEN,
			'sessionId'	=> $this->sessionId,
			'ip'		=> $this->ip,
			'token'		=> $token,
			'formName'	=> $formName,
			'timestamp'	=> time(),
		] );
		return $token;
	}

	/**
	 *	@param		string		$formName
	 *	@param		string		$token
	 *	@return		int
	 */
	public function verifyToken( string $formName, string $token ): int
	{
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
		$this->model->edit( $entry->tokenId, ['status' => self::STATUS_USED] );
			return self::CHECK_OK;
	}

	/**
	 *	@return		int|NULL
	 */
	protected function cancelOutdatedTokens(): ?int
	{
		$outdatedTokens	= $this->model->getAll( [
			'status'	=> self::STATUS_OPEN,
			'timestamp'	=> '< '.( time() - $this->moduleConfig->get( 'duration' ) ),
		] );
		foreach( $outdatedTokens as $token ){
			$this->model->edit( $token->tokenId, [
				'status'	=> self::STATUS_OUTDATED
			] );
		}
		return count( $outdatedTokens );
	}

	/**
	 *	@param		string		$formName
	 *	@return		int|NULL
	 */
	protected function cancelOldTokens( string $formName ): ?int
	{
		$tokens  = $this->model->getAllByIndices( [
			'status'	=> self::STATUS_OPEN,
			'sessionId'	=> $this->sessionId,
			'ip'		=> $this->ip,
			'formName'	=> $formName,
		] );
		foreach( $tokens as $token ){
			$this->model->edit( $token->tokenId, [
				'status'	=> self::STATUS_NOT_USED
			] );
		}
		return count( $tokens );
	}
}
