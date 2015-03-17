# EdiPlug
A package to communicate with EdiMax EdiPlug smart power plugs.

This package will allow you to check the power status of your plug, turn it off and on as well as retrieve and set new power schedules.  At the moment there is no discovery code to locate the plugs, so you need to already know their IP address.

A work in progress with basic functionality.

## Example Code
### Turning a Plug On or Off

```php
// Create object with address, username and password.
$plug = new EdiPlug('192.168.1.100', 'admin', '1234');
// Turn it on
$plug->on();
// Turn it off
$plug->off()
// Or use its power property
$plug->power = true;
```

### Disable All Schedules
```php
	$plug = new EdiPlug('192.168.1.100', 'admin', '1234');

	// Get the week's schedule information from the plug.
	$week_schedule = $plug->schedule;

	// Cycle through each day
	foreach($week_schedule as $day => $day_schedule) {
		// Turn off all the schedules
		$day_schedule->enabled = false;
	}
	// Send new schedule back to the plug
	$plug->schedule = $week_schedule;
```

### Cycle Through Each Period for Tuesday
```php
	$plug = new EdiPlug('192.168.1.100', 'admin', '1234');

	// Get the week's schedule information from the plug.
	$week_schedule = $plug->schedule;

	// Cycle through each on/off period for Tuesday
	foreach($week_schedule[WeekSchedule::TUESDAY] as $period) {
		echo "On at $period->on, off at $period->off<br />\n";
	}
```
### Set Power to Come On Friday 7pm - 11pm
```php
	$plug = new EdiPlug('192.168.1.100', 'admin', '1234');

	$week_schedule = $plug->schedule;

	$friday = $week_schedule[WeekSchedule::FRIDAY];
	$friday->add(TimePeriod::createFromTimes(
		Carbon::createFromTime(19,00),
		Carbon::createFromTime(23,00)
	));
	$friday->enabled = true;

	$plug->schedule = $week_schedule;
```
