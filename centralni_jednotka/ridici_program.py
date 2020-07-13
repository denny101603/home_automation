"""
[WRITE VIRTUAL PIN EXAMPLE] ========================================================================
Environment prepare:
In your Blynk App project:
  - add "Slider" widget,
  - bind it to Virtual Pin V4,
  - set values range 0-255
  - add "LED" widget and assign Virtual Pin V4 to it
  - Run the App (green triangle in the upper right corner).
  - define your auth token for current example and run it
This started program will periodically call and execute event handler "write_virtual_pin_handler".
In app you can move slider that will cause LED brightness change and will send virtual write event
to current running example. Handler will print pin number and it's updated value.
Schema:
====================================================================================================
          +-----------+                        +--------------+                    +--------------+
          |           |                        |              |                    |              |
          | blynk lib |                        | blynk server |                    |  blynk app   |
          |           |                        |  virtual pin |                    |              |
          |           |                        |              |                    |              |
          +-----+-----+                        +------+-------+                    +-------+------+
                |                                     |                                    |
                |                                     |  write event from "Slider" widget  |
                |                                     |                                    |
                |                                     +<-----------------------------------+
                |                                     |                                    |
                |                                     |                                    |
                |                                     |                                    |
                |                                     |                                    |
 event handler  |   write event to hw from server     |                                    |
(user function) |                                     |                                    |
     +-----------<------------------------------------+                                    |
     |          |                                     |                                    |
     |          |                                     |                                    |
     +--------->+                                     |                                    |
                |                                     |                                    |
                |                                     |                                    |
                |                                     |                                    |
                +                                     +                                    +
====================================================================================================
Additional blynk info you can find by examining such resources:
    Downloads, docs, tutorials:     https://blynk.io
    Sketch generator:               http://examples.blynk.cc
    Blynk community:                http://community.blynk.cc
    Social networks:                http://www.fb.com/blynkapp
                                    http://twitter.com/blynk_app
====================================================================================================
"""

import blynklib
import requests
import time
import mysql.connector
import json
from datetime import datetime, timedelta
from threading import Timer
from scapy.all import *

BLYNK_API_HTTP = "http://139.59.206.133/"
BLYNK_AUTH = 'put your blynk authentification token here' #TODO

PIN_WIFI_UPDATE = "V1" #pin for sending new ssid and password for new wifi which modules should connect to
PIN_SECURITY = "V10"
PIN_KOTEL_TIMES = "V41"

EVENT_DB_INDEX = 1
EVENT_SPEC_DB_INDEX = 2
ACTION_DB_INDEX = 3
ACTION_SPEC_DB_INDEX = 4


DEBUG_INFO = 0 #set to 1 for debug prints

WRITE_EVENT_PRINT_MSG = "[WRITE_VIRTUAL_PIN_EVENT] Pin: V{} Value: {}"


# initialize Blynk
blynk = blynklib.Blynk(BLYNK_AUTH)

class Events:
    """Static enum of events for automatic rules"""
    door = "0"
    time = "1"
    deviceConnected = "2"
    temp = "3"
    securityBreak = "4"
    motion = "5"


class Actions:
    """Static enum of actions for automatic rules"""
    light = "0"
    security = "1"
    heating = "2"
    email = "3"


def getDBconnection():
    return mysql.connector.connect(
        host="localhost",
        user="dabu",
        database="put your username here", #TODO
        password="put your password" #TODO
        )

def dbExecute(command):
    """Method for insert, update and other commands, which don't have a result"""
    try:
        db = getDBconnection()
        cursor = db.cursor()
        cursor.execute(command)
        db.commit()
        db.close()
    except Exception as err:
        print(err)

def dbSelect(command):
    """Method for Select-like commands, returns result of select"""
    try:
        db = getDBconnection()
        cursor = db.cursor()
        cursor.execute(command)
        result = cursor.fetchall()
        db.close()
        return result
    except Exception as err:
        print(err)


