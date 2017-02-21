<?php
class View_Helper_Info_Dashboard_Modal_AddPanel extends CMF_Hydrogen_View_Helper_Abstract{

	protected $dashboard;
	protected $panels		= array();

	public function __construct( $env ){
		$this->setEnv( $env );
	}

	public function setDashboard( $dashboard ){
		$this->dashboard	= $dashboard;
	}

	public function setPanels( $panels ){
		$this->panels		= $panels;
	}

	public function render(){
		$fieldPanels	= UI_HTML_Tag::create( 'div', 'Keine Panels vorhanden.', array( 'class' => 'alert alert-info' ) );
		$panelsInBoard	= explode( ',', $this->dashboard->panels );
		if( $this->panels ){
			$fieldPanels	= UI_HTML_Tag::create( 'div', 'Keine weiteren Panels vorhanden.', array( 'class' => 'alert alert-info' ) );
			$list	= array();
			foreach( $this->panels as $panelId => $panel ){
				if( in_array( $panelId, $panelsInBoard ) )
					continue;
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'	=> 'checkbox',
					'name'	=> 'panels[]',
					'value'	=> $panelId,
				) );
				$list[]	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$panel->title, array(
					'class'	=> 'checkbox',
				) );
			}
			if( $list ){
				$heading	= UI_HTML_Tag::create( 'h4', 'Panels to add right now' );
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

		$words		= $this->getWords( NULL, 'info/dashboard' );
		$w			= (object) $words['add-panel'];

$iconAddBoard	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );
		$iconAddPanel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );

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
}
