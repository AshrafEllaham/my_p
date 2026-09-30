# دليل تنفيذ مشروع سعي

هذا الملف هو نقطة الانطلاق الإلزامية قبل تنفيذ أي طلب داخل المشروع. اقرأه كاملًا، ثم افحص الكود الموجود المرتبط بالمهمة قبل التعديل. لا تنشئ نمطًا موازيًا أو تختصر إحدى الطبقات لمجرد أن المهمة صغيرة.

## مرجع واجهات Figma

قبل تعديل أي شاشة أو preview داخل `Figma/`، اقرأ وطبّق:

- `Figma/DESIGN_REFERENCE.md`

هذا المرجع هو مصدر الحقيقة لاسم المشروع، الهوية البصرية، المقاسات، واتجاه الواجهات. لا تغيّر الثوابت أو تستبدلها بقيم عشوائية إلا بطلب صريح من المستخدم.

## المعمارية المعتمدة

كل feature يمر بالطبقات التالية فقط:

```text
HTTP Request
    ↓
FormRequest       validation فقط
    ↓
Controller        استقبال الطلب وإرجاع الرد فقط
    ↓
Service           business logic وحالات الاستخدام
    ↓
Repository        الاستعلامات والتعامل مع قاعدة البيانات
    ↓
Model             تعريف الجدول والـ casts والـ relations فقط
```

المجلدات الأساسية:

```text
app/Enums/
app/Models/Twenty/
app/Repositories/Twenty/
app/Services/Twenty/
app/Http/Controllers/Api/
app/Http/Requests/
app/Http/Resources/Api/
routes/api/
```

استخدم namespace فرعيًا مناسبًا للـ role أو domain عند الحاجة، مع الحفاظ على نفس التقسيم في جميع الطبقات.

## قواعد الطبقات

### Model

- مكانه الافتراضي `app/Models/Twenty/`.
- يحتوي فقط على اسم الجدول عند الحاجة، و`$fillable`، و`casts()`، والـ relations.
- استخدم Enum casts للأعمدة ذات القيم الثابتة، وcasts مناسبة للتواريخ والأرقام وJSON.
- ممنوع business logic أو authorization أو تنسيق responses أو استعلامات خاصة داخل الـ Model.
- لا تستخدم local scopes لإخفاء استعلامات business مخصصة؛ ضعها بوضوح داخل الـ Repository.
- لا تستخدم `$guarded = []`. عرّف `$fillable` صراحةً.

### Models متعددة اللغة

- استخدم `Astrotomic\Translatable\Translatable` فقط للـ Models التي تحتوي على بيانات مترجمة. حزمة Lexi وأي جدول ترجمة polymorphic موحد غير معتمدين في المشروع.
- كل Model مترجم له جدول ترجمة مستقل باسم `{model}_translations` وModel ترجمة مستقل باسم `{Model}Translation` بجوار Model الأساسي.
- الجدول الأساسي يحتوي على الحقول غير المترجمة فقط. جدول الترجمة يحتوي على `id`، وforeign key للجدول الأساسي مع `cascadeOnDelete()`، و`locale`، والحقول المترجمة، وtimestamps عند الحاجة.
- أضف unique composite index على `[foreign_key, locale]` لمنع تكرار ترجمة اللغة نفسها، وأضف index يبدأ بـ `locale` فقط عندما توجد استعلامات فعلية تبدأ بالفلترة حسب اللغة.
- داخل الـ Model الأساسي استخدم trait باسم `Translatable` وعرّف `public array $translatedAttributes` صراحةً. داخل Translation Model عرّف `$fillable` للحقول المترجمة فقط، ولا تضع business logic في أي منهما.
- Validation حقول الترجمة يكون بصيغة واضحة لكل لغة مثل `ar.name` و`en.name`، مع الاعتماد على اللغات المعرفة في `config/translatable.php` وعدم قبول locales أخرى.
- كل إنشاء أو تحديث للكيان وترجماته يتم داخل transaction واحدة في الـ Service، بينما تظل عمليات الحفظ والاستعلام داخل الـ Repository.
- عند عرض قوائم تستخدم خصائص مترجمة، حمّل relation باسم `translations` مسبقًا داخل الـ Repository. ممنوع الاعتماد على lazy loading من Resource أو داخل loop.
- الـ Resource يعرض ترجمة اللغة الحالية من العلاقات المحملة مسبقًا، ولا يشغّل query ولا يخزن الحقول المترجمة في JSON column داخل الجدول الأساسي.