class Security():
    """Class for security matters"""
    ruleWay = "rule"
    apiWay = "api"
    rfidWay = "rfid:" #continues with uid
    timeForDisable = 30 # 30s limit for turning off security
    motionStarted = datetime.now().strftime('%Y-%m-%d %H:%M:%S') #used for finding last motion record in DB (PIR module)
    
    def __init__(self):
        self._timeOfViolation = None #time of the first security violation (there is time limit for turning off security system)
        self._violations = []
        self._alarmTimer = None #Timer for alarm, instance for killing the timer
        state = dbSelect("SELECT `houseLock` FROM `state` WHERE id = 1")
        self._secured = state[0][0]
        if self._secured:
            time = dbSelect("SELECT `dtime` FROM `securityHistory` WHERE newState = 1 order by dtime desc limit 1")
            self._timeSecured = time[0][0]
        else:
            self._timeSecured = None

    def getState(self):
        return self._secured
        
    def setState(self, newState, way):
        """Changes state of security system"""
        if newState == "on" or str(newState) == "1":
            self._timeSecured = datetime.now()
            self._secured = 1
        elif newState == "off" or str(newState) == "0":
            if(way == Security.ruleWay) and not self._secured: #can be quite often, no need to save this
                return
            elif(way == Security.ruleWay) and self._secured:
                if (datetime.now() - self._timeSecured) > timedelta(minutes = 10): #at least 10 minutes from securing (protection from accident unsecuring during user's take off)
                    self._secured = 0
                else:
                    return
            else: #not ruleway
                self._secured = 0

        if not self._secured:
            self._timeOfViolation = None
            self._timeSecured = None
            if self._alarmTimer:
                self._alarmTimer.cancel() #security disarmed, no need for alarm
                self._alarmTimer = None

        requests.get(BLYNK_API_HTTP + BLYNK_AUTH + "/update/V81?value=" + str(self._secured)) #info for rfid module
        if way != "api": #need to send the new value to blynk cloud
                blynk.virtual_write(PIN_SECURITY, self._secured)

        time = datetime.now()
        dbExecute("UPDATE `state` SET `houseLock`=" + str(self._secured) + " WHERE id=1")
        dbExecute("INSERT INTO `securityHistory`(`newState`, `dtime`, `way`) VALUES (" + str(self._secured) + ", '" + time.strftime('%Y-%m-%d %H:%M:%S') + "', '" + way + "')")

    def alarm(self):
        if not self._secured: #security was disarmed meanwhile
            print_debug("alarm not needed")
            return
        print("alaaarm! huii huiii huii")

        emailText = "Čas prvního narušení: " + self._timeOfViolation.strftime('%Y-%m-%d %H:%M:%S')
        if len(self._violations) > 0: #if there are any new violations, send email
            for violation in self._violations:
                emailText += "<br>Příčina: " + violation
            sendEmail({"value1":"NARUŠENÍ BEZPEČNOSTNÍHO SYSTÉMU","value2": emailText})
            self._violations = []

        if self._firstAlarm: #trigger rules only for the first time
            self._firstAlarm = False
            rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.securityBreak) #rules with security break as event
            for rule in rules:
                doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])            

        self._alarmTimer = Timer(60*10, security.alarm)
        self._alarmTimer.start() #every ten minutes call alarm again

    def notify(self, reason):
        if not self._secured:
            return
        print_debug("time since secure: " + str((datetime.now() - self._timeSecured)))
        if (datetime.now() - self._timeSecured) < timedelta(minutes = 5): #security starts working 5 minutes after turning it on
            return

        print_debug("security system has been notified, reason: " + reason)
        print_debug("self._timeOfViolation: " + str(self._timeOfViolation))

        self._violations.append(reason)
        if self._timeOfViolation == None: #first violation
            self._timeOfViolation = datetime.now()
            self._firstAlarm = True
            print_debug("bude nastaven alarm za 30s")
            self._alarmTimer = Timer(Security.timeForDisable, security.alarm)
            self._alarmTimer.start()



security = Security()


def print_debug(message):
    if DEBUG_INFO:
        print(message)


def getHeatingTimes():
    """Gets heating times from DB and parse them to format for heating module"""
    result = dbSelect("SELECT `heatingSettings` FROM `state` WHERE id = 1")
    if result:
        states = json.loads(result[0][0])
        times = [states[0]]
        if states[0] != states[-1]:  # change on midnight
            times.append(0)
        for i in range(1, len(states)):
            if states[i] != states[i - 1]:  # state change
                times.append(i * 15)  # each index is 15 minutes, so i*15 = minutes since midnight

        times = [len(times) - 1] + times
        return times

