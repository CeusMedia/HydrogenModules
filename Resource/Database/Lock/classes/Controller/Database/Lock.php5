<?php
class Controller_Database_Lock extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->model	= new Model_Lock( $this->env );
	}

	protected function getEntryTitle( $lock ){
		$title	= '<em><small class="muted">unbekannt</small></em>';
		$uri	= NULL;
		switch( $lock->subject ){
			case 'WorkMission':
			case 'Work_Missions':
				$model		= new Model_Mission( $this->env );
				$mission	= $model->get( $lock->entryId );
				if( $mission ){
					$title	= Alg_Text_Trimmer::trimCentric( $mission->title, 40 );
					$uri	= './work/mission/view/'.$lock->entryId;
				}
				break;
		}
		if( $uri )
			$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => $uri ) );
		return $title;
	}

	public function index(){
		$modules	= $this->env->getModules();
		$model		= new Model_User( $this->env );
		$locks		= $this->model->getAll();
		foreach( $locks as $lock ){
			$lock->module	= NULL;
			if( $modules->has( $lock->subject ) )
				$lock->module	= $modules->get( $lock->subject )->title;
			$lock->user		= $model->get( $lock->userId );
			$lock->title	= $this->getEntryTitle( $lock );
		}
		$this->addData( 'locks', $locks );
	}

	public function unlock( $lockId ){
		$lock	= $this->model->get( $lockId );
		if( !$lock )
			$this->env->getMessenger()->noteError( 'Diese Sperre existiert nicht mehr.' );
		else
			$this->model->remove( $lockId );
		$this->restart( NULL, TRUE );
	}
}
?>
