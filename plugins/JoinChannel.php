<?php
class JoinChannel implements IGrillPlugin {
	private $bot;

	public function __construct($bot) {
		$this->bot = $bot;
	}

	public function setupHandlers($irc) {
	}

	public function eventOnJoin($irc, $channel) {
		$irc->message(SMARTIRC_TYPE_CHANNEL, $channel, 'Hellooo!');
	}
}
?>