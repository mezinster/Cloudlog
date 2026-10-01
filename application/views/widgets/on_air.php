<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="refresh" content="30">
	<title><?php echo htmlspecialchars($callsign, ENT_QUOTES, 'UTF-8'); ?> - On Air Status</title>
	<style type="text/css">
		* { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			font-family: Arial, "MS Trebuchet", sans-serif;
			background: #fff;
			padding: 10px;
		}
		.widget {
			border: 1px solid #ddd;
			border-radius: 6px;
			padding: 10px 14px 8px;
			display: inline-block;
			min-width: 220px;
			width: 100%;
		}
		.header {
			display: flex;
			align-items: center;
			gap: 8px;
			flex-wrap: wrap;
		}
		.callsign {
			font-size: 1.2em;
			font-weight: bold;
			letter-spacing: 1px;
		}
		.badge {
			display: inline-block;
			padding: 2px 9px;
			border-radius: 10px;
			font-size: 0.72em;
			font-weight: bold;
			white-space: nowrap;
		}
		.badge-on-air {
			background: #28a745;
			color: #fff;
		}
		.badge-qrt {
			background: #6c757d;
			color: #fff;
		}
		.radio-rows {
			margin-top: 7px;
		}
		.radio-row {
			font-size: 0.88em;
			color: #333;
			padding: 3px 0;
			border-top: 1px solid #f0f0f0;
			display: flex;
			align-items: center;
			gap: 5px;
			flex-wrap: wrap;
		}
		.freq {
			font-weight: bold;
		}
		.radio-name {
			color: #999;
			font-size: 0.85em;
		}
		.qrt-msg {
			margin-top: 7px;
			font-size: 0.82em;
			color: #999;
		}
		.powered-by {
			text-align: right;
			margin-top: 8px;
			font-size: 0.72em;
			color: #bbb;
		}
		.powered-by a {
			color: #0066cc;
			text-decoration: none;
		}
	</style>
</head>
<body>
	<div class="widget">
		<div class="header">
			<span class="callsign"><?php echo htmlspecialchars($callsign, ENT_QUOTES, 'UTF-8'); ?></span>
			<?php if ($radio_status->num_rows() > 0): ?>
				<span class="badge badge-on-air">&#9679; ON AIR</span>
			<?php else: ?>
				<span class="badge badge-qrt">QRT</span>
			<?php endif; ?>
		</div>

		<?php if ($radio_status->num_rows() > 0): ?>
		<div class="radio-rows">
			<?php foreach ($radio_status->result() as $row): ?>
			<div class="radio-row">
				<?php if (!empty($row->prop_mode) && $row->prop_mode === 'SAT'): ?>
					<span>&#128752;</span>
					<span class="freq"><?php echo htmlspecialchars($row->sat_name, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php if (!empty($row->mode) && $row->mode !== 'non'): ?>
						<span>&bull; <?php echo htmlspecialchars($row->mode, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php endif; ?>
				<?php else: ?>
					<span>&#128246;</span>
					<?php if (!empty($row->frequency) && $row->frequency != '0'): ?>
						<span class="freq"><?php echo $this->frequency->hz_to_mhz($row->frequency); ?></span>
					<?php endif; ?>
					<?php if (!empty($row->mode) && $row->mode !== 'non'): ?>
						<span>&bull; <?php echo htmlspecialchars($row->mode, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php endif; ?>
					<?php if (!empty($row->power) && $row->power != '0'): ?>
						<span>&bull; <?php echo htmlspecialchars($row->power, ENT_QUOTES, 'UTF-8'); ?>&nbsp;W</span>
					<?php endif; ?>
				<?php endif; ?>
				<?php if (!empty($row->radio)): ?>
					<span class="radio-name">(<?php echo htmlspecialchars($row->radio, ENT_QUOTES, 'UTF-8'); ?>)</span>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php else: ?>
		<p class="qrt-msg">No recent radio activity in the last 15 minutes.</p>
		<?php endif; ?>

		<div class="powered-by">
			Powered by <a href="https://github.com/magicbug/Cloudlog" target="_blank" rel="noopener">Cloudlog</a>
		</div>
	</div>
</body>
</html>
