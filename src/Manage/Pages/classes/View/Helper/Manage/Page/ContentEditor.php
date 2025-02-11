<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Manage_Page_ContentEditor
{
	public const STATUS_INIT		= 0;
	public const STATUS_CONFIGURED	= 1;
	public const STATUS_COLLECTED	= 2;

	protected Environment $env;
	protected ?string $defaultEditorKey	= NULL;
	protected ?string $currentEditorKey	= NULL;
	protected ?string $forcedEditorKey	= NULL;
	protected ?string $bestEditorKey	= NULL;
	protected int $status				= 0;
	protected ?string $format			= NULL;
	protected array $editors			= [];
	protected ?string $type				= NULL;
	protected ?string $labelTemplate	= NULL;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	/**
	 *	@return		string
	 */
	public function getBestEditor(): string
	{
		return $this->collectEditors()->bestEditorKey;
	}

	public function getEditors(): array
	{
		return $this->collectEditors()->editors;
	}

	public function render(): string
	{
		return '';
	}

	public function setCurrentEditor( string $key ): static
	{
		$key	= strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $key ) );
		if( $this->currentEditorKey !== $key ){
			$this->currentEditorKey	= $key;
			$this->status	= static::STATUS_CONFIGURED;
		}
		return $this;
	}

	public function setDefaultEditor( string $key ): static
	{
		$key	= strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $key ) );
		if( $this->defaultEditorKey !== $key ){
			$this->defaultEditorKey	= $key;
			$this->status	= static::STATUS_CONFIGURED;
		}
		return $this;
	}

	public function setForcedEditor( string $key ): static
	{
		$key	= strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $key ) );
		if( $this->forcedEditorKey !== $key ){
			$this->forcedEditorKey	= $key;
			$this->status	= static::STATUS_CONFIGURED;
		}
		return $this;
	}

	public function setFormat( string $format ): static
	{
		$format	= strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $format ) );
		if( $this->format !== $format ){
			$this->format	= $format;
			$this->status	= static::STATUS_CONFIGURED;
		}
		return $this;
	}

	public function setLabelTemplate( $template ): static
	{
		$this->labelTemplate	= $template;
		return $this;
	}

	public function setType( string $type ): static
	{
		$type	= strtolower( preg_replace( '/[^a-z0-9_-]/i', '', $type ) );
		if( $this->type !== $type ){
			$this->type	= $type;
			$this->status	= static::STATUS_CONFIGURED;
		}
		return $this;
	}

	/**
	 *	@return		static
	 */
	protected function collectEditors(): static
	{
		if( $this->status === static::STATUS_COLLECTED )
			return $this;
		$payload	= [
			'list'		=> [],
			'type'		=> $this->type,
			'format'	=> $this->format,
			'default'	=> $this->defaultEditorKey,
			'current'	=> $this->currentEditorKey,
		];
		try{
			$this->env->getCaptain()->callHook(
				'Module',
				'onGetAvailableContentEditor',
				$this,
				$payload
			);
		}
		catch( Throwable $e ){
			$this->env->getLog()?->logException( $e );
		}
		$this->status		= static::STATUS_COLLECTED;
		krsort( $payload['list'] );
		$this->editors	= [];
		$this->bestEditorKey = $payload['list'] ? current( $payload['list'] )->key : '';
		foreach( $payload['list'] as $editor ){
			if( $this->labelTemplate )
				$editor->label	= sprintf( $this->labelTemplate, $editor->label );
			$this->editors[$editor->key]	= $editor->label;
		}
		return $this;
	}
}