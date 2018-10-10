<?php
namespace App\ScheduledJobs\Jobs;

abstract class AbstractShellJob implements JobInterface
{
    /**
     * @return string $command of the job
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return array $arguments of the job
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string $operator of the job
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