def sendTimeData(pin, data):
    requests.put(BLYNK_API_HTTP + BLYNK_AUTH + "/update/" + pin, json=data)
    print_debug("sent data to heating module: " + str(data))



@blynk.handle_event('write V10')  #securitySystem state
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    security.setState(value[0], Security.apiWay)


@blynk.handle_event('write V40')  #kotel is asking for data (start/stop heating)
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))
    if value[0] == "1":
        sendTimeData(PIN_KOTEL_TIMES, getHeatingTimes())

@blynk.handle_event('write V42')  #state of heating system
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    dbExecute("UPDATE `state` SET `heating`=" + str(value[0]) + " WHERE id=1")


@blynk.handle_event('write V49')  #heating module online
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    if value[0] == "1":
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + ",`lastOnline`='" + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "' WHERE id=40")
    else:
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + " WHERE id=40")



@blynk.handle_event('write V50')  #door sensor changed its state
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    security.notify("Otevřely se vchodové dveře")

    moduleID = 50
    time = datetime.now()

    #save new state to DB
    dbExecute("INSERT INTO doors(moduleID, dtime, state) values(" + str(moduleID) + ", '" + time.strftime('%Y-%m-%d %H:%M:%S') + "', " + str(value[0]) + ")")

    #automatic rules handling
    rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.door) #rules with door change as event
    for rule in rules:
        eventSpec = json.loads(rule[EVENT_SPEC_DB_INDEX])
        if eventSpec["door"] == "open" and value[0] == "1": #if event needs opened door and it did open
            doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])
        elif eventSpec["door"] == "close" and value[0] == "0":  #if event needs closed door and it did close
            doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])


@blynk.handle_event('write V59')  #door module online
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    if value[0] == "1":
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + ",`lastOnline`='" + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "' WHERE id=50")
    else:
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + " WHERE id=50")



@blynk.handle_event('write V60')  #IR LED
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    dbExecute("UPDATE `state` SET `roomLED`=" + str(value[0]) + " WHERE id=1")


@blynk.handle_event('write V61')  #temperature in room
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    moduleID = 60

    #data in last hour are just updated, but older stay saved
    time = datetime.now()
    result = dbSelect("SELECT `moduleID`, `dtime` FROM `temperatures` WHERE moduleID = " + str(moduleID) + " and dtime = '" + time.strftime('%Y-%m-%d %H:00:00') + "'") #are there data from last hour
    if result:
        dbExecute("UPDATE `temperatures` SET `temperature`=" + str(value[0]) + " WHERE moduleID=" + str(moduleID) + " and dtime='" + time.strftime('%Y-%m-%d %H:00:00') + "'")
    else:
        dbExecute("INSERT INTO `temperatures`(`moduleID`, `dtime`, `temperature`, `humidity`) VALUES (" + str(moduleID) + ", '" + time.strftime('%Y-%m-%d %H:00:00') + "', " + str(value[0]) + ", -1000)")

    # automatic rules handling
    temp = float(value[0])
    rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.temp)  # rules with temperature as event
    for rule in rules:
        eventSpec = json.loads(rule[EVENT_SPEC_DB_INDEX])
        if eventSpec["place"] == "room":  # if place is room
            print_debug("nalezeno pravidlo s teplotou a pokojem")
            if eventSpec["compare"] == "greater":  #if temp has to be greater than something
                if float(eventSpec["temp"]) < temp:  # if  temp is greater than limit
                    print_debug("vola se doaction")
                    doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])
                    print_debug("volani doaction dokonceno")
            elif eventSpec["compare"] == "less":  #if temp has to be less than something
                if float(eventSpec["temp"]) > temp:  # if  temp is less than limit
                    doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])


