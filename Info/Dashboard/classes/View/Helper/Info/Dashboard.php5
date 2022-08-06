<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Dashboard extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $columns		= 3;
	protected $dashboard;
	protected $panels;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$w	= (object) $this->getWords( 'board', 'info/dashboard' );

		$list	= [];
		foreach( explode( ',', $this->dashboard->panels ) as $panelId ){
			if( !array_key_exists( $panelId, $this->panels ) )
				continue;
			$panel	= $this->panels[$panelId];

			$iconMove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrows' ) );
			$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

			$icon		= '';
			if( $panel->icon )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $panel->icon ) ).'&nbsp;';
			$handle		= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'a', $iconRemove, array(
					'class'		=> 'btn btn-mini btn-inverse handle-icon',
					'href'		=> './info/dashboard/removePanel/'.$panel->id,
					'onclick'	=> 'if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false;',
					'title'		=> $w->buttonRemove,
				) ),
/*				UI_HTML_Tag::create( 'a', $iconMove, array(
					'class'		=> 'btn btn-mini handle-icon handle-button-move',
				) ),*/
				UI_HTML_Tag::create( 'h4', $icon.$panel->heading ),
			), array( 'class' => 'dashboard-panel-handle' ) );
			$container	= UI_HTML_Tag::create( 'div', '', array(
				'class'	=> 'dashboard-panel-container',
				'id'	=> NULL,
			) );

			$list[]	= UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'div', $handle.$container, array(
					'class'		=> 'thumbnail',
				) )
			), array(
				'class'			=> 'dashboard-panel span'.( 12 * $panel->cols / $this->columns ),
				'data-panel-id'	=> $panel->id,
				'id'			=> 'dashboard-panel-'.$panel->id,
			) );
			$script	= 'jQuery("#dashboard-panel-'.$panel->id.' .dashboard-panel-container").load("./'.$panel->url.'/'.$panel->id.'");';
			$this->env->getPage()->js->addScript( $script );
			if( $panel->refresh > 0 ){
				$script	= 'window.setInterval(function(){'.$script.'}, '.( $panel->refresh * 1000 ).');';
				$this->env->getPage()->js->addScriptOnReady( $script );
			}
		}
		$desc	= $this->dashboard->description ? UI_HTML_Tag::create( 'p', nl2br( $this->dashboard->description ) ) : '';
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbnails sortable' ) );
		return UI_HTML_Tag::create( 'div', $desc.$list, array( 'id' => 'dashboard-board' ) );
	}

	public function setColumns( int $columns ): self
	{
		if( !in_array( $columns, array( 1, 2, 3, 4, 6 ) ) )
			$columns	= 3;
		$this->columns	= $columns;
		return $this;
	}

	public function setDashboard( $dashboard ): self
	{
		$this->dashboard	= $dashboard;
		return $this;
	}

	public function setPanels( array $panels ): self
	{
		$this->panels	= $panels;
		return $this;
	}
/*
	public function unregisterPanel( $key )
	{
		if( !isset( $this->panels[$key] ) )
			return FALSE;
		unset( $this->panels[$key] );
		return TRUE;
	}*/
}
