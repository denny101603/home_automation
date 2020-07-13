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
#define BLYNK_PRINT Serial


#include <ESP8266WiFi.h>
#include <EEPROM.h>
#include <BlynkSimpleEsp8266.h>
#include <SPI.h>
#include <MFRC522.h>

#define VPIN_RFID V80 //virtual pin for sending UID
#define VPIN_GET_STATE V81 //virtual pin only for getting security state
#define VPIN_ONLINE V89 //virtual pin for checking if module is online
#define VPIN_WIFI V1 //virtual pin for getting new ssid and password
#define VPIN_SECURITY V10 //virtual pin with security system status (0/1)
#define PIN_BUTTON 5 //D1 button for turning on security system
#define PIN_INTERNAL_LED 2 //D4 LED on for security on, LED off for security off
#define PIN_SS 15 //slave select (SPI)
#define PIN_RST 4 //for RFID reader
#define WIFI_MAX_LEN 30

// You should get Auth Token in the Blynk App.
// Go to the Project Settings (nut icon).
char auth[] = "put your blynk authentification token here"; //TODO


BlynkTimer timer;

MFRC522 rfid(PIN_SS, PIN_RST);

WidgetBridge bridgeRFID(VPIN_RFID);
WidgetBridge bridgeSecurity(VPIN_SECURITY);
WidgetBridge bridgeOnline(VPIN_ONLINE);


typedef struct{
  char ssid[WIFI_MAX_LEN];
  char pswd[WIFI_MAX_LEN];
} Wifi;

//function inspired by example ReadNUID from MFRC522.h library. Online: https://github.com/miguelbalboa/rfid/blob/master/examples/ReadNUID/ReadNUID.ino
void readRFID()
{
  // Reset the loop if no new card present on the sensor/reader. This saves the entire process when idle.
  if ( ! rfid.PICC_IsNewCardPresent())
    return;

  // Verify if the NUID has been readed
  if ( ! rfid.PICC_ReadCardSerial())
    return;

 
  Serial.println(F("The NUID tag is:"));
  Serial.print(F("In hex: "));
  Serial.println(UIDToString(rfid.uid.uidByte, rfid.uid.size));
  bridgeRFID.virtualWrite(VPIN_RFID, UIDToString(rfid.uid.uidByte, rfid.uid.size));

  // Halt PICC
  rfid.PICC_HaltA();

  // Stop encryption on PCD
  rfid.PCD_StopCrypto1();
}


String UIDToString(byte *buffer, byte bufferSize) 
{
  String res = String(buffer[0]);
  for (byte i = 1; i < bufferSize; i++)
  {
    res += " " + String(buffer[i]);
  }
  return res;
}

void ICACHE_RAM_ATTR onButtonClick()
{
    Serial.println("btn");
    digitalWrite(PIN_INTERNAL_LED, 0);
    bridgeSecurity.virtualWrite(VPIN_SECURITY, 1);
}

BLYNK_WRITE(VPIN_GET_STATE)
{
  digitalWrite(PIN_INTERNAL_LED, !param[0].asInt());
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
  bridgeRFID.setAuthToken(auth);
  bridgeOnline.setAuthToken(auth);
  bridgeSecurity.setAuthToken(auth);
}

void setup()
{
  // Debug console
  Serial.begin(9600);
  SPI.begin(); // Init SPI bus
  rfid.PCD_Init(); // Init MFRC522 

  pinMode(PIN_INTERNAL_LED, OUTPUT);
  digitalWrite(PIN_INTERNAL_LED, 1);
  pinMode(PIN_BUTTON, INPUT);
  attachInterrupt(digitalPinToInterrupt(PIN_BUTTON), onButtonClick, RISING);
  
  Wifi wifi;
  EEPROM.begin(512);
  EEPROM.get(0, wifi); //load ssid and password from permanent memory on address 0
  EEPROM.end();

  Blynk.begin(auth, wifi.ssid, wifi.pswd);
  // You can also specify server:
  //Blynk.begin(auth, ssid, pass, "blynk-cloud.com", 80);
  //Blynk.begin(auth, ssid, pass, IPAddress(192,168,1,100), 8080);
}

void loop()
{
  Blynk.run();
  timer.run();
  readRFID();
}
