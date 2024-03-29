<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Localization extends Logic
{
	protected $language;
	protected $languages;

	public function getLanguage()
	{
		return $this->language;
	}

	public function getLanguages()
	{
		return $this->languages;
	}

	public function setLanguage( $language )
	{
		if( !in_array( $language, $this->languages ) )
			throw new RangeException( 'Invalid language: '.$language );
		$this->language		= $language;
	}

	public function translate( $id, $content, $translated = NULL )
	{
		$this->env->getLog()->log("debug", "trying to translate $id to $this->language", $this);
		$indices		= ['language' => $this->language, 'id' => $id];
		$translation	= $this->model->getByIndices( $indices );

		if( $translated !== NULL && strlen( trim( $translated ) ) ){
			if( $this->language === $this->default )
				return 0;
			$data	= array_merge( $indices, ['content' => $translated] );
			if( !$translation )
				return $this->model->add( $data, FALSE );
			return $this->model->edit( $translation->localizationId, $data, FALSE );
		}
		if( $translation )
			return $translation->content;
		return $content;
	}

	protected function __onInit(): void
	{
		$this->model		= new  Model_Localization( $this->env );
		$this->languages	= $this->env->getLanguage()->getLanguages();
		$this->default		= $this->env->getLanguage();
		$this->setLanguage( $this->env->getLanguage()->getLanguage() );
		if(  $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$env				= $frontend->getRemoteEnv( $this->env );
			$this->default		= $frontend->getDefaultLanguage();
			$this->languages	= $frontend->getLanguages();
			$this->setLanguage( $env->getLanguage()->getLanguage() );
		}
	}
}
