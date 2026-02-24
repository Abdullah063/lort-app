#!/bin/bash

API_URL="http://localhost:8000/api/v1/recommendation/send"
API_KEY="3thMTMIsk0K5vs95hhyatfiXmVVOumgQ3O5tJPrtevtF1AsvQ658vXyR7sMmE5bT"

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "Accept-Language: ar" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Ali","email":"alttun0@gmail.com","company":"ABC Teknoloji","position":"Yazılım Mühendisi","sector":"Yazılım","city":"Gaziantep","goals":["Freelance iş bulmak","Yurt dışına açılmak"],"interests":["Yapay Zeka","Web Geliştirme"],"website":"abdullahaltun.com.tr"}' &

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Veli","email":"alttun17@gmail.com","company":"XYZ Finans","position":"Finans Direktörü","sector":"Finans","city":"İstanbul","goals":["Yatırımcı bulmak","Şirket büyütmek"],"interests":["Borsa","Kripto","Gayrimenkul"]}' &

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Ramazan","email":"ramazan351.ra@gmail.com","company":"Tech Commerce","position":"Kurucu","sector":"E-ticaret","city":"Ankara","goals":["Bayilik ağı kurmak","Marka bilinirliği artırmak"],"interests":["Dijital Pazarlama","Lojistik"]}' &

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Mahmut","email":"mahmutaltun2763@gmail.com","company":"Altun İnşaat","position":"Genel Müdür","sector":"İnşaat","city":"Gaziantep","goals":["Yeni projeler bulmak","Tedarikçi ağı genişletmek"],"interests":["Mimari","Gayrimenkul","Sürdürülebilir Enerji"]}' &

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Fatma","email":"abdullahaltun016@gmail.com","company":"Sağlık Plus","position":"Klinik Müdürü","sector":"Sağlık","city":"İzmir","goals":["Klinik zincirleri kurmak","Sağlık turizmi"],"interests":["Tıbbi Cihazlar","Wellness","Spor"]}' &

curl -s -X POST $API_URL -H "Content-Type: application/json" -H "Accept-Language: tr" -H "X-API-Key: $API_KEY" \
  -d '{"name":"Emre","email":"kanburyunusemre28@gmail.com","company":"Kanbur Gıda","position":"İhracat Müdürü","sector":"Gıda","city":"Mersin","goals":["Avrupa pazarına girmek","Organik ürün ihracatı"],"interests":["Tarım","Dış Ticaret","Fuar Organizasyonları"]}' &

wait
echo ""
echo "mesajlar gönderilmeye başladı."