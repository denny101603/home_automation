
let RPI_URL = 'put rapsberry IP address here'; //TODO  E.g.: http://192.168.1.5/
let BLYNK_API_URL = 'http://139.59.206.133/';
let auth_token = "put your blynk authentification token here"; //TODO
let pin_ir = "V60";
let pin_securityState = "V10";
let pin_heatingState = "V42";

let PIN_WIFI = "V1";


/**
 * gets modules from DB (API) and pings them one by one
 */
function pingModules()
{
    let delay = 100; //delay of calling pingModule in ms (blynk API is sometimes a little lazy)

    let request = new XMLHttpRequest();
    request.open('GET', RPI_URL + 'api.php?type=modulesState');
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            console.log('Status:', this.status);
            console.log('Body:', this.responseText);

            let modules = JSON.parse(this.responseText);
            for(let module of modules)
            {
                setTimeout(function () {pingModule(parseInt(module.id))}, delay);
                delay += 1500; //+1,5s
            }
        }
    };
    request.send();
}

/**
 * Pings module (sets virtual pin moduleID+9 to 0)
 * @param moduleID
 */
function pingModule(moduleID)
{
    let pin = "V" + (moduleID + 9).toString(); //all modules are listening for "ping" on virtual pin x9
    let request = new XMLHttpRequest();
    request.open('GET', BLYNK_API_URL + auth_token + '/update/' + pin + '?value=0');
    request.onreadystatechange = function () {
        if (this.readyState === 4) {
            console.log('Status:', this.status);
        }
    };
    request.send();
}

/**
 * deletes rule from DB
 * @param id of rule to delete
 * @param functionToCall function to be called after successful delete
 */
function deleteRule(id, functionToCall)
{
    let request = new XMLHttpRequest();
    request.open('GET', RPI_URL + 'api.php?type=deleteRule&id=' + id.toString());
    request.onreadystatechange = function ()
    {
        if (this.readyState === 4 && this.status === 200) {
            console.log('deleted rule id:', id);

            functionToCall();
        }
    };
    request.send();
}