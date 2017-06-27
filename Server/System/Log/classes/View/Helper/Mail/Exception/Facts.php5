<?php
class View_Helper_Mail_Exception_Facts{

	protected $env;
	protected $exception;
	protected $helper;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( $env ){
		$this->env		= $env;
	}

	/**
	 *	Sets exception to render.
	 *	@access		public
	 *	@param		Exception	$exception		Exception
	 *	@return		self
	 */
	public function setException( Exception $exception ){
		$this->exception	= $exception;
		$this->prepare();
		return $this;
	}

	/**
	 *	Resolves SQLSTATE Code and returns its Meaning.
	 *	@access		protected
	 *	@return		string
	 *	@see		http://developer.mimer.com/documentation/html_92/Mimer_SQL_Mobile_DocSet/App_Return_Codes2.html
	 *	@see		http://publib.boulder.ibm.com/infocenter/idshelp/v10/index.jsp?topic=/com.ibm.sqls.doc/sqls520.htm
	 */
	protected function getMeaningOfSQLSTATE( $SQLSTATE ){
		$class1	= substr( $SQLSTATE, 0, 2 );
		$class2	= substr( $SQLSTATE, 2, 3 );

		$words		= $this->env->getLanguage()->getWords( 'server/system/sqlstate' );
		if( isset( $words[$class1][$class2] ) )
			return $words[$class1][$class2];
		return 'unknown';
	}

	protected function prepare(){
		$words			= $this->env->getLanguage()->getWords( 'server/system/log' );
		$this->helper	= new View_Helper_Mail_Facts( $this->env );
		$this->helper->setLabels( $words['exception-facts'] );
		$this->helper->setTextLabelLength( 12 );
		$this->helper->add( 'message', htmlentities( $this->exception->getMessage(), ENT_COMPAT, 'UTF-8' ) );
		$this->helper->add( 'code', htmlentities( $this->exception->getCode(), ENT_COMPAT, 'UTF-8' ) );

		$list	= array();

		if( $this->exception instanceof Exception_SQL && $this->exception->getSQLSTATE() ){
			$meaning	= self::getMeaningOfSQLSTATE( $this->exception->getSQLSTATE() );
			$this->helper->add( 'sql', $this->exception->getSQLSTATE().': '.$meaning );
		}
		if( $this->exception instanceof Exception_IO  ){
			$this->helper->add( 'io', $this->exception->getResource() );
		}
		if( $this->exception instanceof Exception_Logic ){
			$this->helper->add( 'logic', $this->exception->getSubject() );
		}
		$this->helper->add( 'class', get_class( $this->exception ) );

		$root		= realpath( $this->env->uri );
		$pathName	= ltrim( $this->exception->getFile(), $root );
		$fileName	= '<span class="file">'.pathinfo( $pathName, PATHINFO_FILENAME ).'</span>';
		$extension	= pathinfo( $pathName, PATHINFO_EXTENSION );
		$extension	= '<span class="ext">'.( $extension ? '.'.$extension : '' ).'</span>';
		$path		= '<span class="path">'.dirname( $pathName ).'/</span>';
		$file		= $path.$fileName.$extension;

		$this->helper->add( 'file', $file, $pathName );
		$this->helper->add( 'line', (string) $this->exception->getLine() );
		$this->helper->add( 'root', realpath( $this->env->uri ).'/' );
	}

	public function render(){
		if( !$this->helper )
			throw new RuntimeException( 'No exception set' );
		return $this->helper->render();
	}

	public function renderAsText(){
		if( !$this->helper )
			throw new RuntimeException( 'No exception set' );
		return $this->helper->renderAsText();
	}

	/**
	 *	Removes Document Root in File Names.
	 *	@access		protected
	 *	@static
	 *	@param		string		$fileName		File Name to clear
	 *	@return		string
	 */
	static protected function trimRootPath( $fileName )
	{
		$rootPath	= isset( $_SERVER['DOCUMENT_ROOT'] ) ? $_SERVER['DOCUMENT_ROOT'] : "";
		if( !$rootPath || !$fileName )
			return;
		$fileName	= str_replace( '\\', "/", $fileName );
		$cut		= substr( $fileName, 0, strlen( $rootPath ) );
		if( $cut == $rootPath )
			$fileName	= substr( $fileName, strlen( $rootPath ) );
		return $fileName;
	}
}
?>
