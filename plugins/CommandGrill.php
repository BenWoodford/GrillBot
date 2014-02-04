<?php
class CommandGrill implements IGrillPlugin {
	private $bot;

	private $channels = array();

	public function __construct($bot) {
		$this->bot = $bot;
	}

	public function setupHandlers($irc) {
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY|SMARTIRC_TYPE_NOTICE, '^!join$', $this, 'doJoin');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!leave$', $this, 'doLeave');
	}

	public function doJoin(&$irc, &$data) {
		$this->bot->join("#" . $data->nick);
		$this->addChannel("#" . $data->nick);
	}

	public function doLeave(&$irc, &$data) {
		if($irc->isOpped($data->channel, $data->nick) || substr($data->channel, 1) == $data->nick) { // Op or Owner
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "ok :'(");
			$this->bot->leave($data->channel);
			$this->removechannel($data->channel);
		}
		else
			$irc->log(SMARTIRC_DEBUG_NOTICE, "User " . $data->nick . " is not op in " . $data->channel);
	}

	public function eventOnConnect($irc) {
		$this->bot->checkData("channels.txt");

		$channels = explode("\n", $this->bot->getData("channels.txt"));

		foreach($channels as $ch) {
			$ch = trim($ch);
			$this->bot->join($ch);
			$this->channels[] = $ch;
		}
	}

	public function addChannel($channel) {
		$this->channels[] = $channel;
		$this->saveChannels();
	}

	public function removeChannel($channel) {
		$this->channels = array_values(array_diff($this->channels, array($channel)));
		$this->saveChannels();
	}

	public function saveChannels() {
		$this->bot->saveData("channels.txt", implode("\n", $this->channels));
	}
}
?>