# MARBOHUB POS

แอประบบขาย/สต๊อกหน้าเดียว (`index.html`) ที่คุยกับ backend เล็ก ๆ แบบไฟล์ (`api.php`)
เก็บข้อมูลออเดอร์/สต๊อกไว้ในไฟล์ `marbohub_data.json` บนเซิร์ฟเวอร์ (สร้างอัตโนมัติเมื่อบันทึกครั้งแรก)

แอปนี้ **แยกจาก FeedbackIQ โดยสิ้นเชิง** ไม่ได้ใช้ Expo/React Native และไม่แชร์โค้ดกันเลย
จึงต้อง deploy คนละที่กับ FeedbackIQ

## สิ่งที่ต้องมี

- Hosting ที่รัน **PHP ได้** (shared hosting ทั่วไปที่มี cPanel, เช่น Hostinger, DirectAdmin,
  หรือ free hosting อย่าง InfinityFree/000webhost ก็ใช้ได้) — deploy ที่ Vercel/Netlify **ไม่ได้**
  เพราะที่นั่นไม่มี PHP runtime
- สิทธิ์เขียนไฟล์ในโฟลเดอร์ที่อัปโหลด (PHP ต้องสร้าง/แก้ไข `marbohub_data.json` ได้)

## ขั้นตอน deploy (shared hosting / cPanel)

1. เข้า cPanel → File Manager (หรือใช้ FTP/SFTP) ไปที่โฟลเดอร์เว็บหลัก (มักชื่อ `public_html`
   หรือ `public_html/<โดเมนย่อย>` ถ้าใช้ subdomain)
2. อัปโหลด **ไฟล์ทั้งหมดในโฟลเดอร์นี้** (`index.html`, `api.php`, `.htaccess`) ไปไว้ที่ระดับบนสุดของ
   โฟลเดอร์นั้นโดยตรง (ห้ามอัปโหลดทั้งโฟลเดอร์ `marbohub-pos` เพราะ `index.html` เรียก `api.php` ด้วย
   path สัมพัทธ์ ไฟล์ทั้งหมดต้องอยู่ระดับเดียวกัน — `.htaccess` เป็นไฟล์ที่ขึ้นต้นด้วยจุด บาง FTP client
   ซ่อนไฟล์นี้ไว้ ให้เปิด "show hidden files" ก่อนอัปโหลด)
3. เปิด `https://<โดเมนของคุณ>/` — ควรเจอหน้า login ที่ถาม PIN
4. ทดสอบกรอก PIN (ค่าเริ่มต้นคือ `8888`) แล้วลองบันทึกออเดอร์ เพื่อยืนยันว่า `api.php` เขียนไฟล์
   `marbohub_data.json` ได้สำเร็จ (ถ้าเขียนไม่ได้ ให้เช็ก permission ของโฟลเดอร์ ปกติ 755 ก็พอ)

## ⚠️ ข้อควรระวังด้านความปลอดภัย

- PIN ถูก hardcode ไว้ใน `api.php` (บรรทัด `define('PIN', '8888');`) เป็น plain text — **แนะนำให้
  เปลี่ยนค่านี้ก่อน deploy จริง** และอย่าใช้ PIN เดาง่ายถ้าข้อมูลออเดอร์มีความสำคัญ
- ไม่มี HTTPS บังคับ และไม่มี rate-limit การเดา PIN
- ไฟล์ `.htaccess` ที่แนบมาบล็อกการเข้าถึง `marbohub_data.json`/`marbohub_data.lock` ตรง ๆ ผ่าน URL
  ไว้แล้ว (ใช้ได้กับ Apache ที่เปิด `AllowOverride` — hosting ส่วนใหญ่เปิดให้โดยดีฟอลต์) แต่ถ้า hosting
  ของคุณใช้ Nginx ต้องตั้งค่า block เข้าถึงไฟล์นี้เองที่ server config เพราะ `.htaccess` ใช้ไม่ได้กับ Nginx
- ระบบยัง single-tenant/single-PIN ไม่มีระบบผู้ใช้แยกสิทธิ์ เหมาะกับใช้งานภายในทีมเล็ก ๆ เท่านั้น
