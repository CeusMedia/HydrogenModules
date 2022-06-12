<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_TinyMce extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $data = [])
	{
		View_Helper_TinyMce::load( $env );
		$config		= $env->getConfig()->getAll( 'module.js_tinymce.', TRUE );

		if( $config->get( 'auto' ) && $config->get( 'auto.selector' ) ){
			$language	= $env->getLanguage()->getLanguage();

			$baseUrl	= $env->url;
			if( $env->getModules()->has( 'Resource_Frontend' ) )
				$baseUrl	= Logic_Frontend::getInstance( $env )->getUri();

			/* @todo extract to language file after rethinking this solution */
			$labels	= array(
				'de'	=> 'Deutsch',
				'en'	=> 'Englisch',
			);

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

			$styleFormats	= array(
				array(
					'title'		=> 'Blöcke',
					'items'		=> array(
						array(
							'title'				=> 'Absatz',
							'block'				=> 'p',
						),
						array(
							'title'				=> 'Textblock',
							'block'				=> 'div',
						),
						array(
							'title'				=> 'Zitatblock',
							'block'				=> 'blockquote',
							'wrapper'			=> TRUE,
						),
						array(
							'title'				=> 'vorformatierter Text',
							'block'				=> 'pre',
						),
						array(
							'title'				=> 'Abbildung',
							'block'				=> 'figure',
							'wrapper'			=> TRUE,
						),
						array(
							'title'				=> 'HTML5: Sektion',
							'block'				=> 'section',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						),
						array(
							'title'				=> 'HTML5: Artikel',
							'block'				=> 'article',
							'wrapper'			=> TRUE,
							'merge_siblings'	=> FALSE,
						),
						array(
							'title'				=> 'HTML5: Marginale',
							'block'				=> 'aside',
							'wrapper'			=> TRUE,
						),
					)
				),
				array(
					'title'		=> 'Bildformatierung',
					'items'		=> array(
						array(
							'title'		=> 'Ausrichtung',
							'items'		=> array(
								array(
									'title'		=> 'links',
									'selector'	=> 'img',
									'styles'	=> array( 'float' => 'left', 'margin' => '0 20px 10px 0px'),
								),
								array(
									'title'		=> 'rechts',
									'selector'	=> 'img',
									'styles'	=> array( 'float' => 'right', 'margin' => '0 0 10px 20px'),
								),
							)
						),
						array(
							'title'		=> 'Dekoration',
							'items'		=> array(
								array(
									'title'		=> 'abgerundet',
									'selector'	=> 'img',
									'classes'	=> 'img-rounded',
								),
								array(
									'title'		=> 'kreisrund',
									'selector'	=> 'img',
									'classes'	=> 'img-circle',
								),
								array(
									'title'		=> 'Polaroid',
									'selector'	=> 'img',
									'classes'	=> 'img-polaroid',
								),
							)
						),
						array(
							'title'				=> 'In Lightbox öffnen',
							'selector'			=> 'a',
							'classes'			=> 'fancybox-auto',
						),
					)
				)
			);

			$options	= array(
				'languages'		=> $languages,
				'envUri'		=> $env->url,
				'frontendUri'	=> $baseUrl,
				'frontendTheme'	=> $env->getConfig()->get( 'layout.theme' ),
				'language'		=> $language,
				'styleFormats'	=> $styleFormats,
			);
			if(0){
				$helper	= new View_Helper_TinyMce( $env );
				$options['listImages']	= json_encode( $helper->getImageList() );
				$options['listLinks']	= json_encode( $helper->getLinkList() );
			}
			$context->js->addScriptOnReady( 'ModuleJsTinyMce.configAuto('.json_encode( $options ).')' );
			$context->js->addScriptOnReady( 'ModuleJsTinyMce.applyAuto()' );
		}
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onGetAvailableContentEditor( Environment $env, $context, $module, $payload = [] ){
		if( !empty( $payload->type ) && !in_array( $payload->type, array( 'wys' ) ) )
			return;
		if( !empty( $payload->format ) && !in_array( $payload->format, array( 'html' ) ) )
			return;
		$editor	= (object) array(
			'key'		=> 'tinymce',
			'label'		=> 'TinyMCE',
			'type'		=> 'wys',
			'format'	=> $payload->format,
			'score'		=> 5,
		);
		$criteria	= array(
			'default'		=> 1,
			'current'		=> 2,
			'force'			=> 10,
		);
		foreach( $criteria as $key => $value )
			if( !empty( $payload->$key ) && strtolower( $payload->$key ) === $editor->key )
				$editor->score	+= $value;

//		if( !empty( $payload->format ) ){}
		$key	= str_pad( $editor->score * 1000, 8, '0', STR_PAD_LEFT ).'_'.$editor->key;
		$payload->list[$key]	= $editor;
	}
}
