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

  This example shows how value can be pushed from Arduino to
  the Blynk App.

  WARNING :
  For this example you'll need Adafruit DHT sensor libraries:
    https://github.com/adafruit/Adafruit_Sensor
    https://github.com/adafruit/DHT-sensor-library

  App project setup:
    Value Display widget attached to V5
    Value Display widget attached to V6
 *************************************************************/

/* Comment this out to disable prints and save space */
#define BLYNK_PRINT Serial

//define signals for IR (specific LED string)
#define IR1 0xFF01FE
#define IR2 0xFF817E
#define IR3 0xFF41BE
#define IR4 0xFFC13E
#define IR5 0xFF21DE
#define IR6 0xFFA15E
#define IR7 0xFF619E
#define IR8 0xFFE11E
#define IR9 0xFF11EE  
#define IR10 0xFF916E
#define IR11 0xFF51AE
#define IR12 0xFFD12E
#define IR13 0xFF31CE
#define IR14 0xFFB14E
#define IR15 0xFF718E
#define IR16 0xFFF10E
#define IR17 0xFF09F6
#define IR18 0xFF8976
#define IR19 0xFF49B6
#define IR20 0xFFC936
#define IR21 0xFF29D6
#define IR22 0xFFA956
#define IR23 0xFF6996
#define IR24 0xFFE916

#include <ESP8266WiFi.h>
#include <BlynkSimpleEsp8266.h>
#include <DHT.h>
#include <Arduino.h>
#include <IRremoteESP8266.h>
#include <IRsend.h>
#include <EEPROM.h>

char auth[] = "put your blynk authentification token here"; //TODO

#define DHTPIN 5 //pin D1
#define VPIN_IR V60 //virtual pin for IR (just receiving)
#define VPIN_TEMP V61 //virtual pin for temperature (just sending)
#define VPIN_HUM V62 //virtual pin for humidity (just sending)
#define VPIN_ONLINE V69 //virtual pin for checking if module is online
#define VPIN_WIFI V1 //virtual pin for getting new ssid and password
#define WIFI_MAX_LEN 30

#define DHTTYPE DHT11     // DHT 11

float last_temp = 0;
float last_hum = 0;

const uint16_t IR_led = 4; //pin D2
IRsend irsend(IR_led);  // Set the GPIO to be used to sending the message.

DHT dht(DHTPIN, DHTTYPE);
BlynkTimer timer;
WidgetBridge bridgeTemp(VPIN_TEMP);
WidgetBridge bridgeHum(VPIN_HUM);
WidgetBridge bridgeOnline(VPIN_ONLINE);

typedef struct{
  char ssid[WIFI_MAX_LEN];
  char pswd[WIFI_MAX_LEN];
} Wifi;


void sendSensor()
{
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  
  if (isnan(h) || isnan(t)) {
    if(isnan(t))
      t = -1000;
    if(isnan(h))
      h = -1000;
  }

  if(last_temp - t > 0.25 || t - last_temp > 0.25 ||  last_hum - h > 2 || h - last_hum > 2) //sending only if temperature or humidity has changed
  {
    bridgeTemp.virtualWrite(VPIN_TEMP, t);
    bridgeHum.virtualWrite(VPIN_HUM, h);
      
    last_temp = t; //save last sent temperature and humidity
    last_hum = h;
  }
}

BLYNK_WRITE(VPIN_IR) //receive data
{
  Serial.print("Prijem dat ");
  Serial.print(param.asStr());
  Serial.print("\r\n");
  int buttonReceived = param.asInt();

  uint32_t message;

  if(buttonReceived == 1) //up
  {
    message = IR1;
  }
  else if(buttonReceived == 2) //down
  {
    message = IR2;
  }
  else if(buttonReceived == 3) //off
  {
    message = IR3;
  }
  else if(buttonReceived == 4) //on
  {
    message = IR4;
  }
  else if(buttonReceived == 5) //red
  {
    message = IR5;
  }
  else if(buttonReceived == 6) //green
  {
    message = IR6;
  }
  else if(buttonReceived == 7) //blue
  {
    message = IR7;
  }
  else if(buttonReceived == 8) //white
  {
    message = IR8;
  }
  else if(buttonReceived == 9) //a reddish color
  {
    message = IR9;
  }
  else if(buttonReceived == 10) //a bluish green
  {
    message = IR10;
  }
  else if(buttonReceived == 11) //a purple color
  {
    message = IR11;
  }
  else if(buttonReceived == 12) //flash
  {
    message = IR12;
  }
  else if(buttonReceived == 13) //yellow
  {
    message = IR13;
  }
  else if(buttonReceived == 14) //bluish
  {
    message = IR14;
  }
  else if(buttonReceived == 15) //other purple
  {
    message = IR15;
  }
  else if(buttonReceived == 16) //strobe
  {
    message = IR16;
  }
  else if(buttonReceived == 17) //light green
  {
    message = IR17;
  }
  else if(buttonReceived == 18) //light blue
  {
    message = IR18;
  }
  else if(buttonReceived == 19) //purple again
  {
    message = IR19;
  }
  else if(buttonReceived == 20) //fade
  {
    message = IR20;
  }
  else if(buttonReceived == 21) //another light green
  {
    message = IR21;
  }
  else if(buttonReceived == 22) //more blue
  {
    message = IR22;
  }
  else if(buttonReceived == 23) //red-purple
  {
    message = IR23;
  }
  else if(buttonReceived == 24) //smooth
  {
    message = IR24;
  }
  else
  {
    message = 0;
  }

  if(message)
  {
    irsend.sendNEC(message);  
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
  bridgeTemp.setAuthToken(auth);
  bridgeHum.setAuthToken(auth);
  bridgeOnline.setAuthToken(auth);
}

void setup()
{
  // Debug console
  Serial.begin(9600);
  irsend.begin();

  Wifi wifi;
  EEPROM.begin(512);
  EEPROM.get(0, wifi); //load ssid and password from permanent memory on address 0
  EEPROM.end();

  Blynk.begin(auth, wifi.ssid, wifi.pswd);
  // You can also specify server:
  //Blynk.begin(auth, ssid, pass, "blynk-cloud.com", 80);
  //Blynk.begin(auth, ssid, pass, IPAddress(192,168,1,100), 8080);

  dht.begin();

  // Setup a function to be called every 90s
  timer.setInterval(90000L, sendSensor);
}

void loop()
{
  Blynk.run();
  timer.run();
}
