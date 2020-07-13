#include <EEPROM.h>

typedef struct{
  char ssid[30];
  char pswd[30];
} Wifi;

void setup() {
  // put your setup code here, to run once:
    Serial.begin(9600);
    
    Wifi newWifi;
    strncpy(newWifi.ssid, "put your wifi ssid here", 30); //TODO
    strncpy(newWifi.pswd, "put your wifi password here", 30); //TODO

    EEPROM.begin(512);
    EEPROM.put(0, newWifi);
    if(EEPROM.commit())
        Serial.println("OK");
    else
        Serial.println("FAIL");
}

void loop() {
  // put your main code here, to run repeatedly:

}
