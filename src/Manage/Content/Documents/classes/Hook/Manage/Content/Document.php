<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Content_Document extends Hook
{
	public function onRegisterHints(): void
	{
		if( !class_exists( 'View_Helper_Hint' ) )
			return;
		$words	= $this->env->getLanguage()->getWords( 'manage/content/document' );
		View_Helper_Hint::registerHints( $words['hints'], 'Manage_Content_Documents' );
	}

	public function onTinyMCE_getLinkList(): void
	{
		$frontend		= $this->env->getLogic()->get( 'Frontend' );
		$moduleConfig	= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$pathFront		= $frontend->getPath();
		$pathDocuments	= $moduleConfig->get( 'path.documents' );

		$words			= $this->env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];

		$list			= [];
		if( file_exists( $pathFront ) && is_dir( $pathFront.$pathDocuments ) ){
			$model			= new Model_Document( $this->env, $pathFront.$pathDocuments );
			foreach( $model->index() as $nr => $entry ){
				$list[$entry.$nr]	= (object) [
					'title'	=> /*$prefixes->document.*/$entry,
					'value'	=> $pathDocuments.$entry,
				];
			}
		}
		ksort( $list );
		$list	= array( (object) array(
			'title'	=> $prefixes->document,
			'menu'	=> array_values( $list ),
		) );

//		$this->context->list	= array_merge( $context->list, array_values( $list ) );
		$this->context->list	= array_merge( $this->context->list, $list );
	}
}
