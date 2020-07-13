<!DOCTYPE html>
<!-- inspired by https://getbootstrap.com/docs/4.0/examples/album/ -->
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="https://getbootstrap.com/docs/4.0/assets/img/favicons/favicon.ico">

    <title>Domácnost</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/album/">

    <!-- Bootstrap core CSS -->
    <link href="./Album example for Bootstrap_files/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./Album example for Bootstrap_files/album.css" rel="stylesheet">
</head>

<body onload="init()">

<?php
include_once "commonHTML.php";

printHeader(true);
?>


<main role="main">


    <div class="album py-5 bg-light">
        <div class="container-fluid">
            <h3>Historické záznamy teploty a vlhkosti</h3>
            <div class="btn-group">
                <button onclick="spanPick(1)" id="btn1" type="button" class="btn btn-md btn-secondary">1 den</button>
                <button onclick="spanPick(7)" id="btn7" type="button" class="btn btn-md btn-outline-secondary">7 dní</button>
                <button onclick="spanPick(30)" id="btn30" type="button" class="btn btn-md btn-outline-secondary">30 dní</button>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="card mb-4 box-shadow" id="temperature60card">
                        <canvas id="temperature60"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card mb-4 box-shadow" id="humidity60card">
                        <canvas id="humidity60"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card mb-4 box-shadow" id="temperature0card">
                        <canvas id="temperature0"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card mb-4 box-shadow" id="humidity0card">
                        <canvas id="humidity0"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->

<!-- jQuery -->
<script type="text/javascript" src="MDB-Free_4.16.0/js/jquery.min.js"></script>
<!-- Bootstrap tooltips -->
<script type="text/javascript" src="MDB-Free_4.16.0/js/popper.min.js"></script>
<!-- Bootstrap core JavaScript -->
<script type="text/javascript" src="MDB-Free_4.16.0/js/bootstrap.min.js"></script>
<!-- MDB core JavaScript -->
<script type="text/javascript" src="MDB-Free_4.16.0/js/mdb.min.js"></script>

<!--My constants-->
<script type="text/javascript" src="commonLib.js"></script>


<svg xmlns="http://www.w3.org/2000/svg" width="348" height="225" viewBox="0 0 348 225" preserveAspectRatio="none" style="display: none; visibility: hidden; position: absolute; top: -100%; left: -100%;"><defs><style type="text/css"></style></defs><text x="0" y="17" style="font-weight:bold;font-size:17pt;font-family:Arial, Helvetica, Open Sans, sans-serif">Thumbnail</text></svg></body></html>

<script type="text/javascript">
    const temperature = "temperature";
    const humidity = "humidity";

    function init()
    {
        getData(24, 60);
        getData(24, 0);
    }

    function drawTempChart(data, type, len)
    {
        let label;
        let values = [];
        let times = [];

        data = JSON.parse(data);

        if(data[0].moduleID == 60)
            label = "Pokoj";
        else
            label = "Venku";

        data = data.reverse();
        let item = data[0];
        let lastValue = item[type];
        let lastTimePlusHour = new Date(parseInt(item.dateTime.substr(0, 4)), parseInt(item.dateTime.substr(5, 2)) - 1 , parseInt(item.dateTime.substr(8, 2)), parseInt(item.dateTime.substr(11, 2)) + 1); //date minus 1 hour

        let time = new Date(parseInt(item.dateTime.substr(0, 4)), parseInt(item.dateTime.substr(5, 2)) - 1 , parseInt(item.dateTime.substr(8, 2)), parseInt(item.dateTime.substr(11, 2)), parseInt(item.dateTime.substr(14, 2)));
        if(item[type] == -1000)
        {
            values.push(null);
            lastValue = null;
        }
        else
        {
            values.push(item[type]);
            lastValue = item[type];
        }
        times.push(time.getDate() + ". " + time.getMonth() + ". " + time.getHours() + ":00"); //item.dateTime.length-3));

        let skip = true;
        for (let item of data)
        {
            if(skip) //first value is already saved
            {
                skip = false;
                continue;
            }

            let time = new Date(parseInt(item.dateTime.substr(0, 4)), parseInt(item.dateTime.substr(5, 2)) - 1 , parseInt(item.dateTime.substr(8, 2)), parseInt(item.dateTime.substr(11, 2)), parseInt(item.dateTime.substr(14, 2)));

            //fills hours without new data with "old" data
            while(time.getDate() + ". " + time.getMonth() + ". " + time.getHours() + ":00" !== lastTimePlusHour.getDate() + ". " + lastTimePlusHour.getMonth() + ". " + lastTimePlusHour.getHours() + ":00")
            {
                values.push(lastValue);
                times.push(lastTimePlusHour.getDate() + ". " + lastTimePlusHour.getMonth() + ". " + lastTimePlusHour.getHours() + ":00");
                lastTimePlusHour.setHours(lastTimePlusHour.getHours()+1); // plus hour
            }

            if(item[type] == -1000)
            {
                values.push(null);
                lastValue = null;
            }
            else
            {
                values.push(item[type]);
                lastValue = item[type];
            }
            times.push(time.getDate() + ". " + (time.getMonth()+1) + ". " + time.getHours() + ":00");

            lastTimePlusHour.setHours(lastTimePlusHour.getHours()+1)  ; // plus hour
        }

        if(type === temperature) {
            label += " - teplota";
        }
        else {
            label += " - vlhkost";
        }

        let divElement = document.getElementById(type + data[0].moduleID.toString() + "card"); //get the right chart element
        divElement.innerHTML = "";
        let canvas = document.createElement("canvas");
        canvas.id = type + data[0].moduleID.toString();
        divElement.appendChild(canvas);

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: times.slice(times.length - len, times.length),
                datasets: [{
                    label: label,
                    data: values.slice(values.length - len, values.length),
                    backgroundColor: [
                        'rgba(105, 0, 132, .2)',
                    ],
                    borderColor: [
                        'rgba(200, 99, 132, .7)',
                    ],
                    borderWidth: 2
                }
                ]
            },
            options: {
                responsive: true
            }
        });
    }

    function getData(hours, moduleID)
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=tempHistory&moduleID=' + moduleID + "&len=" + hours);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);

                if(this.status === 200)
                {
                    drawTempChart(this.responseText, temperature, hours);
                    drawTempChart(this.responseText, humidity, hours);
                }
            }
        };
        request.send();
    }

    function spanPick(days)
    {
        getData(days*24, 60); //60 - moduleID of room temp and humidity sensor
        getData(days*24, 0); //0 - outdoor
        document.getElementById("btn1").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn7").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn30").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn" + days.toString()).className = "btn btn-md btn-secondary";
    }

</script>