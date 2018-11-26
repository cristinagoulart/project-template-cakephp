<?php
namespace App\ScheduledJobs\Jobs;

abstract class AbstractShellJob implements JobInterface
{
    /**
     * @var string $operator
     */
    protected $operator = '';

    /**
     * @var string $command
     */
    protected $command = '';

    /**
     * @var string $arguments
     */
    protected $arguments = '';

    /**
     * @return string $command of the job
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return string $arguments of the job
     */
    public function getArguments(): string
    {
        return $this->arguments;
    }

    /**
     * @return string $operator of the job
     */
    public function getOperator(): string
    {
        return $this->operator;
    }
}
