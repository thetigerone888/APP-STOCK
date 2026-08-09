# 🐯 คู่มือติดตั้งระบบ Tiger88 ขึ้น thetiger.one

ระบบนี้ทดสอบครบทุกจุดแล้ว (login, เพิ่ม/แก้/ลบ, sync, การแก้ไขไม่หายแม้ sync ใหม่) พร้อมใช้งานจริง

> **หมายเหตุเรื่องความปลอดภัย:** repo นี้เป็น **public** ดังนั้นเวอร์ชันใน git จึงถูกล้างข้อมูลลับออกแล้ว:
>
> - `config.php` ใน repo เป็น placeholder — ค่าจริง (รหัสผ่าน DB, password hash, sync key, URL ชีต Paypers)
>   อยู่ในไฟล์ zip ชุด deploy เดิมที่เก็บไว้ส่วนตัว หรือสร้างใหม่ตามคอมเมนต์ในไฟล์
> - `seed_data.json` (ข้อมูลค่าใช้จ่ายจริง 902 รายการ) **ไม่ได้ commit ขึ้น repo** — ใช้ไฟล์จาก zip ชุด deploy เดิม
> - รหัสผ่านเข้าเว็บและ sync key จริง จดเก็บไว้ที่ปลอดภัยส่วนตัว อย่าเขียนลงไฟล์ใดๆ ใน repo นี้

---

## ขั้นตอนที่ 1: สร้างฐานข้อมูล MySQL ใน cPanel

1. เข้า cPanel → หา **MySQL® Databases**
2. สร้าง Database ใหม่ เช่น `tiger88` → จะได้ชื่อเต็มประมาณ `xf8mfcn5xnc2_tiger88`
3. สร้าง MySQL User ใหม่ เช่น `tigeruser` พร้อมตั้งรหัสผ่าน → จดรหัสผ่านไว้
4. เลื่อนลงไปหา **Add User to Database** → เลือก user + database ที่สร้าง → ติ๊ก **ALL PRIVILEGES** → Add

## ขั้นตอนที่ 2: Import โครงสร้างตาราง

1. กลับหน้า cPanel → เปิด **phpMyAdmin**
2. คลิกเลือก database `xf8mfcn5xnc2_tiger88` ทางซ้าย
3. แท็บ **Import** → เลือกไฟล์ `schema.sql` → กด **Go**
4. ควรเห็นตาราง `expenses` และ `app_settings` ถูกสร้างขึ้น

## ขั้นตอนที่ 3: แก้ config.php

เปิดไฟล์ `config.php` แล้วแก้ค่า `CHANGE_ME_*` ทุกตัว:

```php
define('DB_NAME', 'xf8mfcn5xnc2_tiger88');      // ชื่อ DB จริงที่สร้าง
define('DB_USER', 'xf8mfcn5xnc2_tigeruser');    // ชื่อ DB user จริงที่สร้าง
define('DB_PASS', '...');                        // รหัสผ่าน DB user ที่ตั้งไว้
define('LOGIN_PASSWORD_HASH', '...');            // hash ของรหัสผ่านเข้าเว็บ
define('SYNC_SECRET_KEY', '...');                // key ลับสำหรับ cron sync
define('PAYPERS_SHEET_URL', '...');              // URL publish-to-web (xlsx) ของชีต Paypers
```

- ถ้ามีไฟล์ `config.php` จาก zip ชุด deploy เดิม ใช้ค่าจากไฟล์นั้นได้เลย (แก้เฉพาะ DB 3 บรรทัดแรก)
- ถ้าต้องสร้างใหม่: วิธีสร้าง `LOGIN_PASSWORD_HASH` และ `SYNC_SECRET_KEY` อยู่ในคอมเมนต์ของ `config.php`

## ขั้นตอนที่ 4: อัปโหลดไฟล์ทั้งหมด

ผ่าน **cPanel → File Manager** ไปที่โฟลเดอร์ `public_html/thetiger.one/` (ตามที่เคยยืนยันไว้ก่อนหน้า) แล้วอัปโหลดไฟล์ทั้งหมดนี้:

```
api.php
auth.php
config.php          (ที่แก้ไขแล้วในขั้นตอน 3)
index.php
login.php
logout.php
schema.sql          (อัปโหลดไว้ก็ได้ ไม่จำเป็นหลัง import แล้ว)
seed_data.json       ← จาก zip ชุด deploy เดิม (ไม่มีใน repo) ต้องมีก่อนรัน seed_import.php
seed_import.php
sync.php
sync_lib.php
xlsx_reader.php
```

> ถ้ามีหน้า Coming Soon (index.html เดิม) อยู่ในโฟลเดอร์นี้ ให้ลบหรือเปลี่ยนชื่อออกก่อน ไม่งั้น `index.php` อาจไม่ถูกเรียกเป็นหน้าแรก (ขึ้นกับ priority ของ index.html vs index.php บนเซิร์ฟเวอร์)

