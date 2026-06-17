<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Localization extends Logic
{
	protected Model_Localization $model;
	protected string $default;
	protected string $language;
	protected array $languages;

	/**
	 *	@return		string
	 */
	public function getLanguage(): string
	{
		return $this->language;
	}

	/**
	 *	@return		array
	 */
	public function getLanguages(): array
	{
		return $this->languages;
	}

	/**
	 *	@param		string		$language
	 *	@return		self
	 */
	public function setLanguage( string $language ): self
	{
		if( !in_array( $language, $this->languages ) )
			throw new RangeException( 'Invalid language: '.$language );
		$this->language		= $language;
		return $this;
	}

	/**
	 *	Returns found content for translation ID in current language.
	 *	Fallback to given content.
	 *
	 *  Also supports adding new translation or update existing by giving translated content.
	 *	Returns ID of translation in this case.
	 *
	 *	@param		string			$id					ID of to find for current language
	 *	@param		string|NULL		$content			Content to find translation for
	 *	@param		string|NULL		$translated			Translation to add or update
	 *	@param		bool			$stripTagsOnAdd		Default: yes
	 *	@return		int|string
	 *	@todo		returning int also is very uncool, think about the way, reading and writing translations could be done cooler
	 *	@todo		add / save is disable if current language is default language - why?
	 */
	public function translate( string $id, ?string $content = NULL, ?string $translated = NULL, bool $stripTagsOnAdd = TRUE ): int|string
	{
		$this->env->getLog()->log("debug", "trying to translate $id to $this->language", $this);
		$indices		= ['language' => $this->language, 'id' => $id];
		$translation	= $this->model->getByIndices( $indices );

		//  ADD / SAVE MODE
		if( NULL !== $translated && '' !== trim( $translated ) ){
			if( $this->language === $this->default )
				return 0;
			$data	= array_merge( $indices, ['content' => $translated] );
			if( !$translation )
				return $this->model->add( $data, FALSE );
			$this->model->edit( $translation->localizationId, $data, $stripTagsOnAdd );
			return $translation->localizationId;
		}

		if( $translation )
			return $translation->content;

		return $content ?? '';
	}

	/**
	 *	@return		void
	 *	@throws		CeusMedia\HydrogenFramework\Environment\Exception
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_Localization( $this->env );
		$this->languages	= $this->env->getLanguage()->getLanguages();
		$this->default		= $this->env->getLanguage()->getLanguage();
		$this->setLanguage( $this->env->getLanguage()->getLanguage() );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$env				= $frontend->getRemoteEnv( $this->env );
			$this->default		= $frontend->getDefaultLanguage();
			$this->languages	= $frontend->getLanguages();
			$this->setLanguage( $env->getLanguage()->getLanguage() );
		}
	}
}
