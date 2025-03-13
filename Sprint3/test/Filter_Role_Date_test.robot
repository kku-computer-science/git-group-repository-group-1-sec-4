*** Settings ***
Library    SeleniumLibrary
Library    DateTime

*** Variables ***
${URL}           https://cs0468.cpkkuhost.com/login
${USERNAME}      admin@gmail.com
${PASSWORD}      12345678
${BROWSER}       chrome
${ROLES}         admin,student,teacher,staff,headproject,guest
${DATE_FILTERS}  ,daily,weekly,monthly,custom  # ค่าว่างตัวแรกคือ -- All Time --

*** Test Cases ***
Login And Go To Activity Report
    [Documentation]    ทดสอบการล็อกอินและไปยังหน้า Activity Report
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Input Text      name=username    ${USERNAME}
    Input Text      name=password    ${PASSWORD}
    Click Button    xpath=//button[@type='submit']
    Wait Until Page Contains    Dashboard    30s
    Sleep    2s
    Go To Activity Report Page
    Wait Until Location Contains    /user/activity-report    10s
    Capture Page Screenshot    filter1.png
    Select And Filter Role    admin
    Capture Page Screenshot    filter2.png
    Select And Filter Role    student
    Capture Page Screenshot    filter3.png
    Select And Filter Role    teacher
    Capture Page Screenshot    filter4.png
    Select And Filter Role    staff
    Capture Page Screenshot    filter5.png
    Select And Filter Role    headproject
    Capture Page Screenshot    filter6.png
    Select And Filter Role    guest
    Capture Page Screenshot    filter7.png
    Select And Filter Role    teacher
    Capture Page Screenshot    filter8.png
    Select And Filter Date    daily   # เลือก Today
    Capture Page Screenshot    filter9.png
    Select And Filter Date    weekly  # เลือก This Week
    Capture Page Screenshot    filter10.png
    Select And Filter Date    monthly
    Capture Page Screenshot    filter11.png
    Select And Filter Date    custom  # เลือก Custom Range
    Capture Page Screenshot    filter12.png
    Set Date Filter    11/03/2025    13/03/2025
    Capture Page Screenshot    filter13.png

*** Keywords ***
Go To Activity Report Page
    [Documentation]    คลิกไปที่หน้า Activity Report
    Click Link    xpath=//a[contains(@class, 'nav-link') and contains(@href, '/user/activity-report')]
    Wait Until Location Contains    /user/activity-report    10s
    Sleep    2s

Select And Filter Role
    [Arguments]    ${ROLE}
    Click Element    xpath=//select[@id='role']/option[@value='${ROLE}']
    Sleep    1s
    Click Filter Button
    Sleep    1s

Select And Filter Date
    [Arguments]    ${DATE}
    Click Element    xpath=//select[@id='date_filter']/option[@value='${DATE}']
    Sleep    1s
    Click Filter Button
    Sleep    1s

Set Date Filter
    [Arguments]    ${START_DATE}    ${END_DATE_VALUE}
    # กำหนดวันที่เริ่มต้นและสิ้นสุด
    Input Text    name=start_date    ${START_DATE}
    Input Text    name=end_date    ${END_DATE_VALUE}
    Sleep    1s
    Click Filter Button
    Sleep    1s

Click Filter Button
    [Documentation]    คลิกปุ่ม Filter
    Click Button    xpath=//button[@type='submit' and contains(@class, 'btn-primary')]
    Sleep    1s