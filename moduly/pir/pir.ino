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

  Simple push notification example

  App project setup:
    Push widget

  Connect a button to pin 2 and GND...
  Pressing this button will also push a message! ;)
 *************************************************************/

/* Comment this out to disable prints and save space */
//#define BLYNK_PRINT Serial


#include <ESP8266WiFi.h>
#include <EEPROM.h>
#include <BlynkSimpleEsp8266.h>

#define VPIN_MOTION V70 //virtual pin for sending movement info
#define VPIN_ONLINE V79 //virtual pin for checking if module is online
#define VPIN_WIFI V1 //virtual pin for getting new ssid and password
#define PIN_PIR 12 //D6 - pin for reading sensor state
#define WIFI_MAX_LEN 30

// You should get Auth Token in the Blynk App.
// Go to the Project Settings (nut icon).
char auth[] = "put your blynk authentification token here"; //TODO


BlynkTimer timer;

WidgetBridge bridgePIR(VPIN_MOTION);
WidgetBridge bridgeOnline(VPIN_ONLINE);


typedef struct{
  char ssid[WIFI_MAX_LEN];
  char pswd[WIFI_MAX_LEN];
} Wifi;

void ICACHE_RAM_ATTR notifyOnMotion()
{
  int state = digitalRead(PIN_PIR);
  bridgePIR.virtualWrite(VPIN_MOTION, state);
  Serial.println(state);
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
  bridgePIR.setAuthToken(auth);
  bridgeOnline.setAuthToken(auth);
}

void setup()
{
  // Debug console
  Serial.begin(9600);

  Wifi wifi;
  EEPROM.begin(512);
  EEPROM.get(0, wifi); //load ssid and password from permanent memory on address 0
  EEPROM.end();

  Blynk.begin(auth, wifi.ssid, wifi.pswd);
  // You can also specify server:
  //Blynk.begin(auth, ssid, pass, "blynk-cloud.com", 80);
  //Blynk.begin(auth, ssid, pass, IPAddress(192,168,1,100), 8080);

  // Setup notification button on pin 0
  pinMode(PIN_PIR, INPUT);

  attachInterrupt(digitalPinToInterrupt(PIN_PIR), notifyOnMotion, CHANGE);
}

void loop()
{
  Blynk.run();
  timer.run();
}
