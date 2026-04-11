<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Portfolio;
use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    // Image mapping recovered from the original Vue components.
    // Order matches the original *Component.vue files.
    private const ARTICLE_IMAGES = [
        'claude_ai' => ['ar' => '/img/articles/1.jpg', 'en' => '/img/articles/1en.jpg'],
        'local_first' => ['ar' => '/img/articles/2.jpg', 'en' => '/img/articles/2en.jpg'],
        'prompt_engineering' => ['ar' => '/img/articles/3.jpg', 'en' => '/img/articles/3en.jpg'],
    ];

    // Rich bilingual structured content for each article
    private const ARTICLE_EXTRAS = [
        'claude_ai' => [
            'reading_time_ar' => '6 دقائق قراءة',
            'reading_time_en' => '6 min read',
            'takeaways_ar' => [
                'Claude يساعد المطورين المبتدئين على كتابة كود أفضل',
                'يفهم سياق المشروع بأكمله، وليس مجرد السطر الحالي',
                'يقدّم شرحاً للمفاهيم المعقدة بلغة واضحة',
                'يقلل من وقت تعلّم التقنيات الجديدة بشكل ملحوظ',
            ],
            'takeaways_en' => [
                'Claude helps junior developers write better code',
                'It understands entire project context, not just the current line',
                'It explains complex concepts in plain language',
                'It drastically shortens the learning curve for new technologies',
            ],
            'tags_ar' => ['ذكاء اصطناعي', 'Claude', 'Anthropic', 'المطورون المبتدئون', 'أدوات تطوير'],
            'tags_en' => ['AI', 'Claude', 'Anthropic', 'Junior Devs', 'Dev Tools'],
            'references' => [
                ['title' => 'Anthropic Claude', 'url' => 'https://www.anthropic.com/claude'],
                ['title' => 'Claude API Documentation', 'url' => 'https://docs.claude.com'],
                ['title' => 'Claude Code', 'url' => 'https://claude.com/code'],
            ],
        ],
        'local_first' => [
            'reading_time_ar' => '7 دقائق قراءة',
            'reading_time_en' => '7 min read',
            'takeaways_ar' => [
                'تطبيقات "المحلي أولاً" تعمل بدون إنترنت بكفاءة عالية',
                'البيانات تُعالج على جهاز المستخدم أولاً ثم تُزامن',
                'تقلل من الاعتماد على خوادم مركزية',
                'تحسّن الخصوصية وسرعة الاستجابة',
            ],
            'takeaways_en' => [
                'Local-first apps work efficiently without an internet connection',
                "Data is processed on the user's device first, then synced",
                'They reduce dependency on central servers',
                'They improve privacy and response time',
            ],
            'tags_ar' => ['تطبيقات ويب', 'Local-First', 'PWA', 'المزامنة', 'العمل دون اتصال'],
            'tags_en' => ['Web Apps', 'Local-First', 'PWA', 'Sync', 'Offline'],
            'references' => [
                ['title' => 'Local-First Software (Ink & Switch)', 'url' => 'https://www.inkandswitch.com/local-first/'],
                ['title' => 'MDN — Progressive Web Apps', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps'],
                ['title' => 'CRDTs Explained', 'url' => 'https://crdt.tech/'],
            ],
        ],
        'prompt_engineering' => [
            'reading_time_ar' => '5 دقائق قراءة',
            'reading_time_en' => '5 min read',
            'takeaways_ar' => [
                'هندسة الأوامر مهارة أساسية لأي مطور حديث',
                'ليست كلمات سحرية — بل وضوح وسياق وهيكل',
                'جودة الأوامر تحدد جودة مخرجات الذكاء الاصطناعي',
                'المطورون الذين يتقنونها يحققون إنتاجية أعلى بكثير',
            ],
            'takeaways_en' => [
                'Prompt engineering is a core skill for any modern developer',
                "It's not magic words — it's clarity, context, and structure",
                'The quality of your prompts determines the quality of AI output',
                'Developers who master it achieve significantly higher productivity',
            ],
            'tags_ar' => ['هندسة الأوامر', 'ذكاء اصطناعي', 'LLM', 'إنتاجية', 'مهارات تقنية'],
            'tags_en' => ['Prompt Engineering', 'AI', 'LLM', 'Productivity', 'Tech Skills'],
            'references' => [
                ['title' => 'Prompt Engineering Guide', 'url' => 'https://www.promptingguide.ai/'],
                ['title' => 'OpenAI Cookbook', 'url' => 'https://cookbook.openai.com/'],
                ['title' => 'Anthropic Prompt Library', 'url' => 'https://docs.claude.com/en/prompt-library'],
            ],
        ],
    ];

    private const PORTFOLIO_IMAGES = [
        'virtual_auction' => '/img/portfolio/1.png',
        'ecommerce_platform' => '/img/portfolio/2.png',
        'beneficiary_services' => '/img/portfolio/3.png',
        'learning_platform' => '/img/portfolio/4.png',
        'hackathon_platform' => '/img/portfolio/5.png',
        'support_system' => '/img/portfolio/6.png',
        'archive_system' => '/img/portfolio/7.png',
    ];

    private const WORKSHOP_IMAGES = [
        'vue' => ['ar' => '/img/workshops/1.jpg', 'en' => '/img/workshops/1en.jpg'],
        'resume' => ['ar' => '/img/workshops/2.jpg', 'en' => '/img/workshops/2en.jpg'],
        'animations' => ['ar' => '/img/workshops/3.jpg', 'en' => '/img/workshops/3en.jpg'],
        'prototypes' => ['ar' => '/img/workshops/4.jpg', 'en' => '/img/workshops/4en.jpg'],
        'digital_presence' => ['ar' => '/img/workshops/5.jpg', 'en' => '/img/workshops/5en.jpg'],
        'digital_self_sufficiency' => ['ar' => '/img/workshops/6.jpg', 'en' => '/img/workshops/6en.jpg'],
    ];

    // YouTube embed URLs recovered from the original Vue WorkshopsComponent.vue
    private const WORKSHOP_VIDEOS = [
        'vue' => 'https://www.youtube.com/embed/8xjveMX9jiM?si=b5Lv8HPcPWVacBw-',
        'resume' => 'https://www.youtube.com/embed/0iBsUJ8ZhMM?si=xv6FET5-UXF4Jtn6',
        'animations' => 'https://www.youtube.com/embed/qoSSMs2RRIg?si=dmpti0SfWB0jbX-D',
        'prototypes' => 'https://www.youtube.com/embed/5qWP0FVLKVI?si=s7USTS7HBi23R6bO',
        'digital_presence' => 'https://www.youtube.com/embed/gIyIzA_4yEI?si=rFBcpp-kN5Tq1Z_n',
        'digital_self_sufficiency' => 'https://www.youtube.com/embed/DxKJkYPTcVo?si=FlkfPzsGgYuZIZPG',
    ];

    private const PORTFOLIO_TECH = [
        'virtual_auction' => ['Vue.js', 'Laravel', 'WebSockets', 'Stripe', 'MySQL', 'Redis'],
        'ecommerce_platform' => ['Nuxt 3', 'Laravel', 'Tailwind CSS', 'Stripe', 'Meilisearch', 'Redis'],
        'beneficiary_services' => ['Vue.js', 'Laravel', 'Inertia.js', 'Tailwind CSS', 'PostgreSQL'],
        'learning_platform' => ['Vue 3', 'Laravel', 'Livewire', 'Video.js', 'FFmpeg', 'AWS S3'],
        'hackathon_platform' => ['Nuxt 3', 'Laravel', 'Pusher', 'Tailwind CSS', 'MySQL'],
        'support_system' => ['Vue.js', 'Laravel', 'Filament', 'Pusher', 'MySQL'],
        'archive_system' => ['Vue.js', 'Laravel', 'Elasticsearch', 'Tesseract OCR', 'MinIO'],
    ];

    private const PORTFOLIO_OUTCOMES_AR = [
        'virtual_auction' => ['+300% زيادة في عدد المزايدات', 'زمن استجابة أقل من 150ms', 'دعم أكثر من 10 آلاف مستخدم متزامن'],
        'ecommerce_platform' => ['+45% زيادة في التحويل', '-60% انخفاض في هجر السلة', 'معالجة آلاف الطلبات يومياً'],
        'beneficiary_services' => ['-70% تقليل وقت المعالجة', '+90% رضا المستفيدين', 'أتمتة كاملة لسير العمل'],
        'learning_platform' => ['+150 مدرباً نشطاً', '+5000 متدرب', '98% معدل إكمال الدورات'],
        'hackathon_platform' => ['إدارة أكثر من 50 فعالية', '+2000 مشارك مسجل', 'تحكيم مباشر وفوري'],
        'support_system' => ['-50% زمن الاستجابة', '+95% رضا العملاء', 'دعم متعدد القنوات'],
        'archive_system' => ['رقمنة مليون وثيقة', 'بحث لحظي بالـOCR', 'أرشفة منظمة حسب الإدارات'],
    ];

    private const PORTFOLIO_OUTCOMES_EN = [
        'virtual_auction' => ['+300% increase in bid volume', 'Sub-150ms response time', '10K+ concurrent users supported'],
        'ecommerce_platform' => ['+45% conversion rate', '-60% cart abandonment', 'Thousands of daily orders'],
        'beneficiary_services' => ['-70% processing time', '+90% beneficiary satisfaction', 'End-to-end workflow automation'],
        'learning_platform' => ['150+ active instructors', '5,000+ enrolled learners', '98% course completion rate'],
        'hackathon_platform' => ['50+ events managed', '2,000+ registered participants', 'Live real-time judging'],
        'support_system' => ['-50% response time', '+95% customer satisfaction', 'Multi-channel support'],
        'archive_system' => ['1M+ digitized documents', 'Instant OCR-powered search', 'Department-level organization'],
    ];

    private const PORTFOLIO_CHALLENGE_AR = [
        'virtual_auction' => 'كان التحدي الأساسي بناء نظام مزادات مباشر يعالج آلاف العروض لحظياً دون تأخير، مع ضمان أمان المعاملات وتكامل مع بوابات الدفع.',
        'ecommerce_platform' => 'تجربة تسوق تقليدية بطيئة ومعدل تحويل منخفض، مع تعقيد في إدارة المخزون وتكامل الشحن عبر متاجر متعددة.',
        'beneficiary_services' => 'إدارة يدوية للطلبات الخيرية تستغرق أسابيع لكل حالة، مع صعوبة في تتبع حالة المستفيدين وتوزيع الخدمات بعدالة.',
        'learning_platform' => 'الحاجة إلى منصة تعليمية تدعم عدداً غير محدود من المدربين والتخصصات، مع إدارة الدورات والفيديوهات والدفع الذاتي.',
        'hackathon_platform' => 'تنظيم هاكاثون يدوياً يتطلب فرقاً كبيرة، مع صعوبة في التسجيل، التحكيم، وإعلان النتائج في الوقت الفعلي.',
        'support_system' => 'توزيع طلبات الدعم الفني عبر البريد الإلكتروني والهاتف كان يسبب ضياع التذاكر وتأخر الاستجابة.',
        'archive_system' => 'أرشفة ورقية لملايين الوثائق يصعب البحث فيها، مع خطر فقدان المستندات الحساسة.',
    ];

    private const PORTFOLIO_CHALLENGE_EN = [
        'virtual_auction' => 'The core challenge was building a real-time auction engine that could handle thousands of concurrent bids with zero lag, while guaranteeing transaction security and seamless payment gateway integration.',
        'ecommerce_platform' => 'A slow, traditional shopping experience with low conversion rates, complicated inventory management, and fragmented shipping integration across multiple stores.',
        'beneficiary_services' => 'Manual charity request handling that took weeks per case, with no clear tracking of beneficiary status and no fair way to distribute services.',
        'learning_platform' => 'The need for a learning platform that could support unlimited instructors across specialties, with course management, video hosting, and automated payments.',
        'hackathon_platform' => 'Running hackathons manually required large teams, with friction in registration, judging, and announcing results in real time.',
        'support_system' => 'Support tickets scattered across email and phone calls meant lost requests and delayed responses.',
        'archive_system' => 'Paper-based archives of millions of documents with no search capability, and constant risk of losing sensitive originals.',
    ];

    private const PORTFOLIO_APPROACH_AR = [
        'virtual_auction' => 'بنيت المنصة باستخدام WebSockets للبث المباشر، مع قاعدة بيانات محسّنة للقراءة المتوازية. أضفت طبقة Redis للتخزين المؤقت للعروض النشطة وتقليل الحمل على قاعدة البيانات.',
        'ecommerce_platform' => 'اعتمدت معمارية headless تفصل الواجهة عن الخلفية، مع بحث فوري عبر Meilisearch، وتوصيات ذكية، وتكامل مباشر مع شركات الشحن.',
        'beneficiary_services' => 'صممت سير عمل يبدأ من استلام الطلب حتى الموافقة، مع تنبيهات تلقائية للمستفيدين ولوحة تحكم للفريق تعرض حالة كل طلب لحظياً.',
        'learning_platform' => 'أنشأت نظاماً معيارياً يسمح للمدربين برفع الدورات ذاتياً، مع معالجة فيديو تلقائية، وإدارة مدفوعات مع تقسيم الإيرادات.',
        'hackathon_platform' => 'طورت نظاماً شاملاً يغطي التسجيل، تكوين الفرق، التحكيم بمعايير مخصصة، وبث النتائج مباشرة عبر Pusher.',
        'support_system' => 'وحدت كل قنوات الدعم في نظام تذاكر موحد مع توزيع تلقائي، مستويات خدمة، وتقارير تحليلية مباشرة.',
        'archive_system' => 'قمت بدمج OCR لاستخراج النصوص من المستندات الممسوحة ضوئياً، مع فهرسة Elasticsearch لبحث فوري، وتخزين آمن عبر MinIO.',
    ];

    private const PORTFOLIO_APPROACH_EN = [
        'virtual_auction' => 'I built the platform on WebSockets for live broadcast, with a read-optimized database and a Redis caching layer for active bids to minimize database load.',
        'ecommerce_platform' => 'I used a headless architecture separating the frontend from the backend, with instant Meilisearch-powered search, smart recommendations, and direct carrier integrations.',
        'beneficiary_services' => 'I designed an end-to-end workflow from request intake to approval, with automatic notifications for beneficiaries and a team dashboard showing live status of every request.',
        'learning_platform' => 'I created a modular system where instructors can self-publish courses, with automatic video processing and revenue-split payment management.',
        'hackathon_platform' => 'I built a comprehensive system covering registration, team formation, judging with custom criteria, and real-time result broadcasting via Pusher.',
        'support_system' => 'I unified every support channel into a single ticketing system with automatic assignment, SLA tiers, and live analytics dashboards.',
        'archive_system' => 'I integrated OCR to extract text from scanned documents, with Elasticsearch indexing for instant search and secure storage via MinIO.',
    ];

    // Structured bilingual content for every workshop.
    // Stored in the workshops.extras JSON column and rendered as cards on the show page.
    private const WORKSHOP_EXTRAS = [
        'vue' => [
            'duration_ar' => 'ساعتان',
            'duration_en' => '2 hours',
            'objectives_ar' => [
                'فهم الفروقات الأساسية بين Vue 2 و Vue 3 و Composition API',
                'بناء أول تطبيق Vue باستخدام Vite من الصفر',
                'إدارة الحالة والتفاعل باستخدام ref و reactive',
                'استخدام مكونات قابلة لإعادة الاستخدام',
            ],
            'objectives_en' => [
                'Understand the core differences between Vue 2, Vue 3, and the Composition API',
                'Build your first Vue application from scratch using Vite',
                'Manage state and reactivity with ref and reactive',
                'Compose reusable components',
            ],
            'audience_ar' => [
                'المطورون المبتدئون في الواجهات الأمامية',
                'مطورو React الذين يريدون تعلم Vue',
                'أي شخص يبني تطبيقات ويب حديثة',
            ],
            'audience_en' => [
                'Frontend developers new to Vue',
                'React developers curious about Vue',
                'Anyone building modern web applications',
            ],
            'topics_ar' => [
                'مقدمة في Vue 3 و Composition API',
                'إعداد البيئة باستخدام Vite',
                'المكونات والـ Props والـ Emits',
                'التوجيه باستخدام Vue Router',
                'إدارة الحالة باستخدام Pinia',
                'التواصل مع API باستخدام Axios',
            ],
            'topics_en' => [
                'Intro to Vue 3 and the Composition API',
                'Project setup with Vite',
                'Components, props, and emits',
                'Routing with Vue Router',
                'State management with Pinia',
                'API integration with Axios',
            ],
            'outcomes_ar' => [
                'قدرة على إنشاء تطبيق Vue 3 كامل',
                'فهم نمط الـ Composition API',
                'جاهزية لبدء مشروع حقيقي',
            ],
            'outcomes_en' => [
                'Confidence to build a full Vue 3 app',
                'Solid grasp of the Composition API pattern',
                'Ready to start a real project',
            ],
        ],
        'resume' => [
            'duration_ar' => 'ساعة ونصف',
            'duration_en' => '90 minutes',
            'objectives_ar' => [
                'كتابة سيرة ذاتية مخصصة للوظائف التقنية',
                'إبراز الإنجازات بدلاً من المهام اليومية',
                'تجنّب الأخطاء الشائعة التي ترفضها أنظمة ATS',
                'بناء ملف شخصي احترافي على LinkedIn',
            ],
            'objectives_en' => [
                'Write a resume tailored for tech roles',
                'Highlight achievements instead of day-to-day tasks',
                'Avoid common mistakes that ATS systems reject',
                'Build a professional LinkedIn profile',
            ],
            'audience_ar' => [
                'الخريجون الجدد من تخصصات الحاسب والتقنية',
                'المطورون الباحثون عن وظيفتهم الأولى',
                'من يرغب بتحديث سيرته الذاتية قبل التقديم',
            ],
            'audience_en' => [
                'Fresh graduates in tech fields',
                'Developers looking for their first job',
                'Anyone refreshing a CV before applying',
            ],
            'topics_ar' => [
                'بنية السيرة الذاتية التقنية الحديثة',
                'صياغة الإنجازات بالأرقام والأثر',
                'اختيار الكلمات المفتاحية لأنظمة ATS',
                'بناء ملف LinkedIn قوي',
                'نصائح لمقابلات العمل التقنية',
            ],
            'topics_en' => [
                'Structure of a modern tech resume',
                'Writing achievement-driven bullet points',
                'ATS-friendly keywords',
                'Building a strong LinkedIn profile',
                'Tech interview preparation tips',
            ],
            'outcomes_ar' => [
                'سيرة ذاتية جاهزة للتقديم',
                'زيادة فرص استدعاء المقابلات',
                'خطة تحسين مستمر للملف المهني',
            ],
            'outcomes_en' => [
                'A ready-to-send resume',
                'Higher interview callback rate',
                'A continuous improvement plan for your profile',
            ],
        ],
        'animations' => [
            'duration_ar' => 'ساعتان',
            'duration_en' => '2 hours',
            'objectives_ar' => [
                'فهم مبادئ الحركة والانتقال في الويب',
                'استخدام CSS animations و transitions بفعالية',
                'إضافة تفاعلات سلسة بمكتبة GSAP',
                'تحسين أداء الحركات لتجنّب تقطّع المتصفح',
            ],
            'objectives_en' => [
                'Understand motion and easing principles on the web',
                'Use CSS animations and transitions effectively',
                'Add smooth interactions with GSAP',
                'Optimize animations to avoid browser jank',
            ],
            'audience_ar' => [
                'مصممو ومطورو الواجهات الأمامية',
                'من يرغب بإضافة تجارب تفاعلية إلى مواقعه',
            ],
            'audience_en' => [
                'Frontend designers and developers',
                'Anyone looking to add interactive experiences to their sites',
            ],
            'topics_ar' => [
                'مبادئ الحركة في الواجهة',
                'CSS Transitions و Keyframes',
                'مقدمة في GSAP و Timeline',
                'ScrollTrigger والحركة عند التمرير',
                'تحسين الأداء باستخدام will-change و transform',
            ],
            'topics_en' => [
                'UI motion principles',
                'CSS Transitions and Keyframes',
                'Intro to GSAP and Timeline',
                'ScrollTrigger and scroll-based motion',
                'Performance optimization with will-change and transform',
            ],
            'outcomes_ar' => [
                'مكتبة شخصية من الحركات الجاهزة',
                'فهم متى تستخدم CSS ومتى تستخدم GSAP',
                'قدرة على بناء تجارب تفاعلية سلسة',
            ],
            'outcomes_en' => [
                'A personal library of ready-to-use animations',
                'Knowing when to use CSS vs GSAP',
                'Ability to craft smooth interactive experiences',
            ],
        ],
        'prototypes' => [
            'duration_ar' => 'ثلاث ساعات',
            'duration_en' => '3 hours',
            'objectives_ar' => [
                'فهم دورة حياة النموذج الأولي',
                'بناء wireframes منخفضة الدقة بسرعة',
                'تصميم نماذج تفاعلية في Figma',
                'إجراء اختبارات قابلية استخدام مع مستخدمين حقيقيين',
            ],
            'objectives_en' => [
                'Understand the prototyping lifecycle',
                'Quickly build low-fidelity wireframes',
                'Design interactive prototypes in Figma',
                'Run usability tests with real users',
            ],
            'audience_ar' => [
                'مصممو UX/UI',
                'مدراء المنتجات',
                'رواد الأعمال الذين يطوّرون فكرة جديدة',
            ],
            'audience_en' => [
                'UX/UI designers',
                'Product managers',
                'Founders validating a new idea',
            ],
            'topics_ar' => [
                'من الفكرة إلى الـ wireframe',
                'أدوات النمذجة الحديثة (Figma, FigJam)',
                'تصميم تفاعلات وانتقالات واقعية',
                'اختبار النماذج مع المستخدمين',
                'التكرار السريع وتحسين التصميم',
            ],
            'topics_en' => [
                'From idea to wireframe',
                'Modern prototyping tools (Figma, FigJam)',
                'Designing realistic interactions and transitions',
                'Testing prototypes with users',
                'Rapid iteration and design improvement',
            ],
            'outcomes_ar' => [
                'نموذج تفاعلي قابل للاختبار',
                'مهارة التحقق من الأفكار قبل البناء',
                'تقليل زمن التطوير بتصحيح المسار مبكراً',
            ],
            'outcomes_en' => [
                'An interactive, testable prototype',
                'The skill of validating ideas before building',
                'Reduced dev time by correcting course early',
            ],
        ],
        'digital_presence' => [
            'duration_ar' => 'ساعة ونصف',
            'duration_en' => '90 minutes',
            'objectives_ar' => [
                'بناء هوية رقمية شخصية أو تجارية واضحة',
                'إنشاء صفحة هبوط بدون كود',
                'استخدام وسائل التواصل بشكل استراتيجي',
                'قياس وتحسين الحضور الرقمي',
            ],
            'objectives_en' => [
                'Build a clear personal or business digital identity',
                'Create a landing page without code',
                'Use social media strategically',
                'Measure and improve your digital presence',
            ],
            'audience_ar' => [
                'أصحاب الأعمال الصغيرة',
                'المستقلون والفريلانسر',
                'الراغبون ببناء علامة شخصية',
            ],
            'audience_en' => [
                'Small business owners',
                'Freelancers',
                'Anyone building a personal brand',
            ],
            'topics_ar' => [
                'عناصر الهوية الرقمية',
                'أدوات بناء الصفحات بدون كود (Notion, Framer, Linktree)',
                'استراتيجية المحتوى الأساسية',
                'الـ SEO للمبتدئين',
                'أدوات التحليل المجانية',
            ],
            'topics_en' => [
                'Elements of a digital identity',
                'No-code page builders (Notion, Framer, Linktree)',
                'Core content strategy',
                'SEO for beginners',
                'Free analytics tools',
            ],
            'outcomes_ar' => [
                'صفحة هبوط جاهزة للإطلاق',
                'خطة محتوى شهرية',
                'حضور رقمي احترافي بأقل تكلفة',
            ],
            'outcomes_en' => [
                'A launch-ready landing page',
                'A monthly content plan',
                'Professional digital presence on a lean budget',
            ],
        ],
        'digital_self_sufficiency' => [
            'duration_ar' => 'ساعتان',
            'duration_en' => '2 hours',
            'objectives_ar' => [
                'تقليل الاعتماد على مزودي الخدمات الخارجية',
                'بناء قدرات رقمية داخلية مستدامة',
                'أتمتة المهام المتكررة',
                'توثيق المعرفة المؤسسية بشكل منظّم',
            ],
            'objectives_en' => [
                'Reduce dependency on external service providers',
                'Build sustainable in-house digital capabilities',
                'Automate repetitive tasks',
                'Systematically document institutional knowledge',
            ],
            'audience_ar' => [
                'مدراء الفرق والإدارات',
                'المسؤولون عن التحول الرقمي',
                'أي فريق يريد العمل بكفاءة أعلى',
            ],
            'audience_en' => [
                'Team and department managers',
                'Digital transformation leads',
                'Any team that wants to work more efficiently',
            ],
            'topics_ar' => [
                'تقييم المهام الداخلية القابلة للأتمتة',
                'أدوات الأتمتة (Zapier, Make, n8n)',
                'أدوات توثيق المعرفة (Notion, Confluence)',
                'التعاون الداخلي بدون أدوات خارجية',
                'بناء قاعدة معرفة قابلة للبحث',
            ],
            'topics_en' => [
                'Identifying internal tasks ready for automation',
                'Automation tools (Zapier, Make, n8n)',
                'Knowledge documentation tools (Notion, Confluence)',
                'Internal collaboration without external tools',
                'Building a searchable knowledge base',
            ],
            'outcomes_ar' => [
                'خريطة لعمليات الفريق القابلة للأتمتة',
                'تقليل التكاليف التشغيلية',
                'زيادة استقلالية الفريق واتخاذ القرار',
            ],
            'outcomes_en' => [
                'A map of team processes ready to automate',
                'Reduced operating costs',
                'Increased team autonomy and decision-making',
            ],
        ],
    ];

    public function run(): void
    {
        $ar = $this->loadLocale('ar');
        $en = $this->loadLocale('en');

        $this->seedArticles($ar, $en);
        $this->seedPortfolios($ar, $en);
        $this->seedWorkshops($ar, $en);
    }

    protected function loadLocale(string $locale): array
    {
        $path = database_path("data/locales/$locale/translation.json");

        return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }

    protected function seedArticles(array $ar, array $en): void
    {
        $arItems = $ar['articles']['items'] ?? [];
        $enItems = $en['articles']['items'] ?? [];

        foreach ($arItems as $key => $arItem) {
            $enItem = $enItems[$key] ?? $arItem;
            $contentAr = $this->renderContent($arItem['content'] ?? []);
            $contentEn = $this->renderContent($enItem['content'] ?? []);

            $images = self::ARTICLE_IMAGES[$key] ?? null;

            Article::updateOrCreate(
                ['slug_ar' => Str::slug($key, '-', 'ar')],
                [
                    'title_ar' => $arItem['title'] ?? $key,
                    'title_en' => $enItem['title'] ?? $key,
                    'slug_en' => Str::slug($key),
                    'excerpt_ar' => Str::limit(strip_tags($contentAr), 200),
                    'excerpt_en' => Str::limit(strip_tags($contentEn), 200),
                    'content_ar' => $contentAr,
                    'content_en' => $contentEn,
                    'cover_image' => $images['ar'] ?? null,
                    'cover_image_en' => $images['en'] ?? null,
                    'extras' => self::ARTICLE_EXTRAS[$key] ?? null,
                    'is_published' => true,
                    'published_at' => isset($arItem['date']) ? $this->parseDate($arItem['date']) : now(),
                ]
            );
        }
    }

    protected function seedPortfolios(array $ar, array $en): void
    {
        $arItems = $ar['portfolio']['projects'] ?? [];
        $enItems = $en['portfolio']['projects'] ?? [];
        $i = 0;

        foreach ($arItems as $key => $arItem) {
            $enItem = $enItems[$key] ?? $arItem;

            $contentAr = $this->buildPortfolioContent('ar', $key, $arItem);
            $contentEn = $this->buildPortfolioContent('en', $key, $enItem);

            Portfolio::updateOrCreate(
                ['slug_ar' => Str::slug($key, '-', 'ar')],
                [
                    'title_ar' => $arItem['title'] ?? $key,
                    'title_en' => $enItem['title'] ?? $key,
                    'slug_en' => Str::slug($key),
                    'description_ar' => $this->stringify($arItem['description'] ?? null),
                    'description_en' => $this->stringify($enItem['description'] ?? null),
                    'content_ar' => $contentAr,
                    'content_en' => $contentEn,
                    'category' => $arItem['category'] ?? null,
                    'category_en' => $enItem['category'] ?? null,
                    'features' => [
                        'ar' => $arItem['features'] ?? [],
                        'en' => $enItem['features'] ?? [],
                        'tech' => self::PORTFOLIO_TECH[$key] ?? [],
                        'outcomes_ar' => self::PORTFOLIO_OUTCOMES_AR[$key] ?? [],
                        'outcomes_en' => self::PORTFOLIO_OUTCOMES_EN[$key] ?? [],
                        'challenge_ar' => self::PORTFOLIO_CHALLENGE_AR[$key] ?? null,
                        'challenge_en' => self::PORTFOLIO_CHALLENGE_EN[$key] ?? null,
                        'approach_ar' => self::PORTFOLIO_APPROACH_AR[$key] ?? null,
                        'approach_en' => self::PORTFOLIO_APPROACH_EN[$key] ?? null,
                    ],
                    'cover_image' => self::PORTFOLIO_IMAGES[$key] ?? null,
                    'sort_order' => $i++,
                    'is_published' => true,
                ]
            );
        }
    }

    protected function buildPortfolioContent(string $locale, string $key, array $item): string
    {
        $isAr = $locale === 'ar';
        $description = $this->stringify($item['description'] ?? null);
        $challenge = ($isAr ? self::PORTFOLIO_CHALLENGE_AR : self::PORTFOLIO_CHALLENGE_EN)[$key] ?? null;
        $approach = ($isAr ? self::PORTFOLIO_APPROACH_AR : self::PORTFOLIO_APPROACH_EN)[$key] ?? null;

        $html = '';
        if ($description) {
            $html .= '<p>'.nl2br(e($description)).'</p>';
        }
        if ($challenge) {
            $html .= '<h3>'.($isAr ? 'التحدي' : 'The Challenge').'</h3><p>'.e($challenge).'</p>';
        }
        if ($approach) {
            $html .= '<h3>'.($isAr ? 'المنهجية والحل' : 'Approach & Solution').'</h3><p>'.e($approach).'</p>';
        }

        return $html;
    }

    protected function seedWorkshops(array $ar, array $en): void
    {
        $arItems = $ar['workshops']['items'] ?? [];
        $enItems = $en['workshops']['items'] ?? [];

        foreach ($arItems as $key => $arItem) {
            $enItem = $enItems[$key] ?? $arItem;

            $images = self::WORKSHOP_IMAGES[$key] ?? null;

            Workshop::updateOrCreate(
                ['slug_ar' => Str::slug($key, '-', 'ar')],
                [
                    'title_ar' => $arItem['title'] ?? $key,
                    'title_en' => $enItem['title'] ?? $key,
                    'slug_en' => Str::slug($key),
                    'description_ar' => $this->stringify($arItem['description'] ?? null),
                    'description_en' => $this->stringify($enItem['description'] ?? null),
                    'platform' => $arItem['platform'] ?? null,
                    'platform_en' => $enItem['platform'] ?? null,
                    'cover_image' => $images['ar'] ?? null,
                    'cover_image_en' => $images['en'] ?? null,
                    'video_url' => self::WORKSHOP_VIDEOS[$key] ?? null,
                    'extras' => self::WORKSHOP_EXTRAS[$key] ?? null,
                    'is_published' => true,
                ]
            );
        }
    }

    protected function stringify($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return collect($value)->filter(fn ($v) => is_string($v))->implode("\n\n");
        }

        return (string) $value;
    }

    protected function renderContent($content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            return collect($content)
                ->map(function ($block) {
                    if (is_string($block)) {
                        return "<p>$block</p>";
                    }
                    if (is_array($block)) {
                        return $this->renderContent($block);
                    }

                    return '';
                })
                ->implode("\n");
        }

        return '';
    }

    protected function parseDate(string $date): ?string
    {
        try {
            return \Carbon\Carbon::parse($date)->toDateTimeString();
        } catch (\Throwable) {
            return now()->toDateTimeString();
        }
    }
}
