function getAwardText(award) {
	switch (award) {
		case 'dxcc': return 'DXCCs';
		case 'was': return 'states';
		case 'iota': return 'IOTAs';
		case 'waz': return 'CQ zones';
		default: return 'items';
	}
}

function setAccumulateLoading(isLoading) {
	var $button = $('#accumulateShowButton');
	if ($button.length) {
		$button.toggleClass('running', isLoading);
		$button.prop('disabled', isLoading);
	}
}

function accumulateRender(band, award, mode, period) {
	setAccumulateLoading(true);

	// using this to change color of legend and label according to background color
	var color = ifDarkModeThemeReturn('white', 'grey');

	$.ajax({
		url: base_url + 'index.php/accumulated/get_accumulated_data',
		type: 'post',
		data: { 'Band': band, 'Award': award, 'Mode': mode, 'Period': period },
		success: function (data) {
			if (!$.trim(data) || data.length === 0) {
				$('#accumulateContainer').empty();
				$('#accumulateContainer').append('<div class="alert alert-info" role="alert">Nothing found for the selected filters.</div>');
				setAccumulateLoading(false);
				return;
			}

			var awardtext = getAwardText(award);
			var periodtext = period == 'month' ? 'Year + month' : 'Year';

			// Remove old chart/table before recreating
			$('#accumulateContainer').empty();
			$('#accumulateContainer').append('<canvas id="myChartAccumulate" width="400" height="150"></canvas><div id="accumulateTable" class="mt-3"></div>');

			$('#accumulateTable').append('<div class="table-responsive"><table style="width:100%" class="accutable table table-sm table-bordered table-hover table-striped align-middle text-center"><thead class="table-light">' +
				'<tr>' +
				'<th scope="col">#</th>' +
				'<th scope="col">' + periodtext + '</th>' +
				'<th scope="col">Accumulated # of ' + awardtext + ' worked</th>' +
				'</tr>' +
				'</thead><tbody></tbody></table></div>');

			var labels = [];
			var dataSeries = [];
			var i = 1;

			var rowElements = data.map(function (row) {
				labels.push(row.year);
				dataSeries.push(row.total);

				var $row = $('<tr></tr>');
				var $iterator = $('<td></td>').html(i++);
				var $type = $('<td></td>').html(row.year);
				var $content = $('<td></td>').html(row.total);
				$row.append($iterator, $type, $content);
				return $row;
			});

			$('.accutable tbody').append(rowElements);

			var ctx = document.getElementById('myChartAccumulate').getContext('2d');
			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Accumulated number of ' + awardtext + ' worked each ' + period,
						data: dataSeries,
						backgroundColor: 'rgba(54, 162, 235, 0.2)',
						borderColor: 'rgba(54, 162, 235, 1)',
						borderWidth: 2,
						color: color
					}]
				},
				options: {
					scales: {
						y: {
							ticks: {
								beginAtZero: true,
								color: color
							}
						},
						x: {
							ticks: {
								color: color
							}
						}
					},
					plugins: {
						legend: {
							labels: {
								color: color
							}
						}
					}
				}
			});

			$('.accutable').DataTable({
				responsive: false,
				ordering: false,
				scrollY: '400px',
				scrollCollapse: true,
				paging: false,
				scrollX: true,
				language: {
					url: getDataTablesLanguageUrl(),
				},
				dom: 'Bfrtip',
				buttons: ['csv']
			});

			// using this to change color of csv-button if dark mode is chosen
			var background = $('body').css('background-color');
			if (background != 'rgb(255, 255, 255)') {
				$('.buttons-csv').css('color', 'white');
			}

			setAccumulateLoading(false);
		},
		error: function () {
			$('#accumulateContainer').empty();
			$('#accumulateContainer').append('<div class="alert alert-danger" role="alert">Unable to load accumulated statistics.</div>');
			setAccumulateLoading(false);
		}
	});
}

function accumulatePlot(form) {
	accumulateRender(form.band.value, form.awardradio.value, form.mode.value, form.periodradio.value);
}

function renderAccumulatedFromComponent() {
	var paramsElement = document.getElementById('accumulateParams');
	if (!paramsElement) {
		return;
	}

	var band = paramsElement.dataset.band || 'All';
	var award = paramsElement.dataset.award || 'dxcc';
	var mode = paramsElement.dataset.mode || 'All';
	var period = paramsElement.dataset.period || 'year';

	accumulateRender(band, award, mode, period);
}

document.body.addEventListener('htmx:afterSwap', function (event) {
	if (event.target && event.target.id === 'accumulateResults') {
		renderAccumulatedFromComponent();
	}
});
