<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Dashboard extends Abstraction
{
	protected int $columns			= 3;
	protected ?object $dashboard	= NULL;
	protected array $panels			= [];

	public function __construct( WebEnvironment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$w	= (object) $this->getWords( 'board', 'info/dashboard' );

		if( NULL === $this->dashboard )
			return '';

		$list	= [];
		foreach( explode( ',', $this->dashboard->panels ) as $panelId ){
			if( !array_key_exists( $panelId, $this->panels ) )
				continue;
			$panel	= $this->panels[$panelId];

			$iconMove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrows'] );
			$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

			$icon		= '';
			if( $panel->icon )
				$icon	= HtmlTag::create( 'i', '', ['class' => $panel->icon] ).'&nbsp;';
			$handle		= HtmlTag::create( 'div', [
				HtmlTag::create( 'a', $iconRemove, [
					'class'		=> 'btn btn-mini btn-inverse handle-icon',
					'href'		=> './info/dashboard/removePanel/'.$panel->id,
					'onclick'	=> 'if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false;',
					'title'		=> $w->buttonRemove,
				] ),
/*				HtmlTag::create( 'a', $iconMove, [
					'class'		=> 'btn btn-mini handle-icon handle-button-move',
				] ),*/
				HtmlTag::create( 'h4', $icon.$panel->heading ),
			], ['class' => 'dashboard-panel-handle'] );
			$container	= HtmlTag::create( 'div', '', [
				'class'	=> 'dashboard-panel-container',
				'id'	=> NULL,
			] );

			$list[]	= HtmlTag::create( 'li', [
				HtmlTag::create( 'div', $handle.$container, [
					'class'		=> 'thumbnail',
				] )
			], [
				'class'			=> 'dashboard-panel span'.( 12 * $panel->cols / $this->columns ),
				'data-panel-id'	=> $panel->id,
				'id'			=> 'dashboard-panel-'.$panel->id,
			] );
//			$script	= 'jQuery("#dashboard-panel-'.$panel->id.' .dashboard-panel-container").load("./'.$panel->url.'/'.$panel->id.'");';
			$script	= 'InfoDashboard.loadPanel("'.$panel->id.'","'.$panel->url.'");';
			$this->env->getPage()->js->addScriptOnReady( $script );
			if( $panel->refresh > 0 ){
				$script	= 'window.setInterval(function(){'.$script.'}, '.( $panel->refresh * 1000 ).');';
				$this->env->getPage()->js->addScriptOnReady( $script );
			}
		}
		$desc	= $this->dashboard->description ? HtmlTag::create( 'p', nl2br( $this->dashboard->description ) ) : '';
		$list	= HtmlTag::create( 'ul', $list, ['class' => 'thumbnails sortable'] );
		return HtmlTag::create( 'div', $desc.$list, ['id' => 'dashboard-board'] );
	}

	public function setColumns( int $columns ): self
	{
		if( !in_array( $columns, [1, 2, 3, 4, 6] ) )
			$columns	= 3;
		$this->columns	= $columns;
		return $this;
	}

	public function setDashboard( ?object $dashboard ): self
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
