<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Novelty_DashboardPanel extends Abstraction
{
	protected array $news		= [];
	protected int $limit		= 10;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		object		$item
	 *	@return		self
	 */
	public function add( object $item ): self
	{
		$this->news[]	= $item;
		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$options	= [];
		$this->env->getCaptain()->callHook( 'Novelties', 'collect', $this, $options );
		if( !$this->news )
			return '';
		$list	= [];
		/** @var Logic_Authentication $logicAuth */
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$userId		= $logicAuth->getCurrentUserId();
		$model		= new Model_Novelty( $this->env );
		$iconAck	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
		foreach( $this->news as $item ){
			$conditions	= [
				'userId'	=> $userId,
				'type'		=> $item->type,
				'entryId'	=> $item->id,
				'timestamp'	=> $item->timestamp,
			];
			if( $model->count( $conditions ) )
				continue;
			$key	= $item->timestamp.'.'.microtime( TRUE );
			$link	= HtmlTag::create( 'a', $item->title, ['href' => $item->url] );
			$date	= View_Helper_Work_Time::formatSeconds( time() - $item->timestamp, '&nbsp;', TRUE );

			$badgeUnit	= substr( $date, -1 );
			$badgeClass	= $badgeUnit == 'm' ? 'important' : ( $badgeUnit == 'h' ? 'info' : '' );

			$date		= HtmlTag::create( 'small', $date, [
				'class' => 'label label-'.$badgeClass,
				'style'	=> 'font-weight: normal'
			] );
			$buttons	= [
				HtmlTag::create( 'button', $iconAck, [
					'class'				=> 'btn btn-mini',
					'title'				=> 'ausblenden',
					'data-type'			=> $item->type,
					'data-id'			=> $item->id,
					'data-timestamp'	=> $item->timestamp,
				] ),
			];
			$type		= $item->typeLabel ?? $item->type;
			$type		= HtmlTag::create( 'small', $type, ['class' => 'muted'] );
			$list[$key]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $date, ['style' => 'text-align: right'] ),
				HtmlTag::create( 'td', $type.'&nbsp;'.$link, ['class' => 'autocut'] ),
				HtmlTag::create( 'td', $buttons, ['style' => 'text-align: right'] ),
			] );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, $this->limit );
		$colgroup	= HtmlElements::ColumnGroup( "45", "", "50" );
		$tbody	= HtmlTag::create( 'tbody', $list );
		$list	= HtmlTag::create( 'table', $colgroup.$tbody, [
			'class'		=> 'table not-table-striped table-condensed table-fixed',
		] );
$script	= '
<script>
let InfoNoveltyDashboardPanel = {
	init: function(){
		jQuery("#dashboard-panel-info-novelty button.btn").on("click", function(){
			let that = jQuery(this);
			that.parent().parent().fadeOut();
			jQuery.ajax({
				url:  "./info/novelty/ajax/dismiss/",
				data: jQuery(this).data(),
				method: "POST",
				dataType: "json",
				success: function(json){
//					console.log(json);
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

	public function setLimit( int $limit ): self
	{
		$this->limit	= min( 100, max( 0, abs( $limit ) ) );
		return $this;
	}
}
