# ติดตั้งบนโฮสติ้ง — prakandd-thailand.com

## ลิงก์สำคัญ (หลังอัพไฟล์ที่ root โดเมน)

| หน้า | URL |
|------|-----|
| หน้าเว็บ | https://prakandd-thailand.com/ |
| **ติดตั้ง DB** | https://prakandd-thailand.com/admin/install.php |
| **หลังบ้าน** | https://prakandd-thailand.com/admin/login.php |

---

## ขั้นตอนสั้นๆ (ให้ลูกค้าทำได้)

### 1) อัพไฟล์
อัพทั้งโปรเจกต์ขึ้นโฮสต์ให้ไฟล์ `index.html` อยู่ที่ root ของโดเมน  
(โฟลเดอร์ `admin/`, `assets/`, `cms/` อยู่คู่กัน)

### 2) สร้างฐานข้อมูลใน cPanel
1. เข้า **cPanel → MySQL Databases**
2. สร้าง Database เช่น `prakandd_agent`
3. สร้าง User + Password
4. **Add User to Database** และให้สิทธิ์ **ALL PRIVILEGES**
5. จดชื่อ DB / User / Password ไว้ (มักมี prefix เช่น `prakandd_`)

### 3) รันตัวติดตั้ง
เปิด: **https://prakandd-thailand.com/admin/install.php**

กรอก:
- **DB Host** → ส่วนใหญ่เป็น `localhost`
- **ชื่อฐานข้อมูล / Username / Password** → จากข้อ 2
- ตั้งรหัสแอดมินที่ต้องการ
- ติ๊ก **นำเข้าข้อมูลเริ่มต้น** (แนะนำ)

กด **ติดตั้งฐานข้อมูล**

### 4) เข้าหลังบ้าน
https://prakandd-thailand.com/admin/login.php  
แล้วกด **เผยแพร่เว็บไซต์** หนึ่งครั้ง

---

## หมายเหตุ

- ตัวติดตั้งจะสร้างไฟล์ `admin/config.local.php` อัตโนมัติ (เก็บรหัส DB — อย่าแชร์ใน Git)
- หลังติดตั้งสำเร็จจะมี `admin/install.lock` กันติดตั้งซ้ำ  
  ถ้าต้องติดตั้งใหม่: ลบ `install.lock` หรือเปิด `install.php?force=1`
- ต้องการ PHP 8+ และส่วนขยาย **PDO MySQL**
