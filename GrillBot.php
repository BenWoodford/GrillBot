<?php
require_once("Net/SmartIRC.php");
require_once("IGrillPlugin.php");

class GrillBot {
	public $irc;
	public $config;

	private $plugins = array();
	private $cooldowns = array();

	private $post_setup_timer = 0;

	function __construct($config) {
		$this->config = $config;
		$this->irc = new Net_SmartIRC();
		$this->irc->setUseSockets(true);
		$this->irc->setChannelSyncing(true);
    	//$this->irc->setDebug(SMARTIRC_DEBUG_ALL);
		$this->irc->connect($config['server']['host'], $config['server']['port']);
		$this->irc->login($config['user']['nick'], $config['user']['realname'], 8, 'GrillBot',$config['user']['token']);
		$this->irc->registerTimeHandler(3000, $this, 'postSetup'); // For after connection.
	}

	function start() {
		$this->setup();
		$this->irc->listen();
	}

	function setup() {
		$this->loadPlugins();
		$this->setupPlugins();
	}

	function postSetup() {
		$this->irc->unregisterTimeid($this->post_setup_timer);
		foreach($this->config['channels'] as $chan) {
			$this->join($chan);
		}

		$this->callPluginEvent('eventOnConnect');
	}

	function checkData($filename) {
		if(!file_exists("data/")) {
			mkdir("data");
			echo "Created data directory.\n";
		}

		if(!file_exists("data/" . $filename)) {
			touch("data/" . $filename);
			echo "Created " . $filename . "\n";
		}
	}

	function getData($filename) {
		if(!file_exists("data/" . $filename)) {
			echo "WARNING: Tried to retrieve data file " . $filename . " but it doesn't exist!";
			return "";
		}

		return file_get_contents("data/" . $filename);
	}

	function saveData($filename, $data) {
		file_put_contents("data/" . $filename, $data);
	}

	function checkCooldown($channel, $key, $time) {
		if(!array_key_exists($channel, $this->cooldowns) || !array_key_exists($key, $this->cooldowns[$channel]) || $this->cooldowns[$channel][$key] < time() + $time) {
			$this->cooldowns[$channel][$key] = time();
			return true;
		}

		return false;
	}

	function loadPlugins() {
		foreach(glob('plugins/*.php') as $plugin) {
			require_once($plugin);
			$info = pathinfo($plugin);

			if(class_exists($info['filename'])) {
				$this->plugins[$info['filename']] = new $info['filename']($this);
				echo "Loaded Plugin: " . $info['filename'] . "\n";
			} else {
				echo "Could not Load Plugin '" . $info['filename'] . "', class name not found.\n";
			}
		}
	}

	function setupPlugins() {
		foreach($this->plugins as $p) {
			$p->setupHandlers($this->irc);
		}
	}

	function callPluginEvent($event, $args = array()) {
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $event)) {
				echo "Calling " . $event . " on " . get_class($plugin) . "\n";
				call_user_func_array(array($plugin, $event), array_merge(array($this->irc), $args));
			}
		}
	}

	function join($channel) {
		if($this->irc->isJoined($channel)) {
			echo "Tried to join a channel we're already in.\n";
			return;
		}

		$this->irc->join($channel);
		$this->channels[$channel] = array();

		$this->callPluginEvent('eventOnJoin', array($channel));
	}

	function leave($channel) {
		if(!$this->irc->isJoined($channel)) {
			echo "Tried to join a channel we're not in.\n";
			return;
		}

		$this->callPluginEvent('eventOnLeave', array($channel));

		$this->irc->part($channel);
		unset($this->channels[$channel]);
	}
}
