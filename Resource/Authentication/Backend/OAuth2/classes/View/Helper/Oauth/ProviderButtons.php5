<?php
class View_Helper_Oauth_ProviderButtons {

	protected $env;
	protected $from;
	protected $linkPath			= './auth/local/login';
	protected $dropdownLabel	= 'more';

	public function __construct( $env ){
		$this->env				= $env;
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
	}

	public function count(){
		$conditions	= array( 'status' => Model_Oauth_Provider::STATUS_ACTIVE );
		return $this->modelProvider->count( $conditions );
	}

	public function setFrom( $from ){
		$this->from	= $from;
		return $this;
	}

	public function setLinkPath( $path ){
		$this->linkPath		= $path;
		return $this;
	}

	public function setDropdownLabel( $label ){
		$this->dropdownLabel	= $label;
		return $this;
	}

	public function render(){
		$conditions	= array( 'status' => Model_Oauth_Provider::STATUS_ACTIVE );
		$orders		= array( 'rank' => 'ASC' );
		$providers	= $this->modelProvider->getAll( $conditions, $orders );
		$buttons	= array();
		$dropdown	= array();
		$from		= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
		foreach( $providers as $provider ){
			$icon	= '';
			if( $provider->icon )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $provider->icon ) ).'&nbsp;';
			$label	=  UI_HTML_Tag::create( 'a', $icon.$provider->title, array(
				'href'	=> $this->linkPath.$provider->oauthProviderId.$from,
				'class'	=> 'btn not-btn-info',
			) );
			if( $provider->rank < 10)
				$buttons[]	= $label;
			else{
				$dropdown[]	= UI_HTML_Tag::create( 'li', array(
					UI_HTML_Tag::create( 'a', $icon.$provider->title, array(
						'href'	=> $this->linkPath.$provider->oauthProviderId.$from,
					) )
				) );
			}
		}
		if( $dropdown ){
			$buttons[]	= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'a', $this->dropdownLabel.' <span class="caret"></span>', array(
					'href'			=> $this->linkPath.$provider->oauthProviderId.$from,
					'class'			=> 'btn dropdown-toggle',
					'data-toggle'	=> 'dropdown'
				) ),
				UI_HTML_Tag::create( 'ul', $dropdown, array( 'class' => 'dropdown-menu' ) ),
			), array( 'class' => 'btn-group' ) );
		}
		return join( ' ', $buttons );
	}
}
?>