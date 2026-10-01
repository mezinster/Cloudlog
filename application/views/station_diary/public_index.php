<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo htmlspecialchars($page_title ?? 'Station Diary', ENT_QUOTES); ?></title>
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/default/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/fontawesome/css/all.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;0,900;1,400&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/leaflet/leaflet.css" />
	<style>
		body {
			background: #efefef;
			font-family: 'Lora', Georgia, serif;
		}

		.diary-shell {
			max-width: 1080px;
			margin: 2.5rem auto;
			background: #fff;
			border: 1px solid #e5e5e5;
			box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
		}

		.diary-inner {
			max-width: 920px;
			margin: 0 auto;
			padding: 1rem 1.5rem 2rem;
		}

		.diary-rule {
			border: 0;
			border-top: 1px solid #e3e6ea;
			opacity: 1;
			margin: 0.75rem 0;
		}

		.diary-top-nav {
			text-align: right;
			font-size: 0.9rem;
		}

		.diary-top-nav a {
			color: #6c757d;
			text-decoration: none;
			margin-left: 1.25rem;
			font-weight: 600;
		}

		.diary-top-nav a:hover {
			text-decoration: underline;
		}

		.diary-title {
			font-family: 'Merriweather', Georgia, serif;
			font-size: 2.6rem;
			font-weight: 900;
			text-align: center;
			color: #2f3a56;
			line-height: 1.2;
			margin: 0.25rem 0;
		}

		.diary-subtitle {
			text-align: center;
			font-style: italic;
			color: #7a8294;
			font-size: 1.15rem;
		}

		.diary-entry {
			page-break-inside: avoid;
			padding: 0.25rem 0 1rem;
		}

		.diary-entry-title {
			font-family: 'Merriweather', Georgia, serif;
			font-size: 2rem;
			font-weight: 700;
			color: #2f3a56;
			margin-bottom: 0.3rem;
		}

		.diary-entry-title a {
			color: inherit;
			text-decoration: none;
		}

		.diary-entry-title a:hover {
			text-decoration: underline;
		}

		.diary-entry-date {
			font-size: 1.15rem;
			font-weight: 600;
			color: #5c6885;
			margin-bottom: 1rem;
		}


		.note-content {
			color: #44506b;
			font-size: 1.12rem;
			line-height: 1.8;
		}

		.note-content p:last-child { margin-bottom: 0; }

		.diary-entry img {
			max-width: 100%;
			height: auto;
			border: 1px solid #d4dae4;
		}

		.diary-qso-box {
			background: #f2f7fd;
			border: 1px solid #cfdff0;
			border-radius: 0.25rem;
			padding: 1rem;
		}

		.diary-qso-title {
			font-size: 1.45rem;
			font-weight: 700;
			color: #2f4f73;
		font-family: 'Merriweather', Georgia, serif;
			font-size: 0.9rem;
			margin-bottom: 0;
		}

		.diary-qso-box .table th {
			background: #e9f2fa;
			color: #2f4f73;
			font-weight: 600;
			border-bottom: 2px solid #cfdff0;
		}

		.diary-qso-box .table td {
			vertical-align: middle;
		}

		.diary-footer {
			text-align: center;
			font-style: italic;
			color: #8b92a1;
			margin-top: 1.5rem;
		}

		.diary-footer a {
			color: #5c6885;
			font-weight: 600;
			text-decoration: none;
		}

		.diary-footer a:hover {
			text-decoration: underline;
		}

		@media (max-width: 768px) {
			.diary-shell {
				margin: 0;
				box-shadow: none;
				border-left: 0;
				border-right: 0;
			}

			.diary-inner {
				padding: 0.75rem 1rem 1.5rem;
			}

			.diary-title {
				font-size: 2rem;
			}

			.diary-entry-title {
				font-size: 1.55rem;
			}

			.diary-top-nav {
				text-align: center;
			}

			.diary-top-nav a {
				margin: 0 0.6rem;
			}
		}

		@media print {
			.no-print { display: none !important; }
			body { background: #fff !important; }
			.diary-shell {
				box-shadow: none !important;
				border: 0 !important;
				margin: 0 !important;
			}
			.diary-qso-box details {
				display: block !important;
			}
			.diary-qso-box details summary {
				display: none !important;
			}
			.diary-qso-box details .table-responsive {
				display: block !important;
			}
		}

		.qso-summary-container {
			margin-top: 0.5rem;
		}

		.reaction-bar {
			padding: 0.5rem 0.75rem;
			border: 1px solid #d7dce6;
			border-radius: 0.375rem;
			background: #f8fafc;
		}

		.entry-actions {
			padding: 0.75rem;
			border: 1px solid #d7dce6;
			border-radius: 0.375rem;
			background: #f8fafc;
		}

		.entry-actions-row {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			flex-wrap: wrap;
		}

		.entry-actions-label {
			font-size: 0.9rem;
			color: #6c757d;
			font-weight: 600;
			margin-right: 0.25rem;
		}

		.reaction-btn.active {
			border-color: #2f4f73;
			background: #eaf2fb;
			color: #2f4f73;
		}
	</style>
</head>
<body>
	<div class="diary-shell">
		<div class="diary-inner">
				<div class="diary-top-nav no-print pt-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
				<div>
					<a href="<?php echo site_url('station-diary/' . rawurlencode($callsign)); ?>">Home</a>
					<a href="<?php echo htmlspecialchars($rss_url, ENT_QUOTES); ?>">RSS</a>
					<?php if (empty($is_single_entry)) { ?>
					<a href="#" onclick="window.print(); return false;">Print</a>
					<?php } ?>
				</div>
				<form method="get" action="<?php echo site_url('station-diary/' . rawurlencode($callsign) . '/search'); ?>" class="d-flex gap-1" role="search">
					<input type="search" name="q" placeholder="Search diary..." value="<?php echo htmlspecialchars($search_query ?? '', ENT_QUOTES); ?>" class="form-control form-control-sm" style="width:180px; font-family:inherit; font-size:0.85rem;" maxlength="100">
					<button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size:0.85rem;"><i class="fas fa-search"></i></button>
				</form>
			</div>

			<hr class="diary-rule">

			<h1 class="diary-title"><?php echo htmlspecialchars($callsign, ENT_QUOTES); ?>'s Station Diary</h1>
			<?php if (!empty($is_search_results)) { ?>
			<div class="diary-subtitle">Search results for: <em><?php echo htmlspecialchars($search_query ?? '', ENT_QUOTES); ?></em> &mdash; <?php echo (int)($search_total ?? 0); ?> <?php echo (int)($search_total ?? 0) === 1 ? 'entry' : 'entries'; ?> found</div>
			<?php } else { ?>
			<div class="diary-subtitle">Notes from my ham radio adventures</div>
			<?php } ?>

			<hr class="diary-rule mb-4">

			<?php if (!empty($entries)) { ?>
				<?php $publicQsoDateTimeFormat = !empty($qso_datetime_format) ? $qso_datetime_format : 'Y-m-d H:i'; ?>
				<?php foreach ($entries as $entry) { ?>
					<?php $entryPermalink = site_url('station-diary/' . rawurlencode($callsign) . '/entry/' . (int)$entry->id); ?>
					<?php
					$sharePermalink = !empty($current_entry_permalink) ? $current_entry_permalink : $entryPermalink;
					$shareText = $entry->title . ' - ' . $callsign . "'s Station Diary";
					$facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($sharePermalink);
					$xShareUrl = 'https://x.com/intent/tweet?url=' . rawurlencode($sharePermalink) . '&text=' . rawurlencode($shareText);
					$blueskyShareUrl = 'https://bsky.app/intent/compose?text=' . rawurlencode($shareText . ' ' . $sharePermalink);
					$reactionTotals = isset($entry_reaction_totals) && is_array($entry_reaction_totals) ? $entry_reaction_totals : array('like' => 0, 'love' => 0, 'fire' => 0);
					$visitorReactionValue = isset($visitor_reaction) ? $visitor_reaction : null;
					?>
					<article class="diary-entry" id="entry-<?php echo (int)$entry->id; ?>">
						<h2 class="diary-entry-title"><a href="<?php echo htmlspecialchars($entryPermalink, ENT_QUOTES); ?>"><?php echo htmlspecialchars($entry->title, ENT_QUOTES); ?></a></h2>
						<hr class="diary-rule mt-0">
						<div class="diary-entry-date"><?php echo date('F j, Y', strtotime($entry->created_at)); ?></div>

				<?php 
				// Process image shortcodes in the note content
				$CI =& get_instance();
				$CI->load->model('note');
				$processed = $CI->note->process_image_shortcodes($entry->note, $entry->images);
				// Remove empty <p><br></p> tags and <br> tags between paragraph tags
				$processedNote = preg_replace('/<p><br\s*\/?><\/p>/i', '', $processed['content']);
				$processedNote = preg_replace('/<\/p>\s*<br\s*\/?>\s*<p>/i', '</p><p>', $processedNote);
				$usedImageIds = $processed['used_image_ids'];
				
				// Filter out images that were used inline
				$remainingImages = array();
				if (!empty($entry->images)) {
					foreach ($entry->images as $image) {
						if (!in_array((int)$image->id, $usedImageIds)) {
							$remainingImages[] = $image;
						}
					}
				}
				?>
				
				<div class="note-content mb-4"><?php echo $processedNote; ?></div>

		<!-- QSO Summary UPDATED VERSION 2.0 -->
		<?php if ((int)$entry->include_qso_summary === 1 && !empty($entry->qso_summary) && (int)($entry->qso_summary['total_qsos'] ?? 0) > 0) { ?>
			<hr class="diary-rule mt-2">
			<div class="bg-light border rounded p-3 mb-3">
				<div class="row g-3">
					<div class="col-6 col-sm-3">
						<div class="text-center">
							<div class="small text-muted">Total QSOs</div>
													<div class="h5 mb-0 fw-bold"><?php echo (int)$entry->qso_summary['total_qsos']; ?></div>
				</div>
			</div>
			<div class="col-6 col-sm-3">
				<div class="text-center">
					<div class="small text-muted">DXCC</div>
					<div class="h5 mb-0 fw-bold"><?php echo (int)$entry->qso_summary['dxcc_worked']; ?></div>
				</div>
			</div>
			<div class="col-6 col-sm-3">
				<div class="text-center">
					<div class="small text-muted">Bands</div>
					<div class="small"><span class="badge bg-primary"><?php echo !empty($entry->qso_summary['bands']) ? htmlspecialchars(implode(', ', $entry->qso_summary['bands']), ENT_QUOTES) : '-'; ?></span></div>
				</div>
			</div>
			<div class="col-6 col-sm-3">
				<div class="text-center">
					<div class="small text-muted">Modes</div>
					<div class="small"><span class="badge bg-secondary"><?php echo !empty($entry->qso_summary['modes']) ? htmlspecialchars(implode(', ', $entry->qso_summary['modes']), ENT_QUOTES) : '-'; ?></span></div>
				</div>
			</div>
		</div>
	</div>

	<div class="qso-summary-container" id="qso-summary-<?php echo (int)$entry->id; ?>">
										<?php if (!empty($entry->qso_summary['highlight_dx'])) { ?>
											<div class="alert alert-info mb-3">
												<div class="small mb-1"><strong>Highlight DX:</strong></div>
												<div class="d-flex align-items-center gap-2">
													<span class="h6 mb-0 highlight-dx-call"><?php echo htmlspecialchars($entry->qso_summary['highlight_dx']->COL_CALL, ENT_QUOTES); ?></span>
													<span class="badge bg-dark highlight-dx-country"><?php echo htmlspecialchars($entry->qso_summary['highlight_dx']->COL_COUNTRY ?? '-', ENT_QUOTES); ?></span>
													<span class="text-muted ms-auto"><span class="highlight-dx-distance"><?php echo (int)$entry->qso_summary['highlight_dx']->COL_DISTANCE; ?></span> km</span>
												</div>
											</div>
										<?php } ?>
										
									<?php
									$entryDate = date('Y-m-d', strtotime($entry->created_at));
									$mapDateFrom = !empty($entry->qso_date_start) ? $entry->qso_date_start : $entryDate;
									$mapDateTo = !empty($entry->qso_date_end) ? $entry->qso_date_end : $entryDate;
									$mapLogbookId = !empty($entry->logbook_id) ? (int)$entry->logbook_id : 0;
									$mapSatOnly = (int)($entry->qso_satellite_only ?? 0) === 1 ? '1' : '0';
									?>

									<div class="mb-2">
										<button type="button" class="btn btn-sm btn-outline-primary no-print me-2" onclick="toggleQsoMap(<?php echo (int)$entry->id; ?>, '<?php echo htmlspecialchars($mapDateFrom, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($mapDateTo, ENT_QUOTES); ?>', <?php echo $mapLogbookId; ?>, '<?php echo $mapSatOnly; ?>')">
											<i class="fas fa-map-marked-alt me-1"></i><span id="map-toggle-text-<?php echo (int)$entry->id; ?>">Show QSO Map</span>
										</button>
									</div>

									<div id="qso-map-<?php echo (int)$entry->id; ?>" class="mb-3" style="display: none; width: 100%; height: 450px; border: 1px solid #ddd; border-radius: 4px;"></div>

									<?php
									$qsoCount = !empty($entry->qso_list) ? count($entry->qso_list) : 0;
									$lazyClass = (!empty($defer_qso_list) && empty($is_single_entry) && $qsoCount === 0) ? ' qso-list-lazy' : '';
									?>
									<details class="mt-2<?php echo $lazyClass; ?>"
										data-entry-id="<?php echo (int)$entry->id; ?>"
										data-callsign="<?php echo htmlspecialchars($callsign, ENT_QUOTES); ?>"
										data-start-date="<?php echo htmlspecialchars($mapDateFrom, ENT_QUOTES); ?>"
										data-end-date="<?php echo htmlspecialchars($mapDateTo, ENT_QUOTES); ?>"
										data-logbook-id="<?php echo (int)$mapLogbookId; ?>"
										data-sat-only="<?php echo htmlspecialchars($mapSatOnly, ENT_QUOTES); ?>"
										data-loaded="<?php echo $qsoCount > 0 ? '1' : '0'; ?>">
										<summary class="fw-bold" style="cursor: pointer; color: #2f4f73;">View QSO List</summary>
										<div class="table-responsive mt-2">
											<table class="table table-sm table-striped">
												<thead>
													<tr>
														<th>Date/Time</th>
														<th>Call</th>
														<th>Band</th>
														<th>Mode</th>
														<th>Country</th>
														<th>Grid</th>
													</tr>
												</thead>
												<tbody class="qso-table-body">
													<?php if (!empty($entry->qso_list)) { ?>
														<?php foreach ($entry->qso_list as $qso) { ?>
															<tr>
																<td><?php echo date($publicQsoDateTimeFormat, strtotime($qso->COL_TIME_ON)); ?></td>
																<td><strong><?php echo htmlspecialchars($qso->COL_CALL, ENT_QUOTES); ?></strong></td>
																<td>
																	<?php
																	$isSatelliteQso = (strtoupper((string)($qso->COL_PROP_MODE ?? '')) === 'SAT') || !empty($qso->COL_SAT_NAME);
																	$bandOrSatellite = $isSatelliteQso
																		? (!empty($qso->COL_SAT_NAME) ? $qso->COL_SAT_NAME : 'SAT')
																		: ($qso->COL_BAND ?? '-');
																	echo htmlspecialchars($bandOrSatellite, ENT_QUOTES);
																	?>
																</td>
																<td><?php echo htmlspecialchars(!empty($qso->COL_SUBMODE) ? $qso->COL_SUBMODE : $qso->COL_MODE, ENT_QUOTES); ?></td>
																<td><?php echo htmlspecialchars($qso->COL_COUNTRY ?? '-', ENT_QUOTES); ?></td>
																<td><?php echo htmlspecialchars($qso->COL_GRIDSQUARE ?? '-', ENT_QUOTES); ?></td>
															</tr>
														<?php } ?>
													<?php } elseif (!empty($defer_qso_list) && empty($is_single_entry)) { ?>
														<tr><td colspan="6" class="text-center text-muted">Expand to load QSO list...</td></tr>
													<?php } ?>
												</tbody>
											</table>
										</div>
									</details>
								</div>
								<?php } ?>

					<?php if (!empty($is_single_entry)) { ?>
						<div class="entry-actions mb-3 no-print">
							<div class="entry-actions-row mb-2 reaction-bar" data-reaction-container="1" data-callsign="<?php echo htmlspecialchars($callsign, ENT_QUOTES); ?>" data-entry-id="<?php echo (int)$entry->id; ?>">
								<span class="entry-actions-label">Reactions:</span>
								<button type="button" class="btn btn-sm btn-outline-secondary reaction-btn <?php echo ($visitorReactionValue === 'like') ? 'active' : ''; ?>" data-reaction="like">👍 <span class="reaction-count" data-reaction-count="like"><?php echo (int)($reactionTotals['like'] ?? 0); ?></span></button>
								<button type="button" class="btn btn-sm btn-outline-secondary reaction-btn <?php echo ($visitorReactionValue === 'love') ? 'active' : ''; ?>" data-reaction="love">❤️ <span class="reaction-count" data-reaction-count="love"><?php echo (int)($reactionTotals['love'] ?? 0); ?></span></button>
								<button type="button" class="btn btn-sm btn-outline-secondary reaction-btn <?php echo ($visitorReactionValue === 'fire') ? 'active' : ''; ?>" data-reaction="fire">🔥 <span class="reaction-count" data-reaction-count="fire"><?php echo (int)($reactionTotals['fire'] ?? 0); ?></span></button>
								<span class="small text-muted ms-2" data-reaction-status="1"></span>
							</div>
							<div class="entry-actions-row">
								<span class="entry-actions-label">Share:</span>
								<a href="<?php echo htmlspecialchars($xShareUrl, ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
									<i class="fab fa-x-twitter me-1"></i>X
								</a>
								<a href="<?php echo htmlspecialchars($blueskyShareUrl, ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
									<i class="fas fa-share-nodes me-1"></i>Bsky
								</a>
								<a href="<?php echo htmlspecialchars($facebookShareUrl, ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
									<i class="fab fa-facebook-f me-1"></i>Facebook
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary" data-copy-link="<?php echo htmlspecialchars($sharePermalink, ENT_QUOTES); ?>">
									<i class="fas fa-link me-1"></i>Copy link
								</button>
								<span class="small text-muted" data-copy-status="1"></span>
							</div>
						</div>
					<?php } ?>

						<?php if (!empty($remainingImages)) { ?>
							<div class="row g-2 mb-3">
								<?php foreach ($remainingImages as $image) { ?>
									<div class="col-md-6">
										<img src="<?php echo base_url() . ltrim($image->filename, '/'); ?>" alt="Diary image" class="img-fluid">
										<?php if (!empty($image->caption)) { ?>
											<div class="small text-muted mt-1"><?php echo htmlspecialchars($image->caption, ENT_QUOTES); ?></div>
										<?php } ?>
									</div>
								<?php } ?>
							</div>
						<?php } ?>

					<?php if (((int)$entry->include_qso_summary === 1 && !empty($entry->qso_summary) && (int)($entry->qso_summary['total_qsos'] ?? 0) > 0) || !empty($remainingImages)) { ?>
						<hr class="diary-rule mt-4">
					<?php } ?>
				</article>
			<?php } ?>

			<?php if (!empty($pagination_links)) { ?>
				<nav aria-label="Station diary pages" class="d-flex justify-content-center no-print mt-3">
						<?php echo $pagination_links; ?>
					</nav>
				<?php } ?>
			<?php } else { ?>
				<div class="text-center py-5">
					<?php if (!empty($is_search_results)) { ?>
						<p class="mb-2">No entries matched your search for <em><?php echo htmlspecialchars($search_query ?? '', ENT_QUOTES); ?></em>.</p>
						<a href="<?php echo site_url('station-diary/' . rawurlencode($callsign)); ?>" class="btn btn-sm btn-outline-secondary">View all entries</a>
					<?php } else { ?>
						<p class="mb-0">No public station diary entries found.</p>
					<?php } ?>
				</div>
			<?php } ?>

			<div class="diary-footer">
				Powered by <a href="https://github.com/magicbug/Cloudlog" target="_blank" rel="noopener noreferrer">Cloudlog</a>
			</div>
		</div>
	</div>

	<script>
		function escapeHtml(text) {
			return (text || '').toString()
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function formatQsoDateTime(dateValue) {
			const date = new Date(dateValue);
			if (isNaN(date.getTime())) {
				return dateValue || '';
			}
			const dateStr = date.toLocaleDateString('en-GB');
			const timeStr = date.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
			return `${dateStr} ${timeStr}`;
		}

		function renderQsoRows(tbody, qsos) {
			if (!tbody) {
				return;
			}

			if (!qsos || qsos.length === 0) {
				tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No QSOs match the selected filters</td></tr>';
				return;
			}

			tbody.innerHTML = qsos.map(qso => {
				const isSatelliteQso = ((qso.COL_PROP_MODE || '').toUpperCase() === 'SAT') || !!qso.COL_SAT_NAME;
				const bandOrSatellite = isSatelliteQso ? (qso.COL_SAT_NAME || 'SAT') : (qso.COL_BAND || '-');
				return `<tr><td>${escapeHtml(formatQsoDateTime(qso.COL_TIME_ON))}</td><td><strong>${escapeHtml(qso.COL_CALL)}</strong></td><td>${escapeHtml(bandOrSatellite)}</td><td>${escapeHtml((qso.COL_SUBMODE || qso.COL_MODE) || '-')}</td><td>${escapeHtml(qso.COL_COUNTRY || '-')}</td><td>${escapeHtml(qso.COL_GRIDSQUARE || qso.COL_VUCC_GRIDS || '-')}</td></tr>`;
			}).join('');
		}

		document.querySelectorAll('details.qso-list-lazy').forEach(detailsEl => {
			detailsEl.addEventListener('toggle', function() {
				if (!detailsEl.open) {
					return;
				}

				if (detailsEl.dataset.loaded === '1') {
					return;
				}

				const tbody = detailsEl.querySelector('.qso-table-body');
				const countEl = detailsEl.querySelector('.qso-count');
				if (tbody) {
					tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading QSO list...</td></tr>';
				}

				const formData = new URLSearchParams();
				formData.append('callsign', detailsEl.dataset.callsign || '');
				formData.append('entry_id', detailsEl.dataset.entryId || '0');
				formData.append('start_date', detailsEl.dataset.startDate || '');
				formData.append('end_date', detailsEl.dataset.endDate || '');
				formData.append('logbook_id', detailsEl.dataset.logbookId || '0');
				formData.append('sat_only', detailsEl.dataset.satOnly || '0');
				
				fetch('<?php echo site_url('stationdiary/get_filtered_qsos'); ?>', {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: formData.toString()
				})
				.then(r => r.json())
				.then(data => {
					if (!data || !data.success) {
						throw new Error((data && data.message) ? data.message : 'Unable to load QSO list');
					}

					renderQsoRows(tbody, data.qso_list || []);
					detailsEl.dataset.loaded = '1';
					if (countEl) {
						countEl.textContent = (data.qso_list || []).length;
					}

					const container = document.querySelector(`#qso-summary-${detailsEl.dataset.entryId}`);
					if (container && data.highlight_dx) {
						const dxCall = container.querySelector('.highlight-dx-call');
						const dxCountry = container.querySelector('.highlight-dx-country');
						const dxDistance = container.querySelector('.highlight-dx-distance');
						if (dxCall) dxCall.textContent = data.highlight_dx.COL_CALL || '-';
						if (dxCountry) dxCountry.textContent = data.highlight_dx.COL_COUNTRY || '-';
						if (dxDistance) dxDistance.textContent = parseInt(data.highlight_dx.COL_DISTANCE || 0, 10);
					}
				})
				.catch(() => {
					if (tbody) {
						tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Unable to load QSO list</td></tr>';
					}
				});
			});
		});

		document.querySelectorAll('.qso-date-filter, .qso-satellite-filter').forEach(el => {
			el.addEventListener('change', function() {
				const entryId = this.dataset.entryId;
				const startDate = document.querySelector(`input[data-entry-id="${entryId}"][data-filter-type="start"]`).value;
				const endDate = document.querySelector(`input[data-entry-id="${entryId}"][data-filter-type="end"]`).value;
				const satOnly = document.querySelector(`#sat-filter-${entryId}`).checked;
				
				const formData = new URLSearchParams();
				formData.append('callsign', '<?php echo htmlspecialchars($callsign ?? '', ENT_QUOTES); ?>');
				formData.append('entry_id', entryId);
				formData.append('start_date', startDate);
				formData.append('end_date', endDate);
				formData.append('sat_only', satOnly ? '1' : '0');
				
				fetch('<?php echo base_url(); ?>index.php/stationdiary/get_filtered_qsos', {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: formData
				})
				.then(r => r.json())
				.then(data => {
					if (data.success) {
						const container = document.querySelector(`#qso-summary-${entryId}`);
						
						// Update highlight DX if present
						const dxCall = container.querySelector('.highlight-dx-call');
						if (dxCall && data.highlight_dx) {
							dxCall.textContent = data.highlight_dx.COL_CALL || '-';
							const dxCountry = container.querySelector('.highlight-dx-country');
							const dxDistance = container.querySelector('.highlight-dx-distance');
							if (dxCountry) dxCountry.textContent = data.highlight_dx.COL_COUNTRY || '-';
							if (dxDistance) dxDistance.textContent = parseInt(data.highlight_dx.COL_DISTANCE || 0);
						}
						
						// Update QSO count and table
						const countEl = container.querySelector('.qso-count');
						if (countEl) countEl.textContent = data.qso_list.length;
						
						const tbody = container.querySelector('.qso-table-body');
						renderQsoRows(tbody, data.qso_list || []);
					}
				})
				.catch(e => console.error('Filter error:', e));
			});
		});
		
		document.querySelectorAll('.qso-filter-reset').forEach(btn => {
			btn.addEventListener('click', function() {
				const entryId = this.dataset.entryId;
				const satCheckbox = document.querySelector(`#sat-filter-${entryId}`);
				if (satCheckbox) satCheckbox.checked = false;
				
				const startInput = document.querySelector(`input[data-entry-id="${entryId}"][data-filter-type="start"]`);
				if (startInput) {
					const today = startInput.value;
					const endInput = document.querySelector(`input[data-entry-id="${entryId}"][data-filter-type="end"]`);
					if (endInput) endInput.value = today;
					startInput.dispatchEvent(new Event('change', {bubbles: true}));
				}
			});
		});

		document.querySelectorAll('[data-reaction-container="1"]').forEach(container => {
			const callsign = container.getAttribute('data-callsign');
			const entryId = container.getAttribute('data-entry-id');
			const statusEl = container.querySelector('[data-reaction-status="1"]');

			container.querySelectorAll('.reaction-btn').forEach(btn => {
				btn.addEventListener('click', function() {
					const reaction = this.getAttribute('data-reaction');
					const formData = new URLSearchParams();
					formData.append('reaction', reaction);

					container.querySelectorAll('.reaction-btn').forEach(button => button.disabled = true);
					if (statusEl) statusEl.textContent = 'Saving...';

					fetch('<?php echo site_url('station-diary'); ?>/' + encodeURIComponent(callsign) + '/entry/' + encodeURIComponent(entryId) + '/react', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: formData.toString()
					})
					.then(r => r.json())
					.then(data => {
						if (!data || !data.success) {
							throw new Error((data && data.message) ? data.message : 'Unable to save reaction');
						}

						container.querySelectorAll('.reaction-btn').forEach(button => {
							const key = button.getAttribute('data-reaction');
							button.classList.toggle('active', key === data.visitor_reaction);
							const countEl = button.querySelector('[data-reaction-count="' + key + '"]');
							if (countEl && data.totals && typeof data.totals[key] !== 'undefined') {
								countEl.textContent = data.totals[key];
							}
						});

						if (statusEl) {
							statusEl.textContent = 'Thanks for reacting!';
							setTimeout(() => { statusEl.textContent = ''; }, 1800);
						}
					})
					.catch(() => {
						if (statusEl) {
							statusEl.textContent = 'Unable to save reaction.';
							setTimeout(() => { statusEl.textContent = ''; }, 2200);
						}
					})
					.finally(() => {
						container.querySelectorAll('.reaction-btn').forEach(button => button.disabled = false);
					});
				});
			});
		});

		document.querySelectorAll('[data-copy-link]').forEach(btn => {
			btn.addEventListener('click', function() {
				const link = this.getAttribute('data-copy-link') || '';
				const copyStatus = this.parentElement ? this.parentElement.querySelector('[data-copy-status="1"]') : null;

				if (!link) {
					return;
				}

				navigator.clipboard.writeText(link)
					.then(() => {
						if (copyStatus) {
							copyStatus.textContent = 'Copied!';
							setTimeout(() => { copyStatus.textContent = ''; }, 1600);
						}
					})
					.catch(() => {
						if (copyStatus) {
							copyStatus.textContent = 'Unable to copy';
							setTimeout(() => { copyStatus.textContent = ''; }, 1600);
						}
					});
			});
		});
	</script>

	<!-- Leaflet Map JavaScript -->
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/leaflet/leaflet.js"></script>
	<script>
		var qsoMaps = {};
		var qsoMapMarkers = {};

		function toggleQsoMap(entryId, dateFrom, dateTo, logbookId, satOnly) {
			const mapContainer = document.getElementById('qso-map-' + entryId);
			const toggleText = document.getElementById('map-toggle-text-' + entryId);
			
			if (!mapContainer || !toggleText) {
				return;
			}

			if (mapContainer.style.display === 'none') {
				mapContainer.style.display = 'block';
				toggleText.textContent = 'Hide QSO Map';
				
				if (!qsoMaps[entryId]) {
					initQsoMap(entryId, dateFrom, dateTo, logbookId, satOnly);
				}
			} else {
				mapContainer.style.display = 'none';
				toggleText.textContent = 'Show QSO Map';
			}
		}

		function initQsoMap(entryId, dateFrom, dateTo, logbookId, satOnly) {
			const mapId = 'qso-map-' + entryId;
			
			// Initialize Leaflet map
			var map = L.map(mapId).setView([20, 0], 2);
			
			// Add tile layer
			var osmUrl = '<?php echo $this->optionslib->get_option("map_tile_server") ?: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"; ?>';
			L.tileLayer(osmUrl, {
				minZoom: 1,
				maxZoom: 12,
				attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
			}).addTo(map);
			
			qsoMaps[entryId] = map;
			qsoMapMarkers[entryId] = [];
			
			// Load QSO data
			fetch('<?php echo site_url("stationdiary/get_qso_map_data"); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'entry_id=' + encodeURIComponent(entryId) + '&date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo) + '&logbook_id=' + encodeURIComponent(logbookId) + '&sat_only=' + encodeURIComponent(satOnly)
			})
			.then(response => response.json())
			.then(data => {
				if (data.error) {
					console.error('Map data error:', data.error);
					return;
				}
				
				if (data.markers && data.markers.length > 0) {
					var bounds = [];
					
					data.markers.forEach(function(markerData) {
						if (markerData.lat && markerData.lng) {
							var confirmed = markerData.confirmed === 'Y';
							var markerColor = confirmed ? '#00AA00' : '#FF0000';
							
							var marker = L.circleMarker([markerData.lat, markerData.lng], {
								radius: 6,
								fillColor: markerColor,
								color: '#fff',
								weight: 1,
								opacity: 1,
								fillOpacity: 0.8
							});
							
							// Build popup HTML
							var popupHtml = '<div style="min-width: 150px;">';
							popupHtml += markerData.flag || '';
							popupHtml += '<strong>' + (markerData.label || 'Unknown') + '</strong><br>';
							popupHtml += markerData.html || '';
							popupHtml += '</div>';
							
							marker.bindPopup(popupHtml);
							marker.addTo(map);
							qsoMapMarkers[entryId].push(marker);
							bounds.push([markerData.lat, markerData.lng]);
						}
					});
					
					// Fit map to markers
					if (bounds.length > 0) {
						map.fitBounds(bounds, { padding: [20, 20] });
					}
				}
				
				// Add station marker if available
				if (data.station && data.station.lat && data.station.lng) {
					var stationMarker = L.marker([data.station.lat, data.station.lng], {
						icon: L.divIcon({
							className: 'station-marker',
							html: '<i class="fas fa-home" style="color: #0066ff; font-size: 20px;"></i>',
							iconSize: [20, 20]
						})
					});
					
					if (data.station.html) {
						stationMarker.bindPopup(data.station.html);
					}
					
					stationMarker.addTo(map);
				}
				
				// Invalidate size to ensure proper rendering
				setTimeout(function() {
					map.invalidateSize();
				}, 100);
			})
			.catch(error => {
				console.error('Error loading map data:', error);
			});
		}
	</script>
