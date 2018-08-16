<?php
namespace App\Shell\Task;

use App\Feature\Factory;
use App\ScheduledJobs\Handlers\CakeShellHandler;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class ScheduledJobsTask extends Shell
{
    /**
     * Add scheduled job record.
     *
     * @param string $job Job name
     * @param array $data Job data
     * @return bool
     */
    public function add($job, array $data)
    {
        if (! $this->isActive()) {
            return false;
        }

        if (! $this->isValid($job)) {
            return false;
        }

        if (! $this->isSupported($job)) {
            return false;
        }

        if ($this->exists($job)) {
            return false;
        }

        $table = TableRegistry::getTableLocator()->get('ScheduledJobs');

        $entity = $table->newEntity(array_merge($data, [
            'name' => sprintf('System [%s] command', $job),
            'job' => $job,
            'active' => true,
            'start_date' => Time::now()
        ]));

        $saved = (bool)$table->save($entity);

        $saved ?
            $this->success(sprintf('Added scheduled job [%s] to the database.', $job)) :
            $this->warn(sprintf('Error adding scheduled job [%s] to database', $job));

        return $saved;
    }

    /**
     * Checks if Scheduled Jobs are active.
     *
     * @return bool
     */
    private function isActive()
    {
        $feature = Factory::get('Module' . DS . 'ScheduledJobs');

        if ($feature->isActive()) {
            return true;
        }

        $this->out('Scheduled jobs are disabled. Skipping...');

        return false;
    }

    /**
     * Validates provided job name.
     *
     * @param string $job Job name
     * @return bool
     */
    private function isValid($job)
    {
        if (! is_string($job)) {
            $this->out(sprintf('Invalid job provided: %s', json_encode($job)));

            return false;
        }

        if ('' === trim($job)) {
            $this->out('No job provided');

            return false;
        }

        return true;
    }

    /**
     * Checks if provided job is supported by the sytem.
     *
     * @param string $job Job name
     * @return bool
     */
    private function isSupported($job)
    {
        if (in_array($job, (new CakeShellHandler())->getList())) {
            return true;
        }

        $this->out(sprintf('Unsupported job provided: %s', $job));

        return false;
    }

    /**
     * Checks if provided job already exists in the database.
     *
     * @param string $job Job name
     * @return bool
     */
    private function exists($job)
    {
        $entity = TableRegistry::getTableLocator()->get('ScheduledJobs')
            ->find('all')
            ->where(['job' => $job])
            ->first();

        if (null === $entity) {
            return false;
        }
        $this->warn(sprintf('Scheduled job "%s" already added, with status "%s"', $job, $entity->active));

        return true;
    }
}
