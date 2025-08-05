#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Adafruit_GFX.h>
#include <Adafruit_PCD8544.h>

const String ssids[2] = {"SiAmin","X28P-2.4G-87B0AA"};
const String passwords[2] = {"1234567890","F450D7ED"};


String numbers[130]; 

#define WIFI_LED 5     // GPIO5
#define Call_LED 4   // GPIO4

#define pin_sclk 13
#define pin_din 14
#define pin_dc 2
#define pin_ce 15
#define pin_rst 0

Adafruit_PCD8544 display = 
Adafruit_PCD8544(pin_sclk,pin_din,pin_dc,pin_ce,pin_rst);


const int maxLines = 6;
const int lineHeight = 8;
String lines[maxLines];

void showMessage(String msg, bool enter = true){
  display.clearDisplay();
  if(enter){
    display.println(msg);
  }else{
    display.print(msg);
  }    
  display.display();
  delay(500);
}

void setup() {
  Serial.begin(9600);
  display.begin();

  display.setContrast(50);

  display.clearDisplay();
  display.display();

  showMessage("Setup device...");

  pinMode(WIFI_LED, OUTPUT);
  pinMode(Call_LED, OUTPUT);

  digitalWrite(WIFI_LED, HIGH);
  digitalWrite(Call_LED, HIGH);
  delay(2000);
  digitalWrite(WIFI_LED, LOW);
  digitalWrite(Call_LED, LOW);

  showMessage("Device setuped");
  delay(2000);
}

void conncetionWifi(){
  while(!managmentWifi()){
    delay(10000);
  }
}

bool managmentWifi(){
  for(int i=0; i<2; i++){
    if(connectToWiFi(ssids[i],passwords[i])){
      return true;
    }
  }
  return false;
}

bool connectToWiFi(String ssid,String password) {
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while(WiFi.status() != WL_CONNECTED && attempts < 20) {
    digitalWrite(WIFI_LED, !digitalRead(WIFI_LED));
    delay(250);
    attempts++;
  }
  if(WiFi.status() == WL_CONNECTED) {    
    showMessage("Connected " + ssid+"\nIP: "+WiFi.localIP().toString());
    digitalWrite(WIFI_LED, HIGH);    
    return true;
  } else {
    showMessage("Failed"+ ssid);
    digitalWrite(WIFI_LED, LOW);
    return false;
  }
}

long getTimeMiliSecound(float hour){
    int sec = hour * 60;
    return sec * 60 * 1000;
}

void loop() {
  static unsigned long lastRequestTime = 0;
  static bool firstRun = true;

  if (millis() > getTimeMiliSecound(24)) { //after 24 hour ESP has been restart
    ESP.restart();
    showMessage("Restart ESP 24");
  }

  if(WiFi.status() != WL_CONNECTED) {
    showMessage("Connection WiFi");
    conncetionWifi();
  } else {    
    digitalWrite(WIFI_LED, HIGH);
    // run code for first time after that run more than 1 hour also when milis is overflowed this code can be ran
    if(firstRun || millis() - lastRequestTime > getTimeMiliSecound(0.25) || millis() < lastRequestTime) {    
      showMessage("Start tasks");
      while(!initSim800()){}
      parseResponse(requestGET("https://airport.siamin.ir/rokh/getNumbers.php"));  
      lastRequestTime = millis();
      firstRun = false;
      showMessage("Finish tasks");
    }
  }
  delay(10000); // sleep loop for 10 sec
}

String requestGET(String url) {
  String resp = "";
  if(WiFi.status() == WL_CONNECTED) {
    showMessage("Send request");
    
    WiFiClientSecure client; 
    HTTPClient http;
    
    client.setInsecure();
    client.setTimeout(10000);
    if(http.begin(client, url)) {
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      http.addHeader("User-Agent", "ESP8266");
      http.addHeader("Connection", "close");
      
      int httpCode = http.GET();    
      
      if(httpCode > 0) {
        String msg = "";    
        if(httpCode == HTTP_CODE_OK) {
          resp = http.getString();
          msg="Get Data>>"+resp;
        } else if (httpCode == HTTP_CODE_GATEWAY_TIMEOUT) {
          requestGET(url);
          msg= "Timeout retry";
        }else {
          msg = "Error_resp: "+http.getString();
        }
        showMessage("Resp_code: "+  String(httpCode)+"|"+msg);
        delay(2500);
      } else {
        showMessage("Failed_err: "+ http.errorToString(httpCode));
         delay(5000);
      }
      
      http.end();
    } else {
      showMessage("Unable conn->server");
    }
  }
  return resp;
}

