<?php

namespace Database\Seeders;

use App\Models\KpiTemplate;
use App\Models\KpiTemplateItem;
use Illuminate\Database\Seeder;

class KpiTemplateSeeder extends Seeder
{
    public function run()
    {
        // Corporate Training Suite
        $corporate = KpiTemplate::updateOrCreate([
            'slug' => 'corporate-training',
        ], [
            'name' => 'Corporate Training Suite',
            'category' => 'corporate_training',
            'description' => 'Essential KPIs for enterprise training programs focused on completion, engagement, and knowledge retention.',
            'use_case' => 'Use this template for general corporate training initiatives, onboarding programs, and professional development.',
        ]);

        $this->createCorporateItems($corporate);

        // Compliance Training Package
        $compliance = KpiTemplate::updateOrCreate([
            'slug' => 'compliance-training',
        ], [
            'name' => 'Compliance Training Package',
            'category' => 'compliance',
            'description' => 'KPIs designed for mandatory compliance and regulatory training with focus on timely completion and certification.',
            'use_case' => 'Ideal for HIPAA, GDPR, anti-corruption, workplace safety, and other regulated compliance training.',
        ]);

        $this->createComplianceItems($compliance);

        // Sales Performance KPIs
        $sales = KpiTemplate::updateOrCreate([
            'slug' => 'sales-performance',
        ], [
            'name' => 'Sales Performance KPIs',
            'category' => 'sales',
            'description' => 'Tracks sales team activity, pipeline velocity, and revenue impact from training initiatives.',
            'use_case' => 'Track sales training effectiveness through activity metrics, conversion rates, and revenue growth.',
        ]);

        $this->createSalesItems($sales);

        // Customer Success & Retention
        $customerSuccess = KpiTemplate::updateOrCreate([
            'slug' => 'customer-success',
        ], [
            'name' => 'Customer Success & Retention',
            'category' => 'customer_success',
            'description' => 'Measures customer satisfaction, support ticket resolution, and retention after training.',
            'use_case' => 'Monitor customer-facing training impact through satisfaction scores, retention rates, and support metrics.',
        ]);

        $this->createCustomerSuccessItems($customerSuccess);

        // Employee Development & Career Growth
        $development = KpiTemplate::updateOrCreate([
            'slug' => 'employee-development',
        ], [
            'name' => 'Employee Development & Career Growth',
            'category' => 'employee_development',
            'description' => 'Tracks career progression, skill acquisition, internal mobility, and promotion rates.',
            'use_case' => 'Monitor employee growth through training hours completed, certifications earned, and internal promotions.',
        ]);

        $this->createDevelopmentItems($development);
    }

    private function createCorporateItems(KpiTemplate $template)
    {
        $items = [
            [
                'name' => 'Training Completion Rate',
                'code' => 'CORP_COMPLETION',
                'description' => 'Percentage of assigned employees who complete training within deadline',
                'type' => 'percentage',
                'weight' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Course Engagement Score',
                'code' => 'CORP_ENGAGEMENT',
                'description' => 'Average engagement rating from course participation and interaction',
                'type' => 'numeric',
                'weight' => 25,
                'sort_order' => 2,
            ],
            [
                'name' => 'Assessment Pass Rate',
                'code' => 'CORP_ASSESSMENT',
                'description' => 'Percentage of assessments passed on first attempt',
                'type' => 'percentage',
                'weight' => 25,
                'sort_order' => 3,
            ],
            [
                'name' => 'Knowledge Retention',
                'code' => 'CORP_RETENTION',
                'description' => 'Score on post-training knowledge assessments after 30 days',
                'type' => 'numeric',
                'weight' => 20,
                'sort_order' => 4,
            ],
        ];

        $this->upsertItems($template, $items);
    }

    private function createComplianceItems(KpiTemplate $template)
    {
        $items = [
            [
                'name' => 'On-Time Completion Rate',
                'code' => 'COMP_TIMELY',
                'description' => 'Percentage of employees completing training by regulatory deadline',
                'type' => 'percentage',
                'weight' => 40,
                'sort_order' => 1,
            ],
            [
                'name' => 'Certification Rate',
                'code' => 'COMP_CERT',
                'description' => 'Percentage of employees who passed certification exam',
                'type' => 'percentage',
                'weight' => 30,
                'sort_order' => 2,
            ],
            [
                'name' => 'Audit Readiness Score',
                'code' => 'COMP_AUDIT',
                'description' => 'Audit compliance score showing training records completeness',
                'type' => 'numeric',
                'weight' => 20,
                'sort_order' => 3,
            ],
            [
                'name' => 'Re-certification Completion',
                'code' => 'COMP_RECERT',
                'description' => 'Percentage of employees completing required refresher training',
                'type' => 'percentage',
                'weight' => 10,
                'sort_order' => 4,
            ],
        ];

        $this->upsertItems($template, $items);
    }

