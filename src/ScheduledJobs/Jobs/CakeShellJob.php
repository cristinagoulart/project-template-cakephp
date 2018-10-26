<?php
namespace App\ScheduledJobs\Jobs;

class CakeShellJob extends AbstractShellJob
{
    protected $operator = './bin/cake';

    protected $command = '';

    protected $arguments = '';

    /**
     * Default construct
     *
     * @param string $command for the scripts.
     */
    public function __construct($command = '')
    {
        $this->command = $command;
        $this->operator = dirname(APP) . DS . 'bin' . DS . 'cake';
    }

    /**
     * {@inheritDoc}
     */
    public function run($arguments = null)
    {
        $this->arguments = $arguments;
        $parts = $this->parseCommand();

        // We might also try: 'exec ' . $this->operator . ' ' ...
        // As per this comment: http://php.net/manual/en/function.proc-get-status.php#93382
        $command = $this->operator . ' ' . implode(' ', $parts) . ' ' . $this->arguments;

        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        $pipes = [];

        $process = proc_open($command, $descriptors, $pipes, dirname(__FILE__), null);

        /* This part seems to work for now, but there might be issues with
         * blocked stream reading and/or blocked process running when the
         * buffer is full.  If that happens, read this comment and refactor:
         * http://php.net/manual/en/function.proc-get-status.php#103189
         */

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        /* WARNING: You'll need a coffee for this next part.
         *
         * proc_close() is very unreliable when it comes to the
         * exit status code.  See the manual for more details:
         * http://php.net/manual/en/function.proc-close.php
         *
         * One of the workarounds is to use proc_get_status(),
         * which is a lot more reliable, but only for the first
         * run after the process finished.  See the manual and
         * comments for more details:
         * http://php.net/manual/en/function.proc-get-status.php
         *
         * An endless loop is not the most elegant idea, and it
         * opens up a bunch of issues (long running processes,
         * suspended processes, etc.), but it should do for
         * now.  Thanks to:
         * https://stackoverflow.com/a/9039451/151647
         *
         * If we run into any issues here, a few things to
         * consider are:
         *
         * 1. A timeout / counter inside the loop, to breakout
         *   and return an error.
         * 2. Prepending the command in proc_open() with 'exec ',
         *   like suggested in the comments to the proc_get_status()
         *   manual.
         *
         * PHP is a lot of fun, but not when it comes to process
         * management, aparently.  Good luck!  May the tests be with
         * you.
         */
        $status = proc_get_status($process);
        while ($status['running']) {
            sleep(1);
            $status = proc_get_status($process);
        }
        proc_close($process);

        $result = [
            'state' => $status['exitcode'],
            'response' => [
                'stdout' => trim($stdout),
                'stderr' => trim($stderr)
            ],
        ];

        return $result;
    }

    /**
     * Parsing Command string into script
     *
     * @return array $shell containing required command parts to be used.
     */
    protected function parseCommand()
    {
        $shell = [];
        $parts = explode('::', $this->command, 2);

        // cutting off App prefix as it's not used anywhere.
        if (preg_match('/^(.*)\:(.*)/', $parts[1], $matches)) {
            $shell[] = $matches[2];
        }

        return $shell;
    }
}
