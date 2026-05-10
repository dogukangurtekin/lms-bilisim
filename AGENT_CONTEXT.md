# AGENT_CONTEXT

## Proje Özeti
- ...

## Kararlar
- [2026-05-09] ...

## Prompt Kuralları
- Kısa cevap
- Gereksiz tekrar yok
- Sadece istenen çıktı

## Yapıldı
- ...

## Sıradaki İş
- ...

## Kalıcı Uygulama Kuralı
- Kullanıcı "AGENT_CONTEXT.md eklenecek notlar" verdiğinde asistan bu notları otomatik olarak AGENT_CONTEXT.md dosyasına yazar.
- Bu işlem için kullanıcıdan ayrıca "dosyaya yaz" onayı beklenmez.

### AGENT_CONTEXT.md eklenecek notlar
- Seçili öğrenci sayımı `form.querySelector` yerine global `.student-row-checkbox:checked` ile yapılmalı.
- `Tüm öğrencileri sil` onayı için ayrı global `confirmDeleteAllStudents()` kullanılmalı.
- Kullanıcı akışında iki butonda da submit öncesi confirm zorunlu.
- Toplu silme UI’si native form submit ile kurulmalı.
- Seçili/tümü silme endpointleri controller’da ayrı metotlarla yönetilmeli.
- Toplu silme işlemlerinde admin yetki kontrolü zorunlu olmalı.

- Sınıflar toplu silmede kalıcı çözüm: butonlar çalışan GET endpoint'lerine bağlanır (classes/secili-sil, classes/tumu-sil), test linki tutulmaz.
