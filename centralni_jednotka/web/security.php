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

printHeader(false);
?>

<main role="main">

    <div class="album py-5 bg-light">
        <div class="container">
            <H3>Nastavení zabezpečení:</H3>
            <H4>Způsoby odstřežení:</H4>
            <H5>RFID karty:</H5>
            <p>Zabezpečení lze odstřežit přiložením přístupové (RFID) karty ke čtečce. Na odstřežení je časový limit 30s po prvním zaznamenaném vniknutí. Poté se spustí alarm a další ochranné prvky.</p>
            <div class="row">
                <div class="col-md">
                    <form>
                        <div class="form-group">
                            <label for="rfidList">Přístupové (RFID) karty:</label>
                            <select onchange="cardPick()" size="7" class="form-control" id="rfidList"></select>
                        </div>
                    </form>
                </div>

                <div class="col-md">
                    <label for="rfidDescription">Podrobnosti o kartě:</label>
                    <form id="rfidDescription">
                        <div class="form-group">
                            <label for="uid">UID:</label>
                            <p id="uid"></p>
                        </div>
                        <div class="form-group">
                            <label for="owner">Jméno uživatele:</label>
                            <input type="text" id="owner" class="form-control" placeholder="Žádný uživatel" data-toggle="tooltip" data-placement="top"
                                   title="Karty bez uživatele neumožňují odblokovat zastřežení. Pro zapnutí této vlastnosti přiřaďte ke kartě jméno uživatele.">
                        </div>
                        <div class="form-group">
                        <button onclick="saveClick()" id="save" type="button" class="btn btn-success ">Uložit uživatele</button>
                            <button onclick="deleteClick()" id="delete" type="button" class="btn btn-danger">Odstranit kartu</button>
                        </div>
                    </form>
                </div>
            </div>

            <H5>Připojení zařízení k Wi-Fi:</H5>
            <p>Zabezpečení se deaktivuje pokud se jedno z následujících zařízení připojí k lokální síti. Toto platí po uplynutí 10 minut od zastřežení.</p>
            <div class="row">
                <div class="col-md">
                    <form>
                        <div class="form-group">
                            <label for="macList">MAC adresy zařízení:</label>
                            <select onchange="macPick()" size="7" class="form-control" id="macList"></select>
                        </div>
                    </form>
                </div>

                <div class="col-md">
                    <label for="macDescription">Nastavení:</label>
                    <form id="macDescription">
                        <div class="form-group">
                            <label for="mac">MAC adresa:</label>
                            <p id="mac"></p>
                            <button onclick="deleteMacClick()" id="deleteMAC" type="button" class="btn btn-danger">Odstranit zařízení</button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="form-group">
                <div class=".col-xs-2">
                    <label for="newMac">Přidat novou MAC adresu:</label>
                    <input type="text" id="newMac" class="form-control" placeholder="18:f0:e4:2b:a4:ca">
                </div>
            </div>
            <div class="form-group">
                <button onclick="addMacClick()" id="addMAC" type="button" class="btn btn-success ">Uložit novou MAC adresu</button>
            </div>

            <div id="recordsSection">
                <br>
                <H3 id="history">Historie bezpečnostního systému:</H3>
                <div class="btn-group">
                    <button onclick="typePick('security')" id="security" type="button" class="btn btn-md btn-outline-secondary">Záznamy zastřežení</button>
                    <button onclick="typePick('door')" id="door" type="button" class="btn btn-md btn-secondary">Záznamy pohybu dveří</button>
                    <button onclick="typePick('motion')" id="motion" type="button" class="btn btn-md btn-outline-secondary">Záznamy pohybu v domě</button>
                </div>
                <br>
                <div class="btn-group">
                    <button onclick="spanPick(1)" id="btn1" type="button" class="btn btn-md btn-secondary">24 hodin</button>
                    <button onclick="spanPick(7)" id="btn7" type="button" class="btn btn-md btn-outline-secondary">7 dní</button>
                    <button onclick="spanPick(30)" id="btn30" type="button" class="btn btn-md btn-outline-secondary">30 dní</button>
                </div>
                <table id="doorRecords" class="table table-striped"></table>
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
    let rulesShowed; //for deleting mac addresses
    let tableType = 'door'; //decides what data are showed in the table
    let timeSpan = 24; //decides how old data are showed in the table (in hours)

    function init()
    {
        reloadRFIDCards();
        getMacAddresses();
        reloadTable();
    }

    function generateMACSelect(macs)
    {
        let selected = false; //for marking first option as selected
        let sel = document.getElementById("macList");
        sel.innerHTML = "";
        for(let mac of macs)
        {
            let opt = document.createElement("option");
            opt.innerText = mac;
            opt.value = mac;
            if(!selected)
            {
                opt.selected = "selected";
                selected = true;
            }
            sel.appendChild(opt);
        }
        macPick();
    }

    function getMacAddresses()
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
                    let macs = [];
                    rulesShowed = [];
                    for(let rule of rules)
                    {
                        if(rule.event == 2 && rule.action == 1) //event device connected to wifi and action concerning security system
                        {
                            if(JSON.parse(rule.actionSpecification).security === "off") //action is to turn off security
                            {
                                macs.push(JSON.parse(rule.eventSpecification).mac);
                                rulesShowed.push(rule);
                            }
                        }
                    }

                    generateMACSelect(macs);
                }
            }
        };
        request.send();
    }

    function deleteMacClick()
    {
        let id;
        for(let rule of rulesShowed)
        {
            if(JSON.parse(rule.eventSpecification).mac === document.getElementById("mac").innerText)
               id = rule.id;
        }
        deleteRule(id, getMacAddresses);
    }

    function addMacClick()
    {
        if(document.getElementById("newMac").value === "")
            return;
        let eventSpec = JSON.stringify({mac: document.getElementById("newMac").value});
        let actionSpec = JSON.stringify({security: "off"});
        let json = JSON.stringify({event: 2, action: 1, eventSpec: eventSpec, actionSpec: actionSpec}); //adding new rule to auto rules

        let request = new XMLHttpRequest();
        request.open('POST', RPI_URL + 'api.php?type=rule');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);

                if(this.status === 200)
                {
                    document.getElementById("addMAC").innerText = "Přidat nové zařízení";
                    getMacAddresses();
                }
                else
                {
                    document.getElementById("addMAC").innerText = "Neúspěch! Uložit znovu";
                }
            }
        };

        request.send(json);
    }

    function macPick()
    {
        document.getElementById("mac").innerText = document.getElementById("macList").value;
    }

    function generateRFIDCardsSelect(cards)
    {
        let selected = false; //for marking first option as selected
        let sel = document.getElementById("rfidList");
        sel.innerHTML = "";
        for(let card of cards)
        {
            let opt = document.createElement("option");
            opt.innerText = card.owner === null ? "Neznámá karta" : card.owner;
            opt.value = card.uid;
            if(!selected)
            {
                opt.selected = "selected";
                selected = true;
            }
            sel.appendChild(opt);
        }
        macPick();
    }

    function reloadRFIDCards()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=rfidCards');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                //console.log('Body:', this.responseText);

                if(this.status === 200)
                {
                    let cards = JSON.parse(this.responseText);

                    generateRFIDCardsSelect(cards);
                }
            }
        };
        request.send();
    }

    function cardPick()
    {
        let uid = document.getElementById("rfidList").value;
        document.getElementById("uid").innerText = uid;

        let options = document.getElementById("rfidList").options;
        let owner = "";
        for (let opt of options)
        {
            if (opt.value === uid)
            {
                if(opt.text !== "Neznámá karta")
                    owner = opt.text;
                break;
            }
        }
        document.getElementById("owner").value = owner;
    }

    function setWayOfSecurityChange(way, cell)
    {
        console.log(way);
        if (way === "rule")
            cell.appendChild(document.createTextNode("Automatickým pravidlem"));
        else if (way === "api")
            cell.appendChild(document.createTextNode("Ostatní"));
        else //rfid
        {
            console.log(way.slice(6));
            let request = new XMLHttpRequest();
            request.open('GET', RPI_URL + 'api.php?type=rfid&uid=' + way.slice(5)); //5 is to slice "rfid:"
            request.onreadystatechange = function () {
                if (this.readyState === 4) {
                    console.log('Status:', this.status);

                    if (this.status === 200) {
                        let card = JSON.parse(this.responseText);
                        cell.appendChild(document.createTextNode("RFID: " + card.owner));
                    }
                    else
                        cell.appendChild(document.createTextNode("RFID: neznámá karta"));

                }
            };
            request.send();
        }
    }

    function generateSecurityRecordsTable(records)
    {
        let table = document.getElementById("doorRecords");
        while(table.hasChildNodes()) //clear table
            table.removeChild(table.firstChild);

        let thead = table.createTHead();
        thead.className += "thead-dark";
        let row = thead.insertRow();
        //header
        for (let colName of ["Čas", "Nový stav", "Způsob změny"])
        {
            let th = document.createElement("th");
            let text = document.createTextNode(colName);
            th.appendChild(text);
            row.appendChild(th);
        }

        let tbody = table.createTBody();
        records = records.reverse();
        for (let record of records) {
            let row = tbody.insertRow();

            let cell = row.insertCell();
            cell.appendChild(document.createTextNode(record["dateTime"]));

            cell = row.insertCell();
            let text = "Zastřeženo";
            if(record["newState"] === "0")
                text = "Vypnuto";
            cell.appendChild(document.createTextNode(text));

            cell = row.insertCell();
            setWayOfSecurityChange(record["way"], cell);
        }
    }

    function loadSecurityRecordsTable()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=secHistory&len=' + timeSpan);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                //console.log('Body:', this.responseText);

                if(this.status === 200)
                {
                    let records = JSON.parse(this.responseText);

                    generateSecurityRecordsTable(records);
                }
            }
        };
        request.send();
    }

    function generateMotionRecordsTable(records)
    {
        let table = document.getElementById("doorRecords");
        while(table.hasChildNodes()) //clear table
            table.removeChild(table.firstChild);

        let thead = table.createTHead();
        thead.className += "thead-dark";
        let row = thead.insertRow();
        //header
        for (let colName of ["Místo", "Čas začátku pohybu", "Čas konce pohybu"])
        {
            let th = document.createElement("th");
            let text = document.createTextNode(colName);
            th.appendChild(text);
            row.appendChild(th);
        }

        let tbody = table.createTBody();
        records = records.reverse();
        for (let record of records) {
            let row = tbody.insertRow();

            let cell = row.insertCell();
            cell.appendChild(document.createTextNode("Chodba"));

            cell = row.insertCell();
            cell.appendChild(document.createTextNode(record["dateTime"]));

            cell = row.insertCell();
            if(record["stopTime"] === null)
                cell.appendChild(document.createTextNode("Neznámý"));
            else
                cell.appendChild(document.createTextNode(record["stopTime"]));
        }
    }

    function loadMotionRecordsTable()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=motionHistory&moduleID=70&len=' + timeSpan);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('motion: Body:', this.responseText);

                if(this.status === 200)
                {
                    let records = JSON.parse(this.responseText);

                    generateMotionRecordsTable(records);
                }
            }
        };
        request.send();
    }

    function reloadTable()
    {
        if(tableType === 'security')
            loadSecurityRecordsTable();
        else if(tableType === 'door')
            loadDoorRecordsTable();
        else if(tableType === 'motion')
            loadMotionRecordsTable();
    }

    function typePick(type)
    {
        document.getElementById("security").className = "btn btn-md btn-outline-secondary";
        document.getElementById("door").className = "btn btn-md btn-outline-secondary";
        document.getElementById("motion").className = "btn btn-md btn-outline-secondary";
        document.getElementById(type).className = "btn btn-md btn-secondary";
        tableType = type;
        reloadTable();
    }

    function spanPick(days)
    {
        document.getElementById("btn1").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn7").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn30").className = "btn btn-md btn-outline-secondary";
        document.getElementById("btn" + days.toString()).className = "btn btn-md btn-secondary";
        timeSpan = days*24;
        reloadTable();
    }

    function generateDoorRecordsTable(records)
    {
        let table = document.getElementById("doorRecords");
        while(table.hasChildNodes()) //clear table
            table.removeChild(table.firstChild);

        let thead = table.createTHead();
        thead.className += "thead-dark";
        let row = thead.insertRow();
        //header
        for (let colName of ["Čas", "Pohyb"])
        {
            let th = document.createElement("th");
            let text = document.createTextNode(colName);
            th.appendChild(text);
            row.appendChild(th);
        }

        let tbody = table.createTBody();
        records.reverse();
        for (let record of records) {
            let row = tbody.insertRow();

            let cell = row.insertCell();
            cell.appendChild(document.createTextNode(record["dateTime"]));

            cell = row.insertCell();
            let text = "Dveře se otevřely";
            if(record["state"] === "0")
                text = "Dveře se zavřely";
            cell.appendChild(document.createTextNode(text));
        }
    }

    /**
     * gets current data from API and regenerates table with door records
     */
    function loadDoorRecordsTable()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=doorHistory&moduleID=50&len=' + timeSpan);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);
                console.log('Body:', this.responseText);

                if(this.status === 200)
                {
                    let records = JSON.parse(this.responseText);

                    generateDoorRecordsTable(records);
                }
            }
        };
        request.send();
    }

    function saveClick()
    {
        let owner = document.getElementById("owner").value;
        if(owner === "")
            owner = null;
        let json = JSON.stringify({uid: document.getElementById("uid").innerText, owner: owner});
        console.log(json);

        let request = new XMLHttpRequest();
        request.open('POST', RPI_URL + 'api.php?type=rfidUpdate');
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Sent JSON:', json);
                console.log('Status:', this.status);

                if(this.status === 200)
                {
                    document.getElementById("save").innerText = "Uložit uživatele";
                    reloadRFIDCards();
                }
                else
                {
                    document.getElementById("save").innerText = "Neúspěch! Uložit znovu";
                }
            }
        };

        request.send(json);
    }

    function deleteClick()
    {
        let request = new XMLHttpRequest();
        request.open('GET', RPI_URL + 'api.php?type=deleteRFIDCard&uid=' + document.getElementById("uid").innerText);
        request.onreadystatechange = function () {
            if (this.readyState === 4) {
                console.log('Status:', this.status);

                if(this.status === 200)
                {
                    reloadRFIDCards();
                }
            }
        };
        request.send();
    }
</script>