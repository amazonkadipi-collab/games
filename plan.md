# Poki Crazy Games — خطة تحسين الواجهة

## الهدف
تحويل بوابة الألعاب إلى تجربة منظمة وسريعة على نمط Poki: اكتشاف واضح من الصفحة الرئيسية، بطاقات ألعاب قابلة للمسح البصري، وصفحة لعب player-first تعمل على desktop وmobile.

## Design direction
- **الحركة البصرية:** editorial arcade / kinetic poster، مستوحاة من سرعة بوابات الألعاب الحديثة لكن بهوية PlayGrid مستقلة، ماشي clone من Poki أو CrazyGames.
- **المبادئ:** player-first، كثافة معلومات مضبوطة، contrast واضح، مسارات تنقل قصيرة، وواجهة مفهومة للكبار والصغار.
- **الألوان:** خلفية ورقية باردة (#f7f9fc) مع Midnight Navy (#101827) كأساس ثقة، Acid Lime (#d7ff4f) كـsignature action color، وCoral (#ff7468) للـenergy/labels.
- **التخطيط:** header ثابت + شريط تصنيفات أفقي قابل للتمرير + hero asymmetrical + sections متتابعة بgrid responsive، وصفحة اللعبة بمشغل واسع ثم metadata ثم الوصف ثم related games.
- **العناصر المميزة:** PlayGrid constellation mark، hero على شكل stacked game cards، category rail كـpills، game cards بنسبة 4:3 مع title strip، وPlay Now overlay على صفحة اللعبة.
- **التفاعل:** hover lift خفيف، focus states واضحة، Play Now لا يحمّل iframe قبل click، والـmobile يستعمل menu/search controls مضغوطة.
- **Typography:** Open Sans للنصوص الوظيفية وOswald للعناوين الموروثة من القالب، مع أحجام ثابتة ومتجاوبة.
- **الهوية:** بوابة ألعاب مجانية سريعة ومباشرة لعشاق browser games؛ الشخصية: جريئة، مرحة، موثوقة.
- **الصوت:** عناوين قصيرة وCTA مباشر مثل “Play something brilliant.” و“Find a game”.
- **Wordmark & Logo:** كلمة PLAYGRID بخط Oswald uppercase، مع mark شبكي من أربع وحدات بألوان lime/coral/cyan/white داخل إطار دائري مائل.
- **Signature brand color:** Acid Lime (#d7ff4f)، لون قابل للتملك ويظهر في CTA، focus states، badges وhover.

## التنفيذ
1. إصلاح شرط rendering الذي يخفي Poki header في `poki-pro`.
2. توحيد route alias للـPopular Games.
3. الحفاظ على card/grid/player CSS الحالي مع التأكد من أن الـheader يصل إلى HTML العام.
4. التحقق من static assets والـresponsive selectors ومسارات game pages.

## بنية المشروع
- `api/`: Vercel PHP entrypoints.
- `arcade_cms/index.php`: routing العام والـSEO endpoints.
- `arcade_cms/assets/includes/`: bootstrap والرندر المشترك.
- `arcade_cms/assets/sources/`: مصادر الصفحات والبيانات.
- `arcade_cms/templates/poki-like/`: header، صفحات home/game، CSS وJS والـassets.
- `vercel.json`: static asset routing وPHP catch-all.
