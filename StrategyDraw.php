<?php 

class StrategyDraw 
{

	protected $draw_time;

	protected $configParams;

	public function setDrawTime($draw) {
		$this->draw=$draw;
	}
	
	protected function getDrawTime(){
		return $this->draw;
	}

	public function setConfigParams($configParams){
		$this->configParams = $configParams;
	}

	protected function getConfigParams(){
		return $this->configParams;
	}
}