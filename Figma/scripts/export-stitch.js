import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const baseDir = path.resolve(__dirname, '..');
const screensDir = path.join(baseDir, 'screens');
const exportDir = path.join(baseDir, 'stitch_export');
const exportScreensDir = path.join(exportDir, 'screens');

if (!fs.existsSync(exportScreensDir)) {
  fs.mkdirSync(exportScreensDir, { recursive: true });
}

// Key representative screens for Stitch
const targetScreens = [
  // Auth & Onboarding
  '01-login.html',
  '02-signup.html',
  '03-verify-otp.html',
  '04-account-type.html',
  '05-profile-setup.html',
  // Shopper Flow
  '08-user-home.html',
  '09-categories.html',
  '10-featured-products.html',
  '11-featured-stores.html',
  '12-product-details.html',
  '13-notifications.html',
  '14-chats.html',
  '15-account.html',
  '16-orders.html',
  '17-wallet.html',
  '18-wallet-transactions.html',
  '20-checkout.html',
  '21-order-details.html',
  '26-order-receipt.html',
  // Merchant Flow
  '06-distributor-setup.html',
  '17-distributor-dashboard.html',
  '18-add-product.html',
  '18-distributor-products.html',
  '19-distributor-ads.html',
  '20-distributor-coupons.html',
  '20-distributor-reviews.html',
  '21-distributor-orders.html',
  '21-distributor-order-details.html',
  '22-distributor-finance.html',
  '22-distributor-inventory.html',
  '23-distributor-disputes.html'
];

// Helper to convert image asset to data URI or inline
function getAssetDataUri(filePath) {
  if (!fs.existsSync(filePath)) return null;
  const ext = path.extname(filePath).toLowerCase();
  const fileData = fs.readFileSync(filePath);
  if (ext === '.svg') {
    return `data:image/svg+xml;utf8,${encodeURIComponent(fileData.toString('utf-8'))}`;
  } else if (ext === '.png') {
    return `data:image/png;base64,${fileData.toString('base64')}`;
  } else if (ext === '.jpg' || ext === '.jpeg') {
    return `data:image/jpeg;base64,${fileData.toString('base64')}`;
  }
  return null;
}

console.log('🔄 جاري تجهيز شاشات سعي كملفات مستقلة (Self-contained) لـ Google Stitch...');

let processedCount = 0;

for (const screenName of targetScreens) {
  const srcPath = path.join(screensDir, screenName);
  if (!fs.existsSync(srcPath)) {
    console.warn(`⚠️ ملف غير موجود: ${screenName}`);
    continue;
  }

  let html = fs.readFileSync(srcPath, 'utf-8');

  // 1. Inline CSS stylesheets
  const cssRegex = /<link\s+rel=["']stylesheet["']\s+href=["']([^"']+)["']\s*\/?>/gi;
  html = html.replace(cssRegex, (match, href) => {
    // If it's a remote URL, keep as is
    if (href.startsWith('http://') || href.startsWith('https://')) {
      return match;
    }
    const cssPath = path.join(screensDir, href);
    if (fs.existsSync(cssPath)) {
      const cssContent = fs.readFileSync(cssPath, 'utf-8');
      return `<style data-source="${href}">\n${cssContent}\n</style>`;
    }
    return match;
  });

  // 2. Replace local images with data URIs
  const imgRegex = /<img\s+([^>]*?)src=["']([^"']+)["']([^>]*?)>/gi;
  html = html.replace(imgRegex, (match, before, src, after) => {
    if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:')) {
      return match;
    }
    const assetPath = path.join(screensDir, src);
    const dataUri = getAssetDataUri(assetPath);
    if (dataUri) {
      return `<img ${before}src="${dataUri}"${after}>`;
    }
    return match;
  });

  // 3. Ensure Inter and Cairo fonts are loaded
  if (!html.includes('family=Cairo')) {
    html = html.replace(
      '<head>',
      `<head>\n  <link rel="preconnect" href="https://fonts.googleapis.com">\n  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">`
    );
  }

  const outPath = path.join(exportScreensDir, screenName);
  fs.writeFileSync(outPath, html, 'utf-8');
  processedCount++;
}

console.log(`✅ تم تجهيز ${processedCount} شاشة مدمجة كلياً في ${exportScreensDir}`);

// Copy DESIGN.md
const designMdSrc = path.join(baseDir, 'DESIGN.md');
const designMdDest = path.join(exportDir, 'DESIGN.md');
if (fs.existsSync(designMdSrc)) {
  fs.copyFileSync(designMdSrc, designMdDest);
  console.log('✅ تم نسخ DESIGN.md القياسي إلى حزمة التصدير.');
}

// Generate Manifest JSON for programmatic SDK upload if needed
const manifest = {
  project: 'سعي - Saey',
  version: '1.0',
  description: 'منصة التجارة والتوزيع - شاشات مرجعية ونظام تصميم معتمد لـ Google Stitch',
  designSystem: 'DESIGN.md',
  screens: targetScreens.map(filename => ({
    file: `screens/${filename}`,
    name: filename.replace(/^\d+-/, '').replace('.html', '').replace(/-/g, ' ')
  }))
};
fs.writeFileSync(path.join(exportDir, 'stitch-manifest.json'), JSON.stringify(manifest, null, 2), 'utf-8');
console.log('✅ تم إنشاء stitch-manifest.json للمزامنة البرمجية.');
