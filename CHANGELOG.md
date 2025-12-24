# ClientHub - Değişiklik Günlüğü

## Versiyon 3.7.940 (Güncel)

### 🎯 Yeni Özellikler

#### 📊 Proje Yönetimi İyileştirmeleri
- **Proje Durumu Yönetimi**: Projeler için durum değiştirme özelliği eklendi
  - Aktif, Beklemede, Tamamlandı, İptal Edildi durumları
  - Proje detay sayfasından hızlı durum değiştirme
- **Proje Finansal Widget'ları**:
  - **Net Kar**: Toplam bütçe - Toplam masraflar
  - **Kalan Alacak**: Toplam faturalandırılan - Toplam ödenen
  - Renk kodlu gösterim (pozitif/negatif değerler için)

#### 💰 Fatura ve Ödeme Entegrasyonu
- **Otomatik Gelir Kaydı**: Fatura ödemeleri otomatik olarak gelir/gider bölümüne kaydediliyor
- **Proje Bağlantısı**: Fatura ödemelerinde proje bilgisi otomatik olarak ekleniyor
- **Fatura Linki**: Gelir/gider detaylarında faturaya direkt bağlantı
- **Proje Gideri Linki**: Gider detaylarında projeye direkt bağlantı

#### 💵 Para Birimi Yönetimi
- **Varsayılan Para Birimi**: Tüm sistemde ayarlardan seçilen varsayılan para birimi kullanılıyor
- **Para Birimi Sembolleri**: Para birimi isimleri yerine semboller gösteriliyor (₺, $, €, vb.)
- **Fatura Modalları**: Para birimi seçme inputları kaldırıldı, otomatik varsayılan para birimi kullanılıyor
- **Dashboard Para Birimi**: Dashboard'da ayarlardan seçilen varsayılan para birimi gösteriliyor

#### 🎨 Kullanıcı Arayüzü İyileştirmeleri
- **Settings Navigasyon**: Tek tıkla ayarlara erişim
- **Dashboard Hızlı İşlemler**: Tüm hızlı işlem butonları çalışır hale getirildi
- **Son Müşteriler**: Dashboard'daki son müşterilere tıklayınca direkt müşteri profili açılıyor
- **Fatura Navigasyonu**: Gelir/gider bölümünden faturaya tıklayınca direkt fatura detayı açılıyor

#### 📝 Görev Yönetimi İyileştirmeleri
- **Proje İçi Görev Ekleme**: Proje içinden görev eklerken proje ve müşteri seçimi otomatik
- **Akıllı Form**: Proje bağlamında görev eklerken gereksiz inputlar gizleniyor

#### 📅 Borçlar Bölümü
- **Tarih Alanları Düzeltmesi**: Borç verilme, son ödeme gibi tarih alanları düzeltildi
- **Boş Tarih Yönetimi**: Boş tarih alanları doğru şekilde işleniyor

#### 🎨 Icon Sistemi
- **Material Icon Desteği**: Eksik iconlar eklendi
  - `arrow-up`, `arrow-down` (gelir/gider için)
  - `arrow-up-circle`, `arrow-down-circle` (borç/alacak için)
  - `minus` (gider işlemleri için)
  - `history` (ödeme geçmişi için)
  - `information-outline` (bilgi mesajları için)

### 🔧 Teknik İyileştirmeler

#### Backend
- **PaymentController**: Fatura ödemelerinde otomatik transaction oluşturma
- **Transaction-Invoice Bağlantısı**: Transaction'larda invoice ID saklama
- **Project-Transaction Bağlantısı**: Fatura ödemelerinde proje bilgisi otomatik ekleme

#### Frontend
- **Vue.js Component İletişimi**: Component'ler arası navigasyon iyileştirildi
- **Window.DomainControl API**: Global navigasyon API'si genişletildi
  - `selectClient(clientId)`: Müşteri seçme
  - `selectProject(projectId)`: Proje seçme
  - `selectInvoice(invoiceId)`: Fatura seçme
- **Settings Event System**: Ayarlar güncellendiğinde otomatik yenileme

### 🐛 Hata Düzeltmeleri

- Dashboard'da giderler 0 görünme sorunu düzeltildi
- Dashboard hızlı işlem butonları çalışmama sorunu düzeltildi
- Settings'e tıklayınca çift menü açılma sorunu düzeltildi
- Ayarlar kaydedildikten sonra dashboard'a yönlendirme sorunu düzeltildi
- Fatura linkine tıklayınca liste açılma sorunu düzeltildi
- Son müşterilere tıklayınca liste açılma sorunu düzeltildi
- Borçlar bölümünde tarih alanları görünmeme sorunu düzeltildi
- Icon eksiklikleri giderildi

### 📋 Modül Listesi

ClientHub aşağıdaki modülleri içermektedir:

1. **Dashboard** - Genel istatistikler ve özet bilgiler
2. **Müşteriler** - Müşteri bilgileri ve iletişim detayları
3. **Domainler** - Domain kayıt takibi ve yenileme hatırlatıcıları
4. **Hosting** - Hosting hesap yönetimi ve sunucu detayları
5. **Websiteler** - Website kayıtları ve yazılım takibi
6. **Hizmetler** - Hizmet tipi tanımları ve müşteri bazlı hizmet kayıtları
7. **Faturalar** - Fatura oluşturma, düzenleme ve takip
8. **Projeler** - Proje yönetimi, durum takibi ve finansal bilgiler
9. **Görevler** - Görev oluşturma, durum takibi ve öncelik yönetimi
10. **Gelir/Gider** - Gelir ve gider işlemleri, kategori yönetimi
11. **Borç/Alacak** - Borç ve alacak takibi, ödeme planları
12. **Raporlar** - İş raporları ve analitikler
13. **Ayarlar** - Sistem ayarları, para birimi yönetimi, modül aktifleştirme

### 🔄 Entegrasyonlar

- **Nextcloud Contacts**: Müşteri bilgileri Nextcloud Contacts ile entegre
- **Nextcloud Files**: Dosya yönetimi Nextcloud Files ile entegre
- **Vue.js 3**: Modern Vue.js 3 framework kullanımı
- **Nextcloud Vue Components**: Nextcloud'un resmi Vue component'leri

### 📦 Bağımlılıklar

- Nextcloud 25 veya üzeri
- PHP 8.0 veya üzeri
- Vue.js 3
- Nextcloud Vue Components

---

## Gelecek Özellikler

- [ ] Çoklu dil desteği genişletme
- [ ] Gelişmiş raporlama ve grafikler
- [ ] E-posta bildirimleri
- [ ] Mobil uygulama desteği
- [ ] API dokümantasyonu
- [ ] Toplu işlemler
- [ ] Gelişmiş arama ve filtreleme

---

## Notlar

- Tüm para birimi işlemleri ayarlardan seçilen varsayılan para birimi kullanılarak yapılmaktadır
- Fatura ödemeleri otomatik olarak gelir/gider bölümüne kaydedilmektedir
- Proje giderleri otomatik olarak proje sahibi müşteri ile ilişkilendirilmektedir
- Dashboard'daki tüm istatistikler gerçek zamanlı olarak güncellenmektedir

