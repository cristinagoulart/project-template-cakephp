<?php
namespace App\Shell;

use App\Feature\Factory as FeatureFactory;
use App\Model\Table\ScheduledJobsTable;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;
use Qobo\Utils\Utility\Lock\FileLock;
use RRule\RRule;
use RuntimeException;

/**
 * Cron shell command.
 *
 * @property \App\Model\Table\ScheduledJobLogs $ScheduledJobLogs
 * @property \App\Model\Table\ScheduledJobsTable $ScheduledJobs
 */
class CronShell extends Shell
{

    public $tasks = ['Lock'];

    protected $featureName = 'ScheduledJobs';

    /**
     * @var \App\Model\Table\ScheduledJobLogsTable
     */
    public $ScheduledJobLogs;

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $feature = FeatureFactory::get('Module' . DS . $this->featureName);

        if (!$feature->isActive()) {
            $this->warn("Scheduled Tasks are disabled.  Nothing to do.");

            return true;
        }

        $this->info('Running Scheduled Tasks...');

        /**
         * @var \App\Model\Table\ScheduledJobsTable $scheduledJobs
         */
        $scheduledJobs = TableRegistry::get('ScheduledJobs');
        $this->ScheduledJobs = $scheduledJobs;

        /**
         * @var \App\Model\Table\ScheduledJobLogsTable $scheduledJobsLogs
         */
        $scheduledJobsLogs = TableRegistry::get('ScheduledJobLogs');
        $this->ScheduledJobLogs = $scheduledJobsLogs;

        $jobs = $this->ScheduledJobs->getJobs(ScheduledJobsTable::JOB_ACTIVE);

        if (empty($jobs)) {
            $this->info("No active Scheduled Tasks found.  Nothing to do.");

            return true;
        }

        $now = $this->ScheduledJobs->getStartDate(Time::now());

        foreach ($jobs as $entity) {
            $shouldRun = false;

            $rrule = $this->ScheduledJobs->getRRule($entity);
            if ($rrule instanceof RRule) {
                $shouldRun = $this->ScheduledJobs->timeToInvoke($now, $rrule);
            }

            if (!$shouldRun) {
                $this->verbose("Skipping Scheduled Task [{$entity->name}]");
                continue;
            }

            $instance = $this->ScheduledJobs->getInstance($entity->job, 'Job');

            if (!$instance) {
                $message = sprintf("Failed to instatiate Job class for [%s]", $entity->job);
                $this->warn($message);
                Log::warning($message);
                continue;
            }

            try {
                $this->info("Starting Scheduled Task [{$entity->name}]");
                $lock = $this->lock(__FILE__, $entity->job);
                $state = $instance->run($entity->options);
                $this->info("Finished Scheduled Task [{$entity->name}]");
                $this->verbose("Scheduled Task [" . $entity->name . "] finished with: " . print_r($state, true));
                if ($state['state'] > 0) {
                    $this->ScheduledJobLogs->logJob($entity, $state, $now);
                }
                $this->info("Logged Scheduled Task [{$entity->name}]");
                $entity = $this->ScheduledJobs->patchEntity($entity, ['last_run_date' => Time::now()]);
                $this->ScheduledJobs->save($entity);
            } catch (Exception $e) {
                $this->info("Scheduled Task [{$entity->name}] is already in progress. Skipping.");
                continue;
            }
            $lock->unlock();
        }

        $this->info('Finished with all Schedule Tasks successfully');
    }

    /**
     * Generate lock file. Abort if lock file is already generated.
     *
     * @param string $file Path to the shell script which acquires lock
     * @param string $class Name of the shell class which acquires lock
     * @return \Qobo\Utils\Utility\FileLock
     */
    public function lock(string $file, string $class)
    {
        $class = preg_replace('/[^\da-z]/i', '_', $class);
        $lockFile = $this->Lock->getLockFileName($file, $class);

        try {
            $lock = new FileLock($lockFile);
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            throw new RuntimeException('Failed to acquire the lock');
        }

        return $lock;
    }
}
