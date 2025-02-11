<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;

class Hook_Info_Contact extends Hook
{
	/**
	 *	@return		void
	 *	@throws		Exception
	 */
	public function onRenderContent(): void
	{
		/** @var WebEnvironment $env */
		$env	= $this->env;
		if( !$this->env->getModules()->has( 'UI_Shortcode' ) )
			return;

		$processor		= new Logic_Shortcode( $this->env );
		$moduleConfig	= $this->env->getConfig()->getAll( 'modules.info_contact.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/contact' );
		$allowedTypes	= $moduleConfig->getAll( 'modal.show.type.' );

		$shortCodes		= [
			'contact:form'		=> [
				'button-class'	=> 'btn',
				'button-label'	=> $words['modal-form']['trigger'],
				'heading'		=> $words['modal-form']['heading'],
				'icon-class'	=> 'fa-envelope',
				'icon-position'	=> 'left',
				'types'			=> join( ',', array_keys( $allowedTypes ) ),
				'type'			=> $moduleConfig->get( 'modal.default.type' ),
				'subject'		=> '',
			]
		];
		$processor->setContent( $this->payload['content'] );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$isFirst		= TRUE;
			$helperModal	= new View_Helper_Info_Contact_Form_Modal( $this->env );
			$helperTrigger	= new View_Helper_Info_Contact_Form_Trigger();
			while( FALSE !== ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					if( str_starts_with( $attr['button-class'], 'btn-' ) )
						$attr['button-class']	= 'btn '.$attr['button-class'];
					if( str_starts_with( $attr['icon-class'], 'fa-' ) )
						$attr['icon-class']		= 'fa fa-fw '.$attr['icon-class'];

					$types	= [];
					if( '' !== trim( $attr['types'] ?? '' ) )
						$types	= preg_split( '/\s*,\s*/', trim( $attr['types'] ) );

					$type	= NULL;
					if( '' !== trim( $attr['type'] ?? '' ) )
						$type	= trim( $attr['type'] );

					$modalId	= 'modal-'.uniqid();
					$helperModal->setId( $modalId );
					$helperModal->setHeading( $attr['heading'] );
					$helperModal->setSubject( trim( $attr['subject'] ) );
					$helperModal->setTypes( $types );
					$helperModal->setType( $type );
		//			$helperModal->setFrom( $env->getRequest()->get( '__path' ) );
					$helperTrigger->setmodalId( $modalId );
					$helperTrigger->setClass( $attr['button-class'] );
					$helperTrigger->setLabel( $attr['button-label'] );
					$helperTrigger->setIcon( $attr['icon-class'] );
					$helperTrigger->setIconPosition( $attr['icon-position'] );
					$replacement	= $helperTrigger->render().$helperModal->render();		//  load news panel
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
					if( $isFirst ){
						$view		= new View( $env );
						$blocks		= (object) [
							'success'	=> $view->loadContentFile( 'html/info/contact/form/success.html' ),
							'error'		=> $view->loadContentFile( 'html/info/contact/form/error.html' ),
						];
						$script	= 'ModuleInfoContactForm.setResultBlocks('.json_encode( $blocks ).');';
						$env->getPage()->js->addScriptOnReady( $script );
						$isFirst = FALSE;
					}
				}
				catch( Exception $e ){
					$this->env->getLog()->logException( $e );
					$this->env->getMessenger()->noteFailure( 'Short code "'.$shortCode.'" failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}
}
