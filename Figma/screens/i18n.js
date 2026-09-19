/**
 * Internationalization (i18n) Engine for Souq Masr Screens
 * Plan 1 - Full Arabic (RTL) & English (LTR) Dual Support
 */

(function() {
  const dictionary = {
    // Top Bar & Global
    "تسجيل الدخول": "Sign In",
    "دخول إلى الحساب Sign In": "Sign In to Account",
    "إنشاء حساب جديد": "Create New Account",
    "ليس لديك حساب؟": "Don't have an account?",
    "لديك حساب بالفعل؟": "Already have an account?",
    "متابعة باستخدام Google": "Continue with Google",
    "أو الدخول عبر": "Or continue with",
    "الهاتف أو البريد": "Phone or Email",
    "كلمة المرور": "Password",
    "نسيت الكلمة؟": "Forgot Password?",
    "تذكرني": "Remember me",
    "الاسم الكامل": "Full Name",
    "وسيلة التسجيل": "Registration Method",
    "الهاتف أو البريد الإلكتروني": "Phone or Email",
    "الدولة": "Country",
    "تأكيد كلمة المرور": "Confirm Password",
    "Google": "Google",
    "Apple": "Apple",
    "التسجيل باستخدام Google أو Apple يتطلب OTP": "Google or Apple sign-up still requires OTP",
    "مستخدم": "User",
    "متجر": "Store",
    "إكسسوارات": "Accessories",
    "عربيات": "Cars",
    "رقم الهاتف المحمول": "Mobile Phone Number",
    "تأكيد ومتابعة": "Verify & Continue",
    "متابعة": "Continue",
    "تخطي": "Skip",
    "إلغاء": "Cancel",
    "حفظ": "Save",
    "تأكيد": "Confirm",
    "رفض": "Decline",
    "بحث": "Search",
    "بحث في الشاشات": "Search Screens",
    "ج.م": "EGP",
    "جنيه": "EGP",
    "ر.س": "SAR",

    // Navigation & Dock
    "الرئيسية": "Home",
    "الأقسام": "Categories",
    "السلة": "Cart",
    "طلباتي": "My Orders",
    "الطلبات": "Orders",
    "حسابي": "Profile",
    "إحصائيات": "Analytics",
    "المبيعات": "Sales",
    "إضافة منتج": "Add Product",
    "الإعلانات": "Ads",
    "المحادثات": "Messages",
    "محادثة": "Chat",
    "متجر": "Store",
    "لوحة التحكم": "Dashboard",

    // Screen 03: OTP
    "تأكيد رمز التحقق (OTP)": "Verify OTP Code",
    "رمز التحقق": "Verification Code",
    "أدخل الرمز المكون من 4 أرقام": "Enter the 4-digit code",
    "إعادة إرسال الرمز": "Resend Code",
    "استلام عبر WhatsApp": "Receive via WhatsApp",

    // Screen 04: Account Type
    "نوع الحساب": "Account Type",
    "اختر طبيعة حسابك": "Choose Account Nature",
    "حدد نوع النشاط المناسب لك للبدء بالمنصة": "Select the role that fits you to get started",
    "حساب مستخدم (متسوق)": "Customer Account (Shopper)",
    "تصفح قسمي الملابس والإلكترونيات، الشراء بسلال منفصلة حسب كل موزع، وتتبع شحناتك بسهولة.": "Browse fashion & electronics, shop with split carts per distributor, and track orders effortlessly.",
    "حساب موزع معتمد (تاجر)": "Verified Distributor Account (Vendor)",
    "امتلك متجراً، أضف منتجاتك بأسعارك وخصوماتك، أطلق إعلانات ممولة، واستلم أرباحك الصافية.": "Own a storefront, list products with custom pricing and discounts, launch ads, and withdraw net profits.",
    "محدد حالياً ✓": "Currently Selected ✓",
    "يتطلب اعتماد الإدارة": "Requires Admin Approval",
    "وفق متطلبات الخطة 1: يخضع حساب الموزع للمراجعة والتدقيق الإداري قبل اعتماده رسمياً.": "Per Plan 1 requirements: Distributor accounts undergo administrative review before formal approval.",
    "متابعة إعداد الحساب ←": "Continue Account Setup →",

    // Screen 05 & 06: Profiles & Setup
    "بيانات الحساب الشخصي": "Buyer Profile Setup",
    "إعداد وتوثيق متجر الموزع": "Distributor Store Setup",
    "اسم المتجر أو العلامة التجارية": "Store or Brand Name",
    "السجل التجاري": "Commercial Register",
    "البطاقة الضريبية": "Tax Card",
    "المحافظة": "Governorate",
    "المدينة": "City",
    "العنوان بالتفصيل": "Detailed Address",
    "رفع صورة أو ملف PDF": "Upload Image or PDF",
    "إرسال طلب الاعتماد والمتابعة": "Submit KYC for Approval",

    // Screen 07: Forgot Password
    "استعادة كلمة المرور": "Reset Password",
    "أدخل بريدك أو رقم هاتفك المسجل لاستلام رابط إعادة التعيين": "Enter registered email or mobile to receive reset link",
    "إرسال رابط الاستعادة": "Send Reset Link",
    "العودة لتسجيل الدخول": "Back to Sign In",

    // Screen 08 & 09: Marketplace Home & Catalog
    "الصفحة الرئيسية للماركت بليس": "Marketplace Home",
    "التوصيل إلى": "Deliver to",
    "القاهرة، مصر": "Cairo, Egypt",
    "عروض حصرية لفترة محدودة": "Exclusive Flash Deals",
    "خصم حتى 40%": "Up to 40% OFF",
    "تسوق الآن": "Shop Now",
    "الأقسام الأساسية": "Top Categories",
    "الأزياء والملابس": "Fashion & Apparel",
    "أزياء وملابس": "Fashion & Apparel",
    "الإلكترونيات": "Electronics",
    "إلكترونيات": "Electronics",
    "أجهزة منزلية": "Home Appliances",
    "عطور وتجميل": "Fragrances & Beauty",
    "الأكثر مبيعاً": "Best Sellers",
    "عروض مميزة": "Featured Deals",
    "أضف للسلة": "Add to Cart",
    "إضافة إلى السلة": "Add to Cart",
    "شراء فوري": "Buy Now",
    "شراء الآن": "Buy Now",
    "موزع معتمد": "Verified Distributor",
    "موزع معتمد ✓": "Verified Distributor ✓",
    "وفر": "Save",
    "جميع الأقسام": "All Categories",
    "الفرز والتصفية": "Sort & Filter",
    "الأحدث": "Newest",
    "الأعلى تقييماً": "Top Rated",
    "الأقل سعراً": "Lowest Price",

    // Screen 10: Search & Filters
    "البحث المتقدم والفلاتر": "Advanced Search & Filters",
    "نطاق السعر": "Price Range",
    "تقييم البائع": "Seller Rating",
    "شحن مجاني فقط": "Free Shipping Only",
    "تطبيق الفلاتر": "Apply Filters",
    "إعادة ضبط": "Reset",

    // Screen 11: Product Details
    "تفاصيل المنتج واختيار الموزع": "Product Details & Offers",
    "المواصفات": "Specifications",
    "المقاس": "Size",
    "اللون": "Color",
    "الكمية": "Quantity",
    "عروض الموزعين لنفس الصنف": "Distributor Offers for this Item",
    "تقييمات المشترين": "Customer Reviews",
    "أرخص عرض": "Best Value",
    "أسرع شحن": "Fastest Delivery",

    // Screen 12: Cart & Checkout
    "سلة المشتريات والدفع": "Cart & Checkout",
    "سلة المشتريات": "Shopping Cart",
    "سلة منفصلة للموزع": "Separate Distributor Cart",
    "كوبون الخصم": "Promo Code",
    "تطبيق الكوبون": "Apply Coupon",
    "ملخص الحساب": "Order Summary",
    "مجموع المنتجات": "Subtotal",
    "تكلفة التوصيل": "Shipping Fee",
    "ضريبة القيمة المضافة": "VAT (14%)",
    "الإجمالي": "Total",
    "اختر طريقة الدفع": "Select Payment Method",
    "فودافون كاش ومحافظ المحمول": "Vodafone Cash & Wallets",
    "بطاقة ائتمان / ميزة": "Bank Card / Meeza",
    "إنستاباي Instapay": "Instapay",
    "الدفع عند الاستلام": "Cash on Delivery (COD)",
    "إتمام الطلب والدفع": "Complete Order & Checkout",

    // Screen 13: Tracking
    "تتبع الطلب وحالة الشحن": "Order Tracking & Delivery",
    "حالة الشحنة": "Shipment Status",
    "تم تأكيد الطلب": "Order Confirmed",
    "جاري التجهيز": "Preparing in Warehouse",
    "تم تسليم الشحنة للمندوب": "Handed to Courier",
    "في الطريق إليك": "Out for Delivery",
    "تم الاستلام بنجاح": "Successfully Delivered",
    "بيانات مندوب التوصيل": "Courier Information",
    "اتصال": "Call",
    "واتساب": "WhatsApp",
    "الفاتورة الإلكترونية": "Digital Invoice",

    // Screen 14 & 15: Live Chat & Inbox
    "محادثة فورية مع الموزع": "Direct Vendor Chat",
    "متصل الآن": "Online",
    "اكتب رسالتك هنا...": "Type your message...",
    "إرسال": "Send",
    "رسائل الموزعين": "Vendor Messages",
    "كل المحادثات": "All Conversations",
    "غير مقروءة": "Unread",

    // Screen 16: Dispute & Reviews
    "التقييمات وبلاغات فض النزاع": "Reviews & Disputes",
    "تقييمك للمنتج والتاجر": "Rate Product & Vendor",
    "فتح بلاغ نزاع رسمي": "Open Formal Dispute",
    "حماية المشتري لمدة 14 يوماً": "14-Day Buyer Protection",
    "سبب تقديم البلاغ": "Reason for Claim",
    "إرفاق صور المعاينة": "Attach Evidence Photos",
    "إرسال البلاغ للإدارة": "Submit Claim to Admin",

    // Screen 17: Distributor Dashboard
    "لوحة تحكم الموزع والأرباح": "Distributor KPI Dashboard",
    "صافي الأرباح الجاهزة للسحب": "Withdrawable Net Profits",
    "طلب سحب أرباح": "Request Payout",
    "مبيعات هذا الشهر": "This Month Sales",
    "إجمالي الطلبات": "Total Orders",
    "تقييم المتجر": "Store Rating",
    "تنبيهات نقص المخزون": "Low Stock Alerts",
    "الطلبات الجديدة": "New Orders",
    "تجهيز وشحن": "Process & Ship",

    // Screen 18: Add Product
    "إضافة وتعديل منتج جديد": "Add & Edit Product",
    "اسم المنتج": "Product Title",
    "سعر البيع": "Selling Price",
    "السعر قبل الخصم": "Original Price",
    "القسم": "Category",
    "الكمية المتوفرة بالمخزون": "Available Stock",
    "رمز المنتج (SKU)": "Product SKU",
    "صور المنتج": "Product Photos",
    "حفظ كمسودة": "Save as Draft",
    "حفظ ونشر المنتج": "Save & Publish Product",

    // Screen 19: Ads Management
    "إدارة الحملات الإعلانية الممولة": "Sponsored Ads Management",
    "رصيد المحفظة الإعلانية": "Ad Wallet Balance",
    "شحن رصيد المحفظة": "Top-up Ad Wallet",
    "الحملات النشطة": "Active Campaigns",
    "إنشاء حملة إعلانية جديدة": "Create New Campaign",
    "الميزانية اليومية": "Daily Budget",
    "مرات الظهور": "Impressions",
    "النقرات": "Clicks",
    "معدل التحويل (CTR)": "CTR & Conversions",

    // Screen 20: Admin Approvals
    "لوحة الإدارة والاعتمادات": "Admin Approvals & KYC",
    "طلبات توثيق الموزعين الجديدة": "Distributor KYC Queue",
    "مراجعة الأوراق الثبوتية": "Review Legal Documents",
    "اعتماد وتفعيل المتجر": "Approve & Activate",
    "رفض الطلب مع إشعار": "Reject with Note",
    "قضايا النزاع المعلقة": "Pending Disputes",
    "استرداد المبلغ للمشتري": "Refund to Buyer",
    "حفظ الشكوى لصالح الموزع": "Resolve for Vendor"
  };

  function getActiveLang() {
    const params = new URLSearchParams(window.location.search);
    return params.get('lang') || localStorage.getItem('souq_lang') || 'ar';
  }

  function applyLanguage(lang) {
    const isEn = lang === 'en';
    document.documentElement.lang = isEn ? 'en' : 'ar';
    document.documentElement.dir = isEn ? 'ltr' : 'rtl';
    
    if (isEn) {
      document.body.classList.add('lang-en');
      document.body.classList.remove('lang-ar');
    } else {
      document.body.classList.add('lang-ar');
      document.body.classList.remove('lang-en');
    }

    try {
      localStorage.setItem('souq_lang', lang);
    } catch (e) {}

    // 1. Process explicit data-en / data-ar attributes
    document.querySelectorAll('[data-en]').forEach(el => {
      if (!el.getAttribute('data-ar')) {
        el.setAttribute('data-ar', el.innerText.trim());
      }
      el.innerText = isEn ? el.getAttribute('data-en') : el.getAttribute('data-ar');
    });

    // 2. Process placeholder data attributes: data-placeholder-en
    document.querySelectorAll('[data-placeholder-en]').forEach(el => {
      if (!el.getAttribute('data-placeholder-ar')) {
        el.setAttribute('data-placeholder-ar', el.placeholder);
      }
      el.placeholder = isEn ? el.getAttribute('data-placeholder-en') : el.getAttribute('data-placeholder-ar');
    });

    // 3. Process inputs & text placeholders
    document.querySelectorAll('input, textarea').forEach(input => {
      const ph = input.placeholder;
      if (!ph) return;
      if (isEn) {
        if (!input._origPlaceholder) input._origPlaceholder = ph;
        if (dictionary[ph]) input.placeholder = dictionary[ph];
      } else {
        if (input._origPlaceholder) input.placeholder = input._origPlaceholder;
      }
    });

    // 4. Translate matching text nodes via dictionary
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
    let node;
    while ((node = walker.nextNode())) {
      // Don't modify script or style tags
      if (node.parentElement && (node.parentElement.tagName === 'SCRIPT' || node.parentElement.tagName === 'STYLE')) {
        continue;
      }

      const txt = node.nodeValue.trim();
      if (!txt) continue;

      if (isEn) {
        if (dictionary[txt]) {
          if (!node._originalAr) node._originalAr = txt;
          node.nodeValue = node.nodeValue.replace(txt, dictionary[txt]);
        }
      } else {
        if (node._originalAr) {
          node.nodeValue = node.nodeValue.replace(txt, node._originalAr);
        }
      }
    }

    // 5. Directional adjustments for back buttons
    document.querySelectorAll('.back-btn').forEach(btn => {
      btn.style.transform = isEn ? 'scaleX(-1)' : 'none';
    });

    // Notify parent if inside iframe
    if (window.parent && window.parent !== window) {
      try {
        window.parent.postMessage({ type: 'LANG_CHANGED', lang: lang }, '*');
      } catch (e) {}
    }
  }

  // Theme (Dark Mode / Light Mode) Management
  function getActiveTheme() {
    const params = new URLSearchParams(window.location.search);
    return params.get('theme') || localStorage.getItem('souq_theme') || 'light';
  }

  function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    if (isDark) {
      document.body.classList.add('dark-mode');
    } else {
      document.body.classList.remove('dark-mode');
    }

    try {
      localStorage.setItem('souq_theme', theme);
    } catch (e) {}

    const btnTheme = document.getElementById('btnStandaloneTheme');
    if (btnTheme) {
      btnTheme.innerText = isDark ? '☀️' : '🌙';
      btnTheme.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }
  }

  // Listen for parent messages (Language & Theme)
  window.addEventListener('message', (e) => {
    if (e.data && e.data.type === 'SET_LANG') {
      applyLanguage(e.data.lang);
    }
    if (e.data && e.data.type === 'SET_THEME') {
      applyTheme(e.data.theme);
    }
  });

  // Standalone floating switcher if loaded directly outside iframe
  function injectStandaloneSwitcher() {
    if (window.self !== window.top) return; // Inside iframe, parent controls it

    if (document.getElementById('standaloneLangSwitch')) return;

    const switcher = document.createElement('div');
    switcher.id = 'standaloneLangSwitch';
    switcher.style.cssText = `
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 9999;
      background: #0F172A;
      color: #FFFFFF;
      border-radius: 30px;
      padding: 4px 8px;
      display: flex;
      align-items: center;
      gap: 4px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.35);
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      font-size: 11px;
      font-weight: 800;
      border: 1px solid rgba(255,255,255,0.15);
    `;

    switcher.innerHTML = `
      <button id="btnLangAr" style="background:transparent; color:#FFF; border:none; padding:4px 10px; border-radius:20px; cursor:pointer; font-weight:800; transition:all .2s;">عربي</button>
      <button id="btnLangEn" style="background:transparent; color:#FFF; border:none; padding:4px 10px; border-radius:20px; cursor:pointer; font-weight:800; transition:all .2s;">English</button>
      <span style="width:1px; height:14px; background:rgba(255,255,255,0.25); margin:0 2px;"></span>
      <button id="btnStandaloneTheme" style="background:transparent; color:#FFF; border:none; padding:4px 8px; border-radius:20px; cursor:pointer; font-size:13px; transition:all .2s;">🌙</button>
    `;

    document.body.appendChild(switcher);

    const updateBtns = (lang) => {
      const btnAr = document.getElementById('btnLangAr');
      const btnEn = document.getElementById('btnLangEn');
      if (!btnAr || !btnEn) return;
      if (lang === 'en') {
        btnEn.style.background = '#2563EB';
        btnAr.style.background = 'transparent';
      } else {
        btnAr.style.background = '#2563EB';
        btnEn.style.background = 'transparent';
      }
    };

    document.getElementById('btnLangAr').onclick = () => { applyLanguage('ar'); updateBtns('ar'); };
    document.getElementById('btnLangEn').onclick = () => { applyLanguage('en'); updateBtns('en'); };
    document.getElementById('btnStandaloneTheme').onclick = () => {
      const nextTheme = getActiveTheme() === 'dark' ? 'light' : 'dark';
      applyTheme(nextTheme);
    };

    updateBtns(getActiveLang());
    applyTheme(getActiveTheme());
  }

  // Run on DOM ready
  function init() {
    applyTheme(getActiveTheme());
    applyLanguage(getActiveLang());
    injectStandaloneSwitcher();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Export globally
  window.setAppLanguage = applyLanguage;
  window.getAppLanguage = getActiveLang;
  window.setAppTheme = applyTheme;
  window.getAppTheme = getActiveTheme;
})();
