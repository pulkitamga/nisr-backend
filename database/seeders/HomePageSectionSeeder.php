<?php

namespace Database\Seeders;

use App\Models\HomePageSection;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class HomePageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['type' => 'main_banner', 'name' => 'Main Banner'],
            ['type' => 'trusted_by', 'name' => 'Trusted By'],
            ['type' => 'deals', 'name' => 'Deals'],
            ['type' => 'products', 'name' => 'Products'],
            ['type' => 'why_choose_us', 'name' => 'Why Choose Us'],
            ['type' => 'why_join_us', 'name' => 'Why Join Us'],
            ['type' => 'blog', 'name' => 'Blog'],
            ['type' => 'client_review', 'name' => 'Client Review'],
            ['type' => 'wholesaler_section', 'name' => 'Wholesaler Section'],
            ['type' => 'find_perfect_match', 'name' => 'Find Perfect Match'],
            ['type' => 'faq', 'name' => 'FAQ Asked Questions'],
            ['type' => 'download_app', 'name' => 'Download Mobile App'],
            [
                'type' => 'flagship_battery_families',
                'name' => 'Flagship Battery Families',
                'value' => [
                    'section' => [
                        'label' => 'Flagship battery families',
                        'title' => 'Battery families.',
                        'description' => '',
                        'cards' => [
                            [
                                'tag' => 'Passenger mobility',
                                'title' => 'Starting power shaped for everyday confidence.',
                                'description' => 'Designed for private vehicles that require dependable ignition performance, consistent reserve power, and clean fitment across daily driving conditions.',
                                'note' => 'Balanced for dependable urban use and repeated start cycles.',
                                'image' => '',
                                'image_alt' => 'Passenger mobility battery family',
                                'redirect_link' => '',
                            ],
                            [
                                'tag' => 'Commercial fleets',
                                'title' => 'Heavy-duty endurance for transport and fleet uptime.',
                                'description' => 'Engineered to support demanding route schedules, vibration resistance, and long operating windows where downtime carries operational cost.',
                                'note' => 'Built for fleet continuity, service discipline, and stable field performance.',
                                'image' => '',
                                'image_alt' => 'Commercial fleets battery family',
                                'redirect_link' => '',
                            ],
                            [
                                'tag' => 'Industrial reserve',
                                'title' => 'Reliable reserve energy for infrastructure and mission-critical loads.',
                                'description' => 'Structured for industrial standby needs, backup systems, and technically supervised applications where continuity is non-negotiable.',
                                'note' => 'Suited to controlled environments requiring durable reserve support.',
                                'image' => '',
                                'image_alt' => 'Industrial reserve battery family',
                                'redirect_link' => '',
                            ],
                        ],
                    ],
                ],
                'translations' => [
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => -1, 'value' => 'عائلات البطاريات الرائدة'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => -1, 'value' => 'عائلات البطاريات.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => -1, 'value' => ''],
                    ['locale' => 'ar', 'key' => 'tag', 'item_index' => 0, 'value' => 'حركة الركاب'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 0, 'value' => 'قدرة تشغيل صُممت لثقة الاستخدام اليومي.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 0, 'value' => 'مناسبة للمركبات الخاصة التي تحتاج إلى أداء تشغيل موثوق وطاقة احتياطية مستقرة وملاءمة نظيفة لظروف القيادة اليومية.'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 0, 'value' => 'توازن عملي للاستخدام الحضري ودورات التشغيل المتكررة.'],
                    ['locale' => 'ar', 'key' => 'image_alt', 'item_index' => 0, 'value' => 'عائلة بطاريات حركة الركاب'],
                    ['locale' => 'ar', 'key' => 'tag', 'item_index' => 1, 'value' => 'الأساطيل التجارية'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 1, 'value' => 'تحمل ثقيل يخدم النقل واستمرارية الأسطول.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 1, 'value' => 'هندسة تدعم الجداول التشغيلية المكثفة ومقاومة الاهتزاز وفترات التشغيل الطويلة حيث يمثل التوقف تكلفة تشغيلية مباشرة.'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 1, 'value' => 'مهيأة لاستمرارية الأسطول وانضباط الخدمة وثبات الأداء الميداني.'],
                    ['locale' => 'ar', 'key' => 'image_alt', 'item_index' => 1, 'value' => 'عائلة بطاريات الأساطيل التجارية'],
                    ['locale' => 'ar', 'key' => 'tag', 'item_index' => 2, 'value' => 'الاحتياطي الصناعي'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 2, 'value' => 'طاقة احتياطية موثوقة للبنية التحتية والأحمال الحرجة.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 2, 'value' => 'مهيأة للاحتياطي الصناعي وأنظمة الدعم الخلفي والتطبيقات الفنية التي لا تقبل انقطاع الاستمرارية.'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 2, 'value' => 'ملائمة للبيئات المضبوطة التي تتطلب دعماً احتياطياً متيناً.'],
                    ['locale' => 'ar', 'key' => 'image_alt', 'item_index' => 2, 'value' => 'عائلة بطاريات الاحتياطي الصناعي'],
                ],
            ],
            [
                'type' => 'core_capabilities',
                'name' => 'Core Capabilities',
                'value' => [
                    'section' => [
                        'label' => 'Core capabilities',
                        'title' => 'Manufacturing, recycling, and quality assurance aligned around one disciplined operating model.',
                        'description' => 'NISR’s CMS story is built around industrial credibility: controlled manufacturing environments, responsible recycling integration, and inspection culture that supports warranty confidence.',
                        'cards' => [
                            [
                                'title' => 'Manufacturing',
                                'description' => 'Production discipline focused on repeatability, throughput control, and dependable execution for market-ready battery systems.',
                            ],
                            [
                                'title' => 'Recycling',
                                'description' => 'A structured recovery path that supports resource efficiency and strengthens the environmental credibility of the overall operation.',
                            ],
                            [
                                'title' => 'Quality assurance',
                                'description' => 'Inspection logic, controlled checkpoints, and validation routines that reinforce durable performance in real operating environments.',
                            ],
                        ],
                    ],
                ],
                'translations' => [
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => -1, 'value' => 'القدرات الأساسية'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => -1, 'value' => 'التصنيع وإعادة التدوير وضمان الجودة ضمن نموذج تشغيلي واحد منضبط.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => -1, 'value' => 'تستند القصة المؤسسية للنسر إلى المصداقية الصناعية: بيئات تصنيع مضبوطة، وتكامل مسؤول لإعادة التدوير، وثقافة فحص تعزز الثقة في الضمان.'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 0, 'value' => 'التصنيع'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 0, 'value' => 'انضباط إنتاجي يركز على التكرار المحكم والتحكم في التدفق والتنفيذ الموثوق لأنظمة البطاريات الجاهزة للسوق.'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 1, 'value' => 'إعادة التدوير'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 1, 'value' => 'مسار استرجاع منظم يدعم كفاءة الموارد ويقوي المصداقية البيئية للعمليات.'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 2, 'value' => 'ضمان الجودة'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 2, 'value' => 'منطق فحص ونقاط تحقق وإجراءات تحقق تدعم الأداء المتين في بيئات التشغيل الفعلية.'],
                ],
            ],
            [
                'type' => 'closed_loop_lifecycle',
                'name' => 'Closed Loop Lifecycle',
                'value' => [
                    'section' => [
                        'label' => 'Closed-loop lifecycle',
                        'title' => 'A cleaner battery lifecycle from production through recovery and reuse.',
                        'description' => 'This section explains the operational loop clearly: manufacture, deploy, recover, recycle, and return material value to the system with disciplined process control.',
                        'value' => 'Closed-loop energy',
                        'cards' => [
                            [
                                'title' => 'Manufacture with controlled material inputs',
                                'description' => 'Battery production begins with disciplined sourcing, process control, and clearly defined assembly standards.',
                                'label' => 'Manufacture',
                                'note' => 'Controlled production',
                            ],
                            [
                                'title' => 'Deploy across mobility and industry',
                                'description' => 'Products are positioned to support passenger vehicles, fleets, and operational systems that depend on reliable stored power.',
                                'label' => 'Deploy',
                                'note' => 'Field performance',
                            ],
                            [
                                'title' => 'Recover and recycle responsibly',
                                'description' => 'Used batteries are brought back into a managed recycling stream to reduce waste and preserve material value.',
                                'label' => 'Recycle',
                                'note' => 'Managed recovery',
                            ],
                            [
                                'title' => 'Reintroduce value into the system',
                                'description' => 'Recovered material supports a more responsible operating model and reinforces the long-term industrial credibility of the brand.',
                                'label' => 'Reintroduce',
                                'note' => 'Material value',
                            ],
                        ],
                    ],
                ],
                'translations' => [
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => -1, 'value' => 'دورة حياة مغلقة'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => -1, 'value' => 'دورة بطارية أنظف من الإنتاج حتى الاسترجاع وإعادة الاستخدام.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => -1, 'value' => 'يوضح هذا القسم الحلقة التشغيلية بوضوح: تصنيع، تشغيل، استرجاع، إعادة تدوير، ثم إعادة إدخال القيمة المادية للنظام بضبط تشغيلي واضح.'],
                    ['locale' => 'ar', 'key' => 'value', 'item_index' => -1, 'value' => 'طاقة مغلقة الحلقة'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 0, 'value' => 'التصنيع بمدخلات خامات مضبوطة'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 0, 'value' => 'تبدأ عملية الإنتاج بمصادر منضبطة وتحكم تشغيلي ومعايير تجميع واضحة.'],
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => 0, 'value' => 'تصنيع'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 0, 'value' => 'إنتاج مضبوط'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 1, 'value' => 'التشغيل في الحركة والصناعة'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 1, 'value' => 'تُوجَّه المنتجات لدعم سيارات الركوب والأساطيل والأنظمة التشغيلية التي تعتمد على طاقة مخزنة موثوقة.'],
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => 1, 'value' => 'تشغيل'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 1, 'value' => 'أداء ميداني'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 2, 'value' => 'الاسترجاع وإعادة التدوير بمسؤولية'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 2, 'value' => 'تُعاد البطاريات المستهلكة إلى مسار تدوير منظم لتقليل الفاقد والحفاظ على قيمة المواد.'],
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => 2, 'value' => 'تدوير'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 2, 'value' => 'استرجاع منظم'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => 3, 'value' => 'إعادة إدخال القيمة إلى النظام'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => 3, 'value' => 'تدعم المواد المسترجعة نموذجاً تشغيلياً أكثر مسؤولية وتعزز المصداقية الصناعية طويلة الأمد للعلامة.'],
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => 3, 'value' => 'إعادة إدخال'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => 3, 'value' => 'قيمة مادية'],
                ],
            ],
            [
                'type' => 'next_step',
                'name' => 'Next Step',
                'value' => [
                    'section' => [
                        'label' => 'Next step',
                        'title' => 'Connect with NISR for corporate, dealer, and technical conversations.',
                        'description' => 'Move visitors toward a serious business action with a premium closing banner that maintains the industrial tone of the page.',
                        'button_text' => 'Contact Sales',
                        'button_link' => route('contacts'),
                        'note' => 'Track Warranty',
                        'secondary_button_link' => route('warranty.track.page'),
                        'image' => '',
                        'image_alt' => 'Battery product close-up',
                    ],
                ],
                'translations' => [
                    ['locale' => 'ar', 'key' => 'label', 'item_index' => -1, 'value' => 'الخطوة التالية'],
                    ['locale' => 'ar', 'key' => 'title', 'item_index' => -1, 'value' => 'تواصل مع النسر للمحادثات المؤسسية ومحادثات الوكلاء والدعم الفني.'],
                    ['locale' => 'ar', 'key' => 'description', 'item_index' => -1, 'value' => 'انقل الزوار نحو خطوة أعمال جادة عبر شريط ختامي راقٍ يحافظ على النبرة الصناعية للصفحة.'],
                    ['locale' => 'ar', 'key' => 'button_text', 'item_index' => -1, 'value' => 'تواصل مع المبيعات'],
                    ['locale' => 'ar', 'key' => 'note', 'item_index' => -1, 'value' => 'تتبع الضمان'],
                    ['locale' => 'ar', 'key' => 'image_alt', 'item_index' => -1, 'value' => 'لقطة مقرّبة لبطارية'],
                ],
            ],
        ];

        foreach ($sections as $definition) {
            $payload = ['name' => $definition['name']];

            if (isset($definition['value'])) {
                $payload['value'] = json_encode($definition['value'], JSON_UNESCAPED_UNICODE);
                $payload['is_active'] = 1;
            }

            $section = HomePageSection::firstOrCreate(
                ['type' => $definition['type']],
                $payload
            );

            if ($section->name !== $definition['name']) {
                $section->name = $definition['name'];
            }

            if (isset($definition['value']) && empty($section->value)) {
                $section->value = json_encode($definition['value'], JSON_UNESCAPED_UNICODE);
                $section->is_active = $section->is_active ?? 1;
            }

            if ($section->isDirty()) {
                $section->save();
            }

            foreach ($definition['translations'] ?? [] as $translation) {
                Translation::firstOrCreate(
                    [
                        'translationable_type' => HomePageSection::class,
                        'translationable_id' => $section->id,
                        'locale' => $translation['locale'],
                        'key' => $translation['key'],
                        'item_index' => (string) $translation['item_index'],
                    ],
                    [
                        'value' => $translation['value'],
                    ]
                );
            }
        }
    }
}