### Repository

- مكانه الافتراضي `app/Repositories/Twenty/`.
- كل Repository يمتد من `MainRepository` ويحقن الـ Model في الـ constructor.
- كل عمليات Eloquent وQuery Builder والـ filters والـ joins والـ eager loading والـ persistence تكون هنا فقط.
- استخدم عمليات `MainRepository` الأساسية مثل `store`, `find`, `update`, و`delete` بدل تكرارها.
- تذكّر أن `MainRepository::store()` ينظف قيم `null` عبر `clearRequest()`.
- للوصول إلى الـ Model من خارج الـ Repository استخدم `getModel()`؛ ممنوع الوصول إلى `->model` لأنه `protected`.
- أعد `Builder` من methods الخاصة بالقوائم عندما يحتاج الـ caller إلى pagination أو composition؛ لا تحوّل النتيجة إلى Collection مبكرًا.
- ممنوع وضع قرارات business أو صلاحيات المستخدم داخل الـ Repository.

### Service

- مكانه الافتراضي `app/Services/Twenty/`.
- يحتوي على business logic، الصلاحيات المرتبطة بحالة الاستخدام، state transitions، والتنسيق بين أكثر من Repository.
- يحقن Repository أو Repository Contract في الـ constructor.
- ممنوع استخدام Model أو Eloquent أو Query Builder مباشرة داخل الـ Service.
- ممنوع قراءة HTTP Request أو استدعاء `auth()` داخل الـ Service؛ مرّر المستخدم أو المعرّفات والبيانات المطلوبة من الـ Controller.
- استخدم database transaction للعملية التي تتضمن عدة عمليات كتابة يجب أن تنجح أو تفشل كوحدة واحدة، مع إبقاء الاستعلامات نفسها داخل Repositories.
- ألقِ exceptions واضحة مثل `ValidationException` أو domain-specific exception، ولا تُرجع HTTP responses من الـ Service.

### Controller

- مكانه تحت `app/Http/Controllers/Api/` حسب الـ role أو domain.
- يكون رفيعًا: يستقبل FormRequest، يحصل على المستخدم الحالي، يستدعي Service، ثم يشكل الرد.
- يحقن Service أو Service Contract في الـ constructor.
- يحصل على المستخدم من `auth('api')->user()` أو المعرّف من `auth('api')->id()`.
- يستخدم `jsonSuccess()`, `generalReturn()`, أو `jsonPaginate()` حسب نوع النتيجة.
- ممنوع validation يدوي، أو business logic، أو Eloquent queries، أو إدارة transaction داخل الـ Controller.
- استخدم type declarations واضحة للمدخلات والنتائج مثل `JsonResponse`.

### FormRequest

- مسؤوليته validation وتهيئة بيانات validation فقط.
- `authorize()` تعيد `true` ما دام middleware هو المسؤول عن حماية المسار.
- استخدم `Rule::enum(SomeEnum::class)` بدل كتابة القيم الثابتة يدويًا.
- استخدم قواعد قاعدة البيانات مثل `exists` و`unique` عند كونها validation بسيطة؛ أما قرارات الصلاحية والحالة فتكون في الـ Service.
- مرّر `$request->validated()` فقط إلى الـ Service، ولا تمرّر Request كاملًا.

### Resource

- مكانه تحت `app/Http/Resources/Api/`.
- مسؤول فقط عن شكل JSON وأسماء الحقول والـ nested resources.
- استخدم `Resource::make()` للعنصر و`Resource::collection()` للمجموعات.
- استخدم `whenLoaded()` و`whenCounted()` للعلاقات والعدادات؛ ممنوع تشغيل query من داخل Resource.
- لا تُضف business calculations أو authorization queries داخل Resource.

### Enum

- أي عمود له مجموعة قيم ثابتة مثل `status`, `type`, أو `priority` يجب أن يكون backed Enum داخل `app/Enums/`.
- استخدم الـ Enum نفسه في migration، وModel cast، وFormRequest، وService، والـ Resource.
- ممنوع تكرار القيمة كنص خام مثل `'pending'` خارج تعريف الـ Enum.
- أضف `label()` عند الحاجة، واجعل النصوص المعروضة قابلة للترجمة عبر ملفات اللغة.

### Routes

- ملفات API مقسمة حسب الدور أو المجال تحت `routes/api/`، مثل `teacher.php`, `assistant.php`, `course.php`, `user.php`, و`school.php`.
- كل route group محمي بـ `auth:api` و`throttle:60,1` وأي middleware خاص بالدور.
- استخدم أسماء routes واضحة وRESTful، ولا تضع closures تحتوي logic.

