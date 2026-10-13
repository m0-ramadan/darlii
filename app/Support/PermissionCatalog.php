<?php

namespace App\Support;

/**
 * Single source of truth for the CMS permission catalogue.
 * Every permission is declared once with its Arabic label and the group it
 * shows up under on the roles & permissions page.
 */
class PermissionCatalog
{
    /** @return array<string, array{label: string, group: string}> */
    public static function all(): array
    {
        return [
            // Properties ------------------------------------------------------
            'property.view' => ['label' => 'عرض العقارات', 'group' => 'properties'],
            'property.create' => ['label' => 'إضافة عقار', 'group' => 'properties'],
            'property.update-own' => ['label' => 'تعديل عقاراته فقط', 'group' => 'properties'],
            'property.update-any' => ['label' => 'تعديل أي عقار', 'group' => 'properties'],
            'property.approve' => ['label' => 'اعتماد / رفض العقارات', 'group' => 'properties'],
            'property.delete' => ['label' => 'حذف العقارات', 'group' => 'properties'],
            'property.feature' => ['label' => 'تمييز العقارات', 'group' => 'properties'],
            'property.assign' => ['label' => 'إسناد العقارات لمستخدم', 'group' => 'properties'],
            'property.stats' => ['label' => 'إحصائيات العقارات', 'group' => 'properties'],

            // Projects --------------------------------------------------------
            'project.view' => ['label' => 'عرض المشروعات', 'group' => 'projects'],
            'project.manage' => ['label' => 'إدارة المشروعات', 'group' => 'projects'],

            // Leads & appointments -------------------------------------------
            'lead.view-own' => ['label' => 'عرض عملائه فقط', 'group' => 'leads'],
            'lead.view-all' => ['label' => 'عرض كل العملاء المحتملين', 'group' => 'leads'],
            'lead.assign' => ['label' => 'إسناد العملاء المحتملين', 'group' => 'leads'],
            'lead.manage' => ['label' => 'إدارة ومتابعة العملاء', 'group' => 'leads'],
            'appointment.manage' => ['label' => 'إدارة المواعيد', 'group' => 'leads'],

            // Users & hierarchy ----------------------------------------------
            'users.view' => ['label' => 'عرض المستخدمين', 'group' => 'users'],
            'users.create' => ['label' => 'إضافة مستخدمين', 'group' => 'users'],
            'users.update' => ['label' => 'تعديل المستخدمين', 'group' => 'users'],
            'users.delete' => ['label' => 'حذف المستخدمين', 'group' => 'users'],
            'users.assign-manager' => ['label' => 'ربط المستخدم بمسؤول', 'group' => 'users'],
            'roles.manage' => ['label' => 'إدارة الأدوار والصلاحيات', 'group' => 'users'],

            // Content ---------------------------------------------------------
            'content.manage' => ['label' => 'إدارة المحتوى (صفحات / مقالات)', 'group' => 'content'],
            'content.testimonials' => ['label' => 'إدارة آراء العملاء', 'group' => 'content'],
            'content.taxonomies' => ['label' => 'إدارة التصنيفات والمواقع والمرافق', 'group' => 'content'],
            'review.moderate' => ['label' => 'مراجعة التقييمات', 'group' => 'content'],
            'media.manage' => ['label' => 'إدارة الوسائط والصور', 'group' => 'content'],

            // Statistics & reports -------------------------------------------
            'stats.view' => ['label' => 'عرض الإحصائيات', 'group' => 'statistics'],
            'stats.export' => ['label' => 'تصدير التقارير', 'group' => 'statistics'],

            // Settings --------------------------------------------------------
            'settings.manage' => ['label' => 'إدارة الإعدادات', 'group' => 'settings'],
            'settings.integrations' => ['label' => 'إدارة التكاملات', 'group' => 'settings'],

            // Finance / system ------------------------------------------------
            'payment.view' => ['label' => 'عرض المدفوعات', 'group' => 'system'],
            'audit.view' => ['label' => 'عرض سجل النشاط', 'group' => 'system'],
        ];
    }

