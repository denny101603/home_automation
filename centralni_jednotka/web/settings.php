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

<body onload="init()">

<?php
include_once "commonHTML.php";

printHeader(false);
?>

<main role="main">

    <div class="album py-5 bg-light">
        <div class="container">
            <h3>Změnit WiFi údaje</h3>
            <p>Nastaví nové přihlašovací údaje k WiFi síti u všech modulů. Dokud bude funkční původní připojení, moduly ho budou
                využívat.<br>Následně se nejpozději do&#160;30 sekund restartují a pokusí připojit k WiFi s novými údaji.</p>
            <form>
                <div id="specFormGroup" class="form-group">
                    <label for="ssid">SSID:</label>
                    <input type="text" id="ssid" class="form-control">
                    <label for="pswd">Heslo:</label>
                    <input type="password" id="pswd" class="form-control">
                </div>
                <button onclick="saveClick()" id="save" type="button" class="btn btn-success">Uložit</button>
            </form>

            <br>
            <h3>Stav modulů</h3>
            <table id="modulesList" class="table table-striped">
            </table>

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
    /**
     * Gets current states of modules and generates table
     */
    function modulesState()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=modulesState');

        request.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                generateModulesTable(JSON.parse(this.responseText));
            }
        };
        request.send();
    }

    function init()
    {
        modulesState();
        pingModules();
        setTimeout(modulesState, 12000) //after 12s
    }

    function getModuleName(moduleID)
    {
        let name = "";
        if(moduleID === "40")
        {
            name += "Kotel";
        }
        else if(moduleID === "50")
        {
            name += "Vchodové dveře";
        }
        else if(moduleID === "60")
        {
            name += "Teplota + ovládání LED (pokoj)";
        }
        else if(moduleID === "70")
        {
            name += "Senzor pohybu (chodba)";
        }
        else if(moduleID === "80")
        {
            name += "Čtečka RFID karet";
        }
        return name;
    }

    function generateModulesTable(modules)
    {
        let table = document.getElementById("modulesList");
        while(table.hasChildNodes()) //clear table
            table.removeChild(table.firstChild);

        let thead = table.createTHead();
        thead.className += "thead-dark";
        let row = thead.insertRow();
        //header
        for (let colName of ["ID", "Název", "Stav", "Naposledy online"])
        {
            let th = document.createElement("th");
            let text = document.createTextNode(colName);
            th.appendChild(text);
            row.appendChild(th);
        }

        let tbody = table.createTBody();
        for (let module of modules) {
            let row = tbody.insertRow();

            let cell = row.insertCell();
            cell.appendChild(document.createTextNode(module["id"]));

            cell = row.insertCell();
            cell.appendChild(document.createTextNode(getModuleName(module["id"])));

            cell = row.insertCell();
            let text = "online";
            if(module["state"] === "0")
                text = "OFFLINE!";
            cell.appendChild(document.createTextNode(text));

            cell = row.insertCell();
            cell.appendChild(document.createTextNode(module["lastOnline"]));
        }
    }

    function saveClick()
    {
        document.getElementById("save").innerText = "Ukládám...";
        let ssid = document.getElementById("ssid").value;
        let pwd = document.getElementById("pswd").value;

        let request = new XMLHttpRequest();
        request.open('GET', BLYNK_API_URL + auth_token + '/update/' + PIN_WIFI + '?value=' + ssid + "&value=" + pwd);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                if(this.status === 200)
                {
                    document.getElementById("save").innerText = "Úspěšně uloženo!";
                    document.getElementById("save").className += " disabled";
                    document.getElementById("save").onclick = function () {};
                }
                else
                {
                    document.getElementById("save").innerText = "Uložení se nezdařilo!";
                }
            }
        };
        request.send();
    }
</script>