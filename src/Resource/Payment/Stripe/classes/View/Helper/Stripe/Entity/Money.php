<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Stripe_Entity_Money extends View_Helper_Stripe_Abstract
{
	public const FORMAT_AMOUNT_CURRENCY			= '%1$s%3$s';
	public const FORMAT_AMOUNT_SPACE_CURRENCY	= '%1$s%2$s%3$s';
	public const FORMAT_CURRENCY_AMOUNT			= '%3$s%1$s';
	public const FORMAT_CURRENCY_SPACE_AMOUNT	= '%3$s%2$s%1$s';

	public const NUMBER_FORMAT_DOT				= 0;
	public const NUMBER_FORMAT_COMMA			= 1;

	protected int $accuracy				= 2;
	protected int $amount				= 0;
	protected string $currency			= "EUR";
	protected string $format			= self::FORMAT_CURRENCY_SPACE_AMOUNT;
	protected ?string $nodeClass		= NULL;
	protected string $nodeName			= 'span';
	protected int $numberFormat			= self::NUMBER_FORMAT_DOT;
	protected string $separator			= "&nbsp;";

	public function render(): string
	{
		$price		= number_format(
			$this->amount / 100,
			$this->accuracy,
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? ',' : '.',
			$this->numberFormat == self::NUMBER_FORMAT_COMMA ? '.' : '´'
		);
		$label	= sprintf( $this->format, $price, $this->separator, $this->currency );
		return HtmlTag::create( $this->nodeName, $label, ['class' => $this->nodeClass] );
	}

	public function set( int $amount, string $currency, ?int $accuracy = NULL ): self
	{
		$this->setAmount( $amount );
		$this->setCurrency( $currency );
		if( $accuracy !== NULL )
			$this->setAccuracy( $accuracy );
		return $this;
	}

	public function setAccuracy( int $accuracy ): self
	{
		$this->accuracy	= $accuracy;
		return $this;
	}

	public function setAmount( int $amount ): self
	{
		$this->amount	= $amount;
		return $this;
	}

	public function setCurrency( string $currency ): self
	{
		$this->currency	= $currency;
		return $this;
	}

	public function setFormat( string $format ): self
	{
		$this->format	= $format;
		return $this;
	}

	public function setNodeClass( string $classNames ): self
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( string $nodeName ): self
	{
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setNumberFormat( int $numberFormat ): self
	{
		$this->numberFormat	= $numberFormat;
		return $this;
	}

	public function setSeparator( string $separator ): self
	{
		$this->separator	= $separator;
		return $this;
	}
}
