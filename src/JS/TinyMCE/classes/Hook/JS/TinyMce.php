<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_TinyMce extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		/** @var WebEnvironment $env */
		$env	= $this->env;
		View_Helper_TinyMce::load( $env );
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );

		if( $config->get( 'auto' ) && $config->get( 'auto.selector' ) ){
			$language	= $env->getLanguage()->getLanguage();

			$baseUrl	= $env->url;
			if( $env->getModules()->has( 'Resource_Frontend' ) )
				$baseUrl	= Logic_Frontend::getInstance( $env )->getUrl();

			/* @todo extract to language file after rethinking this solution */
			$labels	= [
				'de'	=> 'Deutsch',
				'en'	=> 'Englisch',
			];

			/* @todo	WHY? please implement self::getLanguages similar to self::getLanguage */
			$languages	= [];
			$matches	= [];
			foreach( explode( ',', getEnv( 'HTTP_ACCEPT_LANGUAGE' ) ) as $item ){
				preg_match( "/^([a-z]{2})(-([A-Z]{2}))?(;q=([0-9].?[0-9]*))?$/", $item, $matches );
				if( isset( $matches[1] ) && isset( $labels[$matches[1]] ) ){
					$label	= $labels[$matches[1]];
					if( !in_array( $label."=".$matches[1], $languages ) )
						$languages[]	= $label."=".$matches[1];
				}
			}

			$styleFormats	= [
				[
					'title'		=> 'Blöcke',
					'items'		=> [
						[
							'title'				=> 'Absatz',
							'block'				=> 'p',
						],
						[
							'title'				=> 'Textblock',
							'block'				=> 'div',
						],
						[
							'title'				=> 'Zitatblock',
							'block'				=> 'blockquote',
							'wrapper'			=> TRUE,
						],
						[
							'title'				=> 'vorformatierter Text',
							'block'				=> 'pre',
						],
						[
							'title'				=> 'Abbildung',
							'block'				=> 'figure',
							'wrapper'			=> TRUE,
						],
						[
							'title'				=> 'HTML5: Sektion',
							'block'				=> 'section',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						],
						[
							'title'				=> 'HTML5: Artikel',
							'block'				=> 'article',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						],
						[
							'title'				=> 'HTML5: Marginale',
							'block'				=> 'aside',
							'wrapper'			=> TRUE,
						],
					]
				],
				[
					'title'		=> 'Bildformatierung',
					'items'		=> [
						[
							'title'		=> 'Ausrichtung',
							'items'		=> [
								[
									'title'		=> 'links',
									'selector'	=> 'img',
									'styles'	=> ['float' => 'left', 'margin' => '0 20px 10px 0px'],
								],
								[
									'title'		=> 'rechts',
									'selector'	=> 'img',
									'styles'	=> ['float' => 'right', 'margin' => '0 0 10px 20px'],
								],
							]
						],
						[
							'title'		=> 'Dekoration',
							'items'		=> [
								[
									'title'		=> 'abgerundet',
									'selector'	=> 'img',
									'classes'	=> 'img-rounded',
								],
								[
									'title'		=> 'kreisrund',
									'selector'	=> 'img',
									'classes'	=> 'img-circle',
								],
								[
									'title'		=> 'Polaroid',
									'selector'	=> 'img',
									'classes'	=> 'img-polaroid',
								],
							]
						],
						[
							'title'				=> 'In Lightbox öffnen',
							'selector'			=> 'a',
							'classes'			=> 'fancybox-auto',
						],
					]
				]
			];

			$options	= [
				'languages'		=> $languages,
				'envUri'		=> $env->url,
				'frontendUri'	=> $baseUrl,
				'frontendTheme'	=> $env->getConfig()->get( 'layout.theme' ),
				'language'		=> $language,
				'styleFormats'	=> $styleFormats,
			];
			if( 0 ){
				$helper	= new View_Helper_TinyMce( $env );
				$options['listImages']	= json_encode( $helper->getImageList() );
				$options['listLinks']	= json_encode( $helper->getLinkList() );
			}
			$this->context->js->addScriptOnReady( 'ModuleJsTinyMce.configAuto('.json_encode( $options ).')' );
			$this->context->js->addScriptOnReady( 'ModuleJsTinyMce.applyAuto()' );
		}
	}

	/**
	 *	@return		void
	 */
	public function onGetAvailableContentEditor(): void
	{
		if( !empty( $this->payload['type'] ) && $this->payload['type'] !== 'wys')
			return;
		if( !empty( $this->payload['format'] ) && $this->payload['format'] !== 'html')
			return;
		$editor	= (object) [
			'key'		=> 'tinymce',
			'label'		=> 'TinyMCE',
			'type'		=> 'wys',
			'format'	=> $this->payload['format'],
			'score'		=> 5,
		];
		$criteria	= [
			'default'		=> 1,
			'current'		=> 2,
			'force'			=> 10,
		];
		foreach( $criteria as $key => $value )
			if( !empty( $this->payload[$key] ) && strtolower( $this->payload[$key] ) === $editor->key )
				$editor->score	+= $value;

//		if( !empty( $payload['format'] ) ){}
		$key	= str_pad( $editor->score * 1000, 8, '0', STR_PAD_LEFT ).'_'.$editor->key;
		$this->payload['list'][$key]	= $editor;
	}
}
