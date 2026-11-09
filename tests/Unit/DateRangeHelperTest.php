<?php

namespace Tests\Unit;

use Tests\TestCase;

class DateRangeHelperTest extends TestCase
{
    public function test_range_gives_start_and_end_of_days()
    {
        $this->assertSame('2026-11-01 00:00:00', startDate('2026-11-01 to 2026-11-09'));
        $this->assertSame('2026-11-09 23:59:59', endDate('2026-11-01 to 2026-11-09'));
    }

    public function test_single_day_pick_covers_that_whole_day()
    {
        // flatpickr range mode sends just one date when one day is picked
        $this->assertSame('2026-11-09 00:00:00', startDate('2026-11-09'));
        $this->assertSame('2026-11-09 23:59:59', endDate('2026-11-09'));
    }

    public function test_empty_means_no_filter()
    {
        $this->assertSame('', startDate(''));
        $this->assertSame('', endDate(null));
    }
}
