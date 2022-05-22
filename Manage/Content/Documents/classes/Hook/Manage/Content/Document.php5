<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Manage_Content_Document extends CMF_Hydrogen_Hook{

	public static function onRegisterHints( Environment $env, $context, $module, $arguments = NULL ){
		$words	= $env->getLanguage()->getWords( 'manage/content/document' );
		View_Helper_Hint::registerHints( $words['hints'], 'Manage_Content_Documents' );
	}

	static public function onTinyMCE_getLinkList( Environment $env, $context, $module, $arguments = [] ){
		$frontend		= $env->getLogic()->get( 'Frontend' );
		$moduleConfig	= $env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$pathFront		= $frontend->getPath();
		$pathDocuments	= $moduleConfig->get( 'path.documents' );

		$words			= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];

		$list			= [];
		if( file_exists( $pathFront ) && is_dir( $pathFront.$pathDocuments ) ){
			$model			= new Model_Document( $env, $pathFront.$pathDocuments );
			foreach( $model->index() as $nr => $entry ){
				$list[$entry.$nr]	= (object) array(
					'title'	=> /*$prefixes->document.*/$entry,
					'value'	=> $pathDocuments.$entry,
				);
			}
		}
		ksort( $list );
		$list	= array( (object) array(
			'title'	=> $prefixes->document,
			'menu'	=> array_values( $list ),
		) );

//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
	}
}
