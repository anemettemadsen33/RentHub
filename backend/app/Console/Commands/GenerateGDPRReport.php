<?php

namespace App\Console\Commands;

use App\Services\Security\GDPRService;
use Illuminate\Console\Command;

class GenerateGDPRReport extends Command
{
    protected $signature = 'gdpr:report';

    protected $description = 'Generate GDPR compliance report';

    public function handle(GDPRService $gdprService): int
    {
        $this->info('Generating GDPR compliance report...');

        $report = $gdprService->generateComplianceReport();

        $this->info('GDPR Compliance Report');
        $this->info('======================');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Users', $report['total_users']],
                ['Users with Consent', $report['users_with_consent']],
                ['Data Export Requests (30d)', $report['data_export_requests']],
                ['Deletion Requests (30d)', $report['deletion_requests']],
                ['Anonymized Users', $report['anonymized_users']],
                ['Retention Policy Compliant', $report['retention_policy_compliant'] ? 'Yes' : 'No'],
                ['Last Check', $report['last_policy_check']],
            ]
        );

        return Command::SUCCESS;
    }
}