void reportToServer(String number, String status) {
  String resp  = requestGET("https://airport.siamin.ir/rokh/reportNumber.php?number="+number+"&status="+status);
}

void clearNumbers(){
   for(int i=0; i<130; i++) {
    numbers[i] = "";
  }
}

void parseResponse(String response) {
  // clear array
  int numCount = 0;
  clearNumbers();
  
  //split numbers by char ,
  int startIndex = 0;
  int commaIndex = response.indexOf(',');
  
  while(commaIndex != -1 && numCount < 130) {
    numbers[numCount] = response.substring(startIndex, commaIndex);
    numCount++;
    startIndex = commaIndex + 1;
    commaIndex = response.indexOf(',', startIndex);
  }
  
  // add last number
  if(startIndex < response.length() && numCount < 130) {
    numbers[numCount] = response.substring(startIndex);
    numCount++;
  }
  if(!numbers[0].isEmpty()){
    checkNumbers();
  }
  
}

String sendATCommand(String cmd, unsigned long timeout = 1000) {
  Serial.println(cmd);
  delay(500);
  String response;
  unsigned long startTime = millis();
  
  while(millis() - startTime < timeout) {
    while(Serial.available()) {
      char c = Serial.read();
      response += c;
    }
  }
  showMessage("Cmd: "+cmd + "|Response: " + response);
  delay(2000);
  response.trim();
  return response;
}

bool initSim800(){
  showMessage("wait->conn->GSM");
  delay(5000);// this delay for connected to BTS
  String resp = sendATCommand("AT", 1500);
  if (resp.indexOf("OK") == -1) {
    showMessage("GSM restarting");
    sendATCommand("AT+CFUN=1,1", 1500);
    return false;
  }else{
    showMessage("sim800l Ready");
    return true;
  }
  
}

void callNumber(String countryCode,String number,int limitRepeted=2) {
  
  while(!initSim800()){}

  showMessage("CALL " + number+"("+(2-limitRepeted)+")");

  // Initiate call
  delay(5000);
  Serial.println("ATD" + countryCode + number + ";");
  delay(3000);
  // Serial.flush();

  unsigned long callStartTime = millis();
  bool callConnected = false;
  String callStatus = "UNKNOWN";

  // Wait for call status (max 60 seconds)
  while (millis() - callStartTime < 60000) {
    if (Serial.available()) {
      String response = Serial.readString();
      showMessage(response);

      
      showMessage("Resp: " + response);

      // Check for different call statuses
      if (response.indexOf("NO CARRIER") != -1) {
        callStatus = "NO_CARRIER";
        break;
      }
      else if (response.indexOf("BUSY") != -1) {
        callStatus = "BUSY";
        break;
      }
      else if (response.indexOf("NO ANSWER") != -1) {
        callStatus = "NO_ANSWER";
        break;
      }
      else if (response.indexOf("VOICE CALL: BEGIN") != -1 || 
               response.indexOf("OK") != -1 || 
               response.indexOf("CONNECT") != -1) {
        callStatus = "CONNECTED";    
        delay(2500);  
        sendATCommand("ATH", 1000);
        showMessage("Hanging up");
        delay(1000);
        break;
      }
      else if (response.indexOf("ERROR") != -1) {
        callStatus = "CALL_FAILED";
        break;
      }
    }
  }
  
  // Report to server
  showMessage("Call status: " + callStatus);
  if((callStatus=="UNKNOWN" || callStatus=="NO_CARRIER") && limitRepeted>0){
    delay(7000);
    callNumber(countryCode,number,limitRepeted-1);
  }else{
    String serverStatus = "";
    if (callStatus == "NO_CARRIER") serverStatus = "OFF";
    else if (callStatus == "BUSY") serverStatus = "BUSY";
    else if (callStatus == "CONNECTED") serverStatus = "ACTIVE";
    else if (callStatus == "NO_ANSWER") serverStatus = "NO_ANSWER";
    else serverStatus = "UNKNOWN";
    reportToServer(number, serverStatus);
  }
  
}

void checkNumbers(){
  // callNumber("+98","9334743893");
  for(int i=0; i<130; i++) {
    if(!numbers[i].isEmpty()){
      callNumber("+93",numbers[i]);
      delay(2000);
    }else{
      break;
    }
  }
  clearNumbers();
}




