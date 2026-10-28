---
version: "1.0"
name: "سعي - Saey Design System"
description: "نظام التصميم الرقمي لمنصة سعي - تجارة وتوزيع بهوية أسود Uber، مع دعم كامل للغة العربية واتجاه RTL ومقاسات شاشات الهاتف الذكي"
platform: "mobile"
direction: "rtl"
viewport:
  width: 440
  height: 956
  scale: "iPhone 17 Pro Max"
colors:
  primary: "#000000"
  primary-hover: "#111111"
  surface: "#FFFFFF"
  background: "#F7F9FC"
  neutral-light: "#F2F2F2"
  border: "#E1E8F1"
  border-subtle: "#E2E8F0"
  text-primary: "#0F172A"
  text-secondary: "#64748B"
  text-muted: "#94A3B8"
  success: "#10B981"
  success-bg: "#ECFDF5"
  success-text: "#047857"
  warning: "#F59E0B"
  warning-bg: "#FFFBEB"
  warning-text: "#B45309"
  error: "#EF4444"
  error-bg: "#FEF2F2"
  error-text: "#B91C1C"
typography:
  headline-lg:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "24px"
    fontWeight: 900
    lineHeight: 1.3
  headline-md:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "18px"
    fontWeight: 800
    lineHeight: 1.4
  card-title:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 800
    lineHeight: 1.5
  body-md:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 500
    lineHeight: 1.6
  body-sm:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "12px"
    fontWeight: 500
    lineHeight: 1.5
  label-lg:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 800
    lineHeight: 1.2
  label-md:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "12px"
    fontWeight: 700
    lineHeight: 1.4
  caption:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "11px"
    fontWeight: 600
    lineHeight: 1.5
  value-highlight:
    fontFamily: "Inter, Cairo, system-ui, sans-serif"
    fontSize: "20px"
    fontWeight: 900
    lineHeight: 1.3
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  base: "16px"
  lg: "20px"
  xl: "24px"
  xxl: "32px"
rounded:
  none: "0px"
  xs: "6px"
  sm: "8px"
  md: "10px"
  lg: "12px"
  xl: "16px"
  sheet: "24px"
  full: "9999px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#FFFFFF"
    rounded: "{rounded.md}"
    height: "48px"
    padding: "16px"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
    textColor: "#FFFFFF"
    rounded: "{rounded.md}"
    height: "48px"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.md}"
    height: "48px"
    padding: "16px"
  input-field:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.md}"
    height: "48px"
    padding: "12px"
  card-base:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.xl}"
    padding: "16px"
  badge-success:
    backgroundColor: "{colors.success-bg}"
    textColor: "{colors.success-text}"
    rounded: "{rounded.full}"
    padding: "8px"
  badge-warning:
    backgroundColor: "{colors.warning-bg}"
    textColor: "{colors.warning-text}"
    rounded: "{rounded.full}"
    padding: "8px"
  badge-error:
    backgroundColor: "{colors.error-bg}"
    textColor: "{colors.error-text}"
    rounded: "{rounded.full}"
    padding: "8px"
  badge-neutral:
    backgroundColor: "{colors.neutral-light}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.full}"
    padding: "8px"
  status-dot-success:
    backgroundColor: "{colors.success}"
    rounded: "{rounded.full}"
    size: "7px"
  status-dot-warning:
    backgroundColor: "{colors.warning}"
    rounded: "{rounded.full}"
    size: "7px"
  status-dot-error:
    backgroundColor: "{colors.error}"
    rounded: "{rounded.full}"
    size: "7px"
  border-line:
    backgroundColor: "{colors.border}"
    height: "1px"
  divider-subtle:
    backgroundColor: "{colors.border-subtle}"
    height: "1px"
  caption-muted:
    textColor: "{colors.text-muted}"
    typography: "{typography.caption}"
  card-description:
    textColor: "{colors.text-secondary}"
    typography: "{typography.body-sm}"
---

# Overview

نظام التصميم الرقمي لمنصة **سعي** (Saey Design System)، المصمم كمرجع بصري وتقني معتمد لإنشاء وتطوير واجهات الهواتف الذكية وتجارب المستخدمين والتجار.

