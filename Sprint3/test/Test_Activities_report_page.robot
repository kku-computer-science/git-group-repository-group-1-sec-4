*** Settings ***
Library    SeleniumLibrary

*** Variables ***
${URL}           https://cs0468.cpkkuhost.com/login
${USERNAME}      admin@gmail.com  # แทนที่ด้วยชื่อผู้ใช้จริง
${PASSWORD}      12345678    # แทนที่ด้วยรหัสผ่านจริง
${BROWSER}       chrome

*** Test Cases ***
Login And Go To Activity Report
    [Documentation]    ทดสอบการล็อกอินและไปยังหน้า Activity Report
    Open Browser    ${URL}    ${BROWSER}
    Input Text      name=username    ${USERNAME}   # แทนที่ด้วย name หรือ id ของฟอร์ม username
    Input Text      name=password    ${PASSWORD}   # แทนที่ด้วย name หรือ id ของฟอร์ม password
    Click Button    xpath=//button[@type='submit']    # แทนที่ด้วย xpath หรือ selector ของปุ่ม Login
    Wait Until Page Contains    Dashboard    10s    # รอให้หน้า Dashboard หรือข้อความที่แสดงหลังจากล็อกอินโหลดเสร็จ
    Sleep    5s    # ให้เวลาให้หน้าโหลด
    Go To Activities Reports      # คลิกไปที่หน้า Activity Report
    Wait Until Location Contains  /user/activity-report    30s    # รอให้หน้า Activity Report โหลดเสร็จ
    Close Browser

*** Keywords ***
Go To Activities Reports 
    [Documentation]    คลิกไปที่หน้า Activity Report
    Click Link    xpath=//a[contains(@class, 'nav-link') and contains(@href, '/user/activity-report')]  # คลิกไปที่ลิงก์ Activity Report
    Wait Until Location Contains  /user/activity-report    30s
    Sleep    5s    # ให้เวลาให้หน้าโหลด


