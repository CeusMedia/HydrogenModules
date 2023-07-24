<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Oauth_ProviderButtons
{
	protected $env;
	protected $from;
	protected $linkPath			= './auth/local/login';
	protected $dropdownLabel	= 'more';

	public function __construct( $env )
	{
		$this->env				= $env;
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
	}

	public function count()
	{
		$conditions	= ['status' => Model_Oauth_Provider::STATUS_ACTIVE];
		return $this->modelProvider->count( $conditions );
	}

	public function render(): string
	{
		$conditions	= ['status' => Model_Oauth_Provider::STATUS_ACTIVE];
		$orders		= ['rank' => 'ASC'];
		$providers	= $this->modelProvider->getAll( $conditions, $orders );
		$buttons	= [];
		$dropdown	= [];
		$from		= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
		foreach( $providers as $provider ){
			$icon	= '';
			if( $provider->icon )
				$icon	= HtmlTag::create( 'i', '', ['class' => $provider->icon] ).'&nbsp;';
			$label	=  HtmlTag::create( 'a', $icon.$provider->title, array(
				'href'		=> $this->linkPath.$provider->oauthProviderId.$from,
				'class'		=> 'btn not-btn-info oauth2-provider',
				'onclick'	=> "jQuery('#modalLoadingOauth2').modal();",
			) );
			if( $provider->rank < 10)
				$buttons[]	= $label;
			else{
				$dropdown[]	= HtmlTag::create( 'li', array(
					HtmlTag::create( 'a', $icon.$provider->title, array(
						'href'		=> $this->linkPath.$provider->oauthProviderId.$from,
						'class'		=> 'oauth2-provider',
						'onclick'	=> "jQuery('#modalLoadingOauth2').modal();",
					) )
				) );
			}
		}
		if( $dropdown ){
			$buttons[]	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'a', $this->dropdownLabel.' <span class="caret"></span>', [
					'href'			=> $this->linkPath.$provider->oauthProviderId.$from,
					'class'			=> 'btn dropdown-toggle',
					'data-toggle'	=> 'dropdown'
				] ),
				HtmlTag::create( 'ul', $dropdown, ['class' => 'dropdown-menu'] ),
			), ['class' => 'btn-group'] );
		}
		$modal		= $this->renderModal();
		$buttons	= HtmlTag::create( 'div', join( ' ', $buttons ), ['class' => 'oauth2-provider-buttons'] );
		return $buttons.$modal;
	}

	public function setDropdownLabel( string $label ): self
	{
		$this->dropdownLabel	= $label;
		return $this;
	}

	public function setFrom( string $from ): self
	{
		$this->from	= $from;
		return $this;
	}

	public function setLinkPath( string $path ): self
	{
		$this->linkPath		= $path;
		return $this;
	}

	protected function renderModal(): string
	{
		$words		= $this->env->getLanguage()->getWords( 'auth/oauth2' );
		$w			= (object) $words['modal-loading-oauth2'];
		$spinner	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-spin fa-circle-o-notch"] );
		$modal		= '<div id="modalLoadingOauth2" class="modal hide not-fade">
	<div class="modal-header">
		'.HtmlTag::create( 'h4', $w->heading ).'
	</div>
	<div class="modal-body">
		<big>'.$spinner.' '.$w->title.'</big><br/>
		<br/>
		'.HtmlTag::create( 'p', $w->message, ['class' => 'modal-message'] ).'
		'.HtmlTag::create( 'p', $w->slogan, ['class' => 'modal-slogan'] ).'
		<br/>
	</div>
</div>';
		return $modal;
	}
}
