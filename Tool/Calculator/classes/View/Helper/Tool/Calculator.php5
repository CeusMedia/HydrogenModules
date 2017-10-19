<?php
class View_Helper_Tool_Calculator{

	protected $id	= 'calc1';

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function setId( $id ){
		$this->id	= $id;
	}

	public function render(){
		return '
			<div class="calculator" id="'.$this->id.'">
				<div class="panel-left">
					<input type="text" class="calculator-display"/>
					<div class="calculator-messenger"></div>
					<div class="calculator-pad">
						<div>
							<button type="button" value="7" class="btn btn-large input">7</button>
							<button type="button" value="8" class="btn btn-large input">8</button>
							<button type="button" value="9" class="btn btn-large input">9</button>
							<button type="button" value="/" class="btn btn-large input">/</button>
						</div>
						<div>
							<button type="button" value="4" class="btn btn-large input">4</button>
							<button type="button" value="5" class="btn btn-large input">5</button>
							<button type="button" value="6" class="btn btn-large input">6</button>
							<button type="button" value="*" class="btn btn-large input">*</button>
						</div>
						<div>
							<button type="button" value="1" class="btn btn-large input">1</button>
							<button type="button" value="2" class="btn btn-large input">2</button>
							<button type="button" value="3" class="btn btn-large input">3</button>
							<button type="button" value="-" class="btn btn-large input">-</button>
						</div>
						<div>
							<button type="button" value="0" class="btn btn-large input">0</button>
							<button type="button" value="." class="btn btn-large input">.</button>
							<button type="button" value="^" class="btn btn-large input">^</button>
							<button type="button" value="+" class="btn btn-large input">+</button>
						</div>
						<div>
							<button type="button" class="btn btn-large clear">C</button>
							<button type="button" class="btn btn-large evaluate">=</button>
						</div>
					</div>
				</div>
				<div class="panel-right">
					<div class="calculator-scroll"></div>
				</div>
				<div class="clearfix">
				</div>
			</div>';
	}
}
