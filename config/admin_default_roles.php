<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Admin Roles Blueprint
    |--------------------------------------------------------------------------
    |
    | modules:
    |   - '*' means all actions in that module from config/permissions_admin.php
    |   - array means only listed actions
    |
    | permissions:
    |   - explicit non-module permissions (for example: rbac.roles.manage)
    |
    */
    'roles' => [
        [
            'name' => 'Super Admin',
            'modules' => '*',
            'permissions' => ['*'],
        ],
        [
            'name' => 'Operations Manager',
            'modules' => [
                'dashboard' => '*',
                'branch_management' => '*',
                'order_management' => '*',
                'product_management' => '*',
                'promotion_management' => '*',
                'report' => '*',
                'crm_section' => ['access', 'create', 'read', 'update', 'delete'],
                'task_section' => '*',
                'user_section' => '*',
                'wholesaler_section' => '*',
            ],
            'permissions' => ['rbac.roles.manage'],
        ],
        [
            'name' => 'RBAC Manager',
            'modules' => [
                'employee_management' => '*',
                'user_section' => ['read', 'update', 'export'],
                'report' => ['read'],
            ],
            'permissions' => ['rbac.roles.manage'],
        ],
        [
            'name' => 'CRM Manager',
            'modules' => [
                'dashboard' => ['read'],
                'crm_section' => '*',
                'task_section' => '*',
                'report' => ['read', 'export'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'CRM Agent',
            'modules' => [
                'dashboard' => ['read'],
                'crm_section' => [
                    'access',
                    'read',
                    'inbox_list',
                    'inbox_view',
                    'inbox_add_new_message',
                    'inbox_update_ticket_department',
                    'inbox_convert_inquiry',
                    'inbox_convert_bulk_inquiry',
                    'inbox_update_message_type',
                    'inbox_ignore_message',
                    'inbox_mark_spam',
                    'inbox_assign_employee',
                    'inbox_connect_user',
                    'inbox_get_user_info',
                    'lead_list',
                    'lead_show',
                    'lead_view',
                    'lead_convert_to_deal',
                    'lead_disqualify',
                    'lead_assign_employee',
                    'lead_assign_department',
                    'deal_wholesale_list',
                    'deal_wholesale_view',
                    'deal_wholesale_request_quotation',
                    'deal_wholesale_assign_employee',
                    'deal_wholesale_assign_department',
                    'deal_retail_list',
                    'deal_retail_view',
                    'deal_retail_request_quotation',
                    'deal_retail_assign_employee',
                    'deal_retail_assign_department',
                    'chat_box_view',
                ],
                'task_section' => ['read', 'create'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'SLA Manager',
            'modules' => [
                'dashboard' => ['read'],
                'crm_section' => ['access', 'sla_list', 'sla_create', 'sla_edit', 'sla_update', 'sla_delete', 'sla_status_toggle'],
                'report' => ['read'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'Warranty Manager',
            'modules' => [
                'dashboard' => ['read'],
                'warranty_section' => '*',
                'crm_section' => [
                    'access',
                    'warranty_claim_list',
                    'warranty_claim_view',
                    'warranty_claim_triage',
                    'warranty_claim_decide',
                    'warranty_claim_receive',
                    'warranty_claim_diagnose',
                    'warranty_claim_repair',
                    'warranty_claim_qc',
                    'warranty_claim_dispatch',
                    'warranty_claim_replacement',
                    'warranty_claim_close',
                    'warranty_claim_resolve',
                    'warranty_claim_resume',
                    'warranty_claim_submit',
                    'warranty_claim_export',
                ],
                'report' => ['read', 'export'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'Inventory Manager',
            'modules' => [
                'dashboard' => ['read'],
                'product_management' => '*',
                'branch_management' => '*',
                'wholesaler_section' => [
                    'access',
                    'product_list',
                    'product_view',
                    'product_add',
                    'product_edit',
                    'product_delete',
                    'product_status',
                ],
                'report' => ['read', 'export'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'Sales Agent',
            'modules' => [
                'dashboard' => ['read'],
                'pos_management' => ['read', 'create', 'update'],
                'order_management' => [
                    'access',
                    'order_list',
                    'order_view',
                    'order_address',
                    'order_payment_status',
                    'order_shiping_method',
                    'order_deliverey',
                    'order_invoice',
                    'refund_request_list',
                    'refund_request_view',
                ],
                'crm_section' => [
                    'access',
                    'lead_list',
                    'lead_show',
                    'lead_view',
                    'deal_retail_list',
                    'deal_retail_view',
                    'deal_wholesale_list',
                    'deal_wholesale_view',
                ],
                'report' => ['read'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'Content Manager',
            'modules' => [
                'dashboard' => ['read'],
                'cms_section' => '*',
                'promotion_management' => ['read', 'create', 'update'],
                'report' => ['read'],
            ],
            'permissions' => [],
        ],
        [
            'name' => 'Read Only Auditor',
            'modules' => [
                'dashboard' => ['read'],
                'report' => ['read', 'export'],
                'order_management' => ['order_list', 'order_view', 'refund_request_list', 'refund_request_view'],
                'product_management' => ['product_list', 'product_view'],
                'crm_section' => ['lead_list', 'lead_view', 'deal_wholesale_list', 'deal_wholesale_view', 'deal_retail_list', 'deal_retail_view', 'inbox_list', 'inbox_view'],
                'warranty_section' => ['warranty_dashboard', 'warranty_activation_list', 'warranty_activation_view', 'warranty_claim_review'],
                'wholesaler_section' => ['wholesaler_dashboard', 'product_list', 'product_view', 'purchase_request_view', 'quotation_view'],
            ],
            'permissions' => [],
        ],
    ],
];
