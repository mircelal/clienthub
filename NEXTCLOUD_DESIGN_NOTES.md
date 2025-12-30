# Nextcloud Tasarım Notları ve Proje İyileştirme Planı

## 📚 Nextcloud Tasarım İlkeleri

### 1. Layout Yapısı
Nextcloud uygulamaları iki ana layout pattern kullanır:

#### Pattern 1: Navigation → Content → Sidebar
- Sol tarafta navigation (app-navigation)
- Ortada ana content alanı (app-content)
- Sağ tarafta opsiyonel sidebar (app-sidebar)

#### Pattern 2: Navigation → List → Content
- Sol tarafta navigation
- Ortada liste görünümü
- Sağ tarafta detay içeriği

### 2. Layout Components

#### Navigation (app-navigation)
- Genişlik: 300px (sabit)
- Scroll edilebilir
- Her item için icon + text
- Active state belirgin olmalı
- Counter bubbles kullanılabilir

#### Content (app-content)
- Ana içerik alanı
- Responsive olmalı
- Padding: 20px
- Scroll edilebilir

#### List Item
- Hover state olmalı
- Icon + title + metadata
- Action buttons (3-dot menu)
- Avatar/icon sol tarafta

### 3. Atomic Components

#### Buttons
- `button-vue` class kullanılmalı
- Variants: primary, secondary, tertiary, danger
- Icon + text kombinasyonu
- Disabled state

#### Action Menu (3-dot menu)
- Popover kullanılmalı
- Edit, Delete gibi aksiyonlar
- Separator ile gruplandırma

#### Input Fields
- `form-control` class
- Label yukarıda
- Placeholder text
- Validation states

#### Empty Content
- Icon (büyük, muted color)
- Başlık
- Açıklama
- Call-to-action button

#### Counter Bubbles
- Küçük, yuvarlak
- Primary color
- Number gösterir

### 4. Tasarım Prensipleri

#### Renkler
- CSS variables kullanılmalı: `var(--color-main-background)`
- Primary: `var(--color-primary-element)`
- Text: `var(--color-main-text)`
- Muted: `var(--color-text-maxcontrast)`
- Border: `var(--color-border)`

#### Typography
- Font size: 14px (default)
- Line height: 1.5
- Font weight: 400 (normal), 500 (medium), 600 (semibold)

#### Spacing
- Padding: 16px, 20px
- Gap: 8px, 12px, 16px
- Border radius: `var(--border-radius-element)`

#### Icons
- Material Design Icons kullanılmalı
- Size: 18px, 20px, 24px
- Color: inherit veya specific

## 🔍 Mevcut Proje Tasarım Analizi

### Sorunlar

1. **Çok Fazla Tab**
   - 12 tab var: Overview, Tasks, Time Tracking, Files, Notes, Requirements, Challenges, Research, Financials, Linked Items, Sharing, Activity
   - Kullanıcı için kafa karıştırıcı
   - Bazı tab'lar birleştirilebilir

2. **Tab Organizasyonu**
   - Notes, Requirements, Challenges, Research ayrı tab'lar - bunlar birleştirilebilir
   - Files ve Notes benzer içerik - birleştirilebilir
   - Activity ve Overview benzer - birleştirilebilir
   - Tasks ve Time Tracking benzer içerik - birleştirilebilir
   - Sharing ayrı tab olmasına gerek yok - Linked Items ile birleştirilebilir

3. **Tasarım Tutarsızlıkları**
   - Bazı component'ler Nextcloud standartlarına uymuyor
   - Spacing tutarsız
   - Button stilleri karışık

### Öneriler

#### Tab Birleştirme Planı (Final - Güncellenmiş)

**Final Tab Yapısı (5 tab):**
1. **Overview** - Genel bakış, istatistikler, aktivite özeti, harcanan zaman özeti
2. **Tasks & Time** - Görevler ve Zaman takibi birleşik
3. **Documents** - Dosyalar + Notlar birleşik (kategoriler: General, Requirements, Challenges, Research)
4. **Financials** - Faturalar ve finansal bilgiler
5. **Linked & Sharing** - Bağlı öğeler (domains, hostings, websites, services) + Paylaşım ayarları

**Tab Detayları:**

