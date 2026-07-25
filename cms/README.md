# Agent Thailand CMS

ระบบหลังบ้านแบบ Building Blocks ([shadcn/ui](https://github.com/shadcn-ui/ui)) — React + Vite + Tailwind

## คุณสมบัติ

- เมนูแยกชัด: หน้าแรก / หน้าอื่นๆ / แผนประกัน / หมวดหมู่ / FAQ / บล็อก / ตั้งค่า
- **หน้าแรก (Visual Editor)** — พรีวิวเหมือนหน้าบ้าน คลิกข้อความเพื่อแก้ไข
- **แผนประกัน & หมวดหมู่** — ฟอร์มละเอียดครบทุกส่วน
- รองรับ XAMPP (PHP + MySQL) และโหมด Static สำหรับ GitHub Pages

## เปิดใช้งานบน XAMPP

1. เข้าสู่ระบบ PHP admin ก่อน: `http://localhost/1501/admin/login.php`
2. Build CMS (ครั้งแรกหรือเมื่อแก้โค้ด):

```bash
cd cms
npm install
npm run build
```

3. เปิด: `http://localhost/1501/cms/dist/`

หรือรันโหมดพัฒนา:

```bash
cd cms
npm run dev
```

แล้วเปิด `http://localhost:5173` (ต้องล็อกอิน admin บนโดเมนเดียวกันเพื่อบันทึกลง DB)

## เปิดใช้งานบน GitHub Pages

1. Commit โฟลเดอร์ `cms/dist` หลัง `npm run build`
2. เปิด `https://goddarkmarketing.github.io/1501/cms/dist/`
3. โหมด **Static**: โหลดจาก `assets/js/site-content.js` / `plan-data.js`
4. ปุ่มบันทึกจะดาวน์โหลด JSON ร่าง — นำขึ้นเซิร์ฟเวอร์ PHP แล้วเผยแพร่ หรือ commit ไฟล์ JS ที่อัปเดตด้วยมือ

> GitHub Pages ไม่รัน PHP/MySQL ดังนั้นการเผยแพร่จริงผ่าน API ต้องทำบน XAMPP/VPS

## โครงสร้างสำคัญ

| Path | บทบาท |
|------|--------|
| `cms/src` | โค้ด React CMS |
| `cms/dist` | Build สำหรับเปิดบนเว็บ/Git |
| `admin/api/cms-bundle.php` | API โหลด/บันทึก bundle |
| `assets/js/cms-preview-bridge.js` | สะพานคลิกแก้หน้าแรก |
