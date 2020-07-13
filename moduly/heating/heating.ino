/*************************************************************
  Download latest Blynk library here:
    https://github.com/blynkkk/blynk-library/releases/latest

  Blynk is a platform with iOS and Android apps to control
  Arduino, Raspberry Pi and the likes over the Internet.
  You can easily build graphic interfaces for all your
  projects by simply dragging and dropping widgets.

    Downloads, docs, tutorials: http://www.blynk.cc
    Sketch generator:           http://examples.blynk.cc
    Blynk community:            http://community.blynk.cc
    Follow us:                  http://www.fb.com/blynkapp
                                http://twitter.com/blynk_app

  Blynk library is licensed under MIT license
  This example code is in public domain.

 *************************************************************

  Blynk can provide your device with time data, like an RTC.
  Please note that the accuracy of this method is up to several seconds.

  App project setup:
    RTC widget (no pin required)
    Value Display widget on V1
    Value Display widget on V2

  WARNING :
  For this example you'll need Time keeping library:
    https://github.com/PaulStoffregen/Time

  This code is based on an example from the Time library:
    https://github.com/PaulStoffregen/Time/blob/master/examples/TimeSerial/TimeSerial.ino
 *************************************************************/

/* Comment this out to disable prints and save space */
#define BLYNK_PRINT Serial


#include <ESP8266WiFi.h>
#include <BlynkSimpleEsp8266.h>
#include <TimeLib.h>
#include <WidgetRTC.h>
#include <EEPROM.h>

char auth[] = "put your blynk authentification token here"; //TODO


#define MAX_TIMES 51 //maximum is limited by datatype "byte"

#define RELAY_PIN 5 //D1
#define INTERNAL_LED 2 //D4
#define ON_PIN 15 //D8 for "explicitly ON" button
#define OFF_PIN 13 //D7 for "explicitly OFF" button
#define TIME_PIN 12 //D6 for "control by time" button
#define VPIN_ASK_DATA V40 //virtual pin for asking for time data
#define VPIN_RECV_DATA V41 //virtual pin for receiving time data
#define VPIN_STATE V42 //virtual pin with actual state of heating (1-on/0-off controlled by times) or 3 - explicitly OFF or 4 - explicitly ON
#define VPIN_ONLINE V49 //virtual pin for checking if module is online
#define VPIN_WIFI V1 //virtual pin for getting new ssid and password
#define WIFI_MAX_LEN 30

int times[MAX_TIMES]; //times in minutes since midnight for changing state
bool values[MAX_TIMES]; //values which should be set on the time in times with the same index
byte iMax; //actual maximum index of time in times and values - if it is set to 0: stay always on state in values[0]
int actualState = 0; //shadows VPIN_STATE: actual state of heating (1-on/0-off controlled by times) or 3 - explicitly OFF or 4 - explicitly ON

BlynkTimer timer;
int timerIDsetState = NULL; //for setState is always needed max 1 timer - others needs to be deleted
bool buttonTimer = false;
WidgetRTC rtc;
WidgetBridge bridgeRpi(VPIN_ASK_DATA);
WidgetBridge bridgeState(VPIN_STATE);
WidgetBridge bridgeOnline(VPIN_ONLINE);

typedef struct{
  char ssid[WIFI_MAX_LEN];
  char pswd[WIFI_MAX_LEN];
} Wifi;

void setPins(bool value) //turns on/off relay
{
    Serial.print("zapisuji");
    if(value)
    {
      Serial.println(" 1");
      pinMode(RELAY_PIN, OUTPUT);
    }
    else
    {
      Serial.println(" 0");
      pinMode(RELAY_PIN, INPUT);
      
    }
  bridgeState.virtualWrite(VPIN_STATE, actualState);
}

void setState()
{
  if(timerIDsetState != NULL)
      timer.deleteTimer(timerIDsetState);
      
    if(actualState == 3) //controlled explicitly - OFF
    {
        setPins(false);
        return;
    }
    else if(actualState == 4) //controlled explicitly - ON
    {
        setPins(true);
        return;
    }

    //else actualState == 0 or 1 so its gonna set it right according to time

    if (iMax == 0) //stay always in one state
    {
        Serial.print("iMax = 0\n");
        actualState = values[0];
        setPins(values[0]);
        return;
    }

  int minutesSinceMidnight = hour() * 60 + minute();
  int minutesToNextChange;


  String currentTime = String(hour()) + ":" + minute() + ":" + second();
  Serial.print("nyni je" + currentTime);
  
  byte j;
  for (j = 0; j <= iMax; j++)
  {
    if (times[j] > minutesSinceMidnight)
      break;
  }
  if (minutesSinceMidnight >= times[j])
  {
    actualState = (int) values[j];
    setPins(values[j]);
    minutesToNextChange = 24 * 60 - minutesSinceMidnight;
  }
  else
  {
    actualState = (int) values[j - 1];
    setPins(values[j - 1]);
    minutesToNextChange = times[j] - minutesSinceMidnight;
  }

  timerIDsetState = timer.setTimeout(minutesToNextChange * 60 * 1000, setState);
}

