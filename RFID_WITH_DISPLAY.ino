#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>

// 📌 RFID Definitions
#define SS_PIN D2
#define RST_PIN D1
MFRC522 mfrc522(SS_PIN, RST_PIN);

// 📌 WiFi Settings
const char *ssid = "IOT";           // Change to your WiFi SSID
const char *password = "password";  // Change to your WiFi Password
const char *device_token = "3160d8a891dc6e6a";  // Unique device token

// 📌 I2C LCD (Make sure 0x27 is correct, use I2C scanner if needed)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// 📌 Time Variables
unsigned long previousMillis = 0;
String OldCardID = "";

// 📌 Function Prototypes
void SendCardID(String Card_uid);
void connectToWiFi();
void scanI2C();

void setup() {
  Serial.begin(115200);

  // 📌 Initialize I2C and LCD
  Wire.begin(D4, D3);  // ESP8266 I2C pins
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Scanning I2C...");
  
  // 📌 Check I2C Devices
  scanI2C();
  delay(2000);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SCAN YOUR CARD");

  // 📌 Initialize WiFi
  WiFi.mode(WIFI_STA);
  connectToWiFi();

  // 📌 Initialize RFID
  SPI.begin();
  mfrc522.PCD_Init();
}

void loop() {
  // 📌 Reconnect WiFi if disconnected
  if (!WiFi.isConnected()) {
    connectToWiFi();
  }

  // 📌 Reset OldCardID every 15 seconds to prevent duplicate readings
  if (millis() - previousMillis >= 15000) {
    previousMillis = millis();
    OldCardID = "";
  }

  delay(50);

  // 📌 Check if a new RFID card is present
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // 📌 Read Card UID
  String CardID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    CardID += String(mfrc522.uid.uidByte[i], HEX); // Convert UID to HEX format
  }

  CardID.toUpperCase();  // Standardize UID format

  // 📌 Avoid duplicate readings
  if (CardID == OldCardID) {
    return;
  } else {
    OldCardID = CardID;
  }

  // 📌 Display Card ID on LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("CARD ID:");
  lcd.setCursor(0, 1);
  lcd.print(CardID);

  // 📌 Send Card ID to Server
  SendCardID(CardID);
  delay(2000);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SCAN YOUR CARD");
}

void SendCardID(String Card_uid) {
  Serial.println("Sending the Card ID");
  if (WiFi.isConnected()) {
    HTTPClient http;
    WiFiClient client;
    String getData = "?card_uid=" + String(Card_uid) + "&device_token=" + String(device_token);
    String Link = "http://192.168.230.167/rfidattendance/getdata.php" + getData;
    http.begin(client, Link);

    int httpCode = http.GET();
    String payload = http.getString();

    Serial.println(httpCode);
    Serial.println(Card_uid);
    Serial.println(payload);

    // 📌 Update LCD based on response
    lcd.clear();
    lcd.setCursor(0, 0);
    if (httpCode == 200) {
      if (payload.substring(0, 5) == "login") {
        String user_name = payload.substring(5);
        lcd.print("Welcome,");
        lcd.setCursor(0, 1);
        lcd.print(user_name);
      } else if (payload.substring(0, 6) == "logout") {
        String user_name = payload.substring(6);
        lcd.print("Goodbye,");
        lcd.setCursor(0, 1);
        lcd.print(user_name);
      } else if (payload == "succesful") {
        lcd.print("Access Granted!");
      } else if (payload == "available") {
        lcd.print("Card Available!");
      }
    } else {
      lcd.print("Server Error");
    }
    
    delay(2000);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SCAN YOUR CARD");
    http.end();
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(1000);
  WiFi.mode(WIFI_STA);
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi...");

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 20) {  // Timeout after ~10 seconds
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nConnected");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    delay(2000);
  } else {
    Serial.println("\nWiFi Connection Failed!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Failed!");
  }
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SCAN YOUR CARD");
}

// 📌 I2C Scanner Function to check LCD Address
void scanI2C() {
  byte error, address;
  int nDevices = 0;
  
  Serial.println("Scanning I2C devices...");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("I2C Scanning...");

  for (address = 1; address < 127; address++) {
    Wire.beginTransmission(address);
    error = Wire.endTransmission();
    
    if (error == 0) {
      Serial.print("I2C device found at 0x");
      Serial.println(address, HEX);
      nDevices++;
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("LCD Found: 0x");
      lcd.print(address, HEX);
      delay(2000);
      return;
    }
  }
  
  if (nDevices == 0) {
    Serial.println("No I2C devices found!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("LCD NOT Found!");
  }
  delay(2000);
}
