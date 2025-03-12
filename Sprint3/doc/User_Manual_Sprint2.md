# **คู่มือการใช้งาน (User Manual)**  

## **ระบบ: Research Document Management System**  

**ผู้พัฒนา:** กลุ่ม 1 sec 4  
**อัปเดตล่าสุด:** 25/2/2568  

---

## ** 1.บทนำ (Introduction) **  
ระบบ **Research Document Management System** เป็นแพลตฟอร์มที่ช่วยบริหารจัดการเอกสารงานวิจัยของวิทยาลัยการคอมพิวเตอร์  
โดยแพลตฟอร์มนี้มีการจัดทำ **Report Activity** สำหรับ Admin  


---

## ** 2.วิธีใช้เมนู Report Activity **  

1. ตำแหน่งการกดเข้าไปหน้า Report Activity จะทาง Menu ด้านซ้ายมือ
2. คลิกที่ Menu Report Activity
3. ระบบจะแสดง Activity ของผู้ใช้งาน
4. สามารถกดกรองข้อมูลเพื่อดู Activity ในช่วงเวลาที่ต้องการได้
5. สามารถ export file เป็น pdf หรือ docx ได้ 
---

## ** 3.การทำงานของ Report Activity**

1. ระบบจะแสดงรายการกิจกรรมของผู้ใช้ทั้งหมด เช่น Login, Logout
2. ค้นหาและกรองข้อมูลโดยการใช้ role เช่น กรองเฉพาะ Admin, staff , user และค้นหาโดยใช้วันที่
3. แสดงกิจกรรมที่ผู้ใช้งานทำมากที่สุดเป็นกราฟ
4. export file as pdf หรือ docx ได้
---

## **4. ปัญหาที่พบบ่อย**

|&nbsp;ปัญหา&nbsp;|&nbsp;สาเหตุ&nbsp;|&nbsp;วิธีแก้ไข&nbsp;| <br> <br>
|=======================|=======================|=======================| <br> <br>
|&nbsp;ข้อมูลในการ login ไม่บันทึก&nbsp;|&nbsp;Laravel ไม่บันทึก Activity Log ให้&nbsp;|&nbsp;สร้างฟังก์ชันในการบันทึก login แยกออกมา&nbsp;| <br>


---

## **5. คำถามที่พบบ่อย**



Group 1 https://github.com/kku-computer-science/git-group-repository-group-1-sec-4