    /** @return array<string, string> */
    public static function groups(): array
    {
        return [
            'properties' => 'العقارات',
            'projects' => 'المشروعات',
            'leads' => 'العملاء المحتملون',
            'users' => 'المستخدمون والصلاحيات',
            'content' => 'المحتوى والوسائط',
            'statistics' => 'الإحصائيات',
            'settings' => 'الإعدادات',
            'system' => 'النظام',
        ];
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    /**
     * Role slug => permission slugs. Roles are hierarchical:
     * super-admin > admin > manager (مسؤول) > agent (سمسار).
     *
     * @return array<string, array{name: string, label_ar: string, level: int, description: string, permissions: list<string>|string}>
     */
    public static function roles(): array
    {
        return [
            'super-admin' => [
                'name' => 'Super Admin',
                'label_ar' => 'مدير النظام (سوبر أدمن)',
                'level' => 100,
                'description' => 'صلاحيات كاملة غير محدودة على النظام بالكامل.',
                'permissions' => '*',
            ],
            'admin' => [
                'name' => 'Admin',
                'label_ar' => 'أدمن',
                'level' => 90,
                'description' => 'تحكم كامل في العقارات والمستخدمين والإعدادات، ويستطيع إنشاء مسؤولين وسماسرة.',
                'permissions' => '*',
            ],
            'manager' => [
                'name' => 'Manager',
                'label_ar' => 'مسؤول',
                'level' => 60,
                'description' => 'صلاحيات محدودة: إدارة السماسرة التابعين له وعقاراتهم والعملاء المحتملين، بدون الإعدادات أو حذف المستخدمين.',
                'permissions' => [
                    'property.view', 'property.create', 'property.update-own', 'property.update-any',
                    'property.approve', 'property.feature', 'property.assign', 'property.stats',
                    'project.view', 'lead.view-all', 'lead.assign', 'lead.manage', 'appointment.manage',
                    'users.view', 'users.create', 'users.update', 'users.assign-manager',
                    'stats.view', 'content.testimonials', 'review.moderate',
                ],
            ],
            'agent' => [
                'name' => 'Agent',
                'label_ar' => 'سمسار',
                'level' => 30,
                'description' => 'يضيف ويدير عقاراته الخاصة ويتابع عملاءه المحتملين فقط.',
                'permissions' => [
                    'property.view', 'property.create', 'property.update-own',
                    'property.stats', 'lead.view-own', 'lead.manage',
                ],
            ],
            'content-manager' => [
                'name' => 'Content Manager',
                'label_ar' => 'مسؤول المحتوى',
                'level' => 55,
                'description' => 'إدارة الصفحات والمقالات والتصنيفات والوسائط.',
                'permissions' => [
                    'content.manage', 'content.testimonials', 'content.taxonomies',
                    'media.manage', 'review.moderate', 'stats.view',
                ],
            ],
            'support' => [
                'name' => 'Support',
                'label_ar' => 'الدعم الفني',
                'level' => 40,
                'description' => 'متابعة العملاء المحتملين والمواعيد.',
                'permissions' => ['lead.view-all', 'lead.manage', 'appointment.manage', 'property.view', 'users.view'],
            ],
            'finance' => [
                'name' => 'Finance',
                'label_ar' => 'الحسابات',
                'level' => 50,
                'description' => 'متابعة المدفوعات والاشتراكات.',
                'permissions' => ['payment.view', 'stats.view', 'stats.export'],
            ],
            'agency-manager' => [
                'name' => 'Agency Manager',
                'label_ar' => 'مدير وكالة',
                'level' => 45,
                'description' => 'إدارة عقارات وسماسرة الوكالة.',
                'permissions' => ['property.view', 'property.create', 'property.update-any', 'lead.view-all', 'lead.assign', 'users.view'],
            ],
            'customer' => [
                'name' => 'Customer',
                'label_ar' => 'عميل',
                'level' => 10,
                'description' => 'زائر مسجّل، لا يصل إلى لوحة التحكم.',
                'permissions' => [],
            ],
        ];
    }

    /**
     * Roles that get access to the admin panel at all.
     * Agents are included: they only ever see their own listings and leads,
     * but they must be able to manage them from the panel.
     *
     * @return list<string>
     */
    public static function panelRoles(): array
    {
        return ['super-admin', 'admin', 'manager', 'property-manager', 'agent', 'content-manager', 'support', 'finance', 'agency-manager'];
    }
}
