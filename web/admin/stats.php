<?php

function admin_stats() {
	global $conf_base_path;

	// Parameter "period"
	if (isset($_REQUEST['period'])) {
		$period = $_REQUEST['period'];
		if (substr($period, strlen($period)-1) == "h") 
			$period = intval($period) * 60*60;
		else // days
			$period = intval($period) * 24*60*60;
	} else
		$period = 24*60*60; // default period = 1 day

	$sec_min = time() - $period;
	
	// Show statistics
	?>
	<p id="p-return"><a href="admin.php">Return to courses list</a></p>
	<h1>Usage stats</h1>
	
	<p>Period: <a href="admin.php?stats&amp;period=12h">12 hours</a> * <a href="admin.php?stats&amp;period=1d">1 day</a> * <a href="admin.php?stats&amp;period=7d">7 days</a> * <a href="admin.php?stats&amp;period=30d">30 days</a></p>
	
	<script type="text/javascript"
		src="https://www.google.com/jsapi?autoload={
		'modules':[{
		'name':'visualization',
		'version':'1',
		'packages':['corechart']
		}]
		}"></script>

	<script type="text/javascript">
	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('datetime', 'Time');
		data.addColumn('number', 'Users');
		data.addColumn('number', 'Active users');
		
		data.addRows([
		<?php
		foreach(file($conf_base_path . "/user_stats.log") as $logline) {
			$realno=-1;
			$parts = explode(" ", trim($logline));
			$sec = $parts[0];
			$no = $parts[1];
			if (count($parts)>2) $realno = $parts[2];
			if (!isset($realno) || $realno==-1) $realno=$no;
			if ($sec < $sec_min) continue;
			print "[ new Date(" . date("Y,", $sec) . (date("n", $sec)-1) . date(",j,G,i,s", $sec) . ",0), $no, $realno ],\n";
		}
		?>
		]);

		var options = {
		title: 'Number of concurrent users',
		legend: { position: 'bottom' }
		};

		var chart = new google.visualization.LineChart(document.getElementById('chart_users'));

		chart.draw(data, options);
		
		// Second graph - load average
		
		var data2 = new google.visualization.DataTable();
		data2.addColumn('datetime', 'Time');
		data2.addColumn('number', 'c9');
		data2.addColumn('number', 'storage');
		data2.addColumn('number', 'c9prim');
		data2.addColumn('number', 'c9sec');
		
		data2.addRows([
		<?php
		foreach(file($conf_base_path . "/load_stats.log") as $logline) {
			$storage = $prim = $second = 0;
			$ar = explode(" ", trim($logline));
			$sec = intval($ar[0]); $c9 = floatval($ar[1]);
			if (count($ar)>2) $storage = floatval($ar[2]); 
			if (count($ar)>3) $prim = floatval($ar[3]); 
			if (count($ar)>4) $second = floatval($ar[4]); 
			if ($sec < $sec_min) continue;
			print "[ new Date(" . date("Y,", $sec) . (date("n", $sec)-1) . date(",j,G,i,s", $sec) . ",0), $c9, $storage, $prim, $second ],\n";
		}
		?>
		]);

		var options2 = {
		title: 'Server load average (5min)',
		legend: { position: 'bottom' }
		};

		var chart2 = new google.visualization.LineChart(document.getElementById('chart_load'));

		chart2.draw(data2, options2);
		
		// Third graph - ram
		
		var data3 = new google.visualization.DataTable();
		data3.addColumn('datetime', 'Time');
		data3.addColumn('number', 'c9');
		data3.addColumn('number', 'storage');
		data3.addColumn('number', 'c9prim');
		data3.addColumn('number', 'c9sec');
		
		data3.addRows([
		<?php
		$storage = $prim = $second = 0;
		foreach(file($conf_base_path . "/mem_stats.log") as $logline) {
			$ar = explode(" ", trim($logline));
			$sec = intval($ar[0]); $c9 = floatval($ar[1]);
			if (count($ar)>2) $storage = floatval($ar[2]); 
			if (count($ar)>3) $prim = floatval($ar[3]); 
			if (count($ar)>4) $second = floatval($ar[4]); 
			if ($sec < $sec_min) continue;
			$c9 = round(($c9 / 1024) / 1024, 3);
			$storage = round(($storage / 1024) / 1024, 3);
			$prim = round(($prim / 1024) / 1024, 3);
			$second = round(($second / 1024) / 1024, 3);
			print "[ new Date(" . date("Y,", $sec) . (date("n", $sec)-1) . date(",j,G,i,s", $sec) . ",0), $c9, $storage, $prim, $second ],\n";
		}
		?>
		]);

		var options3 = {
		title: 'Memory usage (GiB)',
		legend: { position: 'bottom' }
		};

		var chart3 = new google.visualization.LineChart(document.getElementById('chart_mem'));

		chart3.draw(data3, options3);
	}
	</script>
	<div id="chart_users" style="width: 900px; height: 500px"></div>
	<div id="chart_load" style="width: 900px; height: 500px"></div>
	<div id="chart_mem" style="width: 900px; height: 500px"></div>
	<?php
}

?>