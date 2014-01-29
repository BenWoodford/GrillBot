<?php
require_once("vendor/ProtoIRC/protoirc.php");


class GrillBot {
	private $irc;

	function __construct($config) {
		$connectstring = $config['server']['host'] . ':' . $config['server']['port'];
		echo "Connecting to " . $connectstring . "\n";
		$this->irc = new ProtoIRC($connectstring, function($irc) {
			$irc->stdout("Connected.");
			$irc->send('USER ' . $config['user']['nick']);
			$irc->send('PASS ' . $config['user']['token']);
			$irc->send('NICK ' . $config['user']['nick']);
		
			foreach($config['channels'] as $chan) {
				$irc->join('#' . $chan);
				$irc->send("#" . $chan, "Hello!");
			}
		});
	}

	function start() {
		$this->irc->go();
		echo "Um.\n";
	}
}
