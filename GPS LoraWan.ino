# define USE_GPS 1
# include "LoRaWan.h"
# ifdef USE_GPS
# include "TinyGPS++.h"
#include <SPI.h>
#include <SD.h>
TinyGPSPlus gps;
# endif
#include <avr/dtostrf.h>

#include <Wire.h>
#include "rgb_lcd.h"


rgb_lcd lcd;

/*
  SD card connections:
 ** MOSI - pin 11
 ** MISO - pin 12
 ** CLK - pin 13
 ** CS - pin 4
 */

int chipSelect = 4;

char buffer[256];
static char dtostrfbuffer[20];
bool loggin = false;


void setup(void)
{
    if (loggin)
    {
        SerialUSB.begin(115200);

        SerialUSB.print("age: ");
    }

    pinMode(LED_BUILTIN, OUTPUT);
    pinMode(2, OUTPUT);
    pinMode(3, OUTPUT);



    lcd.begin(16, 2);


    // Print a message to the LCD.


    if (!SD.begin(chipSelect))
    {
        if (loggin)
        {
            SerialUSB.println("Card failed, or not present");
        }
        lcd.setCursor(0, 0);
        lcd.print("SD Error            ");


        // don't do anything more:
        while (1);
    }
    lcd.setCursor(0, 0);
    lcd.print("starting...         ");

    // while(!SerialUSB);
    lora.init();
    lora.setDeviceReset();
    memset(buffer, 0, 256);
    lora.getVersion(buffer, 256, 1);
    //SerialUSB.print(buffer);
    memset(buffer, 0, 256);
    lora.getId(buffer, 256, 1);
    //SerialUSB.print(buffer);
    lora.setKey("xxx", "xxx", "xxx");
    lora.setDeciveMode(LWABP);
    lora.setDataRate(DR0, EU868);
    lora.setChannel(0, 868.1);
    lora.setChannel(1, 868.3);
    lora.setChannel(2, 868.5);
    lora.setReceiceWindowFirst(0, 868.1);
    lora.setReceiceWindowSecond(869.5, DR3);
    lora.setDutyCycle(false);
    lora.setJoinDutyCycle(false);
    lora.setPower(14);
    //digitalWrite(2, HIGH);
    digitalWrite(3, HIGH);
    lcd.setCursor(0, 0);
    lcd.print("Lora OK!   ");
    char c;
# ifdef USE_GPS
    bool locked;
# endif
# ifdef USE_GPS
    Serial.begin(9600); // open the GPS
    locked = false;


    while (true)
    {
        //  SerialUSB.println("!gps.location.isValid()");
        while (Serial.available() > 0)
        {
            //  SerialUSB.println("Serial.available");
            if (gps.encode(c = Serial.read()))
            {
                // SerialUSB.println(" displayInfo()");
                displayInfo();
                delay(1000);
                // SerialUSB.println(gps.location.isValid());
                if (gps.location.isValid())
                {
                    //            locked = true;
                    //break;
                    lcd.setCursor(0, 1);
                    lcd.print("GPS OK!");
                }
            }
            //    SerialUSB.print(c);
        }
        c = 0;
        //  SerialUSB.println("no serial");
        //      if (locked)
        //        break;
    }
# endif
}


