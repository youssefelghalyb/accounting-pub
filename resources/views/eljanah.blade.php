<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دار الجنة للنشر - كتب الأطفال</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jannah-teal': '#4A9B8E',
                        'jannah-pink': '#E31C79',
                        'jannah-light': '#F8FFFE',
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
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="bg-jannah-light">
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/janah.png') }}" alt="دار الجنة للنشر" class="h-12">
                </div>
                <div class="flex items-center gap-8">
                    <a href="#" class="text-jannah-teal hover:text-jannah-pink transition">الرئيسية</a>
                    <a href="#about" class="text-gray-600 hover:text-jannah-pink transition">من نحن</a>
                    <a href="#contact" class="text-gray-600 hover:text-jannah-pink transition">تواصل معنا</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-jannah-teal/10 to-jannah-pink/10 py-20">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <div class="inline-block bg-jannah-pink/10 text-jannah-pink px-4 py-2 rounded-full text-sm font-semibold">
                        ✨ رائدون في نشر كتب الأطفال منذ 2009
                    </div>
                    <h1 class="text-5xl font-bold text-gray-800 leading-tight">
                        نبني جيلاً من <span class="text-jannah-pink">القرّاء</span><br/>
                        المبدعين و<span class="text-jannah-teal">المفكرين</span>
                    </h1>
                    <p class="text-xl text-gray-600 leading-relaxed">
                        دار الجنة للنشر هي منارة للإبداع في عالم أدب الطفل العربي، نقدم محتوى تربوياً هادفاً يجمع بين المتعة والفائدة، ويغرس القيم الأصيلة في نفوس أطفالنا.
                    </p>
                    <div class="flex gap-4">
                        <a href="#about" class="bg-jannah-pink text-white px-8 py-3 rounded-full font-semibold hover:bg-jannah-pink/90 transition shadow-lg">
                            تعرف علينا
                        </a>
                        <a href="#books" class="bg-white text-jannah-teal px-8 py-3 rounded-full font-semibold hover:bg-gray-50 transition border-2 border-jannah-teal">
                            إصداراتنا
                        </a>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="absolute top-0 left-0 w-72 h-72 bg-jannah-teal/20 rounded-full blur-3xl animate-float"></div>
                    <div class="absolute bottom-0 right-0 w-72 h-72 bg-jannah-pink/20 rounded-full blur-3xl" style="animation-delay: 1.5s;"></div>
                    <div class="relative bg-white p-8 rounded-3xl shadow-2xl">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-jannah-teal/10 rounded-2xl p-6 text-center hover:scale-105 transition">
                                <div class="text-4xl mb-2">📚</div>
                                <div class="text-3xl font-bold text-jannah-teal">+250</div>
                                <div class="text-sm text-gray-600 mt-1">إصدار متنوع</div>
                            </div>
                            <div class="bg-jannah-pink/10 rounded-2xl p-6 text-center hover:scale-105 transition">
                                <div class="text-4xl mb-2">👥</div>
                                <div class="text-3xl font-bold text-jannah-pink">+100K</div>
                                <div class="text-sm text-gray-600 mt-1">قارئ صغير</div>
                            </div>
                            <div class="bg-jannah-pink/10 rounded-2xl p-6 text-center hover:scale-105 transition">
                                <div class="text-4xl mb-2">✍️</div>
                                <div class="text-3xl font-bold text-jannah-pink">+45</div>
                                <div class="text-sm text-gray-600 mt-1">كاتب وكاتبة</div>
                            </div>
                            <div class="bg-jannah-teal/10 rounded-2xl p-6 text-center hover:scale-105 transition">
                                <div class="text-4xl mb-2">🏆</div>
                                <div class="text-3xl font-bold text-jannah-teal">22</div>
                                <div class="text-sm text-gray-600 mt-1">جائزة دولية</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    من نحن؟
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    قصة نجاح في عالم النشر والإبداع
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
                <div class="space-y-6">
                    <h3 class="text-3xl font-bold text-gray-800">
                        رحلتنا مع <span class="text-jannah-teal">الإبداع</span>
                    </h3>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        تأسست دار الجنة للنشر عام 2009 برؤية واضحة: أن نكون الخيار الأول للأسر العربية التي تبحث عن محتوى نوعي لأطفالها. بدأنا بـ 5 إصدارات فقط، واليوم نفخر بمكتبة تضم أكثر من 250 كتاباً في مختلف المجالات.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        نؤمن بأن الكتاب الجيد هو أفضل هدية يمكن تقديمها للطفل، فهو يفتح له آفاق المعرفة، ويطور مهاراته اللغوية والإبداعية، ويغرس فيه القيم والأخلاق الحميدة.
                    </p>
                    <div class="grid grid-cols-3 gap-4 pt-4">
                        <div class="bg-jannah-teal/10 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-jannah-teal mb-1">15+</div>
                            <div class="text-sm text-gray-600">سنة خبرة</div>
                        </div>
                        <div class="bg-jannah-pink/10 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-jannah-pink mb-1">12</div>
                            <div class="text-sm text-gray-600">دولة عربية</div>
                        </div>
                        <div class="bg-jannah-teal/10 p-4 rounded-xl text-center">
                            <div class="text-2xl font-bold text-jannah-teal mb-1">98%</div>
                            <div class="text-sm text-gray-600">رضا العملاء</div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="bg-gradient-to-br from-jannah-teal to-jannah-teal/80 p-8 rounded-2xl text-white shadow-xl hover:scale-105 transition">
                                <div class="text-5xl mb-3">📖</div>
                                <div class="font-bold text-lg">محتوى أصيل</div>
                                <p class="text-sm mt-2 text-white/80">كتب تراعي الهوية العربية والإسلامية</p>
                            </div>
                            <div class="bg-gradient-to-br from-jannah-pink to-jannah-pink/80 p-8 rounded-2xl text-white shadow-xl hover:scale-105 transition">
                                <div class="text-5xl mb-3">🎨</div>
                                <div class="font-bold text-lg">رسوم احترافية</div>
                                <p class="text-sm mt-2 text-white/80">فنانون موهوبون يبدعون في كل صفحة</p>
                            </div>
                        </div>
                        <div class="space-y-4 mt-8">
                            <div class="bg-gradient-to-br from-jannah-pink to-jannah-pink/80 p-8 rounded-2xl text-white shadow-xl hover:scale-105 transition">
                                <div class="text-5xl mb-3">✨</div>
                                <div class="font-bold text-lg">جودة عالية</div>
                                <p class="text-sm mt-2 text-white/80">طباعة فاخرة على ورق صديق للبيئة</p>
                            </div>
                            <div class="bg-gradient-to-br from-jannah-teal to-jannah-teal/80 p-8 rounded-2xl text-white shadow-xl hover:scale-105 transition">
                                <div class="text-5xl mb-3">🌟</div>
                                <div class="font-bold text-lg">تطوير مستمر</div>
                                <p class="text-sm mt-2 text-white/80">نواكب أحدث الاتجاهات التربوية</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section id="vision" class="py-20 bg-gradient-to-br from-jannah-teal/5 to-jannah-pink/5">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    رؤيتنا و<span class="text-jannah-pink">رسالتنا</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    نحو مستقبل مشرق لأطفالنا
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <div class="bg-white p-10 rounded-3xl shadow-xl hover:shadow-2xl transition">
                    <div class="bg-jannah-teal/10 w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-4xl">🎯</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">رؤيتنا</h3>
                    <p class="text-lg text-gray-600 leading-relaxed mb-4">
                        أن نكون الناشر الرائد في عالم أدب الطفل العربي، والمرجع الأول للأسر والمؤسسات التعليمية التي تسعى لتنشئة جيل واعٍ ومثقف.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-teal text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">الريادة في النشر العربي للأطفال</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-teal text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">الانتشار في كافة الدول العربية</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-teal text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">التميز في الجودة والإبداع</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-10 rounded-3xl shadow-xl hover:shadow-2xl transition">
                    <div class="bg-jannah-pink/10 w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-4xl">💫</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">رسالتنا</h3>
                    <p class="text-lg text-gray-600 leading-relaxed mb-4">
                        نسعى لإثراء المحتوى العربي الموجه للطفل بكتب نوعية تجمع بين المتعة والفائدة، وتساهم في بناء شخصيته وتطوير مهاراته.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-pink text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">نشر القيم الأصيلة والأخلاق الحميدة</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-pink text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">تطوير مهارات التفكير والإبداع</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="bg-jannah-pink text-white rounded-full p-1 mt-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700">غرس حب القراءة والمعرفة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    قيمنا <span class="text-jannah-teal">الأساسية</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    المبادئ التي نلتزم بها في كل ما نقدمه
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition group">
                    <div class="bg-white w-16 h-16 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition shadow-lg">
                        <span class="text-3xl">🎨</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">الإبداع</h3>
                    <p class="text-gray-600">نبحث دائماً عن الجديد والمبتكر في كل إصدار نقدمه</p>
                </div>

                <div class="bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition group">
                    <div class="bg-white w-16 h-16 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition shadow-lg">
                        <span class="text-3xl">⭐</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">الجودة</h3>
                    <p class="text-gray-600">نلتزم بأعلى معايير الجودة في المحتوى والطباعة</p>
                </div>

                <div class="bg-gradient-to-br from-jannah-teal/5 to-jannah-teal/10 p-8 rounded-2xl hover:shadow-xl transition group">
                    <div class="bg-white w-16 h-16 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition shadow-lg">
                        <span class="text-3xl">🤝</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">المصداقية</h3>
                    <p class="text-gray-600">نحرص على تقديم محتوى موثوق وصادق لأطفالنا</p>
                </div>

                <div class="bg-gradient-to-br from-jannah-pink/5 to-jannah-pink/10 p-8 rounded-2xl hover:shadow-xl transition group">
                    <div class="bg-white w-16 h-16 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition shadow-lg">
                        <span class="text-3xl">💚</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">المسؤولية</h3>
                    <p class="text-gray-600">نشعر بمسؤوليتنا تجاه تنشئة الأجيال القادمة</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-20 bg-gradient-to-br from-jannah-light to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    تصنيفات <span class="text-jannah-teal">إصداراتنا</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    مكتبة شاملة تناسب جميع الأعمار والاهتمامات
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-6 mb-12">
                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-teal">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">📖</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">قصص تربوية</h3>
                    <p class="text-gray-600 text-sm mb-3">قصص هادفة تغرس القيم والأخلاق الحميدة</p>
                    <span class="text-jannah-teal font-semibold text-sm">+85 إصدار</span>
                </div>
                
                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-pink">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🎨</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">كتب التلوين</h3>
                    <p class="text-gray-600 text-sm mb-3">إبداع وفن ينمي المهارات الحركية</p>
                    <span class="text-jannah-pink font-semibold text-sm">+42 إصدار</span>
                </div>
                
                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-teal">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🔬</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">العلوم المبسطة</h3>
                    <p class="text-gray-600 text-sm mb-3">اكتشافات علمية بطريقة شيقة ومسلية</p>
                    <span class="text-jannah-teal font-semibold text-sm">+38 إصدار</span>
                </div>
                
                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-pink">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🌙</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">قصص قبل النوم</h3>
                    <p class="text-gray-600 text-sm mb-3">حكايات جميلة لنوم هادئ وأحلام سعيدة</p>
                    <span class="text-jannah-pink font-semibold text-sm">+52 إصدار</span>
                </div>

                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-pink">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">📚</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">الموسوعات</h3>
                    <p class="text-gray-600 text-sm mb-3">معلومات شاملة في مختلف المجالات</p>
                    <span class="text-jannah-pink font-semibold text-sm">+18 إصدار</span>
                </div>

                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-teal">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🧩</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">الألغاز والألعاب</h3>
                    <p class="text-gray-600 text-sm mb-3">تنمية الذكاء والتفكير المنطقي</p>
                    <span class="text-jannah-teal font-semibold text-sm">+33 إصدار</span>
                </div>

                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-pink">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🕌</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">القصص الإسلامي</h3>
                    <p class="text-gray-600 text-sm mb-3">قصص من التراث الإسلامي العظيم</p>
                    <span class="text-jannah-pink font-semibold text-sm">+45 إصدار</span>
                </div>

                <div class="group bg-white p-8 rounded-2xl hover:shadow-xl transition cursor-pointer border-2 border-transparent hover:border-jannah-teal">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition">🌍</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">ثقافات العالم</h3>
                    <p class="text-gray-600 text-sm mb-3">رحلة معرفية حول العالم وثقافاته</p>
                    <span class="text-jannah-teal font-semibold text-sm">+27 إصدار</span>
                </div>
            </div>
        </div>
    </section>

    {{-- <!-- Featured Books Gallery -->
    <section id="books" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    من <span class="text-jannah-pink">إصداراتنا</span> المميزة
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    اكتشف مجموعة من أحدث وأشهر إصداراتنا
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- Book Showcase 1 -->
                <div class="bg-gradient-to-br from-jannah-teal/5 to-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition group">
                    <div class="h-80 bg-gradient-to-br from-jannah-teal/20 to-jannah-teal/40 flex items-center justify-center relative overflow-hidden">
                        <div class="text-9xl group-hover:scale-110 transition">📚</div>
                        <div class="absolute top-4 right-4 bg-jannah-pink text-white px-4 py-2 rounded-full text-sm font-bold">
                            جديد 2024
                        </div>
                    </div>
                    <div class="p-6">
                        <span class="bg-jannah-teal/10 text-jannah-teal px-3 py-1 rounded-full text-xs font-semibold">قصص تربوية</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-3 mb-2">مغامرات سالم في الغابة</h3>
                        <p class="text-gray-600 mb-4">قصة مشوقة عن الصداقة والشجاعة، تعلّم الأطفال قيمة التعاون ومساعدة الآخرين في أوقات الشدة.</p>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <span>✍️</span>
                            <span>أ. محمد الأحمدي</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span>🎨</span>
                            <span>رسوم: فاطمة السيد</span>
                        </div>
                    </div>
                </div>

                <!-- Book Showcase 2 -->
                <div class="bg-gradient-to-br from-jannah-pink/5 to-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition group">
                    <div class="h-80 bg-gradient-to-br from-jannah-pink/20 to-jannah-pink/40 flex items-center justify-center relative overflow-hidden">
                        <div class="text-9xl group-hover:scale-110 transition">🎨</div>
                        <div class="absolute top-4 right-4 bg-jannah-teal text-white px-4 py-2 rounded-full text-sm font-bold">
                            الأكثر مبيعاً
                        </div>
                    </div>
                    <div class="p-6">
                        <span class="bg-jannah-pink/10 text-jannah-pink px-3 py-1 rounded-full text-xs font-semibold">كتب تلوين</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-3 mb-2">كتاب التلوين الكبير</h3>
                        <p class="text-gray-600 mb-4">100 صفحة من المرح والإبداع، تحتوي على رسوم متنوعة تناسب مختلف الأعمار وتنمي المهارات الفنية.</p>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <span>🎨</span>
                            <span>فريق دار الجنة الفني</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span>📄</span>
                            <span>100 صفحة - ورق فاخر</span>
                        </div>
                    </div>
                </div>

                <!-- Book Showcase 3 -->
                <div class="bg-gradient-to-br from-jannah-teal/5 to-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition group">
                    <div class="h-80 bg-gradient-to-br from-jannah-teal/20 to-jannah-pink/20 flex items-center justify-center relative overflow-hidden">
                        <div class="text-9xl group-hover:scale-110 transition">🌟</div>
                        <div class="absolute top-4 right-4 bg-jannah-pink text-white px-4 py-2 rounded-full text-sm font-bold">
                            حاصل على جوائز
                        </div>
                    </div>
                    <div class="p-6">
                        <span class="bg-jannah-teal/10 text-jannah-teal px-3 py-1 rounded-full text-xs font-semibold">علوم مبسطة</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-3 mb-2">عالم الاكتشافات العلمية</h3>
                        <p class="text-gray-600 mb-4">رحلة علمية ممتعة للأطفال، تشرح المفاهيم العلمية بطريقة بسيطة ومسلية مع تجارب عملية سهلة.</p>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <span>✍️</span>
                            <span>د. أحمد الشريف</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span>🏆</span>
                            <span>جائزة أفضل كتاب علمي 2023</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="#contact" class="inline-block bg-jannah-pink text-white px-10 py-4 rounded-full font-semibold hover:bg-jannah-pink/90 transition shadow-lg text-lg">
                    استعرض الكتالوج الكامل
                </a>
            </div>
        </div>
    </section> --}}

    <!-- Authors Section -->
    <section id="authors" class="py-20 bg-gradient-to-br from-jannah-light to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    <span class="text-jannah-teal">كُتّابنا</span> ورسامونا
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    نخبة من الكتّاب والرسامين الموهوبين الذين يبدعون معنا
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-jannah-teal to-jannah-teal/80 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold">
                        م.أ
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">أ. محمد الأحمدي</h3>
                    <p class="text-jannah-teal font-semibold text-sm mb-3">كاتب قصص الأطفال</p>
                    <p class="text-gray-600 text-sm">متخصص في القصص التربوية، له أكثر من 35 كتاباً منشوراً</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-jannah-pink to-jannah-pink/80 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold">
                        ف.س
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">فاطمة السيد</h3>
                    <p class="text-jannah-pink font-semibold text-sm mb-3">رسامة</p>
                    <p class="text-gray-600 text-sm">فنانة موهوبة، رسمت أكثر من 50 كتاباً بأسلوبها المميز</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-jannah-teal to-jannah-teal/80 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold">
                        د.أ
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">د. أحمد الشريف</h3>
                    <p class="text-jannah-teal font-semibold text-sm mb-3">كاتب علمي</p>
                    <p class="text-gray-600 text-sm">دكتور في الفيزياء، يبسّط العلوم للأطفال بطريقة ممتعة</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-jannah-pink to-jannah-pink/80 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold">
                        ل.ع
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">ليلى العمري</h3>
                    <p class="text-jannah-pink font-semibold text-sm mb-3">كاتبة</p>
                    <p class="text-gray-600 text-sm">متخصصة في قصص ما قبل النوم والخيال الهادف</p>
                </div>
            </div>

            <div class="bg-white p-10 rounded-3xl shadow-xl max-w-4xl mx-auto">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">
                        انضم إلى عائلة <span class="text-jannah-teal">دار الجنة</span>
                    </h3>
                    <p class="text-gray-600">
                        هل أنت كاتب أو رسام موهوب؟ نحن نبحث دائماً عن مواهب جديدة للانضمام إلى فريقنا
                    </p>
                </div>
                <div class="flex justify-center gap-4">
                    <a href="#contact" class="bg-jannah-teal text-white px-8 py-3 rounded-full font-semibold hover:bg-jannah-teal/90 transition">
                        تواصل معنا
                    </a>
                    <a href="#contact" class="bg-white text-jannah-teal px-8 py-3 rounded-full font-semibold hover:bg-gray-50 transition border-2 border-jannah-teal">
                        أرسل أعمالك
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Awards & Recognition -->
    <section id="awards" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    <span class="text-jannah-pink">جوائزنا</span> وإنجازاتنا
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    تقدير دولي وعربي لجهودنا في خدمة أدب الطفل
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="text-6xl mb-4 text-center">🏆</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">جائزة الشارقة لكتب الأطفال</h3>
                    <p class="text-gray-600 text-center mb-3">فزنا بالمركز الأول عن كتاب "رحلة في عالم الخيال"</p>
                    <p class="text-sm text-jannah-teal font-semibold text-center">2023</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="text-6xl mb-4 text-center">🌟</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">جائزة الناشر العربي المتميز</h3>
                    <p class="text-gray-600 text-center mb-3">تقديراً لجهودنا في نشر الثقافة والمعرفة</p>
                    <p class="text-sm text-jannah-teal font-semibold text-center">2022</p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-2xl shadow-lg hover:shadow-xl transition">
                    <div class="text-6xl mb-4 text-center">✨</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">جائزة أفضل كتاب علمي</h3>
                    <p class="text-gray-600 text-center mb-3">عن سلسلة "اكتشف العلوم" التعليمية</p>
                    <p class="text-sm text-jannah-teal font-semibold text-center">2023</p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-jannah-teal/5 to-jannah-pink/5 p-12 rounded-3xl">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-5xl font-bold text-jannah-teal mb-2">22+</div>
                        <p class="text-gray-600">جائزة محلية ودولية</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-pink mb-2">8</div>
                        <p class="text-gray-600">شهادات تقدير من وزارات التربية</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-teal mb-2">15+</div>
                        <p class="text-gray-600">مشاركة في معارض دولية</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-jannah-pink mb-2">50+</div>
                        <p class="text-gray-600">ترشيح لجوائز مختلفة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners & Distribution -->
    <section class="py-20 bg-gradient-to-br from-jannah-light to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    شركاؤنا و<span class="text-jannah-teal">انتشارنا</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    نفخر بشراكاتنا مع أبرز المؤسسات التعليمية والثقافية
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
                <div class="space-y-6">
                    <h3 class="text-3xl font-bold text-gray-800">
                        توزيع <span class="text-jannah-pink">واسع</span> ومتميز
                    </h3>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        كتبنا متوفرة في أكثر من 500 مكتبة ومكتبة مدرسية في 12 دولة عربية، كما نوفر خدمة التوصيل لجميع أنحاء الوطن العربي.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-jannah-teal text-white w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">المكتبات الكبرى</h4>
                                <p class="text-sm text-gray-600">شراكات مع أكبر سلاسل المكتبات</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="bg-jannah-pink text-white w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">المدارس والروضات</h4>
                                <p class="text-sm text-gray-600">توريد لأكثر من 200 مؤسسة تعليمية</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="bg-jannah-teal text-white w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">المعارض الدولية</h4>
                                <p class="text-sm text-gray-600">حضور دائم في أبرز معارض الكتاب</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-xl">
                    <h4 class="text-xl font-bold text-gray-800 mb-6 text-center">الدول التي نصل إليها</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇪🇬</span>
                            <span>مصر</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇸🇦</span>
                            <span>السعودية</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇦🇪</span>
                            <span>الإمارات</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇰🇼</span>
                            <span>الكويت</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇶🇦</span>
                            <span>قطر</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇧🇭</span>
                            <span>البحرين</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇴🇲</span>
                            <span>عُمان</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇯🇴</span>
                            <span>الأردن</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇱🇧</span>
                            <span>لبنان</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇲🇦</span>
                            <span>المغرب</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-teal">🇹🇳</span>
                            <span>تونس</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <span class="text-jannah-pink">🇩🇿</span>
                            <span>الجزائر</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    ماذا يقول <span class="text-jannah-pink">عملاؤنا</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    آراء الأهالي والمعلمين في كتبنا
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-jannah-teal/5 to-white p-8 rounded-2xl shadow-lg">
                    <div class="flex gap-1 mb-4">
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "كتب دار الجنة رائعة! أطفالي يحبون القصص والرسوم الجميلة، وأنا أحب القيم التربوية التي تحملها كل قصة."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-jannah-teal/20 rounded-full flex items-center justify-center text-jannah-teal font-bold">
                            س.م
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">سارة محمد</h4>
                            <p class="text-sm text-gray-600">أم لطفلين</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-jannah-pink/5 to-white p-8 rounded-2xl shadow-lg">
                    <div class="flex gap-1 mb-4">
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "استخدم كتب دار الجنة في مكتبة الفصل، والطلاب يتسابقون لاستعارتها. محتوى ممتاز وجودة طباعة عالية."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-jannah-pink/20 rounded-full flex items-center justify-center text-jannah-pink font-bold">
                            أ.ن
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">أ. نور العلي</h4>
                            <p class="text-sm text-gray-600">معلمة لغة عربية</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-jannah-teal/5 to-white p-8 rounded-2xl shadow-lg">
                    <div class="flex gap-1 mb-4">
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                        <span class="text-yellow-400 text-xl">⭐</span>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "ابنتي كانت لا تحب القراءة، لكن بعد أن أهديتها مجموعة من كتب دار الجنة، أصبحت تطلب كتاباً جديداً كل أسبوع!"
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-jannah-teal/20 rounded-full flex items-center justify-center text-jannah-teal font-bold">
                            خ.ع
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">خالد العبدالله</h4>
                            <p class="text-sm text-gray-600">والد</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gradient-to-br from-jannah-teal/5 to-jannah-pink/5">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">
                        <span class="text-jannah-pink">تواصل</span> معنا
                    </h2>
                    <p class="text-xl text-gray-600">
                        نسعد بالرد على استفساراتكم ومقترحاتكم
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-2xl shadow-xl">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">أرسل لنا رسالة</h3>
                        <form class="space-y-5">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">الاسم الكامل</label>
                                <input type="text" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-jannah-teal outline-none transition" placeholder="أدخل اسمك الكامل">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">البريد الإلكتروني</label>
                                <input type="email" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-jannah-teal outline-none transition" placeholder="example@email.com">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">رقم الهاتف</label>
                                <input type="tel" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-jannah-teal outline-none transition" placeholder="+20 123 456 7890">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">الموضوع</label>
                                <select class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-jannah-teal outline-none transition">
                                    <option>استفسار عام</option>
                                    <option>طلب توزيع</option>
                                    <option>انضمام للفريق</option>
                                    <option>استفسار عن كتاب</option>
                                    <option>أخرى</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">الرسالة</label>
                                <textarea rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-jannah-teal outline-none transition" placeholder="اكتب رسالتك هنا..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-jannah-pink text-white py-4 rounded-xl font-semibold hover:bg-jannah-pink/90 transition shadow-lg text-lg">
                                إرسال الرسالة
                            </button>
                        </form>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">
                            <div class="flex items-start gap-4">
                                <div class="bg-jannah-teal/10 p-4 rounded-xl flex-shrink-0">
                                    <svg class="w-7 h-7 text-jannah-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-2 text-lg">الهاتف</h3>
                                    <p class="text-gray-600 mb-1" dir="ltr">+20 123 456 7890</p>
                                    <p class="text-sm text-gray-500">السبت - الخميس: 9 صباحاً - 5 مساءً</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">
                            <div class="flex items-start gap-4">
                                <div class="bg-jannah-pink/10 p-4 rounded-xl flex-shrink-0">
                                    <svg class="w-7 h-7 text-jannah-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-2 text-lg">البريد الإلكتروني</h3>
                                    <p class="text-gray-600 mb-1">info@daraljannah.com</p>
                                    <p class="text-gray-600">sales@daraljannah.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">
                            <div class="flex items-start gap-4">
                                <div class="bg-jannah-teal/10 p-4 rounded-xl flex-shrink-0">
                                    <svg class="w-7 h-7 text-jannah-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-2 text-lg">العنوان</h3>
                                    <p class="text-gray-600 mb-1">شارع الجامعة، المعادي</p>
                                    <p class="text-gray-600">القاهرة، مصر</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition">
                            <div class="flex items-start gap-4">
                                <div class="bg-jannah-pink/10 p-4 rounded-xl flex-shrink-0">
                                    <svg class="w-7 h-7 text-jannah-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-2 text-lg">ساعات العمل</h3>
                                    <p class="text-gray-600 mb-1">السبت - الخميس: 9:00 ص - 5:00 م</p>
                                    <p class="text-gray-600">الجمعة: مغلق</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div>
                    <h3 class="text-2xl font-bold mb-4 text-jannah-pink">دار الجنة للنشر</h3>
                    <p class="text-gray-400 leading-relaxed mb-4">
                        نبني جيلاً من القرّاء المبدعين والمفكرين من خلال كتب نوعية تجمع بين المتعة والفائدة
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="bg-jannah-teal/20 p-3 rounded-full hover:bg-jannah-teal transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="bg-jannah-pink/20 p-3 rounded-full hover:bg-jannah-pink transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="bg-jannah-teal/20 p-3 rounded-full hover:bg-jannah-teal transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="bg-jannah-pink/20 p-3 rounded-full hover:bg-jannah-pink transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-lg">روابط سريعة</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-jannah-teal transition">الرئيسية</a></li>
                        <li><a href="#about" class="hover:text-jannah-teal transition">من نحن</a></li>
                        <li><a href="#vision" class="hover:text-jannah-teal transition">رؤيتنا ورسالتنا</a></li>
                        <li><a href="#books" class="hover:text-jannah-teal transition">إصداراتنا</a></li>
                        <li><a href="#authors" class="hover:text-jannah-teal transition">كُتّابنا</a></li>
                        <li><a href="#contact" class="hover:text-jannah-teal transition">تواصل معنا</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-lg">تصنيفات الكتب</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-jannah-teal transition">قصص تربوية</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">كتب التلوين</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">العلوم المبسطة</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">قصص قبل النوم</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">الموسوعات</a></li>
                        <li><a href="#" class="hover:text-jannah-teal transition">القصص الإسلامي</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-lg">اشترك في النشرة البريدية</h4>
                    <p class="text-gray-400 mb-4">احصل على آخر أخبار إصداراتنا والعروض الخاصة</p>
                    <form class="space-y-3">
                        <input type="email" class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-jannah-teal outline-none transition text-white" placeholder="بريدك الإلكتروني">
                        <button type="submit" class="w-full bg-jannah-pink text-white py-3 rounded-xl font-semibold hover:bg-jannah-pink/90 transition">
                            اشترك الآن
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400 text-sm">
                    <p>© 2024 دار الجنة للنشر. جميع الحقوق محفوظة.</p>
                    <div class="flex gap-6">
                        <a href="#" class="hover:text-jannah-teal transition">سياسة الخصوصية</a>
                        <a href="#" class="hover:text-jannah-teal transition">الشروط والأحكام</a>
                        <a href="#" class="hover:text-jannah-teal transition">سياسة الاسترجاع</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>