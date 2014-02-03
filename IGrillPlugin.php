<?php

interface IGrillPlugin {
	public function __construct($bot);
	public function setupHandlers($irc);
}

?>