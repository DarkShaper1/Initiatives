<?php global $chartData; ?>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {
        packages: ["corechart"]
    });
    google.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
			<?php
			foreach ( $chartData['data'] as $chrt ) {
				$strings[] = '[' . implode( ',', $chrt ) . ']';
			}
	        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo implode( ',', $strings );
			?>
        ]);

        var options = {
            title: "<?php echo esc_js( $chartData['title'] ); ?>",
            hAxis: {
                title: "<?php echo esc_js( $chartData['title-x'] ); ?>",
                titleTextStyle: {
                    color: "#333"
                }
            },
            vAxis: {
                minValue: 0
            },
            fontSize: 12,
            seriesType: 'bars',
            series: {
                1: {
                    type: 'area'
                }
            }
        };

        var chart = new google.visualization.ComboChart(document.getElementById("chart_div"));
        chart.draw(data, options);
    }
</script>
<div id="chart_div" style="margin:15px 0; width: 100%; height: 300px;"></div>