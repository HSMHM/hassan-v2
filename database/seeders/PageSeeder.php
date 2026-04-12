<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'privacy'],
            [
                'title_ar' => 'سياسة الخصوصية',
                'title_en' => 'Privacy Policy',
                'meta_title_ar' => 'سياسة الخصوصية',
                'meta_title_en' => 'Privacy Policy',
                'meta_description_ar' => 'سياسة الخصوصية لموقع حسان المالكي - كيف نجمع ونستخدم ونحمي بياناتك الشخصية.',
                'meta_description_en' => 'Privacy Policy for Hassan Almalki website - how we collect, use, and protect your personal data.',
                'content_ar' => self::privacyAr(),
                'content_en' => self::privacyEn(),
                'is_published' => true,
            ]
        );
    }

    private static function privacyAr(): string
    {
        return <<<'HTML'
<h2>مقدمة</h2>
<p>نحن في موقع حسان المالكي (<a href="https://almalki.sa">almalki.sa</a>) نلتزم بحماية خصوصيتك. توضّح هذه السياسة كيفية جمع بياناتك واستخدامها وحمايتها عند زيارتك لموقعنا أو التفاعل مع خدماتنا.</p>

<h2>البيانات التي نجمعها</h2>
<h3>بيانات تقدمها أنت مباشرة</h3>
<ul>
    <li><strong>نموذج التواصل:</strong> الاسم، البريد الإلكتروني، رقم الجوال (اختياري)، ونص الرسالة.</li>
</ul>

<h3>بيانات تُجمع تلقائياً</h3>
<ul>
    <li><strong>بيانات التصفح:</strong> عنوان IP، نوع المتصفح، نظام التشغيل، والصفحات التي تمت زيارتها.</li>
    <li><strong>ملفات تعريف الارتباط:</strong> نستخدم ملفات الكوكيز الضرورية فقط لتشغيل الموقع بشكل سليم (مثل تفضيل اللغة).</li>
</ul>

<h2>كيف نستخدم بياناتك</h2>
<ul>
    <li>الرد على استفساراتك ورسائلك عبر نموذج التواصل.</li>
    <li>تحسين تجربة استخدام الموقع وتطوير المحتوى.</li>
    <li>حماية الموقع من الاستخدام غير المشروع (مثل منع الرسائل المزعجة).</li>
</ul>

<h2>مشاركة البيانات مع أطراف ثالثة</h2>
<p>لا نبيع بياناتك الشخصية ولا نشاركها مع أي جهة لأغراض تسويقية. قد نستخدم الخدمات التالية لتشغيل الموقع:</p>
<ul>
    <li><strong>Cloudflare Turnstile:</strong> لحماية نماذج الموقع من الروبوتات.</li>
    <li><strong>منصات التواصل الاجتماعي (Meta، X، LinkedIn):</strong> لنشر المحتوى على حساباتنا الرسمية فقط.</li>
</ul>

<h2>حماية البيانات</h2>
<p>نتخذ تدابير أمنية مناسبة لحماية بياناتك، تشمل:</p>
<ul>
    <li>تشفير الاتصال عبر بروتوكول HTTPS.</li>
    <li>تقييد الوصول إلى البيانات الشخصية على المسؤولين المصرح لهم فقط.</li>
    <li>تطبيق رؤوس أمان HTTP لحماية المتصفح.</li>
</ul>

<h2>حقوقك</h2>
<p>يحق لك:</p>
<ul>
    <li>طلب الاطلاع على بياناتك الشخصية المخزنة لدينا.</li>
    <li>طلب تصحيح أو حذف بياناتك.</li>
    <li>الاعتراض على معالجة بياناتك.</li>
</ul>
<p>لممارسة أي من هذه الحقوق، تواصل معنا عبر <a href="/contact">صفحة التواصل</a>.</p>

<h2>الروابط الخارجية</h2>
<p>قد يحتوي الموقع على روابط لمواقع خارجية. نحن غير مسؤولين عن سياسات الخصوصية أو محتوى تلك المواقع.</p>

<h2>تحديث السياسة</h2>
<p>قد نحدّث هذه السياسة من وقت لآخر. سيتم نشر أي تغييرات على هذه الصفحة مع تحديث تاريخ آخر تعديل.</p>

<h2>تواصل معنا</h2>
<p>إذا كان لديك أي استفسار حول سياسة الخصوصية، يمكنك التواصل معنا عبر <a href="/contact">صفحة التواصل</a> أو عبر البريد الإلكتروني: <a href="mailto:hassan@almalki.sa">hassan@almalki.sa</a>.</p>
HTML;
    }

    private static function privacyEn(): string
    {
        return <<<'HTML'
<h2>Introduction</h2>
<p>At Hassan Almalki's website (<a href="https://almalki.sa">almalki.sa</a>), we are committed to protecting your privacy. This policy explains how we collect, use, and protect your data when you visit our website or interact with our services.</p>

<h2>Data We Collect</h2>
<h3>Data You Provide Directly</h3>
<ul>
    <li><strong>Contact Form:</strong> Name, email address, phone number (optional), and message content.</li>
</ul>

<h3>Data Collected Automatically</h3>
<ul>
    <li><strong>Browsing Data:</strong> IP address, browser type, operating system, and pages visited.</li>
    <li><strong>Cookies:</strong> We only use essential cookies required for the website to function properly (such as language preference).</li>
</ul>

<h2>How We Use Your Data</h2>
<ul>
    <li>Responding to your inquiries and messages via the contact form.</li>
    <li>Improving the website experience and developing content.</li>
    <li>Protecting the website from unauthorized use (such as spam prevention).</li>
</ul>

<h2>Sharing Data with Third Parties</h2>
<p>We do not sell or share your personal data with any third party for marketing purposes. We may use the following services to operate the website:</p>
<ul>
    <li><strong>Cloudflare Turnstile:</strong> To protect website forms from bots.</li>
    <li><strong>Social Media Platforms (Meta, X, LinkedIn):</strong> To publish content on our official accounts only.</li>
</ul>

<h2>Data Protection</h2>
<p>We implement appropriate security measures to protect your data, including:</p>
<ul>
    <li>Encrypted connections via HTTPS protocol.</li>
    <li>Restricted access to personal data to authorized administrators only.</li>
    <li>HTTP security headers for browser protection.</li>
</ul>

<h2>Your Rights</h2>
<p>You have the right to:</p>
<ul>
    <li>Request access to your personal data stored with us.</li>
    <li>Request correction or deletion of your data.</li>
    <li>Object to the processing of your data.</li>
</ul>
<p>To exercise any of these rights, contact us via our <a href="/en/contact">contact page</a>.</p>

<h2>External Links</h2>
<p>The website may contain links to external websites. We are not responsible for the privacy policies or content of those websites.</p>

<h2>Policy Updates</h2>
<p>We may update this policy from time to time. Any changes will be posted on this page with an updated modification date.</p>

<h2>Contact Us</h2>
<p>If you have any questions about this privacy policy, you can reach us via our <a href="/en/contact">contact page</a> or by email: <a href="mailto:hassan@almalki.sa">hassan@almalki.sa</a>.</p>
HTML;
    }
}