**1. Overview Tab:**
- Proje istatistikleri (Client, Type, Status, Dates, Budget)
- Harcanan zaman özeti (toplam süre, kullanıcı bazlı breakdown)
- Son aktiviteler (özet - son 5-10 aktivite)
- Proje açıklaması ve notlar
- Activity log'un özet görünümü

**2. Tasks & Time Tab:**
- **Üst Bölüm: Görevler**
  - Görevler listesi
  - Görev ekleme/düzenleme
  - Görev durumu değiştirme
  - İlerleme çubuğu
- **Alt Bölüm: Zaman Takibi**
  - Timer kontrolleri (başlat/durdur)
  - Zaman girişleri listesi
  - Kullanıcı bazlı zaman özeti
  - Toplam harcanan zaman

**3. Documents Tab:**
- **Dosyalar Bölümü**
  - Dosya yükleme
  - Dosya listesi (kategorilere göre)
  - Dosya indirme/silme
- **Notlar Bölümü**
  - Not kategorileri: General, Requirements, Challenges, Research
  - Kategori filtreleme
  - Rich text editor
  - Not ekleme/düzenleme/silme

**4. Financials Tab:**
- Finansal özet kartları (toplam, ödenen, bekleyen)
- Fatura listesi
- Fatura oluşturma butonu
- Fatura detayları

**5. Linked & Sharing Tab:**
- **Bağlı Öğeler Bölümü**
  - Bağlı domains, hostings, websites, services listesi
  - Yeni öğe bağlama
  - Bağlantı kaldırma
- **Paylaşım Bölümü**
  - Paylaşılan kullanıcılar listesi
  - Kullanıcı ekleme/kaldırma
  - Paylaşım izinleri

## 🎨 Tasarım İyileştirme Planı

### 1. Tab Navigation İyileştirmesi
- 5 tab'a düşür (12'den)
- Icon + text kombinasyonu
- Active state belirgin
- Scroll edilebilir (mobil için)
- Nextcloud standart tab stilleri

### 2. Component Standardizasyonu
- Tüm button'lar `button-vue` class kullanmalı
- Tüm input'lar `form-control` class kullanmalı
- Empty state'ler standart olmalı
- List item'lar tutarlı olmalı
- Action menu'ler (3-dot) standart olmalı

### 3. Spacing ve Layout
- Consistent padding: 20px
- Consistent gap: 16px
- Grid layout kullanılmalı (stat cards için)
- Flexbox layout (list items için)
- Section'lar arası boşluk: 24px

### 4. Color ve Typography
- CSS variables kullanılmalı
- Text color: `var(--color-main-text)`
- Background: `var(--color-main-background)`
- Border: `var(--color-border)`
- Muted text: `var(--color-text-maxcontrast)`

### 5. Empty States
- Büyük icon (48px)
- Başlık
- Açıklama
- Call-to-action button
- Nextcloud standart empty content component

### 6. Loading States
- Skeleton screens veya spinner
- "Loading..." text
- Nextcloud standart loading component

### 7. Section Organization
- Her tab içinde section'lar net ayrılmalı
- Section başlıkları belirgin olmalı
- Section'lar arası separator kullanılabilir

## 📋 Uygulama Adımları

1. ✅ Tab sayısını azalt (12 → 5)
   - Overview: Genel bakış + zaman özeti + aktivite
   - Tasks & Time: Görevler + zaman takibi birleşik
   - Documents: Dosyalar + Notlar (tüm kategoriler)
   - Financials: Finansal bilgiler
   - Linked & Sharing: Bağlı öğeler + paylaşım
2. ✅ Tab navigation'ı iyileştir
3. ✅ Component'leri standardize et
4. ✅ Spacing ve layout'u düzelt
5. ✅ Empty state'leri iyileştir
6. ✅ Loading state'leri ekle
7. ✅ Color ve typography'yi düzelt

## 🔗 Referanslar

- [Nextcloud Design Guidelines](https://docs.nextcloud.com/server/latest/developer_manual/design/index.html)
- [Layout Patterns](https://docs.nextcloud.com/server/latest/developer_manual/design/layout.html)
- [Atomic Components](https://docs.nextcloud.com/server/latest/developer_manual/design/atomic_components.html)
- [Layout Components](https://docs.nextcloud.com/server/latest/developer_manual/design/layout_components.html)
