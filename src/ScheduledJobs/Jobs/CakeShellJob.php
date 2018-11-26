<?php
namespace App\ScheduledJobs\Jobs;

use Exception;
use Symfony\Component\Process\Process;

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
    public function __construct(string $command = '')
    {
        $this->command = $command;
        $this->operator = dirname(APP) . DS . 'bin' . DS . 'cake';
    }

    /**
     * {@inheritDoc}
     */
    public function run($arguments = null): array
    {
        $command = [
            $this->operator,
        ];

        $result = [
            'state' => false,
            'response' => [
                'stdout' => '',
                'stderr' => '',
            ],
        ];

        $this->arguments = $arguments;
        $parts = $this->parseCommand();

        if (!empty($parts)) {
            $command = array_merge($command, $parts);
        }

        if (!empty($this->arguments)) {
            array_push($command, $this->arguments);
        }

        try {
            $process = new Process($command);
            $process->run();
        } catch (Exception $e) {
            $result['response']['stderr'] = $e->getMessage();

            return $result;
        }

        $result['state'] = $process->getExitCode();
        $result['response']['stdout'] = $process->getOutput();
        $result['response']['stderr'] = $process->getErrorOutput();

        return $result;
    }

    /**
     * Parsing Command string into script
     *
     * @return mixed[] $shell containing required command parts to be used.
     */
    protected function parseCommand(): array
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
