<?php
class Controller_Info_Relocation extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $model;

	public function __onInit(){
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Relocation( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_relocation.', TRUE );
	}

	static public function ___onReadyCheckShortcutRoute( $env, $module, $context, $data = array() ){
		$config	= $env->getConfig()->getAll( 'module.info_relocation.', TRUE );	//  shortcut config
		if( $env->getModules()->has( 'Server_Router' ) ){						//  router module is installed
			if( $config->get( 'shortcut' ) ){									//  shortcut is enabled
				$model	= new Model_Route( $env );								//  get router module
				if( !$model->getByIndex( 'target', 'info/relocation/$1' ) ){	//  shortcut route is not set up yet
					$model->add( array(											//  add shortcut route
						'status'	=> 1,										//  ... as active
						'regex'		=> 1,										//  ... as regular expression
						'code'		=> $config->get( 'shortcut.code' ),			//  ... with HTTP code
						'source'	=> $config->get( 'shortcut.source' ),		//  ... with source pattern
						'target'	=> $config->get( 'shortcut.target' ),		//  ... with target pattern
						'createdAt'	=> time(),									//  ... and note creation time
					) );
				}
			}
		}
	}

	/**
	 *	@todo		inform manager
	 */
	public function fail( $id = NULL ){
	}

	public function index( $id = NULL ){
		$words		= (object) $this->getWords( 'index' );						//  load words
		if( !trim( $id ) ){														//  no ID given
			$this->messenger->noteError( $words->errorIdMissing );				//  note error
			$this->restart( 'fail/'.$id, TRUE );								//  redirect to fail page
		}
		$relocation	= $this->model->get( $id );									//  try to get relocation
		if( !$relocation ){														//  no relocation found for ID
			$this->messenger->noteError( $words->errorIdInvalid );				//  note error
			$this->restart( 'fail/'.$id, TRUE );								//  redirect to fail page
		}
		if( $relocation->status == 0 ){											//  relocation is prepared, only
			$this->messenger->noteError( $words->errorIdPrepared );				//  note error
			$this->restart( 'fail/'.$id, TRUE );								//  redirect to fail page
		}
		if( $relocation->status < 0 ){											//  relocation is deactivated
			$this->messenger->noteError( $words->errorIdOutdated );				//  note error
			$this->restart( 'fail/'.$id, TRUE );								//  redirect to fail page
		}

		$referer	= getEnv( 'HTTP_REFERER' );									//  get request origin
		$regex		= "/^".preg_quote( $this->env->url, "/" )."/";				//  quote app base url as regular expression
		if( !preg_match( $regex, $referer ) ){									//  request is from outside
			$this->model->edit( $id, array(										//  update relocation
				'views'		=> $relocation->views + 1,							//  by use count
				'usedAt'	=> time(),											//  and latest use time
			) );
			if( $relocation->status == 1 )										//  status is "activated"
				$this->model->edit( $id, array( 'status' => 2 ) );				//  set new status "used"
		}

		$status		= $this->moduleConfig->get( 'status' );						//  get HTTP status for redirection
		$this->relocate( $relocation->url, $status );							//  relocate to target
	}
}
?>