- **اسم المنتج:** سعي
- **نوع المنصة:** تطبيق تجارة وتوزيع متعدد الأطراف (مستخدم شخصي، متجر/موزع معتمد).
- **اللغة والاتجاه:** اللغة العربية الافتراضية مع دعم كامل لاتجاه اليمين لليسار (`dir="rtl"`).
- **فلسفة التصميم:** بساطة هندسية مستوحاة من فلسفة Uber باللون الأسود والأبيض والأسطح الواضحة، مخصصة للمستخدم العربي لتوفير أعلى درجات الثقة وسهولة الاستخدام وسرعة الإنجاز.
- **الرحلة الأساسية:** شراء مباشر وسريع («شراء الآن») بدون تعقيدات سلة الشراء التقليدية، مع استلام محدد من المتجر وإيصال رسمي وتأكيد أمان.

---

## Colors

تعتمد منصة سعي على تباين قوي ومدروس يركز على الأسود كلون إجراء رئيسي، مع خلفيات فاتحة وأسطح بيضاء نظيفة، وحصر الألوان المضيئة على مؤشرات الحالة الوظيفية فقط.

### Color Usage Rules
- **الأسود الأساسي {colors.primary}:** مخصص حصرياً لأزرار الإجراء الرئيسي (Primary CTA)، التبويب النشط، والأيقونات المفعلة.
- **الأسطح {colors.surface}:** بطاقات المحتوى، الحقول، القوائم المنبثقة، وتكون بيضاء نقية مع حدود واضحة {colors.border}.
- **الخلفية {colors.background}:** رمادي مزرق فاتح جداً يوفر عمقاً مريحاً للعين وتمايزاً عن البطاقات البيضاء.
- **ألوان الحالة (Success / Warning / Error):** للاستخدام الدلالي فقط في الشارات والتنبيهات، وممنوع استخدامها كألوان خلفيات عامة أو أزرار تنقل عادية.

---

## Typography

نظام خطوط مزدوج يجمع بين الرصانة العربية ودقة الأرقام والبيانات التقنية:
- **النص العربي:** يعتمد كلياً على عائلة خط **Cairo** المفتوح ذو الوضوح الفائق على الشاشات المحمولة.
- **الأرقام والأكواد:** تعتمد على عائلة **Inter** للأرقام، التواريخ، أسعار المنتجات، نسب الخصم، أكواد الكوبونات، وأرقام الـ IBAN.
- **الترتيب المعتمد للخط:** `'Inter', 'Cairo', system-ui, -apple-system, sans-serif`.

- الحد الأدنى للنص العربي هو `11px`، والافتراضي للنصوص الثانوية والبيانات المساندة هو `12px`.
- الأرقام والأسعار وأكواد الطلبات والبطاقات اللاتينية تحتفظ باتجاه `dir="ltr"` حتى داخل السياق العربي.

---

## Layout

يعتمد النظام على مقاس مرجعي لشاشات الهاتف المتطورة مع اختبار مستمر للمقاسات المتوسطة والصغيرة.

### Viewport Targets
- **المقاس المرجعي:** `440 × 956px` (iPhone 17 Pro Max) بنسبة عرض `440 / 956`.
- **هاتف متوسط:** `393 × 852px` (iPhone 16 / Pixel).
- **هاتف مدمج:** `360 × 800px` (شاشات أندرويد الاقتصادية).

### Spacing Rules
- حافة المحتوى الجانبية: `16px` (تقل إلى `12px` على الشاشات المدمجة `360px`).
- المسافة بين حقول الإدخال والبطاقات: `10–12px`.
- المسافة بين الأقسام الرئيسية: `20–24px`، وتُضبط بين `12–16px` في الصفحة الرئيسية لتحقيق انسيابية وتجنب التمرير الزائد.
- المسافة بين الأيقونة والنص المساعد: `6–8px`.

---

## Elevation & Depth

يعتمد سعي على العمق الهادئ باستخدام الحدود الدقيقة والظلال الميكروسكوبية، دون إثقال الواجهة بظلال داكنة متعددة الطبقات.

- **ظل البطاقات الافتراضي:** `0 4px 14px rgba(15, 23, 42, 0.035)`
- **ظل التفاعل والتحويم:** `0 9px 24px rgba(15, 23, 42, 0.08)` مع حركة `translateY(-1px)`
- **ظل الزر الرئيسي:** `0 8px 18px rgba(0, 0, 0, 0.18)`
- **القوائم المنبثقة والـ Bottom Sheets:** `0 14px 32px rgba(15, 23, 42, 0.18)`
- **خلفية التعتيم (Backdrop Overlay):** `rgba(15, 23, 42, 0.45)`

---

## Shapes

لغة الأشكال محددة بنعومة هندسية مدروسة مع تناسق ثابت بين المكونات.

