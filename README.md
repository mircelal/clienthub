# ClientHub for Nextcloud

ClientHub - Kapsamlı müşteri, proje ve iş yönetim sistemi. Tüm işletme süreçlerinizi tek bir yerden yönetin.

## Özellikler

### 📊 Dashboard
- Genel istatistikler ve özet bilgiler
- Yaklaşan son kullanma tarihleri takibi
- Ödeme durumu özeti
- Aktif proje ve görev takibi

### 👥 Müşteri Yönetimi
- Müşteri bilgileri ve iletişim detayları
- Notlar ve özel bilgiler
- Müşteriye özel tüm kayıtların görüntülenmesi

### 🌐 Domain Yönetimi
- Domain kayıt takibi
- Son kullanma tarihi takibi
- Yenileme hatırlatıcıları
- Müşteri bazlı domain listeleme

### 🖥️ Hosting Yönetimi
- Hosting hesap yönetimi
- Sunucu detayları ve bilgileri
- Yenileme tarihleri takibi
- Hosting bazlı website ilişkilendirme

### 🌍 Website Yönetimi
- Website kayıtları
- Kurulu yazılım takibi
- Website konfigürasyonları
- Hosting ve müşteri ilişkilendirme

### 🛠️ Hizmet Yönetimi
- Hizmet türleri tanımlama (Domain, Hosting, SSL, vb.)
- Müşterilere özel hizmet kayıtları
- Hizmet yenileme tarihleri takibi
- Yaklaşan son kullanma tarihleri uyarıları
- Otomatik hizmet uzatma

### 📄 Fatura Yönetimi
- Fatura oluşturma ve düzenleme
- Fatura kalemleri yönetimi
- Ödenmemiş faturalar takibi
- Vadesi geçmiş faturalar uyarısı
- Yaklaşan ödeme tarihleri takibi
- Müşteri bazlı fatura listeleme

### 💰 Ödeme Takibi
- Ödeme kayıtları
- Fatura-ödeme ilişkilendirme
- Aylık toplam gelir takibi
- Müşteri bazlı ödeme geçmişi

### 📁 Proje Yönetimi
- Proje oluşturma ve takibi
- Proje kalemleri yönetimi
- Proje durumu takibi (Aktif, Tamamlandı, Beklemede)
- Yaklaşan deadline uyarıları
- Müşteri bazlı proje listeleme

### ✅ Görev Yönetimi
- Görev oluşturma ve takibi
- Görev durumu (Beklemede, Devam Ediyor, Tamamlandı)
- Görev öncelik seviyeleri
- Vadesi geçmiş görevler uyarısı
- Yaklaşan deadline takibi
- Proje ve müşteri bazlı görev filtreleme

## Gereksinimler

- Nextcloud 25 veya üzeri
- PHP 8.0 veya üzeri

## Kurulum

1. Repository'yi klonlayın veya indirin
2. `domaincontrol` klasörünü Nextcloud `apps/` dizinine kopyalayın
3. Uygulamayı etkinleştirin:
   ```bash
   cd /path/to/nextcloud
   php occ app:enable domaincontrol
   ```
4. Uygulama Nextcloud navigasyon menüsünde görünecektir

## Kullanım

1. Nextcloud navigasyon menüsünden "ClientHub" seçeneğine tıklayın
2. Dashboard'dan genel durumu görüntüleyin
3. İlgili sekmeden (Müşteriler, Domainler, Hosting, vb.) yeni kayıtlar ekleyin
4. Her kayıt için detaylı bilgileri girebilir ve takip edebilirsiniz

## Lisans

AGPL-3.0
