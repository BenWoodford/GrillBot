<?php
class JoinChannel implements IGrillPlugin {
	private $bot;

	public function __construct($bot) {
		$this->bot = $bot;
	}

	public function setupHandlers($irc) {
		$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, 'HISTORYEND (\w*)', $this, 'didJoin');
	}

	public function didJoin(&$irc, &$data) {
		$irc->message( $data->type, "#" . $data->messageex[1], 'Hellooo!');
	}
}
?>