## معمارية لوحة التحكم

- ملف علامة سعي الموحد للواجهة هو `public/assets/brand/saey-mark.svg`، ويُعرض عبر Blade component باسم `<x-admin.brand-mark />`. ممنوع رسم نسخة inline أو إنشاء ملف شعار موازٍ؛ عدّل الأصل الموحد فقط عندما يطلب المستخدم تغيير العلامة.
- اللغتان المدعومتان هما العربية `ar` والإنجليزية `en` فقط، وتُضبطان في `config/laravellocalization.php` و`config/translatable.php`. أي نص واجهة جديد يجب إضافته في `lang/ar/` و`lang/en/` بنفس المفتاح، وممنوع تضمين نص واجهة ثابت داخل Blade.
- تبديل لغة لوحة التحكم يتم عبر `<x-admin.language-switch />` ومسار `admin.locale`، ويحفظ الاختيار في الـ session من خلال `App\Http\Middleware\SetLocale`. يجب أن يبقى `lang` و`dir` في الـ master layout ديناميكيين وأن تُراجع الواجهة في وضعي RTL وLTR.
- Controllers لوحة التحكم تحت `app/Http/Controllers/Admin/`، وServices الخاصة بها تحت `app/Services/Admin/`، وModels الخاصة بهوية وإعدادات الإدارة تحت `app/Models/Admin/`.
- Models الدومين المشتركة تظل تحت `app/Models/Twenty/` وتستخدمها الـ API والـ Dashboard دون نسخها.
- كل routes لوحة التحكم موجودة في `routes/admin.php` تحت prefix وname باسم `admin`.
- كل routes لوحة التحكم محمية بـ `auth:admin`، والاستثناء الوحيد هو routes تسجيل الدخول التي تستخدم `guest:admin` مع rate limiting على محاولة الدخول.
- الـ master layout الوحيد هو `resources/views/admin/layout/indexs/index.blade.php`، وكل صفحات الإدارة تمتد منه.
- أجزاء `sidebar`, `header`, `footer`, `_css`, `_js`, `breadcrumb`, و`global_modals` تبقى partials مستقلة تحت `resources/views/admin/layout/inc/`.
- كل قسم في الواجهة يحتوي على صفحات كاملة مثل `index`, `show`, و`pending`، بينما HTML الذي يرجع داخل modal يوضع تحت `parts/`.
- نمط صفحات القوائم: الطلب العادي يعيد View، وطلب AJAX يعيد DataTables JSON من Builder محسن ومفلتر.
- نمط صفحات modal: يتم render لملف `parts/*.blade.php` ثم إعادة HTML داخل JSON دون إرجاع layout كامل.
- عمليات approve/reject/update الخاصة بالـ AJAX تستخدم `dashBoardJson()` الموحد الموجود في base Controller.
- استخدم helpers المشتركة مثل `showButton`, `settingsButton`, `banButton`, و`helperTrans` بدل تكرار HTML للأفعال.
- Admin Services منفصلة تمامًا عن Twenty Services؛ الأولى لقرارات المشرف، والثانية لحالات استخدام الـ API.
- **استثناء لوحة التحكم:** يجوز لـ `App\Services\Admin` حقن Model والتعامل معه مباشرة عندما يكون الاستعلام خاصًا بالـ Dashboard ومعقدًا ولا يحقق Repository abstraction قيمة واضحة. هذا الاستثناء لا ينطبق على `App\Services\Twenty`.
- عند استخدام الاستثناء السابق، يجب أن يظل الاستعلام داخل Admin Service فقط، مع eager loading وselects وpagination وindexes المناسبة، ومنع N+1. إذا أُعيد استخدام الاستعلام أو تضخم الـ Service، انقله إلى Admin Repository مخصص.

## SOLID وDependency Injection

