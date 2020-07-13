<!DOCTYPE html>
<!-- inspired by https://getbootstrap.com/docs/4.0/examples/album/ -->
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

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

<body onload="initData()">

<?php
include_once "commonHTML.php";

printHeader(true);
?>


<main role="main">

    <div class="album py-5 bg-light">
        <div class="container-fluid">

            <div id="warnings"></div>

            <div class="row">

                <div class="col-md">
                    <div class="card mb-4 box-shadow">

                        <div class="card-body">
                            <h3 class="card-header">Pokoj</h3>
                            <table class="table">
                                <tr>
                                    <td>
                                        <a href="charts.php" class="card-text">Teplota:</a>
                                    </td>
                                    <td>
                                        <p id="roomTemp" class="card-text"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="charts.php" class="card-text">Vlhkost:</a>
                                    </td>
                                    <td>
                                        <p id="roomHum" class="card-text"></p>
                                    </td>
                                </tr>
                                <tr >
                                    <td class="align-middle">
                                        <p class="card-text">Osvětlení:</p>
                                    </td>
                                    <td>
                                        <p id="roomLight" class="card-text"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div class="btn-group">
                                            <button id="roomLightBtn" type="button" class="btn btn-md btn-outline-secondary"></button>
                                            <button onclick="setRoomLight(1)" type="button" class="btn btn-md btn-outline-secondary">+</button>
                                            <button onclick="setRoomLight(2)" type="button" class="btn btn-md btn-outline-secondary">-</button>
                                        </div>
                                        <div class="btn-group">
                                            <button onclick="setRoomLight(8)" type="button" class="btn btn-md btn-white">B</button>
                                            <button onclick="setRoomLight(13)" type="button" class="btn btn-md btn-warning">Ž</button>
                                            <button onclick="setRoomLight(5)" type="button" class="btn btn-md  btn-danger">Č</button>
                                            <button onclick="setRoomLight(6)" type="button" class="btn btn-md  btn-success">Z</button>
                                            <button onclick="setRoomLight(7)" type="button" class="btn btn-md  btn-primary">M</button>
                                        </div>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between align-items-center align-middle">
                                <div class="btn-group ">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h3 class="card-header">Dům</h3>
                            <table class="table">
                                <tr>
                                    <td>
                                        <button onclick="securityRedirect()" type="button" class="btn btn-md btn-outline-secondary btn-block">Zabezpečení:</button>
                                    <td class="align-middle">
                                        <p id="securityState" class="card-text"></p>
                                    </td>
                                    <td>
                                        <button onclick="securityChangeState()" id="btnSecurity" type="button" class="btn btn-sm btn-outline-secondary btn-block"></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="security.php#history" class="card-text">Vchodové dveře:</a>
                                    </td>
                                    <td>
                                        <p id="mainDoorState" class="card-text"></p>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-middle">
                                        <button onclick="heatingRedirect()" id="btnHeatingSettings" type="button" class="btn btn-md btn-outline-secondary btn-block">Vytápění:</button>
                                    </td>
                                    <td class="align-middle">
                                        <p id="heatingState" class="card-text"></p>
                                    </td>
                                    <td>
                                        <button id="btnHeating1" type="button" class="btn btn-sm btn-outline-secondary btn-block"></button>
                                        <button id="btnHeating2" type="button" class="btn btn-sm btn-outline-secondary btn-block"></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card mb-4 box-shadow">
                        <div class="card-body">
                            <h3 class="card-header">Venku</h3>
                            <table class="table">
                                <tr>
                                    <td class="align-middle">
                                        <a href="charts.php" class="card-text">Počasí:</a>
                                    </td>
                                    <td class="align-middle">
                                        <p id="outdoorTemp" class="card-text"></p>
                                    </td>
                                    <td>
                                        <img id="weatherIcon" src="" alt=".">
                                    </td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</main>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="Album example for Bootstrap_files/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="Album example for Bootstrap_files/popper.min.js"></script>
<script src="Album example for Bootstrap_files/bootstrap.min.js"></script>
<script src="Album example for Bootstrap_files/holder.min.js"></script>

<!--My constants-->
<script type="text/javascript" src="commonLib.js"></script>

<svg xmlns="http://www.w3.org/2000/svg" width="348" height="225" viewBox="0 0 348 225" preserveAspectRatio="none" style="display: none; visibility: hidden; position: absolute; top: -100%; left: -100%;"><defs><style type="text/css"></style></defs><text x="0" y="17" style="font-weight:bold;font-size:17pt;font-family:Arial, Helvetica, Open Sans, sans-serif">Thumbnail</text></svg></body></html>


