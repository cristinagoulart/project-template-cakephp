<?php

namespace App\Log\Engine;

use Cake\Log\Engine\BaseLog;

trait LevelScopeAwareTrait
{
    /**
     * Check's whether current message can be logged based on level and scope.
     *
     * @param string $level The severity level of the message being written.
     *    See Cake\Log\Log::$_levels for list of possible levels.
     * @param string|array $context Additional information about the logged message
     * @return bool
     */
    public function matchesLevelAndScope(string $level, $context = []): bool
    {
        $context = (array)$context;
        if (isset($context[0])) {
            $context = ['scope' => $context];
        }
        $context += ['scope' => []];

        $levels = $scopes = null;

        if ($this instanceof BaseLog) {
            $levels = $this->levels();
            $scopes = $this->scopes();
        }
        if ($scopes === null) {
            $scopes = [];
        }

        $correctLevel = empty($levels) || in_array($level, $levels);
        $inScope = empty($context['scope']) || $scopes === [] ||
            is_array($scopes) && array_intersect((array)$context['scope'], $scopes);

        return $correctLevel && $inScope;
    }
}
