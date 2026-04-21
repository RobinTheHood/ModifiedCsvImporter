# Modified CSV Importer

## Beschreibung

Das Modul Modified CSV Importer stellt eine wiederverwendbare Basis für CSV-Importe im modified Shop System bereit. Es richtet sich an Entwickler, die Importfunktionen für eigene Module bauen möchten, ohne die wiederkehrenden Grundlagen jedes Mal neu zu implementieren.

Im Kern liefert das Modul eine abstrakte Importer-Klasse, die den technischen Ablauf eines CSV-Imports übernimmt: Datei öffnen, zeilenweise einlesen, Zeichencodierung in UTF-8 umwandeln und jede Zeile an eine eigene Verarbeitungslogik übergeben.

Dadurch kann sich die konkrete Import-Implementierung auf die eigentliche Fachlogik konzentrieren, zum Beispiel auf das Anlegen oder Aktualisieren von Produkten, Kategorien, Herstellern, Versandstatus oder Tags.

## Nutzen

- Einheitlicher Import-Ablauf für verschiedene CSV-Formate
- Weniger Boilerplate-Code in eigenen Importmodulen
- Bessere Wartbarkeit durch klare Trennung von Technik und Fachlogik
- Fortschrittsprotokollierung für Admin-Oberflächen und Statusanzeigen

## Typische Verwendung

Ein Modul erstellt eine eigene Importer-Klasse auf Basis von CsvImporter und implementiert nur die Zeilenverarbeitung. Vor dem Start werden Trennzeichen und Eingabecodierung gesetzt. Anschliessend wird der Import gestartet und der Fortschritt über eine Task-Logdatei bereitgestellt.

Dieses Muster wird bereits produktiv genutzt.

## Zielgruppe

Die Bibliothek ist für Modulentwickler gedacht, die robuste und konsistente CSV-Importe im modified-Umfeld umsetzen wollen.
