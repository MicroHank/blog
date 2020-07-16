
    @extends('layouts.template')

    @section('title', '現在天氣')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('js')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="{{ URL::to('/') }}/Highcharts/code/highcharts.js"></script>
    @endsection

    @section('content')       
        <div id="weather_now"></div>

        <script type="text/javascript">
            $.ajax({
                type :"GET",
                url  : "{{ route('weather.now.api') }}",
                data : {

                },
                dataType: "json",
                success : function(data) {
                    if (data["status"] == 1) {
                        draw_chart(data) ;
                    }
                    else {
                        console.log(data["msg"]) ;
                    }
                    
                }
            }) ;

            function draw_chart(data) {
                var city_name = data["x"] ;
                var y_min_t = data["y1"] ;
                var y_max_t = data["y2"] ;
                var start_t = data["st"] ;
                var end_t = data["et"] ;
                
                Highcharts.chart('weather_now', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: '時段: '+start_t+" ~ "+end_t
                    },
                    subtitle: {
                        text: '資料來源：中央氣象局開放資料平臺'
                    },
                    xAxis: {
                        categories: city_name
                    },
                    yAxis: {
                        min: 0,
                        //max: 45,
                        title: {
                            text: '氣溫'
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle'
                    },
                    plotOptions: {
                         line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: false
                        }
                    },

                    series: [{
                        name: '最低溫',
                        data: y_min_t
                    },{
                        name: '最高溫',
                        data: y_max_t
                    }],
                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                });
            }
        </script>
    @endsection