<?php
class GamerGrill implements IGrillPlugin {
	private $bot;
	private $last = array();

	public function __construct($bot) {
		$this->bot = $bot;
	}

	public function setupHandlers($irc) {
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/gamer ?grill/', $this, 'gotGrill');
	}

	public function gotGrill(&$irc, &$data) {
		if($this->bot->checkCooldown($data->channel, 'grill', 10)) {
			$irc->message( $data->type, $data->channel, 'FrankerZ /');
			$this->last['grill'] = time();
		}
	}
}
?>