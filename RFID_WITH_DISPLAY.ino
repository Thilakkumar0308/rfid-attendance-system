#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// RFID Definitions
#define SS_PIN  D2
#define RST_PIN D1
MFRC522 mfrc522(SS_PIN, RST_PIN);

// WiFi Settings
const char *ssid = "SIVA'S BSNL FIBER";
const char *password = "tHILAK@3210";
const char* device_token = "2cda5a06bd528205";

// Time and Date Variables
int hh;
int mm;
int ss;
String t;
String d;
String date;
String date_time;
String payload;
String arr_days[] = {"SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY"};

// LCD Display Definitions
LiquidCrystal_I2C lcd(0x27, 16, 2); // Address 0x27, 16 columns, 2 rows

// Previous millis for time tracking
unsigned long previousMillis = 0;

// Old card ID for avoiding multiple readings
String OldCardID = "";

// Function prototypes
void SendCardID(String Card_uid);
void connectToWiFi();

void setup() {
  Serial.begin(115200);

  // Initialize WiFi
  WiFi.mode(WIFI_STA);
  connectToWiFi();

  // Initialize LCD display
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SCAN YOUR CARD");

  // Initialize RFID
  SPI.begin();
  mfrc522.PCD_Init();
}

void loop() {
  // Check if RFID card is detected
  if (!WiFi.isConnected()) {
    connectToWiFi();
  }

  if (millis() - previousMillis >= 15000) {
    previousMillis = millis();
    OldCardID = "";
  }
  delay(50);

  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  String CardID ="";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    CardID += mfrc522.uid.uidByte[i];
  }

  if (CardID == OldCardID) {
    return;
  }
  else {
    OldCardID = CardID;
  }

  SendCardID(CardID);
  delay(1000);
}

void SendCardID(String Card_uid) {
  Serial.println("Sending the Card ID");
  if(WiFi.isConnected()){
    HTTPClient http;    
    WiFiClient client;
    String getData = "?card_uid=" + String(Card_uid) + "&device_token=" + String(device_token); 
    String Link = "http://192.168.1.11/rfidattendance/getdata.php" + getData;
    http.begin(client, Link); 
    
    int httpCode = http.GET();   
    String payload = http.getString();    

    Serial.println(httpCode);   
    Serial.println(Card_uid);     
    Serial.println(payload);    

    if (httpCode == 200) {
      if (payload.substring(0, 5) == "login") {
        String user_name = payload.substring(5);
      }
      else if (payload.substring(0, 6) == "logout") {
        String user_name = payload.substring(6);
      }
      else if (payload == "succesful") {
      }
      else if (payload == "available") {
      }
      delay(100);
      http.end();  
    }
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_OFF);        
  delay(1000);
  WiFi.mode(WIFI_STA);
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("Connected");

  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  delay(1000);
}