<script type="text/javascript">
    let completeState; //state of smarthome

    function updateView() {
        document.getElementById("roomTemp").innerText = completeState.roomTemp + " °C";
        document.getElementById("roomHum").innerText = completeState.roomHum + " %";
        if(completeState.roomLED === "3")
        {
            document.getElementById("roomLight").innerText = "Vypnuto";
            document.getElementById("roomLightBtn").innerText = "Rozsviť";
            document.getElementById("roomLightBtn").onclick = function(){ return setRoomLight(4); };
        }
        else
        {
            document.getElementById("roomLight").innerText = "Zapnuto";
            document.getElementById("roomLightBtn").innerText = "Zhasni";
            document.getElementById("roomLightBtn").onclick = function(){ return setRoomLight(3); };
        }

        if(completeState.heating === "0")
        {
            document.getElementById("heatingState").innerText = "Vypnuto (časem)";
            document.getElementById("btnHeating1").innerText = "Zapnout";
            document.getElementById("btnHeating1").onclick = function () {return heatingClick(4)};
            document.getElementById("btnHeating2").innerText = "Vypnout";
            document.getElementById("btnHeating2").onclick = function () {return heatingClick(3)};
        }
        else if(completeState.heating === "1")
        {
            document.getElementById("heatingState").innerText = "Zapnuto (časem)";
            document.getElementById("btnHeating1").innerText = "Zapnout";
            document.getElementById("btnHeating1").onclick = function () {return heatingClick(4)};
            document.getElementById("btnHeating2").innerText = "Vypnout";
            document.getElementById("btnHeating2").onclick = function () {return heatingClick(3)};
        }
        else if(completeState.heating === "3")
        {
            document.getElementById("heatingState").innerText = "Vypnuto (trvale)";
            document.getElementById("btnHeating1").innerText = "Zapnout";
            document.getElementById("btnHeating1").onclick = function () {return heatingClick(4)};
            document.getElementById("btnHeating2").innerText = "Řídit časem";
            document.getElementById("btnHeating2").onclick = function () {return heatingClick(0)};
        }
        else if(completeState.heating === "4")
        {
            document.getElementById("heatingState").innerText = "Zapnuto (trvale)";
            document.getElementById("btnHeating1").innerText = "Řídit časem";
            document.getElementById("btnHeating1").onclick = function () {return heatingClick(0)};
            document.getElementById("btnHeating2").innerText = "Vypnout";
            document.getElementById("btnHeating2").onclick = function () {return heatingClick(3)};
        }

        if (completeState.securitySystem === "0")
        {
            document.getElementById("securityState").innerText = "Nezastřeženo";
            document.getElementById("btnSecurity").innerText = "Zastřežit";
        }
        else
        {
            document.getElementById("securityState").innerText = "Zastřeženo";
            document.getElementById("btnSecurity").innerText = "Odstřežit";
        }

        if (completeState.mainDoor === "1")
        {
            document.getElementById("mainDoorState").innerText = "Otevřeny";
        }
        else
        {
            document.getElementById("mainDoorState").innerText = "Zavřeny";
        }

    }




    function checkModules()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=modulesState');

        request.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                let modules = JSON.parse(this.responseText);
                for(let module of modules)
                {
                    if(module.state === "0")
                    {
                        document.getElementById("warnings").className = "alert alert-danger";
                        document.getElementById("warnings").role = "alert";
                        document.getElementById("warnings").onclick = function () {
                            window.location.href = "settings.php";
                        };
                        document.getElementById("warnings").innerText = "Modul " + module.id + " je offline!";
                    }
                }
            }
        };
        request.send();
    }

    function initData()
    {
        completeStateUpdate();
        getOutdoorTemp();
        pingModules();
        setTimeout(checkModules, 10000); //after 10s check modules (delay so modules can answer and everything is in the database)
        setInterval(completeStateUpdate, 5000);
        setInterval(getOutdoorTemp, 1000 * 60 * 21); //get new data every 21 minutes
    }

    function completeStateUpdate()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=all');

        request.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                completeState = JSON.parse(this.responseText);
                updateView();
            }
        };
        request.send();
    }


    function getOutdoorTemp()
    {
        let request = new XMLHttpRequest();
        request.open('GET', 'http://api.openweathermap.org/data/2.5/weather?id=3077920&APPID=ee934055818d06145b31c4d4b6c8fc06&units=metric');

        request.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200)
            {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                let openWeather = JSON.parse(this.responseText);
                document.getElementById("outdoorTemp").innerText = openWeather.main.temp + " °C";
                document.getElementById("weatherIcon").src = "http://openweathermap.org/img/wn/" + openWeather.weather[0]["icon"] + "@2x.png";
            }
        };
        request.send();
    }

    function setRoomLight(button_number)
    {
        let request = new XMLHttpRequest();

        request.open('GET', BLYNK_API_URL + auth_token + '/update/' + pin_ir + '?value=' + button_number);

        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);
                if(this.status === 200)
                {
                    //completeStateUpdate();
                    completeState.roomLED = button_number.toString();
                    updateView();
                }
            }
        };

        request.send();
    }

    function heatingClick(state)
    {
        completeState.heating = state.toString();
        updateView();
        let request = new XMLHttpRequest();

        request.open('GET', BLYNK_API_URL + auth_token + '/update/' + pin_heatingState + '?value=' + state);

        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                //completeStateUpdate();
            }
        };

        request.send();
    }

    function heatingRedirect() {
        window.location = RPI_URL + 'heating.php';
    }

    function securityRedirect()
    {
        window.location = RPI_URL + 'security.php';

    }

    function securityChangeState()
    {
        let request = new XMLHttpRequest();
        if(completeState.securitySystem === "1")
            completeState.securitySystem = "0";
        else
            completeState.securitySystem = "1";

        request.open('GET', BLYNK_API_URL + auth_token + '/update/' + pin_securityState + '?value=' + completeState.securitySystem);

        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                updateView();
            }
        };

        request.send();
    }
</script>