## ขั้นตอนที่ 5: นำเข้าข้อมูลเดิม 902 รายการ (รันครั้งเดียว)

1. เปิดเบราว์เซอร์ไปที่ `https://thetiger.one/seed_import.php`
2. ควรเห็นข้อความ `เพิ่มใหม่: 902 รายการ`
3. **ลบไฟล์ `seed_import.php` และ `seed_data.json` ทิ้งทันที** ผ่าน File Manager (ป้องกันคนอื่นรันซ้ำหรือเห็นข้อมูลดิบ)

## ขั้นตอนที่ 6: ทดสอบเข้าเว็บ

1. เปิด `https://thetiger.one/` → ควรเจอหน้า login
2. ใส่รหัสผ่านเข้าเว็บ (ตัวที่ตรงกับ `LOGIN_PASSWORD_HASH`) → เข้าสู่ระบบ
3. ควรเห็น Dashboard พร้อมข้อมูล 902 รายการ ยอดรวม ฿1,823,848.08
4. ลองกดปุ่ม **🔄 Sync Sheet** → ควรขึ้น "เพิ่ม 0 รายการใหม่" (เพราะ seed ไปแล้ว)
5. ลองแก้ไข/ลบ 1 รายการ → รีเฟรชหน้า → ควรเห็นการเปลี่ยนแปลงคงอยู่ (ไม่หาย)

## ขั้นตอนที่ 7: ตั้ง Cron Job (sync อัตโนมัติทุกวัน)

1. cPanel → หา **Cron Jobs**
2. เลือกความถี่: **Once Per Day** (เช่น ทุกเที่ยงคืน หรือ 06:00)
3. ในช่อง Command ใส่ (แทน `YOUR_SYNC_SECRET_KEY` ด้วยค่าจริงจาก `config.php`):

```
curl -s "https://thetiger.one/sync.php?key=YOUR_SYNC_SECRET_KEY" >/dev/null 2>&1
```

หรือถ้า cPanel ไม่มี curl ให้ลองแบบ wget:
```
wget -q -O /dev/null "https://thetiger.one/sync.php?key=YOUR_SYNC_SECRET_KEY"
```

4. บันทึก — ระบบจะดึงรายการใหม่จาก Paypers Sheet มาเพิ่มอัตโนมัติทุกวัน โดยไม่ทับรายการที่เคยแก้ไขแล้ว

---

## ✅ สรุปสิ่งที่ระบบนี้แก้ปัญหาให้

| ปัญหาเดิม | แก้อย่างไร |
|---|---|
| แก้ไข/ลบแล้วหายเมื่อรีเฟรช | ข้อมูลอยู่ใน MySQL บนเซิร์ฟเวอร์แล้ว ไม่ใช่ localStorage |
| ปุ่ม Sync ติด CORS ใช้งานไม่ได้จริง | ย้าย sync ไปทำงานฝั่งเซิร์ฟเวอร์ (PHP) ไม่มี CORS |
| Sync ใหม่แล้วทับรายการที่เคยแก้ไข | ใช้เลขแถวในชีตเป็นตัวกันซ้ำ, sync แค่ "เพิ่ม" รายการใหม่ ไม่แตะรายการเดิม |
| ไม่มีการป้องกันคนนอกเข้าถึง | ต้อง login ด้วยรหัสผ่านก่อนเข้าเว็บและก่อนเรียก API ทุกจุด |

## 🧪 ทดสอบมาแล้วว่าใช้ได้จริง

- ✅ Login/logout ทำงานถูกต้อง (รหัสผิดถูกปฏิเสธ, เข้าไม่ได้ถ้าไม่ login)
- ✅ เพิ่ม/แก้ไข/ลบ ผ่าน API บันทึกถาวรจริง
- ✅ XLSX parser อ่านไฟล์ Paypers ได้ตรง 902 รายการ ฿1,823,848.08 (ตรงกับ Python ทุกตัวเลข)
- ✅ Sync ซ้ำหลายครั้งไม่สร้างรายการซ้ำ
- ✅ **แก้ไขรายการแล้ว sync ใหม่ ข้อมูลที่แก้ไม่ถูกทับ** (ทดสอบจริงแล้ว)

## ⚠️ ข้อจำกัดที่ควรรู้

- ระบบ sync ยึดว่า **แถวในชีต Paypers ไม่ถูกย้ายตำแหน่งหรือลบ** (เป็นแบบ append-only ตามที่ Paypers ออกแบบไว้) ถ้ามีการแก้ไขชีตต้นทางแบบแทรกแถวกลาง อาจทำให้ sync ครั้งถัดไปเข้าใจผิดว่าเป็นรายการใหม่
- ถ้าลืมรหัสผ่าน ต้องเข้าไปแก้ `LOGIN_PASSWORD_HASH` ใน `config.php` ใหม่ (ใช้ PHP `password_hash()` สร้างค่าใหม่ ตามคอมเมนต์ในไฟล์)
