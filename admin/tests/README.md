# Admin backend integration tests

ทดสอบหลังบ้านแบบ integration: ยิง HTTP จริงผ่าน Apache แล้วตรวจว่าข้อมูลใน MySQL / ไฟล์ JS ถูกอัปเดตจริง

## ก่อนรัน

1. เปิด **Apache** และ **MySQL** ใน XAMPP
2. มีฐานข้อมูล `agent1501` และเข้าได้ด้วย `admin` / `admin123`

## รันเทสต์

```bat
C:\xampp\php\php.exe c:\xampp\htdocs\1501\admin\tests\run-all.php
```

ถ้าเว็บไม่อยู่ที่ `/1501`:

```bat
set ATEST_BASE=http://localhost/your-path
C:\xampp\php\php.exe c:\xampp\htdocs\1501\admin\tests\run-all.php
```

Exit code `0` = ผ่านทั้งหมด, `1` = มีเคสที่ fail, `2` = รันไม่ได้ (ไม่มี DB/Apache)

## ครอบคลุมอะไรบ้าง

| ส่วน | ตรวจอะไร |
|------|----------|
| Auth | login ผิด/ถูก, โหลดทุกหน้าเมนู, logout |
| Settings | แก้ `site_tagline` แล้วค่าใน DB เปลี่ยน |
| FAQ | สร้าง / แก้ / ลบ |
| Page sections | แก้ hero หน้าแรกผ่าน `page-edit.php` |
| Categories & Plans | CRUD + benefits/tiers |
| Blogs / Promos / Filters | CRUD |
| CMS blocks | แก้ footer |
| Contacts | API สาธารณะ + เปลี่ยนสถานะ + ลบ |
| Users | เพิ่ม / เปลี่ยนรหัส / ล็อกอิน / ลบ |
| APIs | `cms-block-save`, `cms-bundle`, `upload` |
| Publish | ไฟล์ `site-content.js`, `plan-data.js`, `blog-data.js`, `promotion-data.js` มีข้อมูลที่แก้ |
| Guards | API ไม่ล็อกอินถูกปฏิเสธ, ลบหมวดที่มีแผนใช้ไม่ได้ |

ข้อมูลทดสอบใช้ prefix `ATEST_*` และจะถูก cleanup + republish ท้ายสคริปต์