@blynk.handle_event('write V62')  #humidity in room
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    moduleID = 60

    #data in last hour are just updated, but older stay saved
    time = datetime.now()
    result = dbSelect("SELECT `moduleID`, `dtime` FROM `temperatures` WHERE moduleID = " + str(moduleID) + " and dtime = '" + time.strftime('%Y-%m-%d %H:00:00') + "'") #are there data from last hour
    if result:
        dbExecute("UPDATE `temperatures` SET `humidity`=" + str(value[0]) + " WHERE moduleID=" + str(moduleID) + " and dtime='" + time.strftime('%Y-%m-%d %H:00:00') + "'")
    else:
        dbExecute("INSERT INTO `temperatures`(`moduleID`, `dtime`, `humidity`, `temperature`) VALUES (" + str(moduleID) + ", '" + time.strftime('%Y-%m-%d %H:00:00') + "', " + str(value[0]) + ", -1000)")


@blynk.handle_event('write V69')  #IR LED module online/offline
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    if value[0] == "1":
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + ",`lastOnline`= '" + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "' WHERE id=60")
    else:
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + " WHERE id=60")


@blynk.handle_event('write V70')  #PIR sensor changed its state
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    moduleID = 70
    time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    if value[0] == "1":
        security.notify("Zaznamenán pohyb")
        Security.motionStarted = time
    
    if security.getState() and value[0] == "1": #save to DB only if security is on
        dbExecute("INSERT INTO motionHistory(moduleID, dtime) values(" + str(moduleID) + ", '" + time + "')")
    elif value[0] == "0": #motion ended
        dbExecute("UPDATE `motionHistory` SET `stopTime`='" + time + "' WHERE moduleID=" + str(moduleID) + " AND dtime='" + Security.motionStarted + "'")

    #automatic rules handling
    if value[0] == "1":
        rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.motion) #rules with motion detected as event
        for rule in rules:
            eventSpec = json.loads(rule[EVENT_SPEC_DB_INDEX])
            if eventSpec["place"] == "hallway": #motion detected in hallway (by this module)
                doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])


@blynk.handle_event('write V79')  #PIR module online
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    if value[0] == "1":
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + ",`lastOnline`='" + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "' WHERE id=70")
    else:
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + " WHERE id=70")


@blynk.handle_event('write V80')  #rfid module sent UID
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    result = dbSelect("SELECT `owner` FROM `rfidCards` WHERE uid = '" + value[0] + "'") #try to find the rfid tag
    if result: #exists
        if result[0][0] == None: #card has no owner -> possibly foreign card
            security.notify("Pokus o odstřežení neautorizovanou RFID kartou: " + value[0])
        else: #known authorized card
            security.setState(0, Security.rfidWay + value[0]) #rfid:uid
    else: #new card
        security.notify("Pokus o odstřežení neautorizovanou RFID kartou: " + value[0])
        dbExecute("INSERT INTO `rfidCards`(`uid`) VALUES ('" + value[0] + "')")

    

@blynk.handle_event('write V89')  #RFID module online/offline
def write_virtual_pin_handler(pin, value):
    print_debug(WRITE_EVENT_PRINT_MSG.format(pin, value))

    if value[0] == "1":
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + ",`lastOnline`= '" + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "' WHERE id=80")
    else:
        dbExecute("UPDATE `modulesState` SET `state`=" + str(value[0]) + " WHERE id=80")



def sendEmail(json):
    addr = "https://maker.ifttt.com/trigger/send_email/with/key/nrYA1UVSL7RIv0AvmrOWh6aAflo2Nyl14OiMuWm0ST9"
    print_debug("sent e-mail: " + str(requests.post(addr , json=json)))


def doAction(action, actionSpec):
    """Performs action with specifications from actionSpec"""
    specifications = json.loads(actionSpec)
    pin = ""
    value = ""
    action = str(action)

    if action == Actions.light: #lights
        if specifications["place"] == "room":
            pin = "V60"
            if specifications["light"] == "on":
                value = 4
            elif specifications["light"] == "off":
                value = 3
        requests.put(BLYNK_API_HTTP + BLYNK_AUTH + "/update/" + pin, json=[value])
        dbExecute("UPDATE `state` SET `roomLED`=" + str(value) + " WHERE id=1")

    elif action == Actions.security: #security system
        security.setState(specifications["security"], Security.ruleWay)

    elif action == Actions.heating:
        pin = "V42"
        if specifications["state"] == "time":
            value = 0
        elif specifications["state"] == "on":
            value = 4
        elif specifications["state"] == "off":
            value = 3
        requests.put(BLYNK_API_HTTP + BLYNK_AUTH + "/update/" + pin, json=[value])
        dbExecute("UPDATE `state` SET `heating`=" + str(value) + " WHERE id=1")

    elif action == Actions.email:
        sendEmail(specifications)

    else:
        print_debug("action else, need todo here something")




