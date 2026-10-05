# Poki Crazy Games — خطة تحسين الواجهة

## الهدف
تحويل بوابة الألعاب إلى تجربة منظمة وسريعة على نمط Poki: اكتشاف واضح من الصفحة الرئيسية، بطاقات ألعاب قابلة للمسح البصري، وصفحة لعب player-first تعمل على desktop وmobile.

## Design direction
- **الحركة البصرية:** soft arcade / editorial grid مستوحاة من بوابات الألعاب الحديثة، بدون زخرفة زائدة.
- **المبادئ:** player-first، كثافة معلومات مضبوطة، contrast واضح، ومسارات تنقل قصيرة.
- **الألوان:** خلفية فاتحة باردة (#f7f8fa) مع الأزرق Poki (#00a4ff) كـsignature action color، وبطاقات بيضاء وحدود رمادية خفيفة للوضوح.
- **التخطيط:** header ثابت + شريط تصنيفات أفقي قابل للتمرير + sections متتابعة بgrid responsive، وصفحة اللعبة بمشغل واسع ثم metadata ثم الوصف ثم related games.
- **العناصر المميزة:** logo Poki، category rail أفقي، game cards بنسبة 4:3 مع title strip، وPlay Now overlay على صفحة اللعبة.
- **التفاعل:** hover lift خفيف، focus states واضحة، Play Now لا يحمّل iframe قبل click، والـmobile يستعمل menu/search controls مضغوطة.
- **Typography:** Open Sans للنصوص الوظيفية وOswald للعناوين الموروثة من القالب، مع أحجام ثابتة ومتجاوبة.
- **الهوية:** بوابة ألعاب مجانية سريعة ومباشرة لعشاق browser games؛ الشخصية: مرحة، واضحة، موثوقة.
- **الصوت:** عناوين قصيرة وCTA مباشر مثل “Play Now!” و“See all”.

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
