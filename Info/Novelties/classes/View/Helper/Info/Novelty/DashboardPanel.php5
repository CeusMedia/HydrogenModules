<?php
class View_Helper_Info_Novelty_DashboardPanel extends CMF_Hydrogen_View_Helper_Abstract{

	protected $news	= array();

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function add( $item ){
		$this->news[]	= $item;
	}

	public function render(){
		$options	= array();
		$this->env->getCaptain()->callHook( 'Novelties', 'collect', $this, $options );
		if( !$this->news )
			return '';
		$list	= array();
		$userId		= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$model		= new Model_Novelty( $this->env );
		$iconAck	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
		foreach( $this->news as $item ){
			$conditions	= array(
				'userId'	=> $userId,
				'type'		=> $item->type,
				'entryId'	=> $item->id,
				'timestamp'	=> $item->timestamp,
			);
			if( $model->count( $conditions ) )
				continue;
			$key	= $item->timestamp.'.'.microtime( TRUE );
			$link	= UI_HTML_Tag::create( 'a', $item->title, array( 'href' => $item->url ) );
			$date	= View_Helper_Work_Time::formatSeconds( time() - $item->timestamp, '&nbsp;', TRUE );

			$badgeUnit	= substr( $date, -1 );
			$badgeClass	= $badgeUnit == 'm' ? 'important' : ( $badgeUnit == 'h' ? 'info' : '' );


			$date		= UI_HTML_Tag::create( 'small', $date, array(
				'class' => 'label label-'.$badgeClass,
				'style'	=> 'font-weight: normal'
			) );
			$buttons	= array(
				UI_HTML_Tag::create( 'button', $iconAck, array(
					'class'				=> 'btn btn-mini',
					'title'				=> 'ausblenden',
					'data-type'			=> $item->type,
					'data-id'			=> $item->id,
					'data-timestamp'	=> $item->timestamp,
				) ),
			);
			$type		= isset( $item->typeLabel ) ? $item->typeLabel : $item->type;
			$type		= UI_HTML_Tag::create( 'small', $type, array( 'class' => 'muted' ) );
			$list[$key]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $date, array( 'style' => 'text-align: right' ) ),
				UI_HTML_Tag::create( 'td', $type.'&nbsp;'.$link, array( 'class' => 'autocut' ) ),
				UI_HTML_Tag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
			) );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, $this->limit );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "45", "", "50" );
		$tbody	= UI_HTML_Tag::create( 'tbody', $list );
		$list	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array(
			'class'		=> 'table not-table-striped table-condensed table-fixed',
		) );
$script	= '
<script>
var InfoNoveltyDashboardPanel = {
	init: function(){
		jQuery("#dashboard-panel-info-novelty button.btn").bind("click", function(){
			var _this = jQuery(this);
			_this.parent().parent().fadeOut();
			jQuery.ajax({
				url:  "./info/novelty/ajaxDismiss/",
				data: jQuery(this).data(),
				method: "POST",
				dataType: "json",
				success: function(json){
					console.log(json);
				}
			});
		});
	}
};
InfoNoveltyDashboardPanel.init();
</script>
';
		return $list.$script;
	}

	public function setLimit( $limit ){
		$this->limit	= min( 100, max( 0, abs( $limit ) ) );
	}
}
?>
