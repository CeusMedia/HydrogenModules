<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Panel_Mangopay_Cards extends View_Helper_Panel_Mangopay{

	protected $words;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->setOptions( array(
			'linkItem'	=> './manage/my/mangopay/card/view/%s',
			'linkBack'	=> './manage/my/mangopay/card',
			'linkAdd'	=> './manage/my/mangopay/card/add',
		) );
		$this->words	= $this->env->getLanguage()->getWords( 'manage/my/mangopay/card' );
	}

	public function render(){
		$rows		= [];
		foreach( $this->data as $item ){
		//	if( !$item->Active )
		//		continue;
			$link		= HtmlTag::create( 'a', $item->Id, ['href' => sprintf( $this->options->get( 'linkItem' ), $item->Id )] );
			$status		= HtmlTag::create( 'span', $item->Active ? 'aktiv' : 'deaktiviert', ['class' => 'label label-'.( $item->Active ? 'success' : 'important' )] );
			$number		= View_Helper_Panel_Mangopay::renderCardNumber( $item->Alias );
			$provider	= $this->words['cardTypes'][$item->CardType].'<br/><small class="muted">'.$this->words['cardProviders'][$item->CardProvider].'</small>';
			$rows[]		= HtmlTag::create( 'tr', array(
				HtmlTag::create(' td', $link, ['class' => 'cell-card-id'] ),
				HtmlTag::create(' td', $provider, ['class' => 'cell-card-provider'] ),
				HtmlTag::create(' td', $number, ['class' => 'cell-card-title'] ),
				HtmlTag::create(' td', $item->Currency, ['class' => 'cell-card-currency'] ),
				HtmlTag::create(' td', $status, ['class' => 'cell-card-status'] ),
			) );
		}
		$colgroup	= HtmlElements::ColumnGroup( "100", "160", "", "90", "100" );
		$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( 'ID', 'Typ/Anbieter', 'Card Number <small class="muted">(anonymisiert)</small>', 'Currency', 'Status' ) ) );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );
		return '
		<div class="content-panel">
			<h3>Credit Cards <small class="muted"></small></h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="buttonbar">
					<a href="'.$this->options->get( 'linkAdd' ).'" class="btn btn-success"><b class="fa fa-plus"></b>&nbsp;add</a>
					<a href="./manage/my/mangopay/card/refresh" class="btn btn-small"><b class="fa fa-refresh"></b>&nbsp;reload</a>
				</div>
			</div>
		</div>';
	}
}
