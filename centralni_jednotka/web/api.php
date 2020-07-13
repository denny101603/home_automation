<?php
/**
 * 
 */

include_once "dbLib/TempHum.php";
include_once "dbLib/Doors.php";
include_once "dbLib/State.php";
include_once "dbLib/Database.php";
include_once "dbLib/dbObject.php";
include_once "dbLib/Rule.php";
include_once "dbLib/ModuleState.php";
include_once "dbLib/CardsRFID.php";
include_once "dbLib/Motion.php";
include_once "dbLib/SecurityChange.php";
include_once "CompleteState.php";


$db = new Database();


if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $success = false;
    if(!isset($_GET['type']))
    {
        doDie();
    }
    if($_GET["type"] == "heatingTimes")
    {
        $success = updateHeatingTimes(file_get_contents('php://input'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://139.59.206.133/wp6J0nhwcAe7QqGUwSN9ZzRG9p2qipLD/update/V40?value=1'); //notify new heating times written
        $data = curl_exec($ch);
        curl_close($ch);
    }
    elseif ($_GET["type"] == "rule")
    {
        $success = saveNewRule(file_get_contents('php://input'));
    }
    elseif ($_GET["type"] == "rfidUpdate")
    {
        $success = updateRFIDCard(file_get_contents('php://input'));
    }

    if($success)
    {
        return;
    }
    else
    {
        doDie();
    }
}

if(!isset($_GET["type"]))
{
    doDie();
    return;
}


$type = $_GET["type"];

if($type == "all")
{
    $completeState = new CompleteState($db->getConnection());
    if(is_null($completeState))
    {
        doDie();
    }
    echo json_encode($completeState);
}
elseif($type == "temp" or $type == "hum")
{
    if(!isset($_GET["moduleID"]))
    {
        doDie();
    }
    $moduleID = $_GET["moduleID"];
    $tempHum = TempHum::getCurrent($moduleID, $db->getConnection());
    if(is_null($tempHum))
    {
        doDie();
    }
    echo json_encode($tempHum);
}
elseif ($type == "door")
{
    if(!isset($_GET["moduleID"]))
    {
        doDie();
    }
    $moduleID = $_GET["moduleID"];
    $tempHistory = Doors::getCurrent($moduleID, $db->getConnection());
    if(is_null($tempHistory))
    {
        doDie();
    }
    echo json_encode($tempHistory);
}
elseif ($type == "state")
{
    $state = State::getCurrent($db->getConnection());
    if(is_null($state))
    {
        doDie();
    }
    echo json_encode($state);
}
elseif($type == "rules")
{
    $rules = Rule::getAllRules($db->getConnection());
    echo json_encode($rules);
}
elseif($type == "deleteRule")
{
    if(!isset($_GET["id"]))
    {
        doDie();
    }
    if(Rule::deleteByID($_GET["id"], $db->getConnection()))
        return;
    else
    {
        doDie();
    }
}
elseif($type == "heatingTimes")
{
    $state = State::getCurrent($db->getConnection());
    if(is_null($state))
    {
        doDie();
    }
    echo json_encode($state->heatingSettings);
}
elseif ($type == "tempHistory")
{
    if(!isset($_GET["moduleID"]))
        doDie();
    if(!isset($_GET["len"]))
        doDie();

    $tempHistory = TempHum::getLastXHours($_GET["moduleID"], $_GET["len"], $db->getConnection());
    if(is_null($tempHistory))
        doDie();

    echo json_encode($tempHistory);
}
elseif ($type == "doorHistory")
{
    $doorHistory = null;
    if(!isset($_GET["moduleID"]))
        doDie();

    if(!isset($_GET["len"]))
        $doorHistory = Doors::getHistory($_GET["moduleID"], $db->getConnection());
    else
    {
        $doorHistory = Doors::getLastXHours($_GET["moduleID"], $_GET["len"], $db->getConnection());
    }
    if(is_null($doorHistory))
        doDie();

    echo json_encode($doorHistory);
}
elseif ($type == "secHistory")
{
    if(!isset($_GET["len"]))
        doDie();

    $secHistory = SecurityChange::getLastXHours($_GET["len"], $db->getConnection());
    if(is_null($secHistory))
        doDie();

    echo json_encode($secHistory);
}
elseif ($type == "motionHistory")
{
    if(!isset($_GET["moduleID"]))
        doDie();
    if(!isset($_GET["len"]))
        doDie();

    $motionHistory = Motion::getLastXHours($_GET["moduleID"],$_GET["len"], $db->getConnection());

    if(is_null($motionHistory))
        doDie();

    echo json_encode($motionHistory);
}
elseif ($type == "modulesState")
{
    $states = ModuleState::getStates($db->getConnection());
    if(is_null($states))
        doDie();

    echo json_encode($states);
}
elseif ($type == "rfidCards")
{
    $cards = CardsRFID::getAll($db->getConnection());
    if(is_null($cards))
        doDie();

    echo json_encode($cards);
}
elseif ($type == "rfid")
{
    if(!isset($_GET["uid"]))
        doDie();

    $card = CardsRFID::getByUID($_GET["uid"], $db->getConnection());
    if(is_null($card))
        doDie();

    echo json_encode($card);
}
elseif ($type == "deleteRFIDCard")
{
    if(!isset($_GET["uid"]))
        doDie();

    $success = CardsRFID::delete($_GET["uid"], $db->getConnection());
    if(!$success)
        doDie();
}
else
{
    doDie();
}


function doDie()
{
    header("HTTP/1.1 404 Not Found");
    exit();
}

/**
 * saves new rule to DB
 * @param $json string with new rule in json
 * @return bool success
 */
function saveNewRule($json)
{
    global $db;
    $data = json_decode($json);
    $rule = new Rule($data->event, $data->eventSpec, $data->action, $data->actionSpec, $db->getConnection());
    return $rule->saveNew();
}

function updateHeatingTimes($json)
{
    global $db;
    return State::saveHeatingSettings($json, $db->getConnection());
}

function updateRFIDCard($json)
{
    global $db;
    $data = json_decode($json);
    return CardsRFID::update($data->uid, $data->owner, $db->getConnection());
}

?>
