<?php


class CoreServerManager
{
	public function createDirectory($gid, $pi, $user){
		$safeGid = escapeshellarg($gid);
		$safePi = escapeshellarg($pi);
		$safeUser = escapeshellarg($user);

		$exec = "sudo ../bin/addCoreServerDir.sh " . $safeGid . " " . $safePi . " " . $safeUser;
		$exit_status = 1;
		$output_array = array();
		$output = exec($exec,$output_array,$exit_status);
		if ($exit_status) {
			throw new ErrorException("Error Creating directory for user " . $user);
		}
		return $exit_status;
	}
}
