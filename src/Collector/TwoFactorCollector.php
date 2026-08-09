<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage\Collector;

use Neo\Core\Profiler\Interface\CollectorInterface;
use Vendor\NeoPHP\TwoFactorPackage\Service\TwoFactorManager;

final class TwoFactorCollector implements CollectorInterface
{
    public function getName(): string
    {
        return 'two-factor';
    }

    public function collect(): array
    {
        $checks = TwoFactorManager::getChecks();
        $failedCount = count(array_filter($checks, static fn (array $c) => !$c['success']));

        return [
            'total' => count($checks),
            'failedCount' => $failedCount,
            'checks' => $checks,
        ];
    }

    public function inToolbar(): bool
    {
        return false;
    }

    public function inProfiler(): bool
    {
        return true;
    }

    public function toolbarData(): array
    {
        return [
            'label' => 'Two-Factor',
            'value' => '',
            'badge' => null,
        ];
    }

    public function profilerData(): array
    {
        $data = $this->collect();

        if ($data['total'] === 0) {
            return [
                'title' => 'Two-Factor',
                'badge' => null,
                'blocks' => [
                    [
                        'type' => 'kv',
                        'section' => null,
                        'rows' => [
                            ['label' => 'Status', 'value' => 'No two-factor verification was performed during this request.'],
                        ],
                    ],
                ],
            ];
        }

        return [
            'title' => 'Two-Factor',
            'badge' => $data['failedCount'] > 0 ? (string) $data['failedCount'] : null,
            'badgeType' => 'alert',
            'metrics' => [
                ['label' => 'Checks', 'value' => (string) $data['total']],
                ['label' => 'Failed', 'value' => (string) $data['failedCount']],
            ],
            'blocks' => [
                [
                    'type' => 'table',
                    'section' => null,
                    'columns' => ['Action', 'User type', 'User ID', 'Result', 'Reason'],
                    'rows' => array_map(
                        static fn (array $c) => [
                            ucfirst($c['action']),
                            $c['userType'],
                            (string) $c['userId'],
                            $c['success'] ? 'Success' : 'Failed',
                            $c['reason'] ?? '—',
                        ],
                        $data['checks']
                    ),
                ],
            ],
        ];
    }
}