void displayInfo()
{
    bool result = false;
    // result = lora.transferPacket("1", 10);
    uint32_t ag = gps.location.age();
    if (loggin)
    {
        SerialUSB.print("age: ");
        SerialUSB.println(ag);
        SerialUSB.print("sat: ");
        SerialUSB.print(gps.satellites.value());
        SerialUSB.println();
    }
    if (gps.location.isValid() && ag < 1000)
    {
        digitalWrite(LED_BUILTIN, HIGH);

        //digitalWrite(3, LOW);
        char latBuffer[20];
        char longBuffer[20];
        char kmhBuffer[20];
        char hoeheBuffer[20];
        char timeHBuffer[20];
        char timeMBuffer[20];
        char timeSBuffer[20];
        char timeMOBuffer[20];
        char timeDBuffer[20];
        char timeYBuffer[20];
        char cntBuffer[20];
        char fullBuffer[100];
        static char dtostrfbuffer[20];
        float latitude = gps.location.lat();
        float longitude = gps.location.lng();
        dtostrf(latitude, 0, 4, latBuffer);
        dtostrf(longitude, 0, 4, longBuffer);

        dtostrf(gps.date.month(), 0, 0, timeMOBuffer);
        dtostrf(gps.date.day(), 0, 0, timeDBuffer);
        dtostrf(gps.date.year(), 0, 0, timeYBuffer);

        dtostrf(gps.time.hour(), 0, 0, timeHBuffer);
        dtostrf(gps.time.minute(), 0, 0, timeMBuffer);
        dtostrf(gps.time.second(), 0, 0, timeSBuffer);
        String fc = String(gps.date.year()) + String(gps.date.month()) + String(gps.date.day()) + String(gps.time.hour()) + String(gps.time.minute()) + String(gps.time.second());

        fc.toCharArray(cntBuffer, 20);

        float kmh = 0;
        if (loggin)
        {
            kmh = gps.speed.kmph();
        }
        float hoehe = 0;

        if (loggin)
        {
            hoehe = gps.altitude.meters();
        }
        dtostrf(kmh, 0, 4, kmhBuffer);
        dtostrf(hoehe, 0, 4, hoeheBuffer);
        sprintf_P(fullBuffer, PSTR("%s,%s|%s|%s|%s"), latBuffer, longBuffer, kmhBuffer, hoeheBuffer, cntBuffer);
        //sprintf_P(fullBuffer, PSTR("%s,%s|%s|%s|%s.%s.%s %s:%s:%s|%s"), latBuffer, longBuffer, kmhBuffer, hoeheBuffer, timeDBuffer, timeMOBuffer, timeYBuffer, timeHBuffer, timeMBuffer, timeSBuffer, cntBuffer);
        result = lora.transferPacket(fullBuffer);
        digitalWrite(LED_BUILTIN, LOW);
        if (loggin)
        {
            SerialUSB.print(gps.location.lat(), 6);
            SerialUSB.print(F(","));
            SerialUSB.print(gps.location.lng(), 6);
            SerialUSB.print(F("|"));
            SerialUSB.print(kmh, 6);
            SerialUSB.print(F("|"));
            SerialUSB.print(hoehe, 6);
        }
        lcd.setCursor(0, 1);
        lcd.print("GPS OK!           ");
        digitalWrite(2, HIGH);




        File dataFile = SD.open("gpslog.txt", FILE_WRITE);
        String fcn = String(gps.date.year()) + "-" + String(gps.date.month()) + "-" + String(gps.date.day()) + " "  + String(gps.time.hour()) + ":" + String(gps.time.minute()) + ":" + String(gps.time.second());

        // if the file is available, write to it:
        if (dataFile)
        {
            dataFile.print(fullBuffer);
            dataFile.print("|");
            dataFile.print(result);
            dataFile.print("|");
            dataFile.println(fcn);
            dataFile.close();
            // print to the SerialUSB port too:
        }
        else
        {
            if (loggin)
            {
                //SerialUSB.println("error 0 opening gpslog.txt");

            }
        }

    }
    else if (ag < 40000)
    {
        if (loggin)
        {
            SerialUSB.print(F("nicht neu"));
        }

        lcd.setCursor(0, 1);
        lcd.print("GPS OK! n. neu");


        digitalWrite(2, HIGH);
    }
    else
    {

        lcd.setCursor(0, 1);
        lcd.print("XX GPS KO! XX ");

        digitalWrite(2, LOW);
        digitalWrite(LED_BUILTIN, HIGH);
        // lora.transferPacket("no gps");
        digitalWrite(LED_BUILTIN, LOW);
        if (loggin)
        {
            SerialUSB.print(F("INVALID"));
        }

        //  bool result2 = lora.transferPacket("1", 10);
    }
    if (loggin)
    {
        SerialUSB.print(F("  Date/Time: "));
        if (gps.date.isValid())
        {
            SerialUSB.print(gps.date.month());
            SerialUSB.print(F("/"));
            SerialUSB.print(gps.date.day());
            SerialUSB.print(F("/"));
            SerialUSB.print(gps.date.year());
        }
        else
        {
            SerialUSB.print(F("INVALID"));
        }
        SerialUSB.print(F(" "));
        if (gps.time.isValid())
        {
            if (gps.time.hour() < 10) SerialUSB.print(F("0"));
            SerialUSB.print(gps.time.hour());
            SerialUSB.print(F(":"));
            if (gps.time.minute() < 10) SerialUSB.print(F("0"));
            SerialUSB.print(gps.time.minute());
            SerialUSB.print(F(":"));
            if (gps.time.second() < 10) SerialUSB.print(F("0"));
            SerialUSB.print(gps.time.second());
            SerialUSB.print(F("."));
            if (gps.time.centisecond() < 10) SerialUSB.print(F("0"));
            SerialUSB.print(gps.time.centisecond());
        }
        else
        {
            SerialUSB.print(F("INVALID"));
        }
        SerialUSB.println();
    }
}
