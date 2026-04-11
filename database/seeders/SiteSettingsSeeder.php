<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Branding
            ['branding', 'site_name_ar', 'text', 'الموقع الشخصي لحسان المالكي', 'اسم الموقع (عربي)', 'Site Name (AR)'],
            ['branding', 'site_name_en', 'text', 'Hassan Almalki Personal Website', 'اسم الموقع (إنجليزي)', 'Site Name (EN)'],
            ['branding', 'site_logo', 'image', '/img/logo/logo.png', 'شعار الموقع', 'Site Logo'],
            ['branding', 'admin_logo', 'image', '/img/logo/logo.png', 'شعار لوحة التحكم', 'Admin Panel Logo'],
            ['branding', 'login_logo', 'image', '/img/logo/logo.png', 'شعار صفحة الدخول', 'Login Page Logo'],
            ['branding', 'favicon', 'image', '/favicon.ico', 'أيقونة الموقع', 'Favicon'],
            ['branding', 'og_image', 'image', '/img/og-image.jpg', 'صورة المشاركة', 'OG Share Image'],

            // Identity
            ['identity', 'owner_name_ar', 'text', 'حسان المالكي', 'اسم المالك (عربي)', 'Owner Name (AR)'],
            ['identity', 'owner_name_en', 'text', 'Hassan Almalki', 'اسم المالك (إنجليزي)', 'Owner Name (EN)'],
            ['identity', 'profession_ar', 'text', 'مطور تطبيقات ويب ومدير منتجات تقنية', 'المسمى الوظيفي (عربي)', 'Profession (AR)'],
            ['identity', 'profession_en', 'text', 'Web Developer & Digital Product Manager', 'المسمى الوظيفي (إنجليزي)', 'Profession (EN)'],
            ['identity', 'job_title_ar', 'text', 'مطور تطبيقات ويب', 'المسمى الوظيفي القصير (عربي)', 'Job Title Short (AR)'],
            ['identity', 'job_title_en', 'text', 'Web Developer', 'المسمى الوظيفي القصير (إنجليزي)', 'Job Title Short (EN)'],

            // Contact
            ['contact', 'phone', 'text', '+966 596966667', 'رقم الجوال', 'Phone'],
            ['contact', 'email', 'email', 'hassan@almalki.sa', 'البريد الإلكتروني', 'Email'],
            ['contact', 'address_ar', 'text', 'الرياض، المملكة العربية السعودية', 'العنوان (عربي)', 'Address (AR)'],
            ['contact', 'address_en', 'text', 'Riyadh, Saudi Arabia', 'العنوان (إنجليزي)', 'Address (EN)'],
            ['contact', 'whatsapp_number', 'text', '966596966667', 'رقم الواتساب', 'WhatsApp Number'],

            // Social
            ['social', 'twitter_url', 'url', 'https://x.com/eng_hssaan', 'رابط تويتر/X', 'Twitter/X URL'],
            ['social', 'twitter_handle', 'text', '@eng_hssaan', 'معرف تويتر', 'Twitter Handle'],
            ['social', 'linkedin_url', 'url', 'https://www.linkedin.com/in/eng-hssaan/', 'رابط لينكدإن', 'LinkedIn URL'],
            ['social', 'snapchat_url', 'url', 'https://snapchat.com/add/eng.hssaan', 'رابط سناب شات', 'Snapchat URL'],
            ['social', 'snapchat_handle', 'text', 'eng_hssaan', 'معرف سناب شات', 'Snapchat Handle'],
            ['social', 'whatsapp_url', 'url', 'https://wa.me/966596966667', 'رابط الواتساب', 'WhatsApp URL'],

            // SEO
            ['seo', 'meta_title_ar', 'text', 'حسان المالكي | مطور تطبيقات ويب ومدير منتجات تقنية', 'عنوان الميتا (عربي)', 'Meta Title (AR)'],
            ['seo', 'meta_title_en', 'text', 'Hassan Almalki | Web Developer & Digital Product Manager', 'عنوان الميتا (إنجليزي)', 'Meta Title (EN)'],
            ['seo', 'meta_description_ar', 'textarea', 'حسان المالكي - مطور تطبيقات ويب ومدير منتجات تقنية ومحلل أعمال بخبرة 8+ سنوات في تطوير المنصات الرقمية والتحول الرقمي.', 'وصف الميتا (عربي)', 'Meta Description (AR)'],
            ['seo', 'meta_description_en', 'textarea', 'Hassan Almalki - Web developer, digital product manager & business analyst with 8+ years experience building digital platforms.', 'وصف الميتا (إنجليزي)', 'Meta Description (EN)'],

            // Footer
            ['footer', 'copyright_ar', 'text', 'جميع الحقوق محفوظة', 'نص حقوق النشر (عربي)', 'Copyright Text (AR)'],
            ['footer', 'copyright_en', 'text', 'All rights reserved', 'نص حقوق النشر (إنجليزي)', 'Copyright Text (EN)'],
            ['footer', 'footer_description_ar', 'textarea', 'مطور تطبيقات ويب ومدير منتجات تقنية', 'وصف الفوتر (عربي)', 'Footer Description (AR)'],
            ['footer', 'footer_description_en', 'textarea', 'Web developer & digital product manager', 'وصف الفوتر (إنجليزي)', 'Footer Description (EN)'],

            // About
            ['about', 'about_description_ar', 'textarea',
                "مطور تطبيقات ويب، ومدير منتجات تقنية، ومحلل أعمال بخبرة أكثر من 8 سنوات في تطوير وتحليل المنصات الرقمية.\n".
                "عملت على تصميم حلول تقنية مرتبطة بأهداف الأعمال ودعم التحول الرقمي.\n".
                "قدت فرق تقنية متعددة التخصصات، وشاركت في تنفيذ مشاريع ضمن أطر زمنية محددة وبمخرجات عالية الجودة.\n".
                "خبير في توثيق وتحليل المتطلبات، وبناء خرائط طريق للمنتجات الرقمية.\n".
                "كما ساهمت في تطوير المحتوى التقني من خلال محاضرات وورش عمل تدريبية متخصصة.",
                'نبذة عنّي (عربي)', 'About Bio (AR)'],
            ['about', 'about_description_en', 'textarea',
                "Web application developer, technical product manager, and business analyst with over 8 years of experience developing and analyzing digital platforms.\n".
                "I have designed technical solutions tied to business objectives and supported digital transformation across multiple organizations.\n".
                "Led multidisciplinary technical teams, and delivered projects within tight timeframes with high-quality outcomes.\n".
                "Expert in requirements analysis and documentation, and in building roadmaps for digital products.\n".
                "I have also contributed to the technical community through specialized lectures and training workshops.",
                'نبذة عنّي (إنجليزي)', 'About Bio (EN)'],
            ['about', 'birthday', 'text', '15/05/1991', 'تاريخ الميلاد', 'Birthday'],
            ['about', 'years_experience', 'text', '8+', 'سنوات الخبرة', 'Years of Experience'],
            ['about', 'cv_url', 'url', '', 'رابط السيرة الذاتية', 'CV Download URL'],
            ['about', 'pm_skills_ar', 'textarea',
                "اكتشاف المنتج, أبحاث المستخدم النوعية, تحليل رحلة العميل, تحديد الـ OKRs, بناء خرائط طريق المنتج, ترتيب أولويات الميزات (RICE, MoSCoW), إدارة أصحاب المصلحة, صياغة قصص المستخدم, تصميم تجارب A/B, تفسير مؤشرات المنتج, تحليل PMF, استراتيجية الإطلاق (GTM), إدارة دورة حياة المنتج, تيسير ورش اكتشاف المنتج, قيادة فرق متعددة التخصصات, صناعة القرار المبني على البيانات",
                'مهارات إدارة المنتجات (عربي)', 'PM Skills (AR)'],
            ['about', 'pm_skills_en', 'textarea',
                "Product Discovery, Qualitative User Research, Customer Journey Mapping, OKR Definition, Product Roadmapping, Feature Prioritization (RICE, MoSCoW), Stakeholder Management, User Story Writing, A/B Test Design, Product Metrics Interpretation, PMF Analysis, Go-To-Market Strategy, Product Lifecycle Management, Discovery Workshop Facilitation, Cross-Functional Team Leadership, Data-Informed Decision Making",
                'مهارات إدارة المنتجات (إنجليزي)', 'PM Skills (EN)'],
            ['about', 'skills_ar', 'textarea',
                "Laravel 13 + Octane, Filament v5, Livewire 3, Inertia.js v3, Vue 3.5 + Composition API, Nuxt 3, TypeScript متقدم, Tailwind CSS v4, Pest (TDD), PostgreSQL + MySQL متقدم, Redis + طوابير المهام, Docker + Kubernetes, GitHub Actions CI/CD, WebSockets (Reverb / Pusher), تصميم REST و GraphQL APIs, هندسة معمارية قابلة للتوسع",
                'أطر فل ستاك متقدمة (عربي)', 'Expert Fullstack (AR)'],
            ['about', 'skills_en', 'textarea',
                "Laravel 13 + Octane, Filament v5, Livewire 3, Inertia.js v3, Vue 3.5 Composition API, Nuxt 3, Advanced TypeScript, Tailwind CSS v4, Pest (TDD), PostgreSQL + MySQL Tuning, Redis + Queue Architecture, Docker + Kubernetes, GitHub Actions CI/CD, WebSockets (Reverb / Pusher), REST & GraphQL API Design, Scalable System Architecture",
                'أطر فل ستاك متقدمة (إنجليزي)', 'Expert Fullstack (EN)'],
            ['about', 'languages_ar', 'textarea',
                "العربية:اللغة الأم\nالإنجليزية:ممتازة",
                'اللغات (عربي)', 'Languages (AR)'],
            ['about', 'languages_en', 'textarea',
                "Arabic:Native\nEnglish:Fluent",
                'اللغات (إنجليزي)', 'Languages (EN)'],
        ];

        foreach ($settings as [$group, $key, $type, $value, $labelAr, $labelEn]) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'type' => $type,
                    'value' => $value,
                    'label_ar' => $labelAr,
                    'label_en' => $labelEn,
                ]
            );
        }

        SiteSetting::flushCache();
    }
}