- **Single Responsibility:** لا تنقل مسؤولية طبقة إلى طبقة أخرى. قسّم Service أو Repository عندما يصبح له أكثر من سبب مستقل للتغيير.
- **Open/Closed:** فضّل إضافة strategy أو implementation جديدة على تعديل شروط مركزية ضخمة.
- **Liskov Substitution:** أي implementation لعقد يجب أن يحافظ على نفس أنواع المدخلات والنتائج والسلوك المتوقع.
- **Interface Segregation:** استخدم عقودًا صغيرة ومركزة، ولا تنشئ interface ضخمًا يجبر implementations على methods لا تحتاجها.
- **Dependency Inversion:** الطبقات العليا تعتمد على contracts عند وجود حد قابل للاستبدال أو الاختبار، وتُربط implementations في Service Provider.
- استخدم constructor injection فقط. ممنوع إنشاء dependency بـ `new` داخل Controller أو Service.
- لا تنشئ interfaces شكلية بلا فائدة لكل class تلقائيًا؛ أنشئ contract عندما توجد حاجة فعلية للعزل، أو الاستبدال، أو الاختبار، أو أكثر من implementation.
- استخدم DTO/Value Object عندما تصبح الـ arrays بين الطبقات كبيرة أو غير واضحة، مع عدم تمرير كائن Request خارج طبقة HTTP.
- تجنب Service Locator والـ facades داخل business logic عندما يمكن حقن dependency واضحة.

## تحسين الاستعلامات ومنع N+1

هذه القواعد إلزامية في كل endpoint يقرأ بيانات:

- حدّد العلاقات التي يحتاجها الـ Resource مسبقًا، وحمّلها في الـ Repository باستخدام `with()` أو `loadMissing()` المناسب.
- ممنوع تنفيذ query داخل loop، أو accessor، أو Resource، أو relation iteration.
- استخدم `withCount()`, `withSum()`, و`withExists()` بدل تحميل العلاقات كاملة لمجرد الحساب أو التحقق.
- استخدم `whereHas()` أو `whereExists()` للتحقق والفلترة بدل تحميل records في الذاكرة.
- استخدم `select()` للأعمدة المطلوبة في الاستعلامات الثقيلة، مع تضمين primary keys وforeign keys اللازمة للعلاقات.
- عند تحديد أعمدة relation داخل `with()`, لا تنسَ مفتاح العلاقة اللازم لربط النتائج.
- استخدم pagination على مستوى قاعدة البيانات. لا تستخدم `get()` ثم `slice()` أو pagination يدوي في الذاكرة.
- استخدم `simplePaginate()` عندما لا نحتاج إجمالي العدد، و`cursorPaginate()` للقوائم الكبيرة المستقرة متى كان ذلك مناسبًا.
- استخدم `chunkById()` أو `lazyById()` للمعالجة الدفعية، ولا تحمل جدولًا كبيرًا كاملًا في الذاكرة.
- استخدم `exists()` بدل `count() > 0`، واستخدم `value()` أو `pluck()` بدل جلب Models كاملة عند الحاجة لقيم محددة فقط.
- تجنب `SELECT *` والـ joins غير اللازمة والـ duplicate queries.
- لا تستخدم `DB::raw()` إلا لسبب واضح وآمن، مع parameter binding ومنع إدخال المستخدم الخام.
- أضف indexes في migration للأعمدة المستخدمة فعليًا في foreign keys و`WHERE` و`JOIN` و`ORDER BY`، واستخدم composite index بما يطابق نمط الاستعلام وترتيب أعمدته.
- راجع query plan بـ `EXPLAIN` عند إضافة استعلام ثقيل أو غير مباشر، ووثّق سبب أي اختيار غير بديهي.
- في بيئة التطوير والاختبارات، اعتبر lazy loading غير المقصود خطأ يجب إصلاحه، لا سلوكًا مقبولًا.

## الكتابة والتزامن وسلامة البيانات

- استخدم transaction للعمليات متعددة الخطوات التي يجب أن تكون atomic.
- لا تفتح transaction حول calls خارجية بطيئة. افصل التكامل الخارجي واستخدم jobs/events بعد نجاح commit عند الحاجة.
- استخدم `lockForUpdate()` فقط عند وجود race condition حقيقية وداخل transaction قصيرة.
- اجعل العمليات القابلة للتكرار idempotent عندما يمكن أن يعيد العميل أو الـ queue المحاولة.
- لا تستخدم mass assignment ببيانات غير validated.

## Responses والـ Helpers

- `jsonSuccess($data, $msg)` للرد الناجح، ويقبل Model أو Resource أو array.
- `generalReturn($request, $query, Resource::class, $msg)` للقوائم، ويطبق pagination عندما تكون `pagination=on`.
- `jsonPaginate($paginator, $msg)` لرد pagination جاهز.
- حافظ على شكل response موحد، واستخدم مفاتيح الترجمة بدل النصوص المضمنة مباشرة.

## الاختبارات وجودة التنفيذ

