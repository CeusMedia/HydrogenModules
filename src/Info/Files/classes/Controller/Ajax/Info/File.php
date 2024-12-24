<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_File extends AjaxController
{
	/**	@var	Logic_Download							$logic				Logic class for file and folder management */
	protected Logic_Download $logic;

	/**	@var	Dictionary								$options			Module configuration object */
	protected Dictionary $options;

	/**	@var	string									$path				Base path to files */
	protected string $path;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renameFolder(): void
	{
		$folderId	= $this->request->get( 'folderId' );
		$title		= $this->request->get( 'name' );

		$this->logic->renameFolder( $folderId, $title );
		$this->respondData( $this->logic->getFolder( $folderId ) );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->options		= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$this->path			= $this->options->get( 'path' );
		$this->logic		= new Logic_Download( $this->env, $this->path );
	}
}
