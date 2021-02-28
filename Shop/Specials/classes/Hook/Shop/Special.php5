<?php
class Hook_Shop_Special extends CMF_Hydrogen_Hook
{
	static public $articleId		= 0;

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$context		Hook context object
	 *	@param		object						$module			Module object
	 *	@param		public						$payload		Map of hook arguments
	 *	@return		void
	 */
	public static function onPageInit( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$request	= $env->getRequest();
		$model		= new Model_Shop_Bridge( $env );
		$bridge		= $model->getByIndices( array(
			'frontendUriPath'	=> $request->get( '__controller' ).'/',
		) );
		if( !$bridge )
			return;
//		if( $request->get( '__action' ) !== 'article' )
//			return;
		if( !count( $request->get( '__arguments' ) ) )
			return;
		static::$articleId = (int) $request->get( '__arguments' )[0];
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$context		Hook context object
	 *	@param		object						$module			Module object
	 *	@param		public						$payload		Map of hook arguments
	 *	@return		void
	 */
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
//		remark( 'articleId: '.static::$articleId );die;
		if( !static::isSpecial() )
			return;
		$model		= new Model_Shop_Special( $env );
		$special	= $model->getByIndices( array( 'articleId' => static::$articleId ) );
		if( !$special )
			return;

//		remark( 'articleId: '.static::$articleId );
//		print_m( $special );
		if( !strlen( trim( $special->styleFiles ) ) )
			$special->styleFiles	= '[]';
		$files	= json_decode( $special->styleFiles, TRUE );
		foreach( $files as $file )
			$context->addStylesheet( $file );
		if( strlen( trim( $special->styleRules ) ) )
			$context->css->theme->addStyle( $special->styleRules );
		$context->addBodyClass( 'specialOffer' );
	}

	public static function isSpecial(){
		return static::$articleId > 0;
	}
}
