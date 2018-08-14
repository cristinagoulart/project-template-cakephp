<?php
namespace App\ScheduledJobs\Jobs;

use App\ScheduledJobs\Jobs\AbstractShellJob;

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

        $command = $this->operator . ' ' . implode(' ', $parts) . ' ' . $this->arguments;

        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        $pipes = [];

        $process = proc_open($command, $descriptors, $pipes, dirname(__FILE__), null);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $status = proc_close($process);

        $result = [
            'state' => $status,
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
