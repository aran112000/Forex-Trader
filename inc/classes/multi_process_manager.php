<?php

/**
 * Class multi_process_manager
 */
class multi_process_manager {

    private $workers = null;
    private $thread_name = null;
    private $child_pids = [];
    private $parent_thread = false;
    private $script_last_modified_time = null;

    /**
     * multi_process_manager constructor.
     *
     * @param string $thread_name
     * @param array  $workers
     * @param bool   $parent_thread_can_continue_execution  - Should we keep allow execution of this script to continue?
     *                                                          true  = This script will continue running whilst the workers are still being processed
     *                                                          false = This script will halt execution at this point until the workers have completed and then execution will
     *                                                                  continue as normal
     *
     *                                                          Regardless of the value specified here, we will keep the child processes running until completion (unless a fatal
     *                                                          error occurs).
     *
     */
    public function __construct(string $thread_name, array $workers, bool $parent_thread_can_continue_execution = true) {
        $this->setThreadName($thread_name);

        $this->workers = $workers;

        // Start our workers running
        $this->init();

        if (!$parent_thread_can_continue_execution) {
            $this->doWaitForWorkersToComplete();
        }
    }

    /**
     * @param string $thread_name
     */
    public function setThreadName(string $thread_name) {
        $this->thread_name = ucfirst($thread_name);
    }

    /**
     *
     */
    private function init() {
        $workers_required = count($this->workers);
        for ($i = 0; $i < $workers_required; $i++) {
            $pid = pcntl_fork();
            $this->child_pids[] = $pid;
            if ($pid == -1) {
                // Failed to fork
                trigger_error('Failed to spawn worker process');
            } else if ($pid) {
                // We are the parent
                $this->parent_thread = true;
            } else {
                // We are the child
                $worker_number = ($i + 1);

                cli_set_process_title(APP_NAME . ' - ' . $this->thread_name . ': Child Process #' . $worker_number);

                call_user_func($this->workers[$i]);
                break;
            }
        }
    }

    /**
     *
     */
    private function doWaitForWorkersToComplete() {
        // Keep running the parent thread running until all the child processes have completed/exited
        if ($this->parent_thread) {
            while (count($this->child_pids) > 0) {
                foreach ($this->child_pids as $key => $pid) {
                    $res = pcntl_waitpid($pid, $status, WNOHANG);

                    // If the process has already exited
                    if ($res == -1 || $res > 0) {
                        unset($this->child_pids[$key]);
                    }

                    // Check for changes in our PHP scripts, if any are found, then we'll restart the scripts processing.
                    // This helps to prevent leaving workers running for days with out of date code in place.
                    $this->checkForScriptChange();
                }

                sleep(1);
            }
        }
    }

    /**
     * TODO:
     *   As part of future improvement, this should monitor ALL PHP scripts within the root directory and recurse down
     *   At the time of writing `inotify` doesn't compile successfully for PHP7 so this need implementing later.
     *
     *   Glob() could be an option if you need to implement support sooner.
     */
    protected function checkForScriptChange() {
        // At the moment this just covers the PHP file called from command line, in the future, this should cover all
        // files within the __CWD__ directory recursively
        $script_to_monitor = __CWD__ . DIRECTORY_SEPARATOR . __SCRIPT__;

        if ($this->script_last_modified_time === null) {
            $this->script_last_modified_time = filemtime($script_to_monitor);
        }

        clearstatcache(); // PHP caches the result of filemtime(), this flushes its cache
        $last_modified = filemtime($script_to_monitor);

        if ($last_modified > $this->script_last_modified_time) {
            $this->script_last_modified_time = $last_modified;

            if (exec('php -l ' . $script_to_monitor) == 'No syntax errors detected in ' . $script_to_monitor) {
                $this->terminateChildThreads();
                die(exec('php ' . $script_to_monitor));
            } else {
                // Syntax check failed, don't restart the thread
                trigger_error('Syntax check failed on: ' . $script_to_monitor);
            }
        }
    }

    /**
     *
     */
    public function terminateChildThreads() {
        if ($this->parent_thread && !empty($this->child_pids)) {
            foreach ($this->child_pids as $key => $pid) {
                if (posix_kill($pid, 9)) {
                    unset($this->child_pids[$key]);
                }
            }
        }
    }

    /**
     *
     */
    public function __destruct() {
        $this->doWaitForWorkersToComplete();

        $this->terminateChildThreads();
    }
}