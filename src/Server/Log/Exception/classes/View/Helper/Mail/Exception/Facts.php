<?php

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Exception\Logic as LogicException;
use CeusMedia\Common\Exception\SQL as SqlException;
use CeusMedia\HydrogenFramework\Environment as Environment;

class View_Helper_Mail_Exception_Facts
{
	protected Environment $env;
	protected ?Throwable $exception			= NULL;
	protected View_Helper_Mail_Facts $helper;
	protected bool $showPrevious			= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->helper	= new View_Helper_Mail_Facts();
	}

	/**
	 *	Sets exception to render.
	 *	@access		public
	 *	@param		Throwable		$exception		Exception
	 *	@return		self
	 */
	public function setException( Throwable $exception ): self
	{
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
	protected function getMeaningOfSQLSTATE( $SQLSTATE ): string
	{
		$class1	= substr( $SQLSTATE, 0, 2 );
		$class2	= substr( $SQLSTATE, 2, 3 );

		$words		= $this->env->getLanguage()->getWords( 'server/log/exception/sqlstate' );
		return $words[$class1][$class2] ?? 'unknown';
	}

	protected function prepare(): void
	{
		$words			= $this->env->getLanguage()->getWords( 'server/log/exception' );
		$this->helper->setLabels( $words['facts'] );
		$this->helper->setTextLabelLength( 12 );
		$this->helper->add( 'message', htmlentities( $this->exception->getMessage(), ENT_COMPAT, 'UTF-8' ) );
		$this->helper->add( 'code', htmlentities( $this->exception->getCode(), ENT_COMPAT, 'UTF-8' ) );

		$list	= [];

		if( $this->exception instanceof SqlException && $this->exception->getSQLSTATE() ){
			$meaning	= self::getMeaningOfSQLSTATE( $this->exception->getSQLSTATE() );
			$this->helper->add( 'sql', $this->exception->getSQLSTATE().': '.$meaning );
		}
		if( $this->exception instanceof IoException ){
			$this->helper->add( 'io', $this->exception->getResource() );
		}
		if( $this->exception instanceof LogicException ){
			$this->helper->add( 'logic', $this->exception->getSubject() );
		}
		$this->helper->add( 'class', $this->exception !== null ? get_class( $this->exception ) : self::class );

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

		if( $this->showPrevious ){
			if( method_exists( $this->exception, 'getPrevious' ) ){
				$previous	= $this->exception->getPrevious();
			 	if( NULL !== $previous ){
					$helperPrevious	= new View_Helper_Mail_Exception_Facts( $this->env );
//					$helperPrevious->setTextLabelLength( 12 );
					$helperPrevious->setException( $previous );
					$helperPrevious->setShowPrevious();
					$this->helper->add( 'previous', $helperPrevious->render(), $helperPrevious->renderAsText() );
				}
			}
		}
	}

	public function render(): string
	{
		return $this->helper->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render();
	}

	public function renderAsText(): string
	{
		return $this->helper->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render();
	}

	/**
	 *	Set whether previous exception should be included in view.
	 *	@access		public
	 *	@param		boolean			$show			Flag: show previous exceptions
	 *	@return		self
	 */
	public function setShowPrevious( $show = TRUE ): self
	{
		$this->showPrevious	= (bool) $show;
		return $this;
	}

	/**
	 *	Removes Document Root in File Names.
	 *	@access		protected
	 *	@static
	 *	@param		string		$fileName		File Name to clear
	 *	@return		string
	 */
	static protected function trimRootPath( string $fileName ): string
	{
		$rootPath	= $_SERVER['DOCUMENT_ROOT'] ?? "";
		if( !$rootPath || !$fileName )
			return '';
		$fileName	= str_replace( '\\', "/", $fileName );
		$cut		= substr( $fileName, 0, strlen( $rootPath ) );
		if( $cut == $rootPath )
			$fileName	= substr( $fileName, strlen( $rootPath ) );
		return $fileName;
	}
}
