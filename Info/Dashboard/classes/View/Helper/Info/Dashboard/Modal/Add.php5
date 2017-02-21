<?php
class View_Helper_Info_Dashboard_Modal_Add extends CMF_Hydrogen_View_Helper_Abstract{

	protected $panels		= array();

	public function __construct( $env ){
		$this->setEnv( $env );
	}

	public function setPanels( $panels ){
		$this->panels		= $panels;
	}

	public function render(){
		$words		= $this->getWords( NULL, 'info/dashboard' );
		$w			= (object) $words['add'];

		$list			= UI_HTML_Tag::create( 'div', $w->emptyPanels, array( 'class' => 'alert alert-warning' ) );
		if( $this->panels ){
			$list	= array();
			foreach( $this->panels as $panelId => $panel ){
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'	=> 'checkbox',
					'name'	=> 'panels[]',
					'value'	=> $panelId,
				) );
				$list[]	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$panel->title, array(
					'class'	=> 'checkbox',
				) );
			}
			$list	= UI_HTML_Tag::create( 'div', $list, array( 'style' => "padding: 0 0.5em 1em 0.5em;" ) );
		}

		return '
<form action="./info/dashboard/add" method="post">
	<div id="myModalInfoDashboardAdd" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel"><i class="fa fa-fw fa-th"></i>&nbsp;'.$w->heading.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory required">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" placeholder="'.$w->placeholderTitle.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" rows="3" class="span12"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.UI_HTML_Tag::create( 'h5', $w->labelPanels ).'
					'.$list.'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
		</div>
	</div>
</form>';
	}
}
