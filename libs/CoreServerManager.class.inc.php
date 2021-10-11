<?php


class CoreServerManager
{
	const ssh_user = "root"
	const hostname = "core-server.igb.illinois.edu";
	
	
	public function createDirectory($gid, $pi, $user){
		$safeGid = escapeshellarg($gid);
		$safePi = escapeshellarg($pi);
		$safeUser = escapeshellarg($user);

		exec = "sudo ssh " . self::ssh_user . "@" . self::hostname . " \"mkcoredir -g " . $safeGid . " -p " . $safePi . " -u " . $safeUser . "\"";
		$exit_status = 1;
		$output_array = array();
		$output = exec($exec,$output_array,$exit_status);
		return $exit_status;
	}
}
