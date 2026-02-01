<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دار الجنة للنشر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jannah-teal': '#4A9B8E',
                        'jannah-pink': '#E31C79',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #4A9B8E 0%, #3d8278 100%);
        }
        .gradient-pink {
            background: linear-gradient(135deg, #E31C79 0%, #c91567 100%);
        }
    </style>
</head>
<body class="bg-white">
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/janah.png') }}" alt="دار الجنة للنشر" class="h-12">
                </div>
                <nav class="flex items-center gap-8 text-sm font-medium">
                    <a href="#home" class="text-jannah-teal">الرئيسية</a>
                    <a href="#about" class="text-gray-700 hover:text-jannah-teal transition">من نحن</a>
                    <a href="#publications" class="text-gray-700 hover:text-jannah-teal transition">إصداراتنا</a>
                    <a href="#services" class="text-gray-700 hover:text-jannah-teal transition">خدماتنا</a>
                    <a href="#contact" class="text-gray-700 hover:text-jannah-teal transition">اتصل بنا</a>
                </nav>
                
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative overflow-hidden">
        <div class="absolute inset-0 gradient-bg opacity-5"></div>
        <div class="container mx-auto px-6 py-20">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-block bg-jannah-pink/10 text-jannah-pink px-4 py-2 rounded-full text-sm font-semibold mb-6">
                        رائدون منذ 2009
                    </div>
                    <h1 class="text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        نصنع عالماً من<br/>
                        <span class="text-jannah-teal">الإبداع</span> و
                        <span class="text-jannah-pink">المعرفة</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        دار نشر متخصصة في كتب الأطفال، نقدم محتوى تربوياً راقياً يجمع بين الجودة والإبداع
                    </p>
                    <div class="flex gap-4">
                        <a href="#publications" class="gradient-bg text-white px-8 py-4 rounded-xl font-semibold hover:shadow-lg transition">
                            استكشف إصداراتنا
                        </a>
                        <a href="#contact" class="bg-white border-2 border-gray-900 text-gray-900 px-8 py-4 rounded-xl font-semibold hover:bg-gray-900 hover:text-white transition">
                            تواصل معنا
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -top-10 -right-10 w-72 h-72 gradient-bg rounded-full opacity-20 blur-3xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-72 h-72 gradient-pink rounded-full opacity-20 blur-3xl"></div>
                    
                    <div class="relative grid grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
                                <div class="text-5xl font-bold text-jannah-teal mb-2">250+</div>
                                <div class="text-gray-600">إصدار متنوع</div>
                            </div>
                            <div class="gradient-pink text-white rounded-2xl p-8 shadow-xl">
                                <div class="text-5xl font-bold mb-2">15</div>
                                <div>سنة خبرة</div>
                            </div>
                        </div>
                        <div class="space-y-6 mt-12">
                            <div class="gradient-bg text-white rounded-2xl p-8 shadow-xl">
                                <div class="text-5xl font-bold mb-2">12</div>
                                <div>دولة عربية</div>
                            </div>
                            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
                                <div class="text-5xl font-bold text-jannah-pink mb-2">500+</div>
                                <div class="text-gray-600">نقطة بيع</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-jannah-pink font-semibold text-sm uppercase tracking-wider">من نحن</span>
                <h2 class="text-4xl font-bold text-gray-900 mt-4">رحلتنا في عالم النشر</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6">
                        15 عاماً من التميز والإبداع
                    </h3>
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        تأسست دار الجنة للنشر عام 2009 برؤية واضحة: تقديم محتوى نوعي يثري المكتبة العربية للطفل. اليوم، نفخر بمكتبة تضم أكثر من 250 إصداراً في مختلف المجالات.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        نعمل مع نخبة من الكتّاب والرسامين الموهوبين، ونلتزم بأعلى معايير الجودة في كل مرحلة من مراحل الإنتاج، من التحرير والتصميم إلى الطباعة والتوزيع.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-gradient-to-br from-jannah-teal to-jannah-teal/80 text-white p-8 rounded-2xl">
                        <div class="text-4xl mb-4">🎯</div>
                        <h4 class="font-bold text-xl mb-3">رؤيتنا</h4>
                        <p class="text-white/90">الريادة في نشر كتب الأطفال العربية على مستوى الوطن العربي</p>
                    </div>
                    <div class="bg-gradient-to-br from-jannah-pink to-jannah-pink/80 text-white p-8 rounded-2xl">
                        <div class="text-4xl mb-4">💫</div>
                        <h4 class="font-bold text-xl mb-3">رسالتنا</h4>
                        <p class="text-white/90">إثراء المحتوى العربي بكتب نوعية تجمع بين المتعة والفائدة</p>
                    </div>
                    <div class="bg-white border-2 border-gray-200 p-8 rounded-2xl col-span-2">
                        <h4 class="font-bold text-xl mb-4 text-gray-900">قيمنا الأساسية</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <span class="text-jannah-teal font-bold">◆</span>
                                <span>الجودة والتميز</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-jannah-pink font-bold">◆</span>
                                <span>الإبداع والابتكار</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-jannah-teal font-bold">◆</span>
                                <span>الأصالة والمعاصرة</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-jannah-pink font-bold">◆</span>
                                <span>المسؤولية التربوية</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievements -->
            <div class="bg-gray-900 text-white rounded-3xl p-12">
                <div class="text-center mb-10">
                    <h3 class="text-3xl font-bold">إنجازاتنا وجوائزنا</h3>
                </div>
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-5xl font-bold text-jannah-teal mb-2">22+</div>
                        <p class="text-gray-300">جائزة محلية ودولية</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-pink mb-2">8</div>
                        <p class="text-gray-300">شهادات تقدير</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-teal mb-2">15+</div>
                        <p class="text-gray-300">معرض دولي</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-pink mb-2">98%</div>
                        <p class="text-gray-300">رضا العملاء</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Publications Section -->
    <section id="publications" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-jannah-teal font-semibold text-sm uppercase tracking-wider">إصداراتنا</span>
                <h2 class="text-4xl font-bold text-gray-900 mt-4">مكتبة شاملة ومتنوعة</h2>
                <p class="text-xl text-gray-600 mt-4">أكثر من 250 إصداراً في 8 تصنيفات رئيسية</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6 mb-12">
                <div class="group bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-teal cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">📖</div>
                        <div class="text-3xl font-bold text-jannah-teal">85</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">القصص التربوية</h3>
                    <p class="text-gray-600 text-sm">قصص هادفة تغرس القيم والأخلاق</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-pink cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🎨</div>
                        <div class="text-3xl font-bold text-jannah-pink">42</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">كتب التلوين</h3>
                    <p class="text-gray-600 text-sm">تنمية المهارات الفنية والحركية</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-teal cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🔬</div>
                        <div class="text-3xl font-bold text-jannah-teal">38</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">العلوم المبسطة</h3>
                    <p class="text-gray-600 text-sm">محتوى علمي بطريقة شيقة</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-pink cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🌙</div>
                        <div class="text-3xl font-bold text-jannah-pink">52</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">قصص ما قبل النوم</h3>
                    <p class="text-gray-600 text-sm">حكايات جميلة لنوم هادئ</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-pink cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">📚</div>
                        <div class="text-3xl font-bold text-jannah-pink">18</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">الموسوعات</h3>
                    <p class="text-gray-600 text-sm">معلومات شاملة ومنظمة</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-teal cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🧩</div>
                        <div class="text-3xl font-bold text-jannah-teal">33</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">الألغاز والألعاب</h3>
                    <p class="text-gray-600 text-sm">تنمية الذكاء والمنطق</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-pink cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🕌</div>
                        <div class="text-3xl font-bold text-jannah-pink">45</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">القصص الإسلامي</h3>
                    <p class="text-gray-600 text-sm">تراث إسلامي أصيل</p>
                </div>

                <div class="group bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition border-2 border-transparent hover:border-jannah-teal cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl">🌍</div>
                        <div class="text-3xl font-bold text-jannah-teal">27</div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">ثقافات العالم</h3>
                    <p class="text-gray-600 text-sm">رحلة معرفية حول العالم</p>
                </div>
            </div>

            <div class="bg-gradient-to-l from-jannah-teal/10 to-jannah-pink/10 rounded-3xl p-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">هل تريد الكتالوج الكامل؟</h3>
                        <p class="text-gray-600">تصفح مجموعتنا الكاملة من الإصدارات مع التفاصيل الكاملة</p>
                    </div>
                    <a href="#contact" class="gradient-bg text-white px-8 py-4 rounded-xl font-semibold hover:shadow-lg transition whitespace-nowrap">
                        طلب الكتالوج
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-jannah-pink font-semibold text-sm uppercase tracking-wider">خدماتنا</span>
                <h2 class="text-4xl font-bold text-gray-900 mt-4">حلول متكاملة للنشر والتوزيع</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition">
                    <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-3xl">📝</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">النشر والإنتاج</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        خدمات نشر متكاملة من التحرير والتصميم حتى الطباعة بأعلى معايير الجودة
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>تحرير ومراجعة احترافية</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>تصميم وإخراج فني متميز</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>طباعة بجودة عالية</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition">
                    <div class="w-16 h-16 gradient-pink rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-3xl">🚚</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">التوزيع والشحن</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        شبكة توزيع واسعة تغطي 12 دولة عربية مع خدمات لوجستية احترافية
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-pink rounded-full"></span>
                            <span>توزيع في 12 دولة عربية</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-pink rounded-full"></span>
                            <span>شحن سريع وآمن</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-pink rounded-full"></span>
                            <span>إدارة احترافية للمخزون</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition">
                    <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-3xl">🤝</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">حلول الأعمال</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        برامج خاصة للمؤسسات التعليمية والمكتبات والموزعين
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>خصومات للكميات الكبيرة</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>شروط دفع مرنة</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-700">
                            <span class="w-2 h-2 bg-jannah-teal rounded-full"></span>
                            <span>دعم فني مخصص</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Partners -->
            <div class="bg-white rounded-3xl p-12 shadow-lg">
                <div class="text-center mb-12">
                    <h3 class="text-3xl font-bold text-gray-900 mb-4">شركاؤنا</h3>
                    <p class="text-xl text-gray-600">نفخر بثقة شركائنا في مختلف القطاعات</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center p-8 bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 rounded-2xl">
                        <div class="text-5xl mb-4">🏫</div>
                        <div class="text-4xl font-bold text-jannah-teal mb-2">200+</div>
                        <h4 class="font-bold text-xl mb-2 text-gray-900">مؤسسة تعليمية</h4>
                        <p class="text-gray-600">مدارس وروضات في 8 دول</p>
                    </div>

                    <div class="text-center p-8 bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 rounded-2xl">
                        <div class="text-5xl mb-4">📚</div>
                        <div class="text-4xl font-bold text-jannah-pink mb-2">300+</div>
                        <h4 class="font-bold text-xl mb-2 text-gray-900">مكتبة تجارية</h4>
                        <p class="text-gray-600">شراكات مع كبرى السلاسل</p>
                    </div>

                    <div class="text-center p-8 bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 rounded-2xl">
                        <div class="text-5xl mb-4">🌐</div>
                        <div class="text-4xl font-bold text-jannah-teal mb-2">50+</div>
                        <h4 class="font-bold text-xl mb-2 text-gray-900">موزع معتمد</h4>
                        <p class="text-gray-600">في 12 دولة عربية</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-900 text-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-jannah-pink font-semibold text-sm uppercase tracking-wider">تواصل معنا</span>
                <h2 class="text-4xl font-bold mt-4">نسعد بخدمتك</h2>
                <p class="text-xl text-gray-400 mt-4">فريقنا جاهز للإجابة على استفساراتك</p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 max-w-6xl mx-auto">
                <div>
                    <form class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">الاسم</label>
                                <input type="text" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition" placeholder="الاسم الكامل">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">المسمى الوظيفي</label>
                                <input type="text" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition" placeholder="المنصب">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">اسم المؤسسة</label>
                            <input type="text" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition" placeholder="اسم الشركة أو المؤسسة">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">البريد الإلكتروني</label>
                                <input type="email" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition" placeholder="email@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">رقم الهاتف</label>
                                <input type="tel" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition" placeholder="+20 123 456 7890">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">نوع الطلب</label>
                            <select class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition">
                                <option>استفسار عام</option>
                                <option>طلب توزيع</option>
                                <option>طلب كتالوج</option>
                                <option>شراكة تجارية</option>
                                <option>المؤسسات التعليمية</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">الرسالة</label>
                            <textarea rows="4" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 focus:border-jannah-teal outline-none transition resize-none" placeholder="أخبرنا عن احتياجاتك..."></textarea>
                        </div>

                        <button type="submit" class="w-full gradient-bg text-white py-4 rounded-xl font-semibold hover:shadow-xl transition">
                            إرسال الرسالة
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-800 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">📍</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-2">المقر الرئيسي</h3>
                                <p class="text-gray-400">شارع الجامعة، المعادي<br/>القاهرة، مصر</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 gradient-pink rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">📞</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-2">الهاتف</h3>
                                <p class="text-gray-400" dir="ltr">+20 123 456 7890</p>
                                <p class="text-sm text-gray-500 mt-1">السبت - الخميس: 9:00 ص - 5:00 م</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">✉️</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-2">البريد الإلكتروني</h3>
                                <p class="text-gray-400">info@daraljannah.com<br/>sales@daraljannah.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="gradient-bg rounded-2xl p-8">
                        <h3 class="font-bold text-xl mb-3">تابعنا على وسائل التواصل</h3>
                        <p class="mb-6 text-white/80">ابقَ على اطلاع بآخر إصداراتنا وأخبارنا</p>
                        <div class="flex gap-3">
                            <a href="#" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20 transition">
                                <span>f</span>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20 transition">
                                <span>tw</span>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20 transition">
                                <span>in</span>
                            </a>
                            <a href="#" class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center hover:bg-white/20 transition">
                                <span>yt</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-8">
                <div>
                    <div class="text-2xl font-bold mb-4">
                        <span class="text-jannah-teal">دار الجنة</span>
                        <span class="text-gray-500 text-lg mr-2">للنشر</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed">
                        نبني جيلاً من القرّاء المبدعين والمفكرين منذ 2009
                    </p>
                </div>

                <div>
                    <h4 class="font-bold mb-4">الشركة</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#about" class="hover:text-jannah-teal transition">من نحن</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">رؤيتنا ورسالتنا</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">الجوائز والإنجازات</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">فريق العمل</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">الخدمات</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-jannah-teal transition">النشر والإنتاج</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">التوزيع والشحن</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">المؤسسات التعليمية</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">الموزعون</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">النشرة البريدية</h4>
                    <p class="text-gray-400 text-sm mb-4">اشترك لتصلك آخر الإصدارات والعروض</p>
                    <form class="space-y-3">
                        <input type="email" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm focus:border-jannah-teal outline-none transition" placeholder="بريدك الإلكتروني">
                        <button type="submit" class="w-full gradient-pink text-white py-3 rounded-xl text-sm font-semibold hover:shadow-lg transition">
                            اشترك الآن
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-400">
                <p>© 2024 دار الجنة للنشر. جميع الحقوق محفوظة.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-jannah-teal transition">سياسة الخصوصية</a>
                    <a href="#" class="hover:text-jannah-teal transition">الشروط والأحكام</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>