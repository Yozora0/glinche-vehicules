<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('vehicles:sync')->everyFifteenMinutes()->withoutOverlapping();