- **الحقول والأزرار الأساسية:** نصف قطر `10px` {rounded.md}.
- **الحاويات المدمجة والتبويبات:** نصف قطر `12px` {rounded.lg}.
- **البطاقات الرئيسية:** نصف قطر `16px` {rounded.xl} (والبطاقات الفرعية `14px`).
- **القوائم السفلية (Bottom Sheets):** نصف قطر علوي `24px` {rounded.sheet} مع مقبض سحب `38×4px`.
- **شارات الحالة والأزرار الدائرية:** كامل الاستدارة `9999px` {rounded.full}.

---

## Components

مواصفات الذرات والمكونات الأساسية لتوليد وتطوير الشاشات في Stitch.

### 1. الأزرار (Buttons)
- **الزر الرئيسي:** ارتفاع `48px`، لون أسود `#000000`، نص أبيض عريض `14–16px` بوزن `800`، ونصف قطر `10px`.
- **الزر الثانوي:** ارتفاع `48px`، خلفية بيضاء، حد محايد `#E1E8F1`، ونص داكن `#0F172A`.
- **زر الحذف/الخطر:** أحمر `#EF4444` ويُستخدم حصراً للعمليات المدمرة التي تتطلب تأكيداً.
- **أزرار الأيقونات:** مساحة لمس لا تقل عن `40×40px` في الهيدر و`32×32px` داخل البطاقات.

### 2. حقول الإدخال (Inputs)
- ارتفاع `48–54px`، خلفية بيضاء، نصف قطر `10px`، حد محايد `1px`.
- التسمية العلوية: بمحاذاة اليمين، بحجم `12px` ووزن `800`.
- عند التركيز (Focus): حد أسود وهالة خارجية `0 0 0 3px rgba(0, 0, 0, 0.07)`.
- حقل الهاتف يحتوي اختيار الدولة (مصر `+20` افتراضياً، السعودية `+966`، الإمارات `+971`).

### 3. البطاقات (Cards)
- خلفية بيضاء، حد محايد `1px solid #E1E8F1`، نصف قطر `16px` وحشو `14–16px`.
- البطاقات القابلة للنقر تدعم الوصولية (`role="link"` و`tabindex="0"`) وتتحرك للأعلى بمقدار `1px` عند التحويم.
- بطاقات الحالة تتضمن شارة نصية ملونة وخط جانبي داخلي بعرض `3px` من جهة اليمين.

### 4. الهيدر وشريط التنقل
- **هيدر الشاشة:** شبكة ثلاثية متوازنة `grid-template-columns: 42px 1fr 42px`؛ زر الرجوع بحجم `40×40px` على اليمين، العنوان في المنتصف، وزر الإجراء الإضافي على اليسار.
- **شريط التنقل السفلي:** بطاقة عائمة بيضاء بحواف مستديرة وأيقونات SVG خطية ثابتة بدون نص سفلي مزاحم.
- **الأيقونات:** رسومات SVG خطية نظيفة بحجم `20–24px` وسمك خط `1.8px` بنهايات دائرية (`round`). يمنع استخدام الـ Emojis كأيقونات واجهة.

---

## Do's and Don'ts

قواعد صارمة لحماية هوية المنصة ومنع التشتت البصري (UI Drift) عند استخدام وكلاء الذكاء الاصطناعي:

- **افعل (Do):**
  - التزم باتجاه اليمين إلى اليسار (`RTL`) واللغة العربية في كافة الشاشات.
  - استخدم Cairo للنصوص العربية وInter للأرقام والأسعار وأكواد التحقق.
  - اجعل الأسود هو لون الزر الأساسي دائماً ليعكس هوية سعي.
  - حافظ على مساحة لمس مريحة لا تقل عن `44px` لجميع الأزرار التفاعلية.
  - اعتمد تدفق الشراء المباشر («شراء الآن») وتأكيد الاستلام من المتجر.

- **لا تفعل (Don't):**
  - لا تستخدم البنفسجي، الأزرق التجاري الصارخ، أو التدرجات المشبعة كألوان هوية.
  - لا تخلط أنصاف أقطار عشوائية في الشاشة نفسها (مثل دمج `4px` مع `20px` بدون نظام).
  - لا تستخدم أيقونات Emoji بدلاً من أيقونات SVG الخطية القياسية.
  - لا تصغر النص العربي إلى أقل من `11px` إطلاقاً.
  - لا تنشئ سلة مشتريات متعددة المتاجر كخطوة إجبارية؛ الرحلة الحالية مبنية على الشراء المباشر من صفحة المنتج.
