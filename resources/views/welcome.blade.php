<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asteroid Neo</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
            font-size: 3.5rem;
            }
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }

        .form-signin {
            width: 100%;
            max-width: 450px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .checkbox {
            font-weight: 400;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        #my-chart {
            display: none;
        }

        #asteroid-sizes-table {
            display: none;
        }
    </style>
  </head>
<body class="text-center">
    <main class="form-signin">
        <h1 class="h3 mb-3 fw-normal">Asteroid Neo</h1>
        <canvas id="my-chart"></canvas>
        <div class="form-floating mt-3 text-center">
            <input type="text" class="form-control text-center" id="daterange" name="daterange" placeholder="Select Start/End Daterange">
            <label for="daterange">Select Start/End Daterange</label>
        </div>
        <button id="get-stats" class="w-100 btn btn-primary">Get Neo Stats</button>
        <table id="asteroid-sizes-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Asteroid Id</th>
                    <th>Average Size in KMs</th>
                </tr>
            </thead>
            <tbody id="asteroid-sizes-table-body">
            </tbody>
        </table>
    </main>
    <script>
        var startDate;
        var endDate;
        var days;
        var labels = [];
        var points = [];
        var speeds = [];
        var sizes = [];
        var distances = [];
        var maxSpeed = Number.MIN_VALUE;
        var maxSpeedId;
        var minDistance = Number.MAX_VALUE;
        var minDistanceId;
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                opens: 'center'
            }, function(start, end, label) {
                startDate = start;
                endDate = end;
                days = endDate.diff((startDate), 'days');
            });
            $("#get-stats").click(function() {
                let url = "https://api.nasa.gov/neo/rest/v1/feed?start_date=" + startDate.format('YYYY-MM-DD') + "&end_date=" + endDate.format('YYYY-MM-DD') + "&api_key=PbeoOnaFUds6Jqu05j5QuEaOWKsPpjctcercC0dQ";
                $.get(url, function(data, status) {
                    tempDate = startDate;
                    for(let  i=0; i<=days; i++) {
                        labels.push(tempDate.format('YYYY-MM-DD'));
                        points.push(data["near_earth_objects"][tempDate.format('YYYY-MM-DD')].length);
                        for(let  j=0; j<points[i]; j++) {
                            let id = data["near_earth_objects"][tempDate.format('YYYY-MM-DD')][j]['id'];
                            let size = (data["near_earth_objects"][tempDate.format('YYYY-MM-DD')][j]['estimated_diameter']['kilometers']['estimated_diameter_min'] + data["near_earth_objects"][tempDate.format('YYYY-MM-DD')][j]['estimated_diameter']['kilometers']['estimated_diameter_max']) / 2;
                            sizes[id] = size;
                            let distance = data["near_earth_objects"][tempDate.format('YYYY-MM-DD')][j]['close_approach_data'][0]['miss_distance']['kilometers'];
                            distances[id] = distance;
                            if (distance < minDistance) {
                                minDistanceId = id;
                                minDistance = distance;
                            }
                            let speed = data["near_earth_objects"][tempDate.format('YYYY-MM-DD')][j]['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'];
                            speeds[id] = speed;
                            if (speed > maxSpeed) {
                                maxSpeedId = id;
                                maxSpeed = speed;
                            }
                        }
                        tempDate = (tempDate).add(1, 'd');
                    }
                    console.log(speeds);
                    console.log(distances);
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: 'Number of asteroids for each day',
                            backgroundColor: 'rgb(255, 99, 132)',
                            borderColor: 'rgb(255, 99, 132)',
                            data: points,
                        }]
                    };
                    const config = {
                        type: 'line',
                        data: chartData,
                        options: {}
                    };
                    const myChart = new Chart(
                        document.getElementById('my-chart'),
                        config
                    );
                    $("#my-chart").after("<div><strong>Closest Asteroid</strong> (" + minDistanceId + ": " + minDistance + " km)</div>");
                    $("#my-chart").after("<div><strong>Fastest Asteroid</strong> (" + maxSpeedId + ": " + maxSpeed + " km/h)</div>");
                    sizes.forEach(function(size, id) {
                        $('#asteroid-sizes-table-body').append("<tr><td>"+ id +"</td><td>"+ size +"</td></tr>");
                    });
                    $('#asteroid-sizes-table').show();
                });
            });
        });
    </script>
</body>
</html>