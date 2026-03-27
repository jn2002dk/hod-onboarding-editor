# Dokumentation

En simpel WordPress plugin med minimum krævede setup for plugins.
hod-blocks.js indeholder hoved logik
hod-dashboard.js indeholder dashboard logik
hod-onboarding-editor.php håndterer backend logik
hod-styles.css indeholder css kode

## Detaljer

Den består af 2 moduler, begge kan vælges som Gutenberg blocks i WordPress editor, en new employee form samt en editor.
New employee form indsættes på den siden man sender til nye ansatte og de skal herefter udfylde den. Når den er indsent, fremsendes en mail til den relevante afdelingsleder. Vedkommende kan derefter gå in i editor hvor de skal udfylde de resterende detaljer så som hvilke nøgler der skal udleveres, behov for laptop, skal der leveres blomster og i givet fald hvor.

## Implementering

Der er taget højde for sanitering samt XSS angreb i php delen samt fjernet potentielle leaks i error handling

## Forbedringer

1. Detaljer bør enkrypteres før de gemmes i databasen
2. Manglende styling der matcher skolens visuelle udtryk. Dette er udskudt indtil Era har lavet den nye hjemmeside
3. Bruger login for nye brugere mangler VIGTIG
4. Login for HoD mangler VIGTIG