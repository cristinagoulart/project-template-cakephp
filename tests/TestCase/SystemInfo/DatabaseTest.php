<?php

namespace App\Test\TestCase\SystemInfo;

use App\SystemInfo\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testGetDriver(): void
    {
        // With version
        $result = Database::getDriver();
        $this->assertTrue(is_string($result), "getDriver() returned a non-string result");
        $this->assertFalse(empty($result), "getDriver() returned an empty result");

        // Without version
        $result = Database::getDriver();
        $this->assertTrue(is_string($result), "getDriver() returned a non-string result");
        $this->assertFalse(empty($result), "getDriver() returned an empty result");
    }

    public function testGetTables(): void
    {
        // All tables (no match)
        $result = Database::getTables();
        $this->assertTrue(is_array($result), "getTables() returned a non-array result for all tables");
        $this->assertFalse(empty($result), "getTables() returned an empty result for all tables");

        // Matched tables
        $result = Database::getTables('users');
        $this->assertTrue(is_array($result), "getTables() returned a non-array result for matched tables");
        $this->assertFalse(empty($result), "getTables() returned an empty result for matched tables");
    }

    public function testGetTableStats(): void
    {
        $tables = Database::getTables();
        $result = Database::getTablesStats($tables);
        $this->assertTrue(is_array($result), "getTablesStats() returned a non-array result");
        $this->assertFalse(empty($result), "getTablesStats() returned an empty result");
    }
}
