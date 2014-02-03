<?php
class GamerGrill implements IGrillPlugin {
	private $bot;

	public function __construct($bot) {
		$this->bot = $bot;
	}

	public function setupHandlers($irc) {
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/gamer ?grill/i', $this, 'gotGrill');
	}

	public function gotGrill(&$irc, &$data) {
		if($this->bot->checkCooldown($data->channel, 'grill', 10)) {
			$irc->message( $data->type, $data->channel, 'heyyy ;)');
		}
	}
}
?>