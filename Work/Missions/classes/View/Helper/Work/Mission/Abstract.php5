<?php
abstract class View_Helper_Work_Mission_Abstract extends CMF_Hydrogen_View_Helper_Abstract{

	protected $useAvatar	= FALSE;
	protected $users		= array();

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->useAvatar		= class_exists( 'View_Helper_UserAvatar' );
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

	protected function renderUserWithAvatar( $userId, $width = 160 ){
		$modelUser	= new Model_User( $this->env );
		if( !array_key_exists( (int) $userId, $this->users ) )
			$this->users[(int) $userId] = $modelUser->get( (int) $userId );
		if( !$this->users[(int) $userId] )
			return "UNKNOWN";
		$worker	= $this->users[(int) $userId];

		if( !$this->useAvatar )
			return $worker->username;
		$avatar	= new View_Helper_UserAvatar( $this->env );
		$avatar->setUser( $worker );
		$avatar->setSize( 20 );
		$workerPic	= UI_HTML_Tag::create( 'div', $avatar->render(), array( 'class' => 'user-avatar' ) );
		$workerName	= UI_HTML_Tag::create( 'div', $worker->username, array( 'class' => 'user-label autocut', 'style' => 'width: '.$width.'px' ) );
		return UI_HTML_Tag::create( 'div', $workerPic.$workerName, array(
			'class'	=> 'user not-autocut',
			'title'	=> $worker->username,
		) );
	}
}
?>
