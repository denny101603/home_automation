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

printHeader(true);
?>

<main role="main">

    <div class="album py-5 bg-light">
        <div class="container-fluid">
            <H3>Nové pravidlo:</H3>
            <div class="row">
                <div class="col-md-3">
                    <form>
                        <div class="form-group">
                            <label for="eventPicker">Pokud nastane:</label>
                            <select onchange="eventClick()" size="6" class="form-control" id="eventPicker">
                                <option value="0">Dveře se otevřou/zavřou</option>
<!--                                <option value="1">Je přesně ... hodin</option>-->
                                <option value="2">Na Wi-Fi se připojí zařízení ...</option>
                                <option value="3">Teplota v ... je ...</option>
                                <option value="4">Zabezpečení odhalí neoprávněné vniknutí</option>
                                <option value="5">Zaznamenán pohyb ...</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="col-md-3" id="eventSpec">

                </div>

                <div class="col-md-3">
                    <form>
                        <div class="form-group">
                            <label for="actionPicker">Potom proveď:</label>
                            <select onchange="actionClick()" size="6" class="form-control" id="actionPicker">
                                <option value="0">Rozsviť/zhasni světlo... </option>
                                <option value="1">Zastřežení domu...</option>
                                <option value="2">Vytápění...</option>
                                <option value="3">Pošli e-mail...</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="col-md-3" id="actionSpec">

                </div>

            </div>
        </div>

        <div id="rulesSection" class="container-fluid">
            <H3>Existující pravidla:</H3>
            <table id="rulesList" class="table table-striped">
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
    let eventValue = -1; //index of chosen event
    let actionValue = -1; //index of chosen action


    function init() {
        reloadRuleTable();
    }

    /**
     * used for creating of rule's table
     * @returns {string} text description of the event
     * @param value
     */
    function getEventByValue(value)
    {
        let options = document.getElementById("eventPicker").options;
        for (let opt of options)
        {
            if (opt.value === value)
                return opt.text;
        }
        return "";
    }

    function getActionByValue(value)
    {
        let options = document.getElementById("actionPicker").options;
        for (let opt of options)
        {
            if (opt.value === value)
                return opt.text;
        }
        return "";
    }

    //returns text node with description of event specification according to event index
    function getEventSpecDescription(rule)
    {
        let text;
        if (rule.event === "0") //door
        {
            if (JSON.parse(rule.eventSpecification).door === "open")
                text = document.createTextNode("Dveře se otevřou");
            else
                text = document.createTextNode("Dveře se zavřou");
        }
        else if(rule.event === "1") //time
        {
            text = document.createTextNode("Čas je přesně " + JSON.parse(rule.eventSpecification).time);
        }
        else if(rule.event === "2") //connected device
        {
            text = document.createTextNode("MAC adresa zařízení: " + JSON.parse(rule.eventSpecification).mac);
        }
        else if(rule.event === "3") //temperature
        {
            let gl;
            let place;
            let temp = JSON.parse(rule.eventSpecification).temp;

            if (JSON.parse(rule.eventSpecification).place === "outdoor")
                place = "venku";
            else if (JSON.parse(rule.eventSpecification).place === "room")
                place = "v pokoji";
            if (JSON.parse(rule.eventSpecification).compare === "greater")
                gl = "vyšší";
            else
                gl = "nižší";

            text = document.createTextNode("Teplota " + place + " je " + gl + " než " + temp + " °C");
        }
        else if(rule.event === "4") //security system break
        {
            text = document.createTextNode("Zaznamenán průnik do zastřeženého domu");
        }
        else if(rule.event === "5") //motion
        {
            text = document.createTextNode("Zaznamenán pohyb v místnosti: Chodba");
        }
        else
        {
            text = document.createTextNode("Chybí popis");
        }
        return text;
    }

    /**
     * returns text node with description of action specification according to action index
     */
    function getActionSpecDescription(rule)
    {
        let textNode;
        if (rule.action === "0") //light
        {
            let text;
            if(JSON.parse(rule.actionSpecification).place === "room")
                text = "Světlo v pokoji";

            if (JSON.parse(rule.actionSpecification).light === "on")
                text += " se rozsvítí";
            else
                text += " zhasne";

            textNode = document.createTextNode(text);
        }
        else if (rule.action === "1") //security system
        {
            if(JSON.parse(rule.actionSpecification).security === "on")
                textNode = document.createTextNode("Zastřežit dům");
            else
                textNode = document.createTextNode("Odstřežit dům");
        }
        else if (rule.action === "2") //heating system
        {
            if(JSON.parse(rule.actionSpecification).state === "on")
                textNode = document.createTextNode("Zapnout vytápění");
            else if(JSON.parse(rule.actionSpecification).state === "off")
                textNode = document.createTextNode("Vypnout vytápění");
            else
                textNode = document.createTextNode("Řídit vytápění časem");
        }
        else if (rule.action === "3") //send email
        {
            let text = "Předmět: " + JSON.parse(rule.actionSpecification).value1.toString();
            text += " Text: " + JSON.parse(rule.actionSpecification).value2.toString();
            textNode = document.createTextNode(text);
        }
        else
        {
            textNode = document.createTextNode("chybí popis!");
        }
        return textNode;
    }

    function generateRulesTable(rules)
    {
        let table = document.getElementById("rulesList");
        while(table.hasChildNodes()) //clear table
            table.removeChild(table.firstChild);

        let thead = table.createTHead();
        thead.className += "thead-dark";
        let row = thead.insertRow();
        //header
        for (let colName of ["událost", "Podrobnosti události", "Akce", "Podrobnosti akce", ""])
        {
            let th = document.createElement("th");
            let text = document.createTextNode(colName);
            th.appendChild(text);
            row.appendChild(th);
        }

        let tbody = table.createTBody();
        for (let rule of rules) {
            let row = tbody.insertRow();

            let cell = row.insertCell();
            cell.appendChild(document.createTextNode(getEventByValue(rule["event"])));

            cell = row.insertCell();
            cell.appendChild(getEventSpecDescription(rule));

            cell = row.insertCell();
            cell.appendChild(document.createTextNode(getActionByValue(rule["action"])));

            cell = row.insertCell();
            cell.appendChild(getActionSpecDescription(rule));

            cell = row.insertCell();
            let btn = document.createElement("button");
            btn.type = "button";
            btn.className = "btn btn-sm btn-danger";
            btn.innerText = "Smaž";
            btn.onclick = function(){return deleteRule(rule.id, reloadRuleTable)};
            cell.appendChild(btn);
        }
    }

    function reloadRuleTable()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=rules');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                //console.log('Body:', this.responseText);

                if(this.status === 200)
                {
                    let rules = JSON.parse(this.responseText);

                    generateRulesTable(rules);
                }
            }
        };
        request.send();
    }



    function eventClick()
    {
        let eventSpecHtml = '';
        eventValue = document.getElementById("eventPicker").value;
        if (eventValue == 0) //door open/close
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <label for="eventSpecForm">Dveře se:</label>\n' +
                '                            <select class="form-control" id="eventSpecForm">\n' +
                '                                <option value="open">otevřou</option>\n' +
                '                                <option value="close">zavřou</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        else if (eventValue == 1) //time
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <label for="eventSpecForm">Čas je přesně:</label>\n' +
                '                            <input type="time" id="eventSpecForm" class="form-control">\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        else if (eventValue == 2) //device connected to wifi
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <label for="eventSpecForm">MAC adresa zařízení:</label>\n' +
                '                            <input type="text" id="eventSpecForm" class="form-control">\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        else if (eventValue == 3) //temperature
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <label for="eventSpecForm">Teplota:</label>\n' +
                '                            <select class="form-control" id="eventSpecForm">\n' +
                '                                <option value="room">v pokoji</option>\n' +
                '                                <option value="outdoor">venku</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <label for="eventSpecForm2">je:</label>\n' +
                '                            <select class="form-control" id="eventSpecForm2">\n' +
                '                                <option value="greater">větší</option>\n' +
                '                                <option value="less">menší</option>\n' +
                '                            </select>\n' +
                '                        <div class="form-group">\n' +
                '                        </div>\n' +
                '                            <label for="eventSpecForm3">než:</label>\n' +
                '                            <input type="number" placeholder="°C" id="eventSpecForm3" class="form-control">\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        else if (eventValue == 4) //security system break
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <p>Pokud je dům zastřežený a systém odhalí pohyb/otevření dveří nebo podobné.</p>\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        else if (eventValue == 5) //motion
        {
            eventSpecHtml = '<form>\n' +
                '                        <label for="specFormGroup">Podrobnosti události:</label>\n' +
                '                        <div id="specFormGroup" class="form-group">\n' +
                '                            <label for="eventSpecForm">Pohyb v místnosti:</label>\n' +
                '                            <select class="form-control" id="eventSpecForm">\n' +
                '                                <option value="hallway">chodba</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                    </form>';
        }
        document.getElementById("eventSpec").innerHTML = eventSpecHtml;
    }

    function actionClick()
    {
        let actionSpecHtml = '';
        actionValue = document.getElementById("actionPicker").value;
        if (actionValue == 0) //lights...
        {
            actionSpecHtml = '<form>\n' +
                '                        <label for="actionSpecFormGroup">Podrobnosti následné akce:</label>\n' +
                '                        <div id="actionSpecFormGroup" class="form-group">\n' +
                '                            <select class="form-control" id="actionSpecForm">\n' +
                '                                <option value="on">Rozsviť</option>\n' +
                '                                <option value="off">Zhasni</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                        <div class="form-group">\n' +
                '                            <label for="actionSpecForm2">světlo:</label>\n' +
                '                            <select class="form-control" id="actionSpecForm2">\n' +
                '                                <option value="room">v pokoji</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                    </form>'
        }
        else if (actionValue == 1) //security system...
        {
            actionSpecHtml = '<form>\n' +
                '                        <label for="actionSpecFormGroup">Podrobnosti následné akce:</label>\n' +
                '                        <div id="actionSpecFormGroup" class="form-group">\n' +
                '                            <select class="form-control" id="actionSpecForm">\n' +
                '                                <option value="on">Zastřežit</option>\n' +
                '                                <option value="off">Odstřežit</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                    </form>'
        }
        else if (actionValue == 2) //heating system
        {
            actionSpecHtml = '<form>\n' +
                '                        <label for="actionSpecFormGroup">Podrobnosti následné akce:</label>\n' +
                '                        <div id="actionSpecFormGroup" class="form-group">\n' +
                '                            <label for="actionSpecForm">Vytápění:</label>\n' +
                '                            <select class="form-control" id="actionSpecForm">\n' +
                '                                <option value="on">zapnout</option>\n' +
                '                                <option value="off">vypnout</option>\n' +
                '                                <option value="time">řídit časem</option>\n' +
                '                            </select>\n' +
                '                        </div>\n' +
                '                    </form>'
        }
        else if (actionValue == 3) //e-mail
        {
            actionSpecHtml = '<form>\n' +
                '                        <label for="actionSpecFormGroup">Podrobnosti následné akce:</label>\n' +
                '                        <div id="actionSpecFormGroup" class="form-group">\n' +
                '                            <label for="actionSpecForm">Předmět e-mailu:</label>\n' +
                '                            <input type="text" id="actionSpecForm" class="form-control">\n' +
                '                            <label for="actionSpecForm2">Text e-mailu:</label>\n' +
                '                            <input type="text" id="actionSpecForm2" class="form-control">\n' +
                '                        </div>\n' +
                '                    </form>'
        }

        let saveButtonHtml = '<button onclick="saveClick()" id="save" type="button" class="btn btn-success">Uložit pravidlo</button>';
        document.getElementById("actionSpec").innerHTML = actionSpecHtml + saveButtonHtml;

    }

    function getEventSpec(eventIndex)
    {
        let json;
        if(eventIndex == 0) //door
        {
            json = JSON.stringify({door: document.getElementById("eventSpecForm").value});
        }
        else if(eventIndex == 1) //time
        {
            let time = document.getElementById("eventSpecForm").value;
            if(time === "") //no time selected
            {
                return false;
            }
            else
            {
                json = JSON.stringify({time: time});
            }
        }
        else if(eventIndex == 2) //connected device to wifi
        {
            let mac = document.getElementById("eventSpecForm").value;
            if(mac === "") //no mac address filled
            {
                return false;
            }
            else
            {
                json = JSON.stringify({mac: mac});
            }
        }
        else if(eventIndex == 3) //temperature
        {
            let place = document.getElementById("eventSpecForm").value;
            let compare = document.getElementById("eventSpecForm2").value;
            let temp = document.getElementById("eventSpecForm3").value;

            if(temp === "") //no temperature selected
            {
                return false;
            }
            else
            {
                json = JSON.stringify({place: place, compare:compare, temp:temp});
            }
        }
        else if(eventIndex == 4) //security system break
        {
            json = ""; //no specifications
        }
        else if(eventIndex == 5) //motion
        {
            let place = document.getElementById("eventSpecForm").value;
            json = JSON.stringify({place: place});
        }
        return json;
    }

    function getActionSpec(actionIndex)
    {
        let json;
        if(actionIndex == 0) //light
        {
            let light = document.getElementById("actionSpecForm").value;
            let place = document.getElementById("actionSpecForm2").value;
            json = JSON.stringify({light: light, place: place});
        }
        else if(actionIndex == 1) //security system
        {
            json = JSON.stringify({security: document.getElementById("actionSpecForm").value});
        }
        else if(actionIndex == 2) //heating system
        {
            json = JSON.stringify({state: document.getElementById("actionSpecForm").value});
        }
        else if(actionIndex == 3) //send e-mail
        {
            let subject = document.getElementById("actionSpecForm").value;
            let text = document.getElementById("actionSpecForm2").value;
            json = JSON.stringify({value1: subject, value2: text});
        }
        return json;
    }

    function saveClick()
    {
        if(document.getElementById("save").className === "btn btn-outline-success disabled") //button is disabled
            return; //do nothing
        if(eventValue < 0 || actionValue < 0) //not selected event or action
        {
            document.getElementById("save").innerText = "Neúspěch! Uložit znovu";
            return;
        }

        let eventSpec = getEventSpec(eventValue);
        let actionSpec = getActionSpec(actionValue);

        if(eventSpec === false || actionSpec === false) //specifications not complete
        {
            document.getElementById("save").innerText = "Neúspěch! Uložit znovu";
            return;
        }

        document.getElementById("save").className = "btn btn-outline-success disabled";

        let json = JSON.stringify({event: eventValue, action: actionValue, eventSpec: eventSpec, actionSpec: actionSpec});

        let request = new XMLHttpRequest();
        request.open('POST', RPI_URL + 'api.php?type=rule');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Sent JSON:', json);
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                if(this.status === 200)
                {
                    document.getElementById("save").innerText = "Uloženo";
                    setTimeout(reloadRuleTable, 500);
                }
                else
                {
                    document.getElementById("save").innerText = "Neúspěch! Uložit znovu";
                    document.getElementById("save").className = "btn btn-outline-success";
                }
            }
        };

        request.send(json);
    }
</script>