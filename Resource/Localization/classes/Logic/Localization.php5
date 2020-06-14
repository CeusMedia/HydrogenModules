<?php
class Logic_Localization extends CMF_Hydrogen_Logic{

	protected $language;
	protected $languages;

	public function __onInit(){
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

	public function getLanguage(){
		return $this->language;
	}

	public function getLanguages(){
		return $this->languages;
	}

	public function setLanguage( $language ){
		if( !in_array( $language, $this->languages ) )
			throw new RangeException( 'Invalid language: '.$language );
		$this->language		= $language;
	}

	public function translate( $id, $content, $translated = NULL ){
		$this->env->getLog()->log("debug", "trying to translate $id to $this->language", $this);
		$indices		= array( 'language' => $this->language, 'id' => $id );
		$translation	= $this->model->getByIndices( $indices );
		
		if( $translated !== NULL && strlen( trim( $translated ) ) ){
			if( $this->language === $this->default )
				return 0;
			$data	= array_merge( $indices, array( 'content' => $translated ) );
			if( !$translation )
				return $this->model->add( $data, FALSE );
			return $this->model->edit( $translation->localizationId, $data, FALSE );
		}
		if( $translation )
			return $translation->content;
		return $content;
	}
}
