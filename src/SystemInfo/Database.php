<?php
namespace App\SystemInfo;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use ReflectionClass;

/**
 * Database class
 *
 * This is a helper class that assists with
 * fetching a variety of database information
 * from the system.
 */
class Database
{
    /**
     * Get driver and version
     *
     * Get the database driver in use, and, if possible,
     * the version of the database engine
     *
     * @param bool $skipVersion Do not include the version
     * @return string
     */
    public static function getDriver(bool $skipVersion = false): string
    {
        $driver = ConnectionManager::get('default')->getDriver();
        // Find the class name of the driver without namespace
        $driver = new ReflectionClass($driver);
        $driver = $driver->getShortName();
        $driver = strtoupper($driver);

        if ($skipVersion) {
            return $driver;
        }

        // Find version of the database engine
        switch ($driver) {
            case 'MYSQL':
                $version = ConnectionManager::get('default')->execute("SELECT VERSION()");
                $version = $version->fetch()[0];
                $driver .= ' ' . $version;

                break;
        }

        return $driver;
    }

    /**
     * Get tables
     *
     * This method returns a list of all tables found
     * in the default database connection, that match
     * a given string in the name.
     *
     * If not match string provided, all found tables
     * are returned.
     *
     * @param string $match String to match in the table names
     * @return string[]
     */
    public static function getTables(string $match = ''): array
    {
        $tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();

        if (empty($match)) {
            return $tables;
        }

        $result = [];
        foreach ($tables as $table) {
            if (preg_match("/$match/", $table)) {
                $result[] = $table;
            }
        }

        return $result;
    }

    /**
     * Get tables stats
     *
     * This method returns a variety of stats, such as
     * the count of all records, deleted records, etc.
     * for a given list of tables.
     *
     * @param string[] $tables List of tables to get stats for
     * @return mixed[]
     */
    public static function getTablesStats(array $tables): array
    {
        // Initialize tables stats
        $allTables = [];
        foreach ($tables as $table) {
            $allTables[$table] = [];
        }

        // Engine-specific stats are much faster to generate
        $engine = static::getDriver(true);
        switch ($engine) {
            case 'MYSQL':
                $allTables = static::getMysqlTablesStats($tables);
                break;
        }

        $result = [];
        foreach ($allTables as $table => $stats) {
            $result[$table] = $stats;

            $result[$table]['size'] = isset($stats['size']) ? $stats['size'] : 'N/A';
            $result[$table]['total'] = isset($stats['total']) ? $stats['total'] : static::getTotalRecordsCount($table);
            $result[$table]['deleted'] = isset($stats['deleted']) ? $stats['deleted'] : static::getTrashedRecordsCount($table);
        }

        return $result;
    }

    /**
     * Get MySQL tables stats
     *
     * This method returns a variety of stats, such as
     * the count of all records, table size, etc.
     * for a given list of tables.
     *
     * @param string[] $tables List of tables to get stats for
     * @return mixed[]
     */
    protected static function getMysqlTablesStats(array $tables): array
    {
        $result = [];
        $sth = ConnectionManager::get('default')->execute("SHOW TABLE STATUS");
        while ($data = $sth->fetch('assoc')) {
            if (in_array($data['Name'], $tables)) {
                $result[$data['Name']] = [
                    'total' => $data['Rows'],
                    'size' => $data['Data_length'] + $data['Index_length'],
                ];
            }
        }

        return $result;
    }

    /**
     * Get count of all records in a given table
     *
     * @param string $table Table to get the count from
     * @return int
     */
    protected static function getTotalRecordsCount(string $table): int
    {
        $result = 0;

        // Bypassing any CakePHP logic for permissions, pagination, and so on,
        // and executing raw query to get reliable data.
        $sth = ConnectionManager::get('default')->execute("SELECT COUNT(*) AS total FROM `$table`");
        $sth = $sth->fetch('assoc');
        $result = $sth['total'];

        return $result;
    }

    /**
     * Get count of trashed records in a given table
     *
     * @param string $table Table to get the count from
     * @return int
     */
    protected static function getTrashedRecordsCount(string $table): int
    {
        $result = 0;

        $tableInstance = TableRegistry::get($table);
        if ($tableInstance->hasField('trashed')) {
            $sth = ConnectionManager::get('default')->execute("SELECT COUNT(*) AS deleted FROM `$table` WHERE `trashed` IS NOT NULL AND `trashed` <> '0000-00-00 00:00:00'");
            $sth = $sth->fetch('assoc');
            $result = $sth['deleted'];
        }

        return $result;
    }
}
