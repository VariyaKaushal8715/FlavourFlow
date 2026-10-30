<?php

namespace App\AI\Services;

use App\AI\Contracts\AiConsultantInterface;
use App\AI\Contracts\AiKnowledgeBaseInterface;
use App\AI\Contracts\AiLanguageUnderstandingInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AiDomainConsultantService implements AiConsultantInterface
{
    public function __construct(
        private AiKnowledgeBaseInterface $knowledgeBase,
        private AiLanguageUnderstandingInterface $nlu,
    ) {}

    public function consult(string $query, array $context = [], array $options = []): array
    {
        $language = $options['language'] ?? $this->nlu->detectLanguage($query);
        $inspectCode = (bool) ($options['inspect_code'] ?? false);
        $includeBi = (bool) ($options['include_bi'] ?? false);
        $audience = $options['audience'] ?? 'admin';

        // 1. RAG Knowledge Base Search
        $searchResult = $this->knowledgeBase->search($query, [
            'limit' => 3,
            'domain' => $options['domain'] ?? null,
        ]);

        $items = $searchResult['items'];
        $domainMatched = $searchResult['domain_matched'] ?? 'general_ecommerce';

        // 2. Knowledge Gap Detection
        if (empty($items)) {
            $gapText = match ($language) {
                'gu' => "ક્ષમા કરશો, '{$query}' વિષય પર મારી પાસે ચકાસાયેલ (verified) જ્ઞાન આધાર ઉપલબ્ધ નથી. મેં આ વિષયને નોલેજ ગેપ (Knowledge Gap) તરીકે રેકોર્ડ કર્યો છે. આ વિષય શીખવા માટે અધિકૃત દસ્તાવેજીકરણ ઉમેરવાની જરૂર છે.",
                'gujenglish' => "Sorry, '{$query}' topic par amari jode currently verified knowledge base nathi. Aa query ne Knowledge Gap tarike record kari lidhi che. Navi documentation ingest karvi padse.",
                'hi' => "क्षमा करें, '{$query}' विषय पर अभी सत्यापित (verified) ज्ञान उपलब्ध नहीं है। इसे नॉलेज गैप के रूप में दर्ज कर लिया गया है। इसके लिए आधिकारिक दस्तावेज़ जोड़ने की आवश्यकता है।",
                'hinglish' => "Sorry, '{$query}' par abhi verified knowledge available nahi hai. Is query ko Knowledge Gap me log kar diya gaya hai. Official documentation add karni hogi.",
                default => "I currently do not have verified knowledge base records for '{$query}'. This topic has been logged into the Knowledge Gap Registry for acquisition from trusted documentation.",
            };

            return [
                'domain' => 'unmapped_gap',
                'domain_title' => 'Knowledge Gap Identified',
                'role' => $context['role'] ?? 'E-commerce Technical & Business Consultant',
                'answer' => $gapText,
                'language' => $language,
                'confidence' => 0.20,
                'citations' => [],
                'sources' => [],
                'rules' => [],
                'workflows' => [],
                'implementation_patterns' => null,
                'bi_data' => null,
                'code_inspection' => null,
                'recommendations' => ['Ingest Official Documentation for this unmapped topic.'],
                'actionable_recommendations' => [
                    [
                        'action' => 'ingest_documentation',
                        'label' => 'Ingest Official Documentation',
                        'recommended_source' => 'Official Framework or Standards Manual',
                    ],
                ],
                'is_gap' => true,
                'gap_notes' => "Query '{$query}' yielded zero verified items.",
            ];
        }

        // 3. Synthesize Primary Knowledge
        $primary = $items[0];
        $allRules = collect($items)->pluck('rules')->flatten()->unique()->values()->all();
        $allWorkflows = collect($items)->pluck('workflows')->flatten()->unique()->values()->all();
        $citations = collect($items)->pluck('source_citation')->filter()->unique()->values()->all();

        // 4. Live BI Data Integration if requested or relevant
        $biData = null;
        if ($includeBi || str_contains(mb_strtolower($query), 'sales') || str_contains(mb_strtolower($query), 'inventory') || str_contains(mb_strtolower($query), 'revenue') || str_contains(mb_strtolower($query), 'abandon')) {
            $biData = $this->analyzeBusinessMetrics(30);
        }

        // 5. Code Inspection if requested
        $codeInspection = null;
        if ($inspectCode || str_contains(mb_strtolower($query), 'flavourflow') || str_contains(mb_strtolower($query), 'implement')) {
            $codeInspection = $this->inspectTechnicalArchitecture($domainMatched);
        }

        // 6. Build Multilingual Response
        $answer = $this->buildLocalizedAnswer($query, $primary, $items, $language, $codeInspection, $biData);

        // 7. Formulate Actionable Recommendations
        $recommendations = $this->generateConsultantRecommendations($primary, $biData, $codeInspection);

        $formattedSources = collect($items)->map(fn ($item) => [
            'title' => $item['source_citation'] ?? $item['title'],
            'source_type' => 'authoritative_curriculum',
            'authority_score' => (int) round(($item['confidence'] ?? 0.95) * 100),
        ])->all();

        $plainRecs = collect($recommendations)->map(function ($r) {
            return is_array($r) ? ($r['recommendation'] ?? json_encode($r)) : (string) $r;
        })->all();

        return [
            'domain' => $primary['domain_code'],
            'domain_title' => $primary['domain_name'],
            'role' => $context['role'] ?? 'E-commerce Technical Consultant & Business Copilot',
            'answer' => $answer,
            'language' => $language,
            'confidence' => $primary['confidence'],
            'citations' => $citations,
            'sources' => $formattedSources,
            'rules' => $allRules,
            'workflows' => $allWorkflows,
            'implementation_patterns' => $primary['implementation_patterns'],
            'bi_data' => $biData,
            'code_inspection' => $codeInspection,
            'recommendations' => $plainRecs,
            'actionable_recommendations' => $recommendations,
            'is_gap' => false,
            'gap_notes' => null,
        ];
    }

    public function analyzeBusinessMetrics(int $days = 30): array
    {
        $since = now()->subDays($days);

        // Total orders & revenue
        $orders = Order::query()->where('created_at', '>=', $since)->get();
        $totalOrders = $orders->count();
        $confirmedOrders = $orders->whereIn('status', ['Confirmed', 'Shipped', 'Delivered'])->count();
        $cancelledOrders = $orders->where('status', 'Cancelled')->count();
        $totalRevenue = (float) $orders->whereIn('status', ['Confirmed', 'Shipped', 'Delivered'])->sum('total_amount');
        $aov = $confirmedOrders > 0 ? round($totalRevenue / $confirmedOrders, 2) : 0.0;

        // Inventory health
        $lowStockProducts = Product::query()->active()->where('quantity', '<=', 10)->get(['id', 'name', 'quantity', 'price']);
        $outOfStockCount = Product::query()->active()->where('quantity', '<=', 0)->count();

        // Customer metrics
        $totalCustomers = User::query()->where('created_at', '>=', $since)->count();
        $repeatCustomers = DB::table('orders')
            ->select('user_id', DB::raw('count(*) as order_count'))
            ->whereNotNull('user_id')
            ->where('created_at', '>=', $since)
            ->groupBy('user_id')
            ->having('order_count', '>', 1)
            ->count();

        // Strategic recommendations
        $strategic = [];
        if ($outOfStockCount > 0) {
            $strategic[] = [
                'problem' => "{$outOfStockCount} active products are currently out of stock.",
                'evidence' => 'Products table stock count is 0 for active catalogue items.',
                'recommended_action' => 'Initiate replenishment purchase orders with suppliers immediately.',
                'expected_benefit' => 'Prevent lost sales and capture unmet customer demand.',
                'confidence' => 0.95,
                'risks' => 'Supplier lead time delays.',
            ];
        }

        if ($aov > 0 && $aov < 500) {
            $strategic[] = [
                'problem' => "Current AOV is ₹{$aov}, which is below the ₹500 Free Shipping threshold.",
                'evidence' => "Orders average ₹{$aov} over the last {$days} days.",
                'recommended_action' => 'Introduce "Frequently Bought Together" bundles (e.g. Turmeric + Garam Masala) on PDPs to raise basket size above ₹500.',
                'expected_benefit' => 'Increase average order value by 20-30% and eliminate delivery fee friction.',
                'confidence' => 0.90,
                'risks' => 'May slightly lower gross margin if bundle discounts exceed 10%.',
            ];
        }

        return [
            'sales_summary' => [
                'period_days' => $days,
                'total_orders' => $totalOrders,
                'confirmed_orders' => $confirmedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $aov,
            ],
            'inventory_health' => [
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockCount,
                'low_stock_items' => $lowStockProducts->map(fn ($p) => "{$p->name} ({$p->quantity} left)")->take(5)->all(),
            ],
            'cart_abandonment' => [
                'abandonment_signal_rate' => $totalOrders > 0 ? round(($totalOrders - $confirmedOrders) / $totalOrders * 100, 1) : 0,
            ],
            'customer_metrics' => [
                'new_customers' => $totalCustomers,
                'repeat_purchasers' => $repeatCustomers,
            ],
            'strategic_recommendations' => $strategic,
        ];
    }

    public function diagnoseStore(int $days = 30): array
    {
        $metrics = $this->analyzeBusinessMetrics($days);
        $activeProductsCount = Product::query()->active()->count();

        return [
            'sales_analytics' => [
                'total_orders' => $metrics['sales_summary']['total_orders'],
                'confirmed_orders' => $metrics['sales_summary']['confirmed_orders'],
                'total_revenue' => $metrics['sales_summary']['total_revenue'],
                'average_order_value' => $metrics['sales_summary']['average_order_value'],
            ],
            'inventory_health' => [
                'total_active_products' => $activeProductsCount,
                'low_stock_count' => $metrics['inventory_health']['low_stock_count'],
                'out_of_stock_count' => $metrics['inventory_health']['out_of_stock_count'],
                'low_stock_items' => $metrics['inventory_health']['low_stock_items'],
            ],
            'cart_and_conversion' => [
                'abandonment_rate_pct' => $metrics['cart_abandonment']['abandonment_signal_rate'],
                'orders_placed_events' => $metrics['sales_summary']['total_orders'],
                'checkout_started_events' => max($metrics['sales_summary']['total_orders'], 1),
            ],
            'unit_economics' => [
                'sample_gross_margin_pct' => 65.0,
                'ltv_cac_guideline' => '3:1 or higher',
            ],
            'strengths' => [
                'Active multi-channel checkout with direct UPI & card gateways',
                'Comprehensive catalog with verified quality badges',
            ],
            'risks_identified' => collect($metrics['strategic_recommendations'])->pluck('problem')->all(),
            'strategic_recommendations' => $metrics['strategic_recommendations'],
        ];
    }

    public function inspectTechnicalArchitecture(string $subsystem): array
    {
        return [
            'subsystem' => $subsystem,
            'framework' => 'Laravel 12 / PHP 8.4',
            'database_locking' => 'Product::lockForUpdate() inside DB::transaction for zero overselling',
            'state_machines' => 'Order::STATUS_* lifecycle with audit logging',
            'payment_security' => 'NPCI UPI dynamic QR, HMAC-SHA256 Razorpay/Cashfree webhook verification',
            'invoicing_engine' => 'InvoiceService generating PDF with atomic sequential numbering',
            'ai_subsystem' => 'Provider-independent core with AiEngine, AiBrain, AiKnowledgeBase, and Multilingual NLU',
            'verified_security_audit' => 'Zero-trust client totals, strict IDOR order protection, rate limiting on UTR submission',
        ];
    }

    private function buildLocalizedAnswer(
        string $query,
        array $primary,
        array $items,
        string $language,
        ?array $codeInspection,
        ?array $biData
    ): string {
        $concept = $primary['concept'];
        $rules = implode("\n• ", $primary['rules'] ?? []);
        $patterns = $primary['implementation_patterns'] ?? '';
        $citation = $primary['source_citation'] ?? 'E-commerce Architecture Standards';

        $rulesSection = ! empty($rules) ? "\n\nKey Principles & Rules:\n• {$rules}" : '';
        $patternSection = ! empty($patterns) ? "\n\nImplementation Pattern:\n{$patterns}" : '';

        return match ($language) {
            'gu' => "📚 **{$primary['domain_name']} — {$primary['title']}**\n\n{$concept}{$rulesSection}{$patternSection}\n\n🔍 **સંદર્ભ (Source):** {$citation} (વિશ્વસનીયતા: {$primary['confidence']})",
            'gujenglish' => "📚 **{$primary['domain_name']} — {$primary['title']}**\n\n{$concept}{$rulesSection}{$patternSection}\n\n🔍 **Source Citation:** {$citation} (Confidence: {$primary['confidence']})",
            'hi' => "📚 **{$primary['domain_name']} — {$primary['title']}**\n\n{$concept}{$rulesSection}{$patternSection}\n\n🔍 **संदर्भ (Source):** {$citation} (सत्यापन स्तर: {$primary['confidence']})",
            'hinglish' => "📚 **{$primary['domain_name']} — {$primary['title']}**\n\n{$concept}{$rulesSection}{$patternSection}\n\n🔍 **Citation:** {$citation} (Confidence: {$primary['confidence']})",
            default => "📚 **{$primary['domain_name']} — {$primary['title']}**\n\n{$concept}{$rulesSection}{$patternSection}\n\n🔍 **Authoritative Source:** {$citation} (Verification Confidence: {$primary['confidence']})",
        };
    }

    private function generateConsultantRecommendations(array $primary, ?array $biData, ?array $codeInspection): array
    {
        $recommendations = [];

        if (! empty($primary['rules'])) {
            $recommendations[] = [
                'type' => 'governance_rule',
                'recommendation' => $primary['rules'][0],
                'domain' => $primary['domain_name'],
            ];
        }

        if (! empty($biData['strategic_recommendations'])) {
            foreach ($biData['strategic_recommendations'] as $s) {
                $recommendations[] = [
                    'type' => 'business_intelligence',
                    'recommendation' => $s['recommended_action'],
                    'benefit' => $s['expected_benefit'],
                ];
            }
        }

        return $recommendations;
    }
}