    private function createSalesItems(KpiTemplate $template)
    {
        $items = [
            [
                'name' => 'Training Adoption Rate',
                'code' => 'SALES_ADOPTION',
                'description' => 'Percentage of sales team members completing sales training',
                'type' => 'percentage',
                'weight' => 20,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sales Activity Increase',
                'code' => 'SALES_ACTIVITY',
                'description' => 'Percentage increase in sales activities (calls, meetings) post-training',
                'type' => 'percentage',
                'weight' => 25,
                'sort_order' => 2,
            ],
            [
                'name' => 'Win Rate Improvement',
                'code' => 'SALES_WINRATE',
                'description' => 'Percentage increase in deal closure rate for trained vs untrained team',
                'type' => 'percentage',
                'weight' => 30,
                'sort_order' => 3,
            ],
            [
                'name' => 'Revenue Impact',
                'code' => 'SALES_REVENUE',
                'description' => 'Revenue growth attributed to sales training initiatives',
                'type' => 'numeric',
                'weight' => 25,
                'sort_order' => 4,
            ],
        ];

        $this->upsertItems($template, $items);
    }

    private function createCustomerSuccessItems(KpiTemplate $template)
    {
        $items = [
            [
                'name' => 'Customer Satisfaction Score',
                'code' => 'CUST_SATISFACTION',
                'description' => 'Net Promoter Score or CSAT from post-training customer surveys',
                'type' => 'numeric',
                'weight' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Support Ticket Reduction',
                'code' => 'CUST_TICKETS',
                'description' => 'Percentage reduction in support tickets from trained customer base',
                'type' => 'percentage',
                'weight' => 25,
                'sort_order' => 2,
            ],
            [
                'name' => 'Customer Retention Rate',
                'code' => 'CUST_RETENTION',
                'description' => 'Percentage of customers retained 12 months post-training',
                'type' => 'percentage',
                'weight' => 30,
                'sort_order' => 3,
            ],
            [
                'name' => 'Upsell Conversion Rate',
                'code' => 'CUST_UPSELL',
                'description' => 'Percentage of trained customers who upgraded or purchased additional services',
                'type' => 'percentage',
                'weight' => 15,
                'sort_order' => 4,
            ],
        ];

        $this->upsertItems($template, $items);
    }

    private function createDevelopmentItems(KpiTemplate $template)
    {
        $items = [
            [
                'name' => 'Training Hours Completed',
                'code' => 'DEV_HOURS',
                'description' => 'Average training hours per employee per quarter/year',
                'type' => 'numeric',
                'weight' => 20,
                'sort_order' => 1,
            ],
            [
                'name' => 'Certification Achievement Rate',
                'code' => 'DEV_CERTS',
                'description' => 'Percentage of employees earning industry certifications',
                'type' => 'percentage',
                'weight' => 25,
                'sort_order' => 2,
            ],
            [
                'name' => 'Internal Promotion Rate',
                'code' => 'DEV_PROMOTION',
                'description' => 'Percentage of promotions filled from internally trained talent',
                'type' => 'percentage',
                'weight' => 30,
                'sort_order' => 3,
            ],
            [
                'name' => 'Skill Development Coverage',
                'code' => 'DEV_SKILLS',
                'description' => 'Percentage of employees trained in targeted development skills',
                'type' => 'percentage',
                'weight' => 25,
                'sort_order' => 4,
            ],
        ];

        $this->upsertItems($template, $items);
    }

    private function upsertItems(KpiTemplate $template, array $items)
    {
        foreach ($items as $item) {
            KpiTemplateItem::updateOrCreate(
                [
                    'template_id' => $template->id,
                    'code' => $item['code'],
                ],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'weight' => $item['weight'],
                    'is_active' => $item['is_active'] ?? true,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]
            );
        }

        $template->update(['item_count' => $template->items()->count()]);
    }
}
