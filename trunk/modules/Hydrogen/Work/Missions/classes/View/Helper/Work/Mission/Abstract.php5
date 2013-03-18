<?php
abstract class View_Helper_Work_Mission_Abstract extends CMF_Hydrogen_View_Helper_Abstract{

	protected $useGravatar	= FALSE;

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->useGravatar		= $this->env->getModules()->has( 'UI_Helper_Gravatar' );
	}

	protected function formatDays( $days ){
		if( $days > 365.25 )
			return floor( $days / 365.25 )."y";
		if( $days > 30.42 )
			return floor( $days / 30.42 )."m";
		if( $days > 7 )
			return floor( $days / 7 )."w";
		return $days;
	}

	protected function renderTime( $timestamp ){
		$hours	= date( 'H', $timestamp );
		$mins	= '<sup><small>'.date( 'i', $timestamp ).'</small></sup>';
		return $hours.$mins;
	}

	protected function renderUserWithAvatar( $userId ){
		$modelUser	= new Model_User( $this->env );
		$worker		= $modelUser->get( $userId );
		if( !$this->useGravatar )
			return $worker->username;
		$gravatar	= new View_Helper_Gravatar( $this->env );
		$workerPic	= $gravatar->getImage( $worker->email, 20 );
		$workerPic	= UI_HTML_Tag::create( 'span', $workerPic, array( 'class' => 'user-avatar' ) );
		$workerName	= UI_HTML_Tag::create( 'span', $worker->username, array( 'class' => 'user-label' ) );
		return UI_HTML_Tag::create( 'div', $workerPic.' '.$workerName );
	}
}
?>
