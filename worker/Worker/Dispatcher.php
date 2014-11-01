<?php
/*
This file is part of Erben
A tool for making full e-books from pages digitized by Czech National Library
Copyright (C) 2014 Czech Pirate Party

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace Worker;

class Dispatcher {
	private $proclist = null;
	private $pid;
	private $db;
	private $lastjob = 0;

	public function __construct() {
		$this->pid = posix_getpid();
		$this->db = new \Common\Database();
	}

	# sign up into list of running worker processes
	# return false if another process has been active since last heartbeat
	private function heartbeat() {
		$params = array('pid' => $this->pid);
		$db = $this->db;
		$db->beginTransaction();

		try {
			$db->query('LOCK TABLE erb_workers IN EXCLUSIVE MODE');
			$stmt = $db->query('SELECT * FROM erb_workers');
			$proclist = array();

			while ($row = $stmt->fetch()) {
				$proclist[$row['pid']] = $row['heartbeat'];
			}

			unset($proclist[$this->pid]);

			if (is_null($this->proclist)) {
				# Clean up possible garbage left by another
				# process with the same PID
				$this->cleanup();
				$db->query('INSERT INTO erb_workers (pid, heartbeat) VALUES (:pid, NOW())', $params);
			} else {
				foreach ($proclist as $pid => $hbeat) {
					# Exit if another process is running
					if (!isset($this->proclist[$pid]) || $this->proclist[$pid] != $hbeat) {
						$db->rollback();
						return false;
					}
				}

				$db->query('UPDATE erb_workers SET heartbeat = NOW() WHERE pid = :pid', $params);
			}

			$this->proclist = $proclist;
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e;
		}

		return true;
	}

	# sign out from list of running worker processes
	private function cleanup() {
		$params = array('pid' => $this->pid);
		$db = $this->db;
		$db->query('DELETE FROM erb_jobrun WHERE worker = :pid', $params);
		$db->query('DELETE FROM erb_workers WHERE pid = :pid', $params);
	}

	# unblock failed job and log error message
	private function cancelJob($jobid, $error) {
		$db = $this->db;
		$db->beginTransaction();

		try {
			$params = array('jobid' => $jobid);
			$db->query('DELETE FROM erb_jobrun WHERE job = :jobid', $params);
			$params['pid'] = $this->pid;
			$params['error'] = $error;
			$db->query('INSERT INTO erb_joberror (job, pid, errdate, message) VALUES (:jobid, :pid, NOW(), :error)', $params);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e;
		}
	}

	# load next free job, process it and save results back to database
	# returns false if there are no more jobs to run (some jobs may have
	# been skipped because they were taken by another process at the time
	# or due to runtime failure)
	private function processTask() {
		$db = $this->db;
		$db->beginTransaction();

		# load next free job
		try {
			$params = array('lastid' => $this->lastjob);
			$db->query('LOCK TABLE erb_jobrun IN EXCLUSIVE MODE');
			$stmt = $db->query('SELECT erb_jobs.* FROM (SELECT MIN(erb_jobs.id) AS jobid FROM erb_jobs LEFT JOIN erb_jobrun ON erb_jobs.id = erb_jobrun.job WHERE erb_jobs.id > :lastid AND erb_jobrun.job IS NULL) AS tmp INNER JOIN erb_jobs ON tmp.jobid = erb_jobs.id', $params);
			$job = $stmt->fetch();
			$stmt->closeCursor();

			# No more jobs, exit
			if (!$job) {
				$db->rollback();
				return false;
			}

			$params = array('job' => $job['id'], 'pid' => $this->pid);
			$db->query('INSERT INTO erb_jobrun (job, worker, started) VALUES (:job, :pid, NOW())', $params);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e;
		}

		# process the job
		$this->lastjob = $job['id'];
		$taskparams = @unserialize($job['input']);

		if (!preg_match('/^[a-z0-9_]+$/i', $job['jobtype'])) {
			$this->cancelJob($job['id'], 'Invalid task name');
			return true;
		}

		$class = "\\Task\\{$job['jobtype']}";

		if (!class_exists($class)) {
			$this->cancelJob($job['id'], "Task class \"$class\" not found");
			return true;
		}

		try {
			$task = new $class();
			$task->run($taskparams);
		} catch (\Exception $e) {
			$this->cancelJob($job['id'], $e->getMessage());
			return true;
		}

		# save results and delete the job
		$db->beginTransaction();

		try {
			$task->saveResult($db);
			$log = $task->getLog();
		} catch (\Exception $e) {
			$db->rollback();
			$this->cancelJob($job['id'], $e->getMessage());
			return true;
		}

		try {
			$params = array('jobid' => $job['id'], 'pid' => $this->pid);
			foreach ($log as $msg) {
				$params['error'] = $msg;
				$db->query('INSERT INTO erb_joberror (job, pid, errdate, message) VALUES (:jobid, :pid, NOW(), :error)', $params);
			}

			$params = array('jobid' => $job['id']);
			$db->query('DELETE FROM erb_jobrun WHERE job = :jobid', $params);
			$db->query('DELETE FROM erb_jobs WHERE id = :jobid', $params);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			$db->query('DELETE FROM erb_jobrun WHERE job = :jobid', $params);
			throw $e;
		}

		return true;
	}

	# dispatcher main loop
	public function run() {
		try {
			while ($this->heartbeat() && $this->processTask());
		} catch (\Exception $e) {
			$this->cleanup();
			throw $e;
		}

		$this->cleanup();
	}
}
