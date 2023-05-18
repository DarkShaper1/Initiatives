<?php
//namespace Ehesp\SteamLogin;
if(!interface_exists('SteamLoginInterface')){
	interface SteamLoginInterface{
	    public function url($return);
		public function validate();
	}
}
