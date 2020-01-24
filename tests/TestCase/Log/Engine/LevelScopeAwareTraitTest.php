<?php

namespace App\Test\TestCase\Log\Engine;

use App\Log\Engine\JsonLinesLog;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LevelScopeAwareTraitTest extends TestCase
{
    public function testMatchesLevelAndScopeWithConfiguredLevels(): void
    {
        $config = ['levels' => [LogLevel::ERROR, LogLevel::WARNING]];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            $this->assertSame(
                in_array($level, $config['levels'], true),
                $logEngine->matchesLevelAndScope($level)
            );
        }
    }

    public function testMatchesLevelAndScopeWithNullLevels(): void
    {
        $config = ['levels' => null];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            $this->assertTrue($logEngine->matchesLevelAndScope($level));
        }
    }

    public function testMatchesLevelAndScopeWithEmptyLevels(): void
    {
        $config = ['levels' => []];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            $this->assertTrue($logEngine->matchesLevelAndScope($level));
        }
    }

    public function testMatchesLevelAndScopeWithConfiguredScopes(): void
    {
        $config = ['scopes' => ['orders']];
        $logEngine = new JsonLinesLog($config);

        $this->assertTrue($logEngine->matchesLevelAndScope(LogLevel::DEBUG, ['scope' => 'orders']));
        $this->assertTrue($logEngine->matchesLevelAndScope(LogLevel::DEBUG, ['orders']));
        $this->assertTrue($logEngine->matchesLevelAndScope(LogLevel::DEBUG, 'orders'));

        $this->assertFalse($logEngine->matchesLevelAndScope(LogLevel::DEBUG, ['scope' => 'payments']));
        $this->assertFalse($logEngine->matchesLevelAndScope(LogLevel::DEBUG, ['payments']));
        $this->assertFalse($logEngine->matchesLevelAndScope(LogLevel::DEBUG, 'payments'));
    }

    public function testMatchesLevelAndScopeWithNullScopes(): void
    {
        $config = ['levels' => [], 'scopes' => null];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            foreach (['orders', 'payments'] as $scope) {
                $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => $scope]));
            }
        }
    }

    public function testMatchesLevelAndScopeWithEmptyScopes(): void
    {
        $config = ['levels' => [], 'scopes' => []];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            foreach (['orders', 'payments'] as $scope) {
                $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => $scope]));
            }
        }
    }

    public function testMatchesLevelAndScopeWithFalseScopes(): void
    {
        $config = ['levels' => [], 'scopes' => false];
        $logEngine = new JsonLinesLog($config);
        $levels = (new \ReflectionClass(LogLevel::class))->getConstants();

        foreach ($levels as $level) {
            foreach (['orders', 'payments'] as $scope) {
                $this->assertFalse($logEngine->matchesLevelAndScope($level, ['scope' => $scope]));
            }
        }

        foreach ($levels as $level) {
            $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => []]));
            $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => false]));
            $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => null]));
            $this->assertTrue($logEngine->matchesLevelAndScope($level, ['scope' => '']));
        }
    }

    public function testMatchesLevelAndScopeWithConfiguredLevelsAndScopes(): void
    {
        $config = ['levels' => [LogLevel::ERROR, LogLevel::EMERGENCY], 'scopes' => ['orders', 'comments']];
        $logEngine = new JsonLinesLog($config);
        $levels = array_diff(
            (new \ReflectionClass(LogLevel::class))->getConstants(),
            $config['levels']
        );

        foreach ($levels as $level) {
            foreach ($config['scopes'] as $scope) {
                $this->assertFalse($logEngine->matchesLevelAndScope($level, $scope));
            }
        }

        foreach (['payments', 'invoices'] as $scope) {
            foreach ($config['levels'] as $level) {
                $this->assertFalse($logEngine->matchesLevelAndScope($level, $scope));
            }
        }

        foreach ($config['levels'] as $level) {
            foreach ($config['scopes'] as $scope) {
                $this->assertTrue($logEngine->matchesLevelAndScope($level, $scope));
            }
        }
    }
}
