<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Relocation extends Controller
{
	protected MessengerResource $messenger;
	protected Model_Relocation $model;

	/**
	 *	@todo		inform manager
	 */
	public function fail( $id = NULL )
	{
	}

	/**
	 *	@param		?string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( ?string $id = NULL ): void
	{
		$words		= (object) $this->getWords( 'index' );				//  load words
		if( !trim( $id ) ){														//  no ID given
			$this->messenger->noteError( $words->errorIdMissing );				//  note error
			$this->restart( 'fail/'.$id, TRUE );				//  redirect to fail page
		}
		$relocation	= $this->model->get( $id );									//  try to get relocation
		if( !$relocation ){														//  no relocation found for ID
			$this->messenger->noteError( $words->errorIdInvalid );				//  note error
			$this->restart( 'fail/'.$id, TRUE );				//  redirect to fail page
		}
		if( $relocation->status == 0 ){											//  relocation is prepared, only
			$this->messenger->noteError( $words->errorIdPrepared );				//  note error
			$this->restart( 'fail/'.$id, TRUE );				//  redirect to fail page
		}
		if( $relocation->status < 0 ){											//  relocation is deactivated
			$this->messenger->noteError( $words->errorIdOutdated );				//  note error
			$this->restart( 'fail/'.$id, TRUE );				//  redirect to fail page
		}

		$referer	= getEnv( 'HTTP_REFERER' );							//  get request origin
		$regex		= "/^".preg_quote( $this->env->url, '/' ).'/';		//  quote app base url as regular expression
		if( !preg_match( $regex, $referer ) ){									//  request is from outside
			$this->model->edit( $id, [											//  update relocation
				'views'		=> $relocation->views + 1,							//  by use count
				'usedAt'	=> time(),											//  and latest use time
			] );
			if( 1 === (int) $relocation->status )								//  status is "activated"
				$this->model->edit( $id, ['status' => 2] );						//  set new status "used"
		}

		$status		= $this->moduleConfig->get( 'status' );				//  get HTTP status for redirection
		$this->relocate( $relocation->url, $status );							//  relocate to target
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Relocation( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_relocation.', TRUE );
	}
}
