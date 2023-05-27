# Project Manager
Informatika 2 - VIAUAB01 - házi feladat

Név: Varga Csaba    
Neptun-kód: K04JA8  
A bemutató videó URL-je: https://www.youtube.com/watch?v=CAkkHLS_JkU

# Specifikáció

## Feladat informális leírása

A ***Project Manager*** egy webalapú alkalmazás, amely lehetővé teszi az egyének vagy csapatok számára, hogy szervezett módon kezeljék feladataikat, projektjeiket és ezek határidejét. A rendszer felületet biztosít a feladatok létrehozásához és kiosztásához, a határidők meghatározásához. A felhasználók vizualizálhatják az előrehaladásukat, hogy munkájukkal naprakészek maradjanak.

## Elérhető funkciók

Az alkalmazás a következő funkciókat biztosítja:
- Alap funkciók a menüből:
    - Bejelentkezés: Bejelentkezési oldal és link a regisztrációs oldalra
    - Kezdőlap: Néhány információt jelenít meg az alkalmazásról 
    - Projektek kezelése: 
        - Projektvezető esetében:
            - Látja az alkamazottainak az előrehaladását
            - Feladatokat tud kiosztani
            - Határidőket tudja módosítani
        - Alkalmazott esetében: 
            - Látja a számára kioszott feladatokat
            - Látja a feladatainak a határidejét
            - Le tudja adni az elkészített feladatokat
- Admin jogosultság esetén elérhető egy Adatbázis oldal, ahol az összes adatbázisban lévő adatot lehet megjeleníteni, módosítani és újakat beírni.

## Adatbázis séma

Az adatbázisban a következő entitásokat és attribútumokat tároljuk:
- **user**: user_id, username, password, email, dark_mode, access_level
    - dark_mode: A kiválasztott CSS felhasználónkénti tárolását segíti elő
    - access_level: jogosultsági köröket tartalmazza: *admin*, *project_lead*, *employee* és *guest*
- **project**: project_id, title, description, due_date, status
    - status: Az adott project állapotát tárolja: *not_started*, *in_progress* és *finished*

Ezeket a táblákat a **user_has_project** kapcsolótábla kapcsolja össze.

A fenti adatok tárolását az alábbi séma szemlélteti:
![database screenshot](assets/db.PNG)

# Dokumentáció
## Fájlok
- `connection.php`
- `footer.php`
- `index.php`
- `login.php`
- `logout.php`
- `menu.php`
- `profile.php`
- `projects_admin.php`
- `projects_delete.php`
- `projects_edit_admin.php`
- `projects_edit.php`
- `projects_employee.php`
- `projects_helper.php`
- `projects_lead.php`
- `projects_task_delete.php`
- `projects_task_edit.php`
- `projects_task.php`
- `projects.php`
- `register.php`
- `users_delete.php`
- `users_edit.php`
- `users_insert.php`
- `users.php`