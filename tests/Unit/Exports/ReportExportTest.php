<?php

namespace Tests\Unit\Exports;

use App\Exports\ReportExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private $reportExport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportExport = new ReportExport(random_int(1, 10), random_int(1, 10), random_int(1, 10));
    }

    public function test_collection()
    {
        $this->reportExport->collection();
        $this->assertTrue(true);
    }

    public function test_column_widths()
    {
        $this->reportExport->columnWidths();
        $this->assertTrue(true);
    }
}