def updateOutdoorTempHum():
    """Periodically gets outdoor temperature and humidity from openweathermaps.org and save them to DB"""
    Timer(60*20, updateOutdoorTempHum).start()  # call the function every 20 minutes

    r = requests.get('http://api.openweathermap.org/data/2.5/weather?id=3077920&APPID=ee934055818d06145b31c4d4b6c8fc06&units=metric')
    temp = r.json()['main']['temp']
    hum = r.json()['main']['humidity']
    moduleID = 0

    # data in last hour are just updated, but older stay saved
    time = datetime.now()
    result = dbSelect("SELECT `moduleID`, `dtime` FROM `temperatures` WHERE moduleID = " + str(moduleID) + " and dtime = '" + time.strftime('%Y-%m-%d %H:00:00') + "'")  # are there data from last hour
    if result:
        dbExecute("UPDATE `temperatures` SET `temperature`=" + str(temp) + " WHERE moduleID=" + str(moduleID) + " and dtime='" + time.strftime('%Y-%m-%d %H:00:00') + "'")
    else:
        dbExecute("INSERT INTO `temperatures`(`moduleID`, `dtime`, `temperature`, `humidity`) VALUES (" + str(moduleID) + ", '" + time.strftime('%Y-%m-%d %H:00:00') + "', " + str(temp) + ", " + str(hum) + ")")

    # automatic rules handling
    rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.temp)  # rules with temperature as event
    for rule in rules:
        eventSpec = json.loads(rule[EVENT_SPEC_DB_INDEX])
        if eventSpec["place"] == "outdoor":  # if place is outdoor
            if eventSpec["compare"] == "greater":  #if temp has to be greater than something
                if float(eventSpec["temp"]) < temp:  # if outdoor temp is greater than limit
                    doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])
            elif eventSpec["compare"] == "less":  #if temp has to be less than something
                if float(eventSpec["temp"]) > temp:  # if outdoor temp is less than limit
                    doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])



# inspired by https://stackoverflow.com/questions/47641318/scapy-check-the-message-type-of-a-captured-dhcp-packet
def dhcpSniff(pkt):
    """Checks the sniffed packet and if it is DHCP request, it triggers automatic rule for newly connected device (MAC address based)"""
    if pkt.haslayer(DHCP):
        req_type = next(opt[1] for opt in pkt[DHCP].options if isinstance(opt, tuple) and opt[0] == 'message-type')

        if req_type != 3: # Message type is not request
            return

        # automatic rules handling
        rules = dbSelect("SELECT * FROM `rules` WHERE `event` = " + Events.deviceConnected)  # rules with newly connected device as event
        for rule in rules:
            eventSpec = json.loads(rule[EVENT_SPEC_DB_INDEX])
            if eventSpec["mac"] == pkt[Ether].src:  # if the mac address in rule is the same as the newly connected
                doAction(rule[ACTION_DB_INDEX], rule[ACTION_SPEC_DB_INDEX])
                print_debug("Newly connected device {} triggered an action.".format(pkt[Ether].src))


# inspired by https://stackoverflow.com/questions/47641318/scapy-check-the-message-type-of-a-captured-dhcp-packet
def scapySniff():
    """Used for sniffing DHCP requests - registers newly connected devices"""
    sniff(prn=dhcpSniff, count=0, store=0)


# code to run after rpi restart
sendTimeData(PIN_KOTEL_TIMES, getHeatingTimes())
Timer(1, updateOutdoorTempHum).start() #start after 1 sec in new thread
Timer(1, scapySniff).start() #start after 1 sec in new thread

###########################################################
# infinite loop that waits for event
###########################################################
while True:
    blynk.run()
