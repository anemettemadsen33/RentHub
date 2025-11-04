<?php

namespace App\Console\Commands;

use App\Services\Security\VulnerabilityScanner;
use Illuminate\Console\Command;

class SecurityScanCommand extends Command
{
    protected $signature = 'security:scan 
                          {--report : Generate detailed report}
                          {--json : Output as JSON}';

    protected $description = 'Run comprehensive security vulnerability scan';

    public function handle(VulnerabilityScanner $scanner): int
    {
        $this->info('Starting security vulnerability scan...');
        $this->newLine();

        $results = $scanner->runScan();

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));

            return 0;
        }

        $this->displayResults($results);

        if ($this->option('report')) {
            $report = $scanner->generateReport();
            $filename = 'security_report_'.date('Y-m-d_His').'.md';
            file_put_contents(storage_path("logs/{$filename}"), $report);
            $this->info("Detailed report saved to: storage/logs/{$filename}");
        }

        return $results['total_vulnerabilities'] > 0 ? 1 : 0;
    }

    private function displayResults(array $results): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Vulnerabilities', $results['total_vulnerabilities']],
                ['Critical', "<fg=red>{$results['critical']}</>"],
                ['High', "<fg=yellow>{$results['high']}</>"],
                ['Medium', $results['medium']],
                ['Low', $results['low']],
            ]
        );

        if (! empty($results['vulnerabilities'])) {
            $this->newLine();
            $this->warn('Vulnerabilities Found:');
            $this->newLine();

            foreach ($results['vulnerabilities'] as $vuln) {
                $color = match ($vuln['severity']) {
                    'critical' => 'red',
                    'high' => 'yellow',
                    'medium' => 'blue',
                    default => 'white',
                };

                $this->line("<fg={$color}>[{$vuln['severity']}]</> {$vuln['type']}");
                $this->line("  Description: {$vuln['description']}");
                $this->line("  Recommendation: {$vuln['recommendation']}");
                $this->newLine();
            }
        } else {
            $this->newLine();
            $this->info('✓ No vulnerabilities found!');
        }
    }
}
