<?php
abstract class View_Helper_Work_Mission_Abstract extends CMF_Hydrogen_View_Helper_Abstract{

	protected $useAvatar	= FALSE;
	protected $users		= array();
	protected $modules;

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->modules		= $this->env->getModules();
		$useAvatar			= $this->modules->has( 'Manage_My_User_Avatar' );
		$useGravatar		= $this->modules->has( 'UI_Helper_Gravatar' );
		$this->useAvatar	= $useAvatar || $useGravatar;
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

		$avatar	= '';
		if( $this->modules->has( 'Manage_My_User_Avatar' ) ){
			$avatar	= new View_Helper_UserAvatar( $this->env );
			$avatar->setUser( $worker );
			$avatar->setSize( 20 );
			$avatar->useGravatar( $this->env->getConfig()->get( 'module.manage_my_user_avatar.use.gravatar' ) );
			$avatar	= $avatar->render();
		}
		else if( $this->modules->has( 'UI_Helper_Gravatar' ) ){
			$avatar	= new View_Helper_Gravatar( $this->env );
			$avatar->setUser( $worker );
			$avatar->setSize( 20 );
			$avatar	= $avatar->render();
		}

		$workerPic	= UI_HTML_Tag::create( 'div', $avatar, array( 'class' => 'user-avatar' ) );
		$workerName	= UI_HTML_Tag::create( 'div', $worker->username, array( 'class' => 'user-label autocut', 'style' => 'width: '.$width.'px' ) );
		return UI_HTML_Tag::create( 'div', $workerPic.$workerName, array(
			'class'	=> 'user not-autocut',
			'title'	=> $worker->username,
		) );
	}
}
?>
