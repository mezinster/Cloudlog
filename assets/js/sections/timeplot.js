function setTimeplotLoading(isLoading) {
	var $button = $('#timeplotShowButton');
	if ($button.length) {
		$button.toggleClass('running', isLoading);
		$button.prop('disabled', isLoading);
	}
}

function timeplotRender(band, dxcc, cqzone) {
	setTimeplotLoading(true);
	$('.alert').remove();

	$.ajax({
		url: base_url + 'index.php/timeplotter/getTimes',
		type: 'post',
		data: { 'band': band, 'dxcc': dxcc, 'cqzone': cqzone },
		success: function (tmp) {
			setTimeplotLoading(false);
			if (tmp.ok == 'OK') {
				plotTimeplotterChart(tmp);
			} else {
				$('#container').remove();
				$('#info').remove();
				$('#timeplotter_div').append('<div class="alert alert-danger" role="alert">' + tmp.error + '</div>');
			}
		},
		error: function () {
			setTimeplotLoading(false);
			$('#container').remove();
			$('#info').remove();
			$('#timeplotter_div').append('<div class="alert alert-danger" role="alert">Unable to load Timeplotter data.</div>');
		}
	});
}

function timeplot(form) {
	timeplotRender(form.band.value, form.dxcc.value, form.cqzone.value);
}

function plotTimeplotterChart(tmp) {
	$("#container").remove();
	$("#info").remove();
	$("#timeplotter_div").append('<p id="info">' + tmp.qsocount + ' contacts were plotted.</p><div id="container" style="height: 600px;"></div>');
	var color = ifDarkModeThemeReturn('white', 'grey');
	var options = {
		chart: {
			type: 'column',
			zoomType: 'xy',
			renderTo: 'container',
			backgroundColor: getBodyBackground()
		},
		title: {
			text: 'Time Distribution',
			style: {
				color: color
			}
		},
		xAxis: {
			categories: [],
			crosshair: true,
			type: "category",
			min:0,
			max:47,
			labels: {
				style: {
					color: color
				}
			}
		},
		yAxis: {
			title: {
				text: '# QSOs',
				style: {
					color: color
				}
			},
			labels: {
				style: {
					color: color
				}
			}
		},
		rangeSelector: {
			selected: 1
		},
		tooltip: {
			formatter: function () {
				if(this.point) {
					return "Time: " + options.xAxis.categories[this.point.x] +
						"<br />Callsign(s) worked (max 5): " + myComments[this.point.x] +
						"<br />Number of QSOs: <strong>" + series.data[this.point.x] + "</strong>";
				}
			}
		},
		legend: {
			itemStyle: {
				color: color
			}
		},
		series: []
	};
	var myComments=[];

	var series = {
		data: []
	};

	$.each(tmp.qsodata, function(){
		myComments.push(this.calls);
		options.xAxis.categories.push(this.time);
		series.name = 'Number of QSOs';
		series.data.push(this.count);
	});

	options.series.push(series);

	var chart = new Highcharts.Chart(options);
}

function renderTimeplotFromComponent() {
	var paramsElement = document.getElementById('timeplotParams');
	if (!paramsElement) {
		return;
	}

	var band = paramsElement.dataset.band || 'All';
	var dxcc = paramsElement.dataset.dxcc || 'All';
	var cqzone = paramsElement.dataset.cqzone || 'All';

	timeplotRender(band, dxcc, cqzone);
}

document.body.addEventListener('htmx:afterSwap', function (event) {
	if (event.target && event.target.id === 'timeplotterResults') {
		renderTimeplotFromComponent();
	}
});
