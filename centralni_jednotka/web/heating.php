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

printHeader(false);
?>

<main role="main">
    <div class="album py-5 bg-light">
        <div class="container">

            <div>

                <div id="hourPicker1">
                    <h3>Ráno</h3>
                </div>
                <div id="hourPicker2">
                    <h3>Dopoledne</h3>
                </div>
                <div id="hourPicker3">
                    <h3>Odpoledne</h3>
                </div>
                <div id="hourPicker4">
                    <h3>Večer</h3>
                </div>

                <p></p>
                <button onclick="saveClick()" id="save" type="button" class="btn btn-success">Uložit nové nastavení</button>
                <p id="savedText"></p>

                <h4>Podrobnější nastavení</h4>
                <div id="minutePicker1">
                    <h4>Ráno</h4>
                </div>
                <div id="minutePicker2">
                    <h4>Dopoledne</h4>
                </div>
                <div id="minutePicker3">
                    <h4>Odpoledne</h4>
                </div>
                <div id="minutePicker4">
                    <h4>Večer</h4>
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

    let times = []; //array with 96 items - each means 15 minutes span in a day, 0 means heating turned off, 1 on
    let buttonOnClass = "btn btn-sm btn-success ";
    function updateView()
    {
        for(let i = 0; i < times.length; i++)
        {
            let btn = document.getElementById(i.toString());
            if(times[i] === 1) //heating turned on on this time
            {
                btn.className = buttonOnClass;
                btn.value = 1;
            }
            else
            {
                btn.className = "btn btn-sm btn-outline-secondary ";
                btn.value = 0;
            }
            if(i % 4 === 3) //for each hour button
            {
                let number = (i-3)/4+100;
                let btnHour = document.getElementById(number.toString());
                if(times[i] === times[i - 1] && times[i] === times[i - 2] && times[i] === times[i - 3]) //during all hour the same state is set
                {
                    if(times[i] === 1)
                    {
                        btnHour.className = buttonOnClass;
                        btnHour.value = 1;
                    }
                    else
                    {
                        btnHour.className = "btn btn-sm btn-outline-secondary ";
                        btnHour.value = 0;
                    }
                }
                else
                {
                    btnHour.className = "btn btn-sm btn-light ";
                    btnHour.value = 2;
                }
            }
        }
    }

    function createButton(hours) {
        let btn = document.createElement("button");
        btn.className = "btn btn-sm btn-outline-secondary ";
        btn.type = "button";
        btn.value = 0;
        btn.id = (hours + 100).toString(); //shift by 100 to tell apart hours from quarter hours
        btn.innerText = hours.toString().padStart(2, "0") + ":00 - " + (hours + 1).toString().padStart(2, "0") + ":00";
        btn.onclick = function () {
            if(this.value == 0 || this.value == 2) //0 means not chosen, 2 means partly chosen
            {
                times[hours*4] = 1;
                times[hours*4 +1] = 1;
                times[hours*4 +2] = 1;
                times[hours*4 +3] = 1;
                this.value = 1;
            }
            else if(this.value == 1)
            {
                times[hours*4] = 0;
                times[hours*4 +1] = 0;
                times[hours*4 +2] = 0;
                times[hours*4 +3] = 0;
                this.value = 0;
            }
            updateView();
        };
        return btn;
    }

    function createMinButton(hours, minutes)
    {
        let btn = document.createElement("button");
        btn.className = "btn btn-sm btn-outline-secondary";
        btn.type = "button";
        btn.value = 0;
        btn.id = (hours * 4 + minutes / 15).toString(); //also index in times array
        btn.innerText = hours.toString().padStart(2, "0") + ":" + minutes.toString().padStart(2, "0");
        btn.onclick = function () {
            if(this.value == 0) //0 means not chosen
            {
                times[parseInt(this.id)] = 1;
                this.value = 1;
                console.log("writing 1");
            }
            else if(this.value == 1)
            {
                times[parseInt(this.id)] = 0;
                this.value = 0;
            }
            updateView();
        };
        return btn;
    }

    function initData()
    {

        let hours=0;
        let hourPicker = "hourPicker";
        let minutePicker = "minutePicker";
        let s = 0;

        //create pick buttons (hours)
        for(; hours < 24; hours ++)
        {
            if(hours % 6 === 0)
                s++;
            document.getElementById(hourPicker + s).appendChild(createButton(hours));
        }

        //create pick buttons (quarter hours)
        s = 0;
        for(hours = 0; hours < 24; hours ++)
        {
            if(hours % 6 === 0)
                s++;
            for(let minutes = 0; minutes < 59; minutes += 15)
            {
                document.getElementById(minutePicker + s).appendChild(createMinButton(hours, minutes));
            }
        }

        //get times from DB
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=heatingTimes');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText.slice(1, this.responseText.length-1));

                if(this.status === 200)
                {
                    times = JSON.parse(this.responseText.slice(1, this.responseText.length-1));
                    updateView();
                }
            }
        };
        request.send();
    }

    /**
     * Sends time settings of heating system to api which should take care of the rest (sending it to the right module etc)
     */
    function saveClick()
    {
        document.getElementById("savedText").innerText = "Ukládám...";
        let json = JSON.stringify(times);

        let request = new XMLHttpRequest();
        request.open('POST', RPI_URL + 'api.php?type=heatingTimes');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Sent JSON:', json);
                console.log('Status:', this.status);

                if(this.status === 200)
                {
                    document.getElementById("savedText").innerText = "Úspěšně uloženo!";
                    setTimeout(function () {document.getElementById("savedText").innerText = "";}, 6000);
                }
                else
                {
                    document.getElementById("savedText").innerText = "Uložení se nezdařilo!";
                }
            }
        };

        request.send(json);
    }

</script>