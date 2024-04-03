<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Database_Lock extends Controller
{
	protected Model_Lock $model;

	public function ajaxRenderDashboardPanel( string $panelId ): void
	{
		$modelUser	= new Model_User( $this->env );
		$locks		= $this->model->getAll();
		foreach( $locks as $lock )
			$lock->user	= $modelUser->get( $lock->userId );
		$this->addData( 'locks', $locks );
	}

	public function index(): void
	{
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

	public function unlock( string $lockId ): void
	{
		$lock	= $this->model->get( $lockId );
		if( !$lock )
			$this->env->getMessenger()->noteError( 'Diese Sperre existiert nicht mehr.' );
		else
			$this->model->remove( $lockId );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_Lock( $this->env );
	}

	protected function getEntryTitle( object $lock ): string
	{
		$title	= '<em><small class="muted">unbekannt</small></em>';
		$uri	= NULL;
		switch( $lock->subject ){
			case 'WorkMission':
			case 'Work_Missions':
				$model		= new Model_Mission( $this->env );
				$mission	= $model->get( $lock->entryId );
				if( $mission ){
					$title	= TextTrimmer::trimCentric( $mission->title, 40 );
					$uri	= './work/mission/view/'.$lock->entryId;
				}
				break;
		}
		if( $uri )
			$title	= HtmlTag::create( 'a', $title, ['href' => $uri] );
		return $title;
	}
}