BLYNK_WRITE(VPIN_RECV_DATA) //receive data
{
    Serial.print("Prijem dat ");
    Serial.print(param[0].asStr());
    Serial.print(param[1].asStr());

    iMax = param[0].asInt();
    
    if(iMax > MAX_TIMES-1)
    {
        iMax = 0;
        return;
    }

    bridgeRpi.virtualWrite(VPIN_ASK_DATA, 0); //got data

    if(iMax == 0) //all day no change
      values[0] = param[1];


    bool state = (bool) param[1].asInt(); //first state on midnight
    if(param[2] != 0) //midnight not sent
    {
      values[0] = state;
      times[0] = 0;
      iMax++;
      state = !state;

      for (int i = 0; i < iMax; i++)
      {
          values[i+1] = state;
          state = !state;
          times[i+1] = param[i+2];
          Serial.print(param[i+2].asStr());
      }
    }
    else
    {
        for (int i = 0; i < iMax; i++)
        {
            values[i] = state;
            state = !state;
            times[i] = param[i+2];
            Serial.print(param[i+2].asStr());
        }
    }
  
    for (int i = 0; i < iMax; ++i)
    {
        Serial.print(times[i]);
        Serial.print(" - val:");
        Serial.println(values[i]);
    }
    if(timerIDsetState != NULL)
      timer.deleteTimer(timerIDsetState);
    timerIDsetState = timer.setTimeout(5000, setState);
}

BLYNK_WRITE(VPIN_STATE) //receive explicit set of state
{
    actualState = param[0].asInt();
    setState();
}

void askForData()
{
  bridgeRpi.virtualWrite(VPIN_ASK_DATA, 0);
  bridgeRpi.virtualWrite(VPIN_ASK_DATA, 1);
}

void turnOffTimerAndLED()
{
  digitalWrite(INTERNAL_LED, 1);
  buttonTimer = false;
}

void onButtonCheck()
{
    if(digitalRead(ON_PIN) == 0) //if button is not pushed anymore
    {
      turnOffTimerAndLED();
      return;
    }
    actualState = 4;
    setState();
    digitalWrite(INTERNAL_LED, 0);
    timer.setTimeout(500, turnOffTimerAndLED);
}
void offButtonCheck()
{
    if(digitalRead(OFF_PIN) == 0) //if button is not pushed anymore
    {
      turnOffTimerAndLED();
      return;
    }
    actualState = 3;
    setState();
    digitalWrite(INTERNAL_LED, 0);
    timer.setTimeout(500, turnOffTimerAndLED);
}
void timeButtonCheck()
{
    if(digitalRead(TIME_PIN) == 0) //if button is not pushed anymore
    {
      turnOffTimerAndLED();
      return;
    }
    actualState = 0;
    setState();
    digitalWrite(INTERNAL_LED, 0);
    timer.setTimeout(500, turnOffTimerAndLED);
}

void ICACHE_RAM_ATTR onButtonClick()
{
  if(buttonTimer == false)
  {
    buttonTimer = true;
    Serial.println("onButton");
    timer.setTimeout(500, onButtonCheck);
  }
}
void ICACHE_RAM_ATTR offButtonClick()
{
    if(buttonTimer == false)
    {
      buttonTimer = true;
      Serial.println("offButton");
      timer.setTimeout(500, offButtonCheck);
    }
}
void ICACHE_RAM_ATTR timeButtonClick()
{
  if(buttonTimer == false)
  {
    buttonTimer = true;
    Serial.println("timeButton");
    timer.setTimeout(500, timeButtonCheck);
  }
}

BLYNK_WRITE(VPIN_ONLINE) //receive request
{
  bridgeOnline.virtualWrite(VPIN_ONLINE, 1); //confirm this module is online
}

void checkConnection() //if Blynk connection stops working, restarts module -> connects to new wifi
{
  if (Blynk.connected())
  {
    Serial.println("Puvodni Wifi stale pripojena");
    return;
  }
  Serial.println("Puvodni pripojeni prestalo fungovat, restartuji modul.");
  ESP.restart();
}

BLYNK_WRITE(VPIN_WIFI) //receive new ssid and password
{
    Wifi newWifi;
    strncpy(newWifi.ssid, param[0].asStr(), WIFI_MAX_LEN);
    strncpy(newWifi.pswd, param[1].asStr(), WIFI_MAX_LEN);

    EEPROM.begin(512);
    EEPROM.put(0, newWifi);
    if(EEPROM.commit())
        Serial.println("ulozeny nove udaje k wifi");
    else
        Serial.println("nepovedlo se ulozit nove udaje k wifi!");

    EEPROM.end();

    timer.setInterval(1000*30, checkConnection); //every 30s check connection
}

BLYNK_CONNECTED()
{
  bridgeRpi.setAuthToken(auth);
  bridgeState.setAuthToken(auth);
  bridgeOnline.setAuthToken(auth);
}

void setup()
{
  // Debug console
  Serial.begin(9600);

  pinMode(RELAY_PIN, INPUT);
  
  Wifi wifi;
  EEPROM.begin(512);
  EEPROM.get(0, wifi); //load ssid and password from permanent memory on address 0
  EEPROM.end();

  Blynk.begin(auth, wifi.ssid, wifi.pswd);
  // You can also specify server:
  //Blynk.begin(auth, ssid, pass, "blynk-cloud.com", 80);
  //Blynk.begin(auth, ssid, pass, IPAddress(192,168,1,100), 8080);

  // Begin synchronizing time
  rtc.begin();

  pinMode(ON_PIN, INPUT);
  pinMode(OFF_PIN, INPUT);
  pinMode(TIME_PIN, INPUT);
  pinMode(INTERNAL_LED, OUTPUT);
  digitalWrite(INTERNAL_LED, 1); //turn off LED

  attachInterrupt(digitalPinToInterrupt(ON_PIN), onButtonClick, RISING);
  attachInterrupt(digitalPinToInterrupt(OFF_PIN), offButtonClick, RISING);
  attachInterrupt(digitalPinToInterrupt(TIME_PIN), timeButtonClick, RISING);

  // Other Time library functions can be used, like:
  //   timeStatus(), setSyncInterval(interval)...
  // Read more: http://www.pjrc.com/teensy/td_libs_Time.html
  timer.setTimeout(5000,askForData);
}

void loop()
{
  Blynk.run();
  timer.run();
}
