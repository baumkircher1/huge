  1. CAPTCHA komplett entfernt

  - CAPTCHA-Validierung aus RegistrationModel.php:93-102 entfernt
  - CAPTCHA-HTML-Code aus register/index.php:19-22 entfernt
  - Die Funktion registrationInputValidation() prüft jetzt nur noch Username, E-Mail und Passwort

  2. E-Mail-Verifikation deaktiviert

  - Keine E-Mail wird mehr versendet
  - Der gesamte E-Mail-Versand-Code wurde entfernt
  - user_activation_hash wird nicht mehr erstellt

  3. Automatische Aktivierung

  - In writeNewUserToDatabase() wird user_active direkt auf 1 gesetzt
  - Benutzer sind sofort nach Registrierung aktiv und können sich einloggen