- كل behavior جديد يحتاج Feature Test يغطي المسار الناجح، validation، authorization، والحالات المهمة للفشل.
- اختبر state transitions والـ business rules في Service tests عند تعقيدها.
- أضف اختبارًا يلتقط N+1 أو راقب عدد الاستعلامات في endpoints الحساسة عندما يكون ذلك عمليًا.
- استخدم factories بدل إنشاء بيانات الاختبار يدويًا بشكل متكرر.
- شغّل الاختبارات المرتبطة أولًا، ثم مجموعة الاختبارات الكاملة قبل التسليم.
- شغّل formatter وأدوات التحليل الساكن المعتمدة في المشروع قبل التسليم.
- لا تغيّر behavior غير مرتبط بالمهمة، ولا تعدّل ملفات المستخدم الحالية دون ضرورة.

## خطوات التنفيذ الإلزامية لأي Feature

1. افهم حالة الاستخدام وافحص الكود والـ conventions الموجودة المرتبطة بها.
2. حدّد الـ Enum والـ migration/indexes والعلاقات المطلوبة.
3. نفّذ أو حدّث Model كتعريف بيانات فقط.
4. ضع كل الاستعلامات في Repository مع eager loading وpagination المناسبين.
5. ضع القرارات والتنسيق وstate transitions في Service.
6. أنشئ FormRequest للـ validation وResource لشكل الرد.
7. اجعل Controller رفيعًا وأضف route داخل ملف الدور الصحيح مع middleware المناسب.
8. أضف الترجمات والاختبارات اللازمة.
9. راجع N+1، الأعمدة المحملة، indexes، وعدد الاستعلامات قبل اعتبار المهمة مكتملة.
10. شغّل الاختبارات والـ formatter، ثم لخّص ما تغير وأي افتراضات مهمة.

## قائمة مراجعة قبل التسليم

- لا يوجد query خارج Repository، باستثناء استعلامات `App\Services\Admin` المسموحة والمبررة في قسم معمارية لوحة التحكم.
- لا يوجد Model أو Eloquent داخل Twenty Service أو أي Controller، وأي استخدام مباشر داخل Admin Service يلتزم بالاستثناء الموثق أعلاه.
- لا يوجد business logic داخل Model أو Controller أو Resource.
- لا توجد قيم status/type/priority مكتوبة كنص بدل Enum.
- لا توجد queries داخل loops أو Resources.
- كل Model متعدد اللغة يستخدم Astrotomic وله Translation Model وجدول `{model}_translations` مستقل مع unique index على الـ foreign key والـ locale.
- كل العلاقات المستخدمة محملة مسبقًا دون N+1.
- الاستعلامات الكبيرة paginated وتحمّل الأعمدة المطلوبة فقط.
- الـ indexes المناسبة موجودة في migrations.
- dependencies محقونة، ومسؤولية كل class واحدة وواضحة.
- response يستخدم Resource والـ helper المناسب.
- الاختبارات ذات الصلة ناجحة.

## مراجعة إلزامية بعد انتهاء أي مهمة

هذه المرحلة شرط لإتمام أي مهمة، وليست خطوة اختيارية:

1. بعد الانتهاء من التنفيذ وقبل إرسال الرد النهائي، أعد قراءة ملف `AGENTS.md` كاملًا من القرص؛ لا تعتمد على الذاكرة أو على قراءة سابقة في بداية المهمة.
2. افحص كل الملفات التي أُنشئت أو عُدّلت، وراجع الـ diff الفعلي للتأكد أن التغيير محدود في نطاق الطلب.
3. طابق التغييرات بندًا بندًا مع قواعد الطبقات، وSOLID، وتحسين الاستعلامات، ومنع N+1، وسلامة البيانات، والاختبارات الواردة في هذا الملف.
4. عند تعديل ملفات داخل `Figma/`، أعد مراجعة `Figma/DESIGN_REFERENCE.md` أيضًا وقارن النتيجة به.
5. أصلح أي مخالفة يتم اكتشافها، ثم أعد تشغيل الاختبارات والـ formatter والفحوص المرتبطة بالتغيير.
6. لا تعتبر المهمة مكتملة ولا ترسل الرد النهائي قبل انتهاء هذه المراجعة بنجاح.
7. اذكر في ملخص التسليم أن مراجعة الالتزام بـ `AGENTS.md` تمت، ووضّح أي استثناء اضطراري أو بند تعذر التحقق منه بدل تجاهله.
