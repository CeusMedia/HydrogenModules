<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Dashboard_Modal_AddPanel extends Abstraction
{
	protected $dashboard;
	protected $panels		= [];

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function render(): string
	{
		$w				= (object) $this->getWords( 'add-panel', 'info/dashboard' );

		$fieldPanels	= HtmlTag::create( 'div', $w->emptyPanels, ['class' => 'alert alert-info'] );
		$panelsInBoard	= explode( ',', $this->dashboard->panels );
		if( $this->panels ){
			$fieldPanels	= HtmlTag::create( 'div', $w->noMorePanels, ['class' => 'alert alert-info'] );
			$list	= [];
			foreach( $this->panels as $panelId => $panel ){
				if( in_array( $panelId, $panelsInBoard ) )
					continue;
				$input	= HtmlTag::create( 'input', NULL, array(
					'type'	=> 'checkbox',
					'name'	=> 'panels[]',
					'value'	=> $panelId,
				) );
				$key	= str_pad( $panel->rank, 3, 0, STR_PAD_LEFT ).'.'.uniqid();
				$list[$key]	= HtmlTag::create( 'label', $input.'&nbsp;'.$panel->title, array(
					'class'	=> 'checkbox',
				) );
			}
			ksort( $list );
			if( $list ){
				$heading	= HtmlTag::create( 'h4', $w->labelPanels );
				$fieldPanels	= '
				<div class="row-fluid">
					<div class="span12">
						'.$heading.'
						<div style="padding: 0 0.5em 1em 0.5em;">
							'.join( $list ).'
						</div>
					</div>
				</div>';
			}
		}

		$iconAddBoard	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );
		$iconAddPanel	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-square'] );

		return '
<form action="./info/dashboard/addPanels" method="post">
	<div id="myModalInfoDashboardAddPanel" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">'.$iconAddPanel.'&nbsp;'.$w->heading.'</h3>
		</div>
		<div class="modal-body">
			'.$fieldPanels.'
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
		</div>
	</div>
</form>';
	}

	public function setDashboard( $dashboard ): self
	{
		$this->dashboard	= $dashboard;
		return $this;
	}

	public function setPanels( array $panels ): self
	{
		$this->panels		= $panels;
		return $this;
	}
}
