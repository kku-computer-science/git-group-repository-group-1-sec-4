# **คู่มือการใช้งาน (User Manual)**  

## **ระบบ: Research Document Management System**  

**ผู้พัฒนา:** กลุ่ม 1 sec 4  
**อัปเดตล่าสุด:** 13/2/2568  

---

## **1. บทนำ (Introduction)**  

ระบบ **Research Document Management System** เป็นแพลตฟอร์มที่ช่วยบริหารจัดการเอกสารงานวิจัยของวิทยาลัยการคอมพิวเตอร์  
โดยแพลตฟอร์มนี้รองรับหลายภาษา ได้แก่ **ภาษาไทย, ภาษาอังกฤษ และภาษาจีน**  

---

## **2. วิธีเปลี่ยนภาษา (How to Change Language)**  

1. ตำแหน่งเปลี่ยนภาษาอยู่ทางขวาบนของเว็บไซต์  
2. คลิกที่ปุ่มไอคอนธงชาติ  
3. เลือกภาษา เช่น **ไทย / English / 中文**  
4. ระบบจะเปลี่ยนภาษาให้ทันที  

---

## **3. การจัดการภาษาในระบบ**  

ระบบของเรารองรับการแปลภาษาทั้งจาก **ฐานข้อมูล (Database)** และ **Laravel Localization** โดยจะใช้วิธีที่เหมาะสมกับข้อมูลแต่ละประเภท  

### **3.1 การใช้ฐานข้อมูล (Database Translation)**  
- ข้อความที่อาจมีการเปลี่ยนแปลง เช่น **ชื่อของนักวิจัย หรือชื่อโครงการ** จะถูกเก็บไว้ในฐานข้อมูล  
- ตัวอย่างโครงสร้างของฐานข้อมูล:  
    ```sql
    CREATE TABLE research_projects (
        id INT PRIMARY KEY,
        project_name_en VARCHAR(255),
        project_name_th VARCHAR(255),
        project_name_cn VARCHAR(255)
    );

### **3.2 การใช้ Laravel Localization
- ระบบใช้การแปลภาษาที่แปลล่วงหน้าไว้แล้วโดยจะแยกเก็บไฟล์
- เช่น resources/lang/en/message.php
      resources/lang/th/message.php

- วิธีเรียกใช้ => {{ trans(message.Home) }}

---

## **4. ปัญหาที่พบบ่อย**

|ปัญหา					|สาเหตุ					|วิธีแก้ไข					|
|=======================|=======================|=======================|
|ภาษาไม่เปลี่ยน				|ยังไม่ได้มีการแปลคำนี้		|เพิ่มคำแปลในไฟล์ message.php|
|กดเปลี่ยนภาษาแล้วค่าไม่แสดง  |การดึงข้อมูลผิดพลาด		|ตรวจสอบการดึงข้อมูลของ code แล้วทำการแก้ไข|

---

## **5. คำถามที่พบบ่อย**


Group 1 https://github.com/kku-computer-science/git-group-repository-group-1-sec-4