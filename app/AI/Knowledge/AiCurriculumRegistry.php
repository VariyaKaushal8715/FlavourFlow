<?php

namespace App\AI\Knowledge;

class AiCurriculumRegistry
{
    /**
     * Get the master 18 e-commerce domains curriculum definition.
     * Each domain contains structured beginner, intermediate, and advanced levels.
     *
     * @return list<array{
     *     code: string,
     *     name: string,
     *     description: string,
     *     priority: int,
     *     topics: list<array{
     *         slug: string,
     *         name: string,
     *         level: string,
     *         description: string,
     *         source: array{title: string, type: string, url: string, authority_score: float},
     *         items: list<array{
     *             title: string,
     *             memory_type: string,
     *             concept: string,
     *             rules: list<string>,
     *             workflows: list<string>,
     *             implementation_patterns: string,
     *             examples: string,
     *             common_problems: string,
     *             confidence: float
     *         }>
     *     }>
     * }>
     */
    public static function getCurriculum(): array
    {
        return static::getMasterCurriculum();
    }

    public static function getMasterCurriculum(): array
    {
        return [
            // Domain 1: E-commerce Fundamentals
            [
                'code' => 'ecommerce_fundamentals',
                'name' => 'E-commerce Fundamentals',
                'description' => 'Business models (B2B, B2C, D2C, Marketplaces), store lifecycle, fundamental terminology, and planning.',
                'priority' => 10,
                'topics' => [
                    [
                        'slug' => 'business-models-b2c-d2c-marketplaces',
                        'name' => 'B2C, D2C, B2B & Marketplace Business Models',
                        'level' => 'beginner',
                        'description' => 'Comparison of direct-to-consumer, marketplace, and wholesale operations.',
                        'source' => [
                            'title' => 'E-commerce Business Models and Operational Frameworks (Harvard Business Review & NRF)',
                            'type' => 'reputable_industry',
                            'url' => 'https://hbr.org/topic/ecommerce',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'D2C vs Marketplace Unit Economics',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'D2C (Direct to Consumer) provides full customer data ownership, higher gross margin, and brand loyalty, but requires higher Customer Acquisition Cost (CAC). Marketplaces offer built-in footfall at the expense of 10-25% commissions and zero customer data ownership.',
                                'rules' => [
                                    'D2C brands must achieve at least 3:1 LTV:CAC ratio to remain solvent without subsidies.',
                                    'Never rely on a single distribution channel; hybrid D2C + marketplace expansion is the standard.',
                                ],
                                'workflows' => [
                                    '1. Validate core SKU demand on high-intent channels.',
                                    '2. Build owned storefront on performant stack (Laravel) with direct checkout.',
                                    '3. Capture email/SMS consent on first purchase to establish owned remarketing.',
                                ],
                                'implementation_patterns' => 'FlavourFlow implements a D2C architecture using Laravel, providing full data ownership, custom pricing rules, and zero intermediary platform commissions.',
                                'examples' => 'FlavourFlow sells authentic farm-fresh spices directly to consumers via flavourflow.test, keeping 100% of the retail price.',
                                'common_problems' => 'Overspending on paid ads before establishing organic repeat purchase rate; failure to factor in return costs when pricing D2C goods.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'ecommerce-store-lifecycle',
                        'name' => 'E-commerce Store Lifecycle & Operations',
                        'level' => 'intermediate',
                        'description' => 'The end-to-end operational lifecycle: acquisition, conversion, fulfillment, retention, and scaling.',
                        'source' => [
                            'title' => 'Retail Operations Handbook (National Retail Federation)',
                            'type' => 'reputable_industry',
                            'url' => 'https://nrf.com/retail-operations',
                            'authority_score' => 0.94,
                        ],
                        'items' => [
                            [
                                'title' => 'E-commerce Order Operations Lifecycle',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'The operational store lifecycle begins at catalog syndication, proceeds to cart capture, deterministic payment settlement, warehouse picking & packing, courier handoff, delivery verification, and post-purchase loyalty.',
                                'rules' => [
                                    'Inventory must be committed (locked) atomically to prevent overselling.',
                                    'Every transition in the order lifecycle must emit an audit event and notify the customer.',
                                ],
                                'workflows' => [
                                    'Customer Checkout -> Payment Verification -> Order Locked -> Pick & Pack -> Shipped -> Out for Delivery -> Delivered -> Review Solicitation.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses Order::STATUS constants with a state-machine pattern and OrderNotification event dispatching.',
                                'examples' => 'When a spice order is placed, stock is reserved in DB::transaction, status becomes Pending, and transitions to Confirmed once payment is verified.',
                                'common_problems' => 'Stock decrementing before payment is authorized, causing phantom stock depletion on abandoned carts.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'cross-border-and-channel-expansion',
                        'name' => 'Cross-Border Commerce & Channel Aggregation',
                        'level' => 'advanced',
                        'description' => 'Multi-channel order routing, export documentation, and customs compliance.',
                        'source' => [
                            'title' => 'World Customs Organization: E-Commerce Cross-Border Framework',
                            'type' => 'government_regulatory',
                            'url' => 'http://www.wcoomd.org',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'International Tariffs and Cross-Border Exporting',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Cross-border commerce requires landed-cost computation (duty, VAT/GST, and handling fees) at checkout to prevent courier refusal at customs.',
                                'rules' => [
                                    'Always state whether prices are Delivered Duty Paid (DDP) or Delivered Duty Unpaid (DDU).',
                                    'Exporting agricultural products like spices requires phytosanitary certificates and FSSAI export clearance in India.',
                                ],
                                'workflows' => [
                                    'Identify Buyer Country -> Calculate HSN-specific tariffs -> Present all-inclusive DDP price -> Generate commercial export invoice.',
                                ],
                                'implementation_patterns' => 'FlavourFlow validates country in DeliverySetting and scopes shipping availability.',
                                'examples' => 'Shipping organic turmeric to Europe requires HSN code 0910.30 and phytosanitary declarations.',
                                'common_problems' => 'Surprising foreign customers with unexpected import duties upon arrival, causing returned shipments.',
                                'confidence' => 0.96,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 2: Business Strategy and Management
            [
                'code' => 'business_strategy',
                'name' => 'Business Strategy & Unit Economics',
                'description' => 'Gross/Net margins, product pricing strategies, CAC, LTV, unit economics, competitor analysis, and risk management.',
                'priority' => 20,
                'topics' => [
                    [
                        'slug' => 'pricing-models-and-gross-margins',
                        'name' => 'Retail Pricing Models & Gross Margin Fundamentals',
                        'level' => 'beginner',
                        'description' => 'Cost-plus, value-based, and psychological retail pricing.',
                        'source' => [
                            'title' => 'Harvard Business School: Strategic Pricing Framework',
                            'type' => 'reputable_industry',
                            'url' => 'https://hbsp.harvard.edu',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Cost-Plus vs Value-Based Pricing in Food & Pantry',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Cost-plus pricing adds a static markup over COGS. Value-based pricing charges based on consumer perception of purity, origin, and grade. Specialty spices command 50-70% gross margins under value pricing vs 15-20% commodity margins.',
                                'rules' => [
                                    'Direct-sourced single-origin spices should never be priced as generic commodity bulk goods.',
                                    'Psychological pricing (.00 or .99) must be consistently applied across all catalog categories.',
                                ],
                                'workflows' => [
                                    '1. Compute ingredient + jar + label cost.',
                                    '2. Benchmark premium organic competitors.',
                                    '3. Establish retail tier (e.g. ₹149 for 100g organic turmeric).',
                                ],
                                'implementation_patterns' => 'FlavourFlow models set transparent retail pricing with unit badges (e.g. ₹99 per 100g).',
                                'examples' => 'Red Chilli Powder is priced at ₹99.00 (cost ₹35, gross margin 64.6%).',
                                'common_problems' => 'Engaging in price wars with low-quality adulterated commodity brands.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'unit-economics-margins-cac-ltv',
                        'name' => 'Unit Economics, Contribution Margins, and LTV:CAC',
                        'level' => 'intermediate',
                        'description' => 'Formulas and benchmarks for gross margin, contribution margin, and customer lifetime value.',
                        'source' => [
                            'title' => 'Corporate Finance Institute: E-commerce Unit Economics',
                            'type' => 'reputable_industry',
                            'url' => 'https://corporatefinanceinstitute.com/resources/valuation/unit-economics/',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Contribution Margin Calculation in Physical Goods',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Contribution Margin 1 (CM1) = Revenue - COGS. Contribution Margin 2 (CM2) = CM1 - Shipping - Payment Gateway Fees - Packaging. True e-commerce profitability requires positive CM2 on first order.',
                                'rules' => [
                                    'Free shipping thresholds should always be set at 15-25% above the current Average Order Value (AOV).',
                                    'Payment gateway fees (typically 2% + GST in India) must be factored into line-item margin calculations.',
                                ],
                                'workflows' => [
                                    '1. Compute Unit COGS (raw ingredients, jars, packaging).',
                                    '2. Add shipping fee allocation (Rs. 50 flat under Rs. 500).',
                                    '3. Subtract discounts and gateway commissions.',
                                    '4. Determine net unit contribution.',
                                ],
                                'implementation_patterns' => 'FlavourFlow sets free shipping at ₹500, which protects CM2 on spice packs whose typical individual price is ₹99 - ₹299.',
                                'examples' => 'Order of ₹600: COGS ₹240, Packaging ₹30, Gateway fee (2%) ₹12, Shipping ₹0. CM2 = ₹318 (53% margin).',
                                'common_problems' => 'Offering unconditional free shipping on low-ticket items (e.g. ₹99 spice pack with ₹50 shipping costs wipes out 100% of gross margin).',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'dynamic-pricing-and-portfolio-profitability',
                        'name' => 'Dynamic Pricing & Portfolio Profitability Management',
                        'level' => 'advanced',
                        'description' => 'Elasticity-driven pricing, inventory-clearing discounts, and margin balancing.',
                        'source' => [
                            'title' => 'MIT Sloan Management Review: Revenue Management & Dynamic Pricing',
                            'type' => 'reputable_industry',
                            'url' => 'https://sloanreview.mit.edu',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Price Elasticity and Inventory Clearance Strategy',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Spices nearing harvest expiry or with slow turnover should be dynamically discounted via coupon codes or bundles to free up working capital before freshness degrades.',
                                'rules' => [
                                    'Never discount hero SKU prices permanently; use bundle discounts or limited-time promotional codes.',
                                    'Maintain minimum floor prices to prevent brand equity degradation.',
                                ],
                                'workflows' => [
                                    'Analyze slow-moving inventory (days in stock > 90) -> Generate targeted WELCOME10 or bundle promotion -> Monitor inventory clearance rate.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CouponService validates promotional discounts with usage limits and minimum order values.',
                                'examples' => 'Bundle Royal Garam Masala with slow-moving Coriander Powder at 15% combined discount.',
                                'common_problems' => 'Training customers to only purchase during deep discount cycles.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 3: Storefront, UI/UX and Conversion
            [
                'code' => 'storefront_ux',
                'name' => 'Storefront, UI/UX and Conversion',
                'description' => 'Homepage architecture, category navigation, PLP/PDP UX, mobile-first responsive design, and conversion optimization.',
                'priority' => 30,
                'topics' => [
                    [
                        'slug' => 'mobile-first-navigation-and-layout',
                        'name' => 'Mobile-First Storefront & Navigation Architecture',
                        'level' => 'beginner',
                        'description' => 'Viewport optimization, thumb-friendly tap targets, and streamlined category menus.',
                        'source' => [
                            'title' => 'Google Mobile UX Guidelines & Baymard Mobile UX Research',
                            'type' => 'official_provider',
                            'url' => 'https://web.dev/explore/mobile',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'Thumb-Zone Navigation and Tap Target Sizing',
                                'memory_type' => 'store_business',
                                'concept' => 'Over 75% of Indian e-commerce orders originate on mobile phones. Critical action buttons (Add to Cart, Checkout, Search) must be at least 48x48px and reachable in the natural bottom-half thumb zone.',
                                'rules' => [
                                    'Primary buttons must have minimum 48px height for touch targets.',
                                    'Category filters must be collapsible into a bottom sheet or off-canvas drawer on mobile screens.',
                                ],
                                'workflows' => [
                                    'Design mobile viewport first (360px - 414px) -> Verify thumb reach -> Enhance to desktop grid.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses mobile-first Tailwind utility classes (e.g., w-full py-3.5 sm:w-auto).',
                                'examples' => 'On mobile, FlavourFlow cart CTA spans full width with high-contrast amber/brand coloring.',
                                'common_problems' => 'Tiny dropdowns and unclickable pagination links on mobile touchscreens.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'pdp-plp-conversion-ux',
                        'name' => 'Product Listing (PLP) & Product Detail (PDP) UX Best Practices',
                        'level' => 'intermediate',
                        'description' => 'High-converting design patterns for e-commerce search, filtration, and product pages.',
                        'source' => [
                            'title' => 'Baymard Institute: E-Commerce UX Research & Benchmarks',
                            'type' => 'reputable_industry',
                            'url' => 'https://baymard.com/ecommerce-ux-research',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'PDP Sticky Buy Box & Trust Elements',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'High-converting PDPs present a visible price, clear unit weight, instant stock indicator, primary CTA (Add to Cart) above the fold, and trust signals (FSSAI, return policy, secure checkout badges).',
                                'rules' => [
                                    'Mobile PDPs must feature a persistent or easily reachable Add to Cart button.',
                                    'Price per unit (e.g. ₹99 per 100g) significantly reduces price hesitation and aids consumer comparison.',
                                ],
                                'workflows' => [
                                    'Display High-Res Product Image -> Show Rating & Verified Reviews -> Display Price & Grammage -> Quick Add to Cart -> Show Related Spice Blends.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses Blade view components in products/show.blade.php with responsive Tailwind flex/grid, rating badges, stock status badges, and related product cross-sells.',
                                'examples' => 'On Kashmiri Red Chilli PDP, the user sees ₹299.00 (100 g), "In Stock (30 packs)", culinary usage instructions, and Add to Cart button.',
                                'common_problems' => 'Hiding shipping fees until final checkout step; missing ingredient or allergen disclosures on food products.',
                                'confidence' => 0.96,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'cro-ab-testing-and-core-web-vitals',
                        'name' => 'Conversion Rate Optimization & Core Web Vitals',
                        'level' => 'advanced',
                        'description' => 'Measuring LCP, FID, CLS, and executing rigorous A/B checkout tests.',
                        'source' => [
                            'title' => 'Google Web Vitals & CRO Scientific Testing Frameworks',
                            'type' => 'official_provider',
                            'url' => 'https://web.dev/vitals/',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Core Web Vitals Impact on E-commerce Conversion',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Every 100ms improvement in Largest Contentful Paint (LCP) increases conversion rate by up to 1.1%. Cumulative Layout Shift (CLS) under 0.1 prevents accidental button clicks.',
                                'rules' => [
                                    'LCP must stay under 2.5 seconds on real mobile 4G networks.',
                                    'Always reserve explicit aspect-ratio or width/height attributes on product images to prevent CLS layout jumping.',
                                ],
                                'workflows' => [
                                    'Audit PageSpeed / Lighthouse -> Optimize WebP/AVIF images -> Defer non-critical analytics JS -> Profile server response time (TTFB).',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses pre-compiled Vite CSS/JS assets and responsive image tags with explicit aspect ratios.',
                                'examples' => 'FlavourFlow product grid renders responsive image placeholders with aspect-square to guarantee zero layout shift.',
                                'common_problems' => 'Loading uncompressed 5MB camera photos directly from database on product grid pages.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 4: Product and Catalogue Management
            [
                'code' => 'product_catalog',
                'name' => 'Product and Catalogue Management',
                'description' => 'SKU taxonomy, variants, attributes, category hierarchies, descriptions, SEO metadata, and product archival.',
                'priority' => 40,
                'topics' => [
                    [
                        'slug' => 'product-attributes-and-specifications',
                        'name' => 'Product Attributes, Specifications & Sensory Details',
                        'level' => 'beginner',
                        'description' => 'Structuring sensory food data: aroma, heat level, origin, and shelf life.',
                        'source' => [
                            'title' => 'Food Safety and Standards Authority of India (FSSAI) Labeling Guidelines',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.fssai.gov.in',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Sensory and Food Compliance Attributes',
                                'memory_type' => 'store_business',
                                'concept' => 'Culinary products require structured specifications: heat scale (mild, medium, fiery), harvest origin, grind type (whole, coarse, micro-ground), and shelf life/expiry.',
                                'rules' => [
                                    'Mandatory FSSAI license numbers and shelf life statements must appear on all spice packs.',
                                    'Nutritional tables must detail energy, protein, carbohydrate, and fat per 100g.',
                                ],
                                'workflows' => [
                                    'Receive batch from origin farm -> Log harvest date & moisture test -> Store batch specifications in catalog.',
                                ],
                                'implementation_patterns' => 'FlavourFlow Product model includes category, unit (e.g. 100 g), and usage_instructions.',
                                'examples' => 'Kashmiri Red Chilli includes aroma and deep red color specifications without pungent heat.',
                                'common_problems' => 'Missing batch packaging dates and net weight markings, violating legal metrology rules.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'sku-variants-catalog-taxonomy',
                        'name' => 'SKU Architecture, Variants & Taxonomy Management',
                        'level' => 'intermediate',
                        'description' => 'Standardized product structuring, barcode/SKU creation, and category hierarchy.',
                        'source' => [
                            'title' => 'GS1 Standard for Product Identification & Barcodes',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.gs1.org/standards/barcodes',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'SKU Formatting and Stock Separation',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'A Stock Keeping Unit (SKU) must be unique, human-readable, alphanumeric, and encode essential attributes: CATEGORY-NAME-SIZE/VARIANT (e.g., SP-GARAM-100G).',
                                'rules' => [
                                    'Never reuse an existing SKU even after a product is archived or deleted.',
                                    'SKU uniqueness must be enforced at database level with a unique index constraint.',
                                ],
                                'workflows' => [
                                    'Define Category Hierarchy -> Create Parent Product -> Generate Unique Alphanumeric SKU -> Attach High-Res Image Assets -> Set Initial Stock & Reorder Threshold.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses Product model with unique `sku` and `slug` columns in database schema.',
                                'examples' => 'Royal Garam Masala has SKU `FF-RGM-100G`, slug `royal-garam-masala`, category `Blended Spices`.',
                                'common_problems' => 'Hardcoding variant data in product descriptions instead of structured attributes, breaking search and automated stock tracking.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'product-lifecycle-and-bundle-architecture',
                        'name' => 'Product Lifecycle, Bundling & Dynamic Kits',
                        'level' => 'advanced',
                        'description' => 'Virtual bundles, component inventory deduction, and soft archival.',
                        'source' => [
                            'title' => 'Enterprise Product Information Management (PIM) Standards',
                            'type' => 'reputable_industry',
                            'url' => 'https://en.wikipedia.org/wiki/Product_information_management',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'Virtual Bundles and Component Stock Deductions',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'A "Pantry Starter Kit" bundle does not have its own physical warehouse stock; its available inventory is dynamically equal to min(stock(turmeric), stock(chilli), stock(garam_masala)). Purchasing the bundle must atomically decrement each component SKU.',
                                'rules' => [
                                    'Never decouple bundle purchases from child SKU inventory tracking.',
                                    'Archiving a product must keep past orders intact (soft delete or is_active=false, never cascade delete OrderItem).',
                                ],
                                'workflows' => [
                                    'Customer buys Bundle -> DB Transaction locks all component items -> Decrements each child item -> Records bundle parent SKU in order.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses OrderItem with immutable snapshot fields (product_name, product_slug, sku, unit_price) so historical orders remain valid even if products are edited or deleted.',
                                'examples' => 'If Kashmiri Saffron has 2 units left, a "Royal Spice Gift Box" containing saffron cannot sell more than 2 units.',
                                'common_problems' => 'Hard deleting product records from database, causing foreign key crashes when displaying past customer order receipts.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 5: Inventory and Warehouse Management
            [
                'code' => 'inventory_warehouse',
                'name' => 'Inventory and Warehouse Management',
                'description' => 'Real-time stock tracking, inventory locking, reorder points, warehouse movements, and overselling prevention.',
                'priority' => 50,
                'topics' => [
                    [
                        'slug' => 'stock-tracking-and-reorder-points',
                        'name' => 'Stock Tracking, Safety Stock & Reorder Points',
                        'level' => 'beginner',
                        'description' => 'Calculating lead times, minimum thresholds, and low-stock alerting.',
                        'source' => [
                            'title' => 'APICS / ASCM Supply Chain Operations Reference (SCOR) Model',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.ascm.org',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Reorder Point Formula (ROP)',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Reorder Point (ROP) = (Average Daily Usage × Supplier Lead Time in Days) + Safety Stock. If daily sales are 5 packs, lead time is 7 days, and safety stock is 10 packs, ROP = (5 × 7) + 10 = 45 packs.',
                                'rules' => [
                                    'Automatic alerts must trigger as soon as physical stock crosses below the ROP threshold.',
                                    'Never wait until stock reaches zero before issuing purchase orders to farm suppliers.',
                                ],
                                'workflows' => [
                                    'Monitor inventory levels -> Compare against ROP -> Trigger low-stock alert in admin panel -> Dispatch supplier purchase order.',
                                ],
                                'implementation_patterns' => 'FlavourFlow Product model defines scopeLowStock() and AdminAiController monitors low-stock signals.',
                                'examples' => 'Royal Garam Masala triggers warning badge when stock drops to 10 packs or below.',
                                'common_problems' => 'Ignoring supplier seasonal shutdowns (e.g. monsoon spice transport delays) when calculating lead times.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'multi-location-and-batch-expiry',
                        'name' => 'Batch Tracking, FIFO & Food Expiry Management',
                        'level' => 'intermediate',
                        'description' => 'First-In, First-Out (FIFO) warehouse picking to prevent perishable spice degradation.',
                        'source' => [
                            'title' => 'WHO & FAO Guidelines on Food Hygiene & Storage',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.fao.org',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'FIFO Principles for Spices & Aromatics',
                                'memory_type' => 'store_business',
                                'concept' => 'Essential oils in ground spices volatilize over time. First-In, First-Out (FIFO) ensures older batches with earlier packaging dates are picked and shipped first, guaranteeing maximum aroma for customers.',
                                'rules' => [
                                    'Warehouse pickers must always be directed to the oldest valid batch (FIFO).',
                                    'Products within 60 days of expiry must be removed from prime picking zones.',
                                ],
                                'workflows' => [
                                    'Inbound Batch Arrival -> Record Batch No & Best-Before Date -> Bin Location -> Pick order according to FIFO priority.',
                                ],
                                'implementation_patterns' => 'FlavourFlow models support batch labeling and shelf-life tracking.',
                                'examples' => 'Batch #B2026-08 of Coriander Powder is dispatched before Batch #B2026-10.',
                                'common_problems' => 'Stacking new boxes in front of older stock, causing back-of-shelf inventory to quietly expire.',
                                'confidence' => 0.96,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'concurrency-overselling-prevention',
                        'name' => 'Concurrency Control & Overselling Prevention',
                        'level' => 'advanced',
                        'description' => 'Pessimistic locking and atomic DB transactions for high-volume checkout.',
                        'source' => [
                            'title' => 'Database Concurrency in Financial & Retail Systems (ACM SIGMOD & Martin Fowler)',
                            'type' => 'reputable_industry',
                            'url' => 'https://martinfowler.com/eaaCatalog/pessimisticOfflineLock.html',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Pessimistic Locking for Stock Integrity',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Overselling occurs when two concurrent requests read sufficient stock and both decrement. In Laravel/SQL, wrapping stock verification and decrement in DB::transaction with `Product::lockForUpdate()` prevents race conditions.',
                                'rules' => [
                                    'Always query inventory items using `lockForUpdate()` during checkout execution.',
                                    'Roll back transactions immediately if available quantity < requested quantity.',
                                ],
                                'workflows' => [
                                    'DB::beginTransaction() -> Product::lockForUpdate()->find($id) -> Validate stock >= quantity -> OrderItem::create() -> Product::decrement() -> DB::commit().',
                                ],
                                'implementation_patterns' => 'FlavourFlow implements lockForUpdate() in CheckoutController::store() across all payment methods (COD, UPI, Razorpay, Cashfree).',
                                'examples' => 'If 1 pack of Kashmiri Saffron remains, two customers checking out at the exact millisecond will be queued; the second receives a clean 422 Out of Stock error.',
                                'common_problems' => 'Using standard Eloquent find() followed by save(), resulting in double spending and negative inventory balances under load.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 6: Cart, Checkout and Pricing
            [
                'code' => 'cart_checkout_pricing',
                'name' => 'Cart, Checkout and Pricing',
                'description' => 'Cart architecture, persistence, validation, coupon calculations, tax logic, abandoned cart recovery.',
                'priority' => 60,
                'topics' => [
                    [
                        'slug' => 'cart-persistence-and-guest-checkout',
                        'name' => 'Cart Persistence, Session Management & Guest Checkout',
                        'level' => 'beginner',
                        'description' => 'Managing cart data across sessions, database migration upon login, and reducing guest friction.',
                        'source' => [
                            'title' => 'Baymard Institute: Reasons for Cart Abandonment',
                            'type' => 'reputable_industry',
                            'url' => 'https://baymard.com/lists/cart-abandonment-rate',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'Cart Merging Upon Customer Authentication',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'When a guest customer adds items to a session cart and subsequently logs in, the session cart items must merge with any previously saved database cart rather than overwriting it.',
                                'rules' => [
                                    'Forcing account creation before cart viewing causes up to 24% immediate checkout abandonment.',
                                    'Cart items must store price snapshots or validate price changes before final order confirmation.',
                                ],
                                'workflows' => [
                                    'Guest Cart in Session -> User Logs In -> Retrieve User Saved Cart -> Merge duplicate items (sum quantities) -> Save to persistent database.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CartService encapsulates session and database cart management.',
                                'examples' => 'User adds 2 packs of turmeric as guest, logs in, and retains the 2 packs in checkout.',
                                'common_problems' => 'Silently clearing customer cart upon session expiration without database persistence.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'coupon-engine-and-discount-rules',
                        'name' => 'Promotional Engines, Discount Rules & Stacking Logic',
                        'level' => 'intermediate',
                        'description' => 'Configuring coupon validation, percentage vs fixed discounts, and coupon abuse prevention.',
                        'source' => [
                            'title' => 'E-Commerce Promotion Mechanics & Fraud Mitigation (Shopify & BigCommerce Engineering)',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.shopify.com/blog/ecommerce-promotions',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'Coupon Abuse Prevention and Usage Tracking',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Coupon engines must enforce: per-customer usage limits, minimum order subtotals, non-combinable rules, and excluded categories to prevent discount stacking fraud.',
                                'rules' => [
                                    'Discount amount must never exceed the subtotal (total cannot be negative).',
                                    'Usage counts must be incremented atomically upon order confirmation, and decremented if an unconfirmed order is cancelled.',
                                ],
                                'workflows' => [
                                    'Input coupon code -> Validate existence, active dates, min subtotal -> Check per-user usage table -> Compute discount -> Apply to checkout subtotal.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CouponService validates coupon rules and records usage in CouponUsage table.',
                                'examples' => 'Code WELCOME10 grants 10% discount on orders above ₹299 for first-time buyers.',
                                'common_problems' => 'Allowing multiple coupons to be stacked on top of already-discounted clearance items.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'checkout-validation-and-pricing-integrity',
                        'name' => 'Checkout Validation, Pricing Integrity & Coupon Security',
                        'level' => 'advanced',
                        'description' => 'Authoritative server-side calculation of payable amounts and coupon validation.',
                        'source' => [
                            'title' => 'OWASP E-Commerce Security Guide & PCI-DSS Requirements',
                            'type' => 'official_framework',
                            'url' => 'https://cheatsheetseries.owasp.org/cheatsheets/E-Commerce_Payment_Security_Cheat_Sheet.html',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Zero-Trust Client Pricing Principle',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'The client browser must never be allowed to dictate the payable amount, delivery charge, or discount value. The server must recalculate all totals directly from database records.',
                                'rules' => [
                                    'Never accept a `total_amount` or `price` parameter from the HTTP request body.',
                                    'Coupon validation must check: expiry date, minimum order value, usage limit per user, and applicable category.',
                                ],
                                'workflows' => [
                                    'Receive Cart & Form Data -> Query current product prices from DB -> Compute subtotal -> Verify coupon via CouponService -> Compute delivery charge -> Authoritative Grand Total.',
                                ],
                                'implementation_patterns' => 'In FlavourFlow CheckoutController, $subtotal is derived from $cart->subtotal() and delivery charge is evaluated serverside: $subtotal >= 500 ? 0.0 : 50.0.',
                                'examples' => 'If a malicious customer modifies client HTML input to value="1.00", the server disregards it and charges the genuine ₹248.00 calculated from product records.',
                                'common_problems' => 'Accepting total_amount from front-end JavaScript hidden inputs, enabling trivial parameter tampering attacks.',
                                'confidence' => 1.0,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 7: Orders and Fulfillment
            [
                'code' => 'orders_fulfillment',
                'name' => 'Orders and Fulfillment',
                'description' => 'Order state machines, lifecycle transitions, packing workflows, courier tracking, and return/refund processing.',
                'priority' => 70,
                'topics' => [
                    [
                        'slug' => 'order-packing-and-warehouse-dispatch',
                        'name' => 'Pick, Pack & Shipping Label Generation',
                        'level' => 'beginner',
                        'description' => 'Physical packing workflows, parcel weighment, and courier handoff manifests.',
                        'source' => [
                            'title' => 'Warehousing Education and Research Council (WERC) Best Practices',
                            'type' => 'reputable_industry',
                            'url' => 'https://werc.org',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'Pick List and Packing Slip Verification',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'A warehouse packing slip lists exact SKUs, quantities, and batch numbers. Double scanning before box sealing reduces packing error rate from 2% down to less than 0.05%.',
                                'rules' => [
                                    'Fragile glass spice jars must be bubble-wrapped with tamper-evident seal stickers.',
                                    'The packing slip must accompany every dispatched box.',
                                ],
                                'workflows' => [
                                    'Print Pick List -> Gather Items from Bins -> Verify Barcode / SKU -> Pack in corrugated box -> Affix Courier Shipping Label.',
                                ],
                                'implementation_patterns' => 'FlavourFlow admin order view provides printable order receipts with itemised tables.',
                                'examples' => 'Order ORD-20261030-XYZ generates a slip with 2x Royal Garam Masala and 1x Kashmiri Saffron.',
                                'common_problems' => 'Mixing up customer shipping labels during peak sales, sending orders to wrong addresses.',
                                'confidence' => 0.96,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'returns-replacements-and-refund-pipeline',
                        'name' => 'Reverse Logistics, Returns & Replacement Management',
                        'level' => 'intermediate',
                        'description' => 'Managing customer return requests, photo inspections, and refund triggers.',
                        'source' => [
                            'title' => 'Reverse Logistics Association Standards',
                            'type' => 'reputable_industry',
                            'url' => 'https://rla.org',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'Food Item Return and Replacement Integrity',
                                'memory_type' => 'store_business',
                                'concept' => 'Opened food items cannot be restocked due to food safety laws. If an item arrives damaged or tampered, a replacement or refund is issued with photographic verification.',
                                'rules' => [
                                    'Customer return requests must be filed within 7 days of verified delivery.',
                                    'Refunds must only be disbursed after admin approval or warehouse return receipt.',
                                ],
                                'workflows' => [
                                    'Customer submits ReturnRequest with photo -> Admin reviews in AdminOrderController -> Approve -> Trigger gateway refund or dispatch replacement.',
                                ],
                                'implementation_patterns' => 'FlavourFlow implements ReturnRequest and RefundRequest models with status transitions.',
                                'examples' => 'Customer reports damaged jar; admin approves refund via Razorpay/Cashfree API and updates order status.',
                                'common_problems' => 'Disbursing refunds before checking if the initial payment was settled or fraudulent.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'order-state-machines-idempotency',
                        'name' => 'Order State Machines, Transition Rules & Idempotency',
                        'level' => 'advanced',
                        'description' => 'Strict lifecycle management preventing illegal transitions and duplicate fulfillment.',
                        'source' => [
                            'title' => 'Enterprise Integration Patterns: Messaging & Idempotent State Transitions',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.enterpriseintegrationpatterns.com/patterns/messaging/IdempotentReceiver.html',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Deterministic Order Status State Machine',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'An order must only transition forward along legal paths: Pending -> Confirmed -> Shipped -> Out for Delivery -> Delivered. Cancelled is only permitted from Pending or Confirmed prior to dispatch.',
                                'rules' => [
                                    'Never mark an unverified UPI or pending gateway order as Confirmed or Paid.',
                                    'Dispatched/Delivered orders cannot be cancelled; they must go through the ReturnRequest pipeline.',
                                ],
                                'workflows' => [
                                    'Pending -> (Payment Verified) -> Confirmed -> (Warehouse Pack) -> Shipped -> (Courier API Update) -> Out for Delivery -> Delivered.',
                                ],
                                'implementation_patterns' => 'FlavourFlow Order model defines STATUS_* constants, validates status updates in AdminOrderController, and logs all changes.',
                                'examples' => 'When a direct UPI payment is submitted, the order stays Pending until an admin or bank API verifies the 12-digit UTR reference.',
                                'common_problems' => 'Allowing customers to cancel orders that are already shipped, leading to lost inventory and uncollected charges.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 8: Payments and Financial Operations
            [
                'code' => 'payments_finance',
                'name' => 'Payments and Financial Operations',
                'description' => 'UPI dynamic QR, Razorpay, Cashfree, COD, webhook signature verification, UTR reconciliation, and PDF tax invoicing.',
                'priority' => 80,
                'topics' => [
                    [
                        'slug' => 'payment-methods-cod-and-gateways',
                        'name' => 'Payment Gateways, Card Networks & Cash on Delivery (COD)',
                        'level' => 'beginner',
                        'description' => 'Comparing COD risk, credit/debit card processing, and netbanking in India.',
                        'source' => [
                            'title' => 'Reserve Bank of India (RBI): Payment and Settlement Systems in India',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.rbi.org.in',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'COD Economics and Return to Origin (RTO) Risk',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Cash on Delivery (COD) typically experiences 15-30% Return to Origin (RTO) refusal rates in India, incurring double courier costs (forward + reverse). Pre-paid UPI payments suffer near 0% RTO refusal.',
                                'rules' => [
                                    'High-ticket orders (> ₹2,000) should restrict COD or mandate partial pre-payment.',
                                    'Verify customer mobile number via OTP before confirming high-risk COD orders.',
                                ],
                                'workflows' => [
                                    'Select Payment -> COD: verify address & phone -> Online: route to UPI / Razorpay / Cashfree.',
                                ],
                                'implementation_patterns' => 'FlavourFlow supports COD with instant order placement and pre-paid gateways for lower RTO.',
                                'examples' => 'FlavourFlow encourages pre-paid UPI by offering clear dynamic QR codes.',
                                'common_problems' => 'Allowing anonymous guests to place bulk COD orders with fake phone numbers.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'payment-reconciliation-and-utr-settlement',
                        'name' => 'Bank Reconciliation, UTR Evidence & Settlement Workflows',
                        'level' => 'intermediate',
                        'description' => 'Reconciling 12-digit Unique Transaction Reference (UTR) numbers against bank statements.',
                        'source' => [
                            'title' => 'Indian Banks Association (IBA) & NPCI Guidelines on Transaction Reconciliation',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.iba.org.in',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'UTR Submission vs Verified Settlement',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'When a customer enters a 12-digit UTR and clicks "I Have Paid", this represents customer-provided evidence, NOT proof of payment. Payment is only settled when verified by bank feed or merchant verification API.',
                                'rules' => [
                                    'UTR numbers must be strictly 12 numeric digits in standard Indian banking systems.',
                                    'Duplicate UTR submission across different orders must be rejected immediately.',
                                ],
                                'workflows' => [
                                    'Customer Enters UTR -> Check UTR uniqueness in payments table -> Store as unverified reference -> Admin verifies bank credit -> Transition to Paid.',
                                ],
                                'implementation_patterns' => 'FlavourFlow UpiVerificationService enforces unique references, prevents token replay, and isolates simulated demo payments from real orders.',
                                'examples' => 'UTR `429812345678` is validated for 12 digits and saved with status="pending" until bank clearance.',
                                'common_problems' => 'Allowing the same UTR to confirm multiple orders placed by different user accounts.',
                                'confidence' => 1.0,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'upi-qr-and-webhook-security',
                        'name' => 'UPI Dynamic QR Specifications & Webhook Signature Verification',
                        'level' => 'advanced',
                        'description' => 'NPCI UPI specifications, dynamic QR parameters, and HMAC-SHA256 webhook security.',
                        'source' => [
                            'title' => 'National Payments Corporation of India (NPCI) UPI Linking Specifications',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.npci.org.in/what-we-do/upi/product-overview',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'NPCI Compliant UPI Dynamic QR Parameters',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'NPCI UPI payment URI scheme: upi://pay?pa={VPA}&pn={NAME}&am={AMOUNT}&cu=INR&tn={ORDER_NO}&tr={REF_ID}. All amounts must be formatted with exactly 2 decimal places.',
                                'rules' => [
                                    'Never trust client UTR submission as instant proof of settlement; treat UTR as customer-submitted evidence pending verification.',
                                    'Payment webhooks from Razorpay or Cashfree must be verified using HMAC-SHA256 signature verification before marking orders paid.',
                                ],
                                'workflows' => [
                                    '1. Compute exact server order total.',
                                    '2. Encode NPCI URI with merchant VPA & order number.',
                                    '3. Generate QR code on server.',
                                    '4. Customer pays and submits 12-digit UTR.',
                                    '5. Verify against payment gateway API or bank reconciliation before confirmation.',
                                ],
                                'implementation_patterns' => 'FlavourFlow implements QrCodeService with SVG generation, CashfreeService and RazorpayService with webhook verification, and OrderFulfillmentService for atomic order confirmation.',
                                'examples' => 'For order ORD-20261030-XYZ: `upi://pay?pa=flavourflow@icici&pn=FlavourFlow%20Spices&am=248.00&cu=INR&tn=ORD-20261030-XYZ`',
                                'common_problems' => 'Marking orders Paid immediately when the customer clicks "I Have Paid", opening the store to trivial fraud with fake UTR numbers.',
                                'confidence' => 1.0,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 9: Customer Management and CRM
            [
                'code' => 'crm_customer_management',
                'name' => 'Customer Management and CRM',
                'description' => 'Customer profiles, segmentation, purchase history, lifetime value, loyalty, reviews, and retention strategies.',
                'priority' => 90,
                'topics' => [
                    [
                        'slug' => 'customer-profiles-and-account-portal',
                        'name' => 'Customer Profile Security & Self-Service Account Portal',
                        'level' => 'beginner',
                        'description' => 'Managing addresses, password updates, and self-service order tracking.',
                        'source' => [
                            'title' => 'Customer Identity & Access Management (CIAM) Best Practices',
                            'type' => 'reputable_industry',
                            'url' => 'https://auth0.com/intro-to-iam/what-is-ciam',
                            'authority_score' => 0.95,
                        ],
                        'items' => [
                            [
                                'title' => 'Customer Self-Service Order Hub',
                                'memory_type' => 'store_business',
                                'concept' => 'Allowing customers to view past orders, track delivery in real time, and download GST tax invoices directly reduces incoming support tickets by up to 45%.',
                                'rules' => [
                                    'Customers must only view their own orders (strict user_id scoping).',
                                    'Updating customer email must require current password verification.',
                                ],
                                'workflows' => [
                                    'Customer visits /account/orders -> Views recent orders -> Clicks Track Delivery or Download Tax Invoice.',
                                ],
                                'implementation_patterns' => 'FlavourFlow Account/OrderController provides self-service order tracking and PDF receipt downloads.',
                                'examples' => 'A customer logs in and views order history, re-ordering their favourite spices with 1 click.',
                                'common_problems' => 'Failing to provide order tracking links, leading to customer support overload asking "Where is my order?".',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'rfm-segmentation-customer-retention',
                        'name' => 'RFM Segmentation & Customer Retention Strategies',
                        'level' => 'intermediate',
                        'description' => 'Recency, Frequency, and Monetary (RFM) segmentation for targeting high-value customers.',
                        'source' => [
                            'title' => 'Journal of Interactive Marketing: RFM Customer Analysis',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.sciencedirect.com/journal/journal-of-interactive-marketing',
                            'authority_score' => 0.93,
                        ],
                        'items' => [
                            [
                                'title' => 'RFM Scoring in E-commerce',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'RFM evaluates: Recency (days since last purchase), Frequency (total orders in 365 days), and Monetary value (total spend). Champions (top 20%) generate up to 70% of lifetime profit in pantry & spice retail.',
                                'rules' => [
                                    'Never send generic blast discounts to active Champions; reward them with VIP early access or premium samples.',
                                    'At-risk customers (high historical spend, no purchase in 60-90 days) should receive personalized reactivation offers.',
                                ],
                                'workflows' => [
                                    'Query Customer Orders -> Compute Recency Days, Order Count, Total Spend -> Assign Tier -> Trigger Automated Re-engagement or VIP Retention.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiAnalyzer evaluates customer purchase frequency and order totals to classify customer segments.',
                                'examples' => 'A customer who bought Royal Garam Masala 45 days ago is suggested a replenishment reminder for whole spices.',
                                'common_problems' => 'Failing to track repeat customer frequency and spending all marketing budget on acquisition instead of retention.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'loyalty-programs-and-predictive-ltv',
                        'name' => 'Predictive LTV & Tiered Loyalty Architecture',
                        'level' => 'advanced',
                        'description' => 'Point-based rewards, tier qualification, and predictive churn modeling.',
                        'source' => [
                            'title' => 'Kellogg School of Management: Loyalty Programs & Customer Equity',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.kellogg.northwestern.edu',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Tiered Loyalty Point Mechanics in D2C',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Tiered loyalty (Silver, Gold, Platinum) with non-cash benefits (free express delivery, complimentary saffron samples) produces 2.4x higher incremental retention than pure cashback.',
                                'rules' => [
                                    'Points expiration dates must be clearly disclosed (typically 12 months rolling inactivity).',
                                    'Points cannot be redeemed for cash or refunded upon order cancellation.',
                                ],
                                'workflows' => [
                                    'Customer completes verified order -> Credit points based on net total -> Upgrade tier if threshold crossed -> Notify customer.',
                                ],
                                'implementation_patterns' => 'FlavourFlow models support user loyalty milestones and coupon reward integration.',
                                'examples' => 'Earn 10 Spice Points per ₹100 spent, redeemable on artisanal blends.',
                                'common_problems' => 'Allowing points redemption to drop order totals below shipping and gateway fee costs.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 10: Marketing and Advertising
            [
                'code' => 'marketing_advertising',
                'name' => 'Marketing and Advertising',
                'description' => 'Meta/Google ads, WhatsApp/Email marketing, ROAS analysis, seasonal campaigns, and discount optimization.',
                'priority' => 100,
                'topics' => [
                    [
                        'slug' => 'organic-and-social-media-marketing',
                        'name' => 'Organic Social Media & Recipe Content Marketing',
                        'level' => 'beginner',
                        'description' => 'Using culinary videos, recipe tutorials, and Instagram Reels to drive organic storefront traffic.',
                        'source' => [
                            'title' => 'Content Marketing Institute: Food & Beverage D2C Playbook',
                            'type' => 'reputable_industry',
                            'url' => 'https://contentmarketinginstitute.com',
                            'authority_score' => 0.94,
                        ],
                        'items' => [
                            [
                                'title' => 'Culinary Video Content to Direct Storefront Traffic',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Food and spice retail converts exceptionally well when paired with step-by-step 30-second recipe videos showing the sizzle of whole spices in hot ghee (tadka). The video links directly to the spice blend PDP.',
                                'rules' => [
                                    'Always provide ingredient grammage and direct shop links in video descriptions.',
                                    'Highlight pure sensory qualities: aroma, unpolished natural color, zero preservatives.',
                                ],
                                'workflows' => [
                                    'Create Recipe Content -> Post to Instagram/YouTube -> Include UTM tracking tag -> Direct to FlavourFlow recipe/product page.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CustomerAiAssistantService includes dedicated culinary recipe guidance to convert conversational queries into spice sales.',
                                'examples' => '"How to make authentic Gujarati Dal" reel linking directly to FlavourFlow Cumin Seeds and Turmeric.',
                                'common_problems' => 'Posting product photos without culinary context or demonstration of taste and aroma.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'roas-cac-campaign-optimization',
                        'name' => 'ROAS, Blended CAC & Multi-Channel Marketing Optimization',
                        'level' => 'intermediate',
                        'description' => 'Return on Ad Spend (ROAS) calculation, attribution models, and conversion tracking.',
                        'source' => [
                            'title' => 'Meta Business & Google Ads Attribution Frameworks',
                            'type' => 'official_provider',
                            'url' => 'https://www.facebook.com/business/help/attribution',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Target ROAS vs Break-Even ROAS',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Break-Even ROAS = 1 / Gross Margin %. If gross margin is 50%, break-even ROAS is 2.0x. Any campaign operating above 2.0x is generating net positive operating contribution.',
                                'rules' => [
                                    'Never scale a campaign whose ROAS is below the break-even threshold unless running a targeted top-of-funnel brand campaign with a strict ceiling.',
                                    'WhatsApp marketing in India delivers 45-60% open rates compared to 15-20% on email for order updates and abandoned cart triggers.',
                                ],
                                'workflows' => [
                                    '1. Compute Product Gross Margin.',
                                    '2. Derive Break-Even ROAS.',
                                    '3. Launch High-Intent Search Ads for Specific Products.',
                                    '4. Retarget Add-to-Cart abandoners via WhatsApp / Email.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiRecommendationEngine analyzes winning products with high conversion rates to recommend targeted ad campaigns.',
                                'examples' => 'Promote high-margin Organic Turmeric and Royal Garam Masala in festival seasons (Diwali/Navratri) with 3.5x target ROAS.',
                                'common_problems' => 'Optimizing ads for clicks instead of verified purchase conversions; neglecting attribution latency on repeat orders.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'omnichannel-retargeting-and-marketing-mix',
                        'name' => 'Omnichannel Retargeting & Marketing Mix Modeling',
                        'level' => 'advanced',
                        'description' => 'Server-side Conversion API (CAPI), first-party pixel tracking, and marketing mix attribution.',
                        'source' => [
                            'title' => 'Meta Conversions API (CAPI) & Google Server-Side Tagging Documentation',
                            'type' => 'official_provider',
                            'url' => 'https://developers.facebook.com/docs/marketing-api/conversions-api',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'Server-Side Conversion API Tracking',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Browser ad-blockers and iOS privacy restrictions block 30-40% of client-side tracking pixels. Server-side Conversion API sends purchase events directly from the Laravel backend to Meta/Google, ensuring 100% conversion attribution.',
                                'rules' => [
                                    'Server conversion events must include hashed email and phone number for accurate ad attribution.',
                                    'Never dispatch duplicate purchase events; use a consistent event_id equal to order_number.',
                                ],
                                'workflows' => [
                                    'Order verified on server -> Backend triggers CAPI payload with order_number, total, and SHA256(email) -> Meta ad accounts record true ROAS.',
                                ],
                                'implementation_patterns' => 'FlavourFlow ai_events table records all checkout conversion signals with unique event IDs.',
                                'examples' => 'Sending Purchase event for ORD-20261030-XYZ directly from Laravel upon payment capture.',
                                'common_problems' => 'Relying exclusively on browser client JavaScript pixels, undercounting real ad revenue by 35%.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 11: SEO and Content Management
            [
                'code' => 'seo_content',
                'name' => 'SEO and Content Management',
                'description' => 'Technical SEO, product/category structured data (Schema.org), canonical URLs, Core Web Vitals, and indexing.',
                'priority' => 110,
                'topics' => [
                    [
                        'slug' => 'on-page-seo-and-keyword-research',
                        'name' => 'On-Page SEO, Keyword Research & Title Tags',
                        'level' => 'beginner',
                        'description' => 'Crafting optimized title tags, meta descriptions, and H1 hierarchies for spices and food products.',
                        'source' => [
                            'title' => 'Google Search Central: SEO Starter Guide',
                            'type' => 'official_provider',
                            'url' => 'https://developers.google.com/search/docs/fundamentals/seo-starter-guide',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'E-Commerce Product Title & Meta Formula',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'High-ranking e-commerce titles follow: [Brand] + [Product Name] + [Purity / Spec] + [Weight / Unit]. Meta descriptions should emphasize fast delivery, pure origin, and clear pricing.',
                                'rules' => [
                                    'Title tags should stay between 50-60 characters to avoid search result truncation.',
                                    'Every product page must have exactly one H1 tag matching the product title.',
                                ],
                                'workflows' => [
                                    'Research primary query (e.g. "pure kashmiri chilli online") -> Set Title Tag -> Write meta description with CTA -> Verify H1.',
                                ],
                                'implementation_patterns' => 'FlavourFlow layout dynamically renders `<title>{{ $title ?? "FlavourFlow" }}</title>`.',
                                'examples' => 'Title: "Royal Garam Masala (100g) — 100% Pure Spice Blend | FlavourFlow"',
                                'common_problems' => 'Leaving default duplicate title tags across multiple catalog pages.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'ecommerce-structured-data-schema-seo',
                        'name' => 'E-commerce Structured Data, Schema.org & Technical SEO',
                        'level' => 'intermediate',
                        'description' => 'Google Search Central standards for Product, Offer, AggregateRating, and BreadcrumbList markup.',
                        'source' => [
                            'title' => 'Google Search Central: Product Structured Data Documentation',
                            'type' => 'official_provider',
                            'url' => 'https://developers.google.com/search/docs/appearance/structured-data/product',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Schema.org Product & Offer Markup',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Including JSON-LD structured data on PDPs allows Google to display rich snippets with live price, availability (InStock / OutOfStock), and customer review ratings directly in search results.',
                                'rules' => [
                                    'Prices in JSON-LD must match the visible page price exactly.',
                                    'Every product page must contain a self-referencing canonical URL tag to prevent duplicate content penalties from filter/sort query strings.',
                                ],
                                'workflows' => [
                                    'Render PDP Blade view -> Inject JSON-LD with @context: "https://schema.org/", @type: "Product", name, image, offers: {@type: "Offer", price, priceCurrency: "INR", availability} -> Add canonical URL.',
                                ],
                                'implementation_patterns' => 'FlavourFlow templates include OpenGraph meta tags, canonical URL links, and Schema.org Product markup.',
                                'examples' => '{"@context": "https://schema.org/", "@type": "Product", "name": "Organic Turmeric", "offers": {"@type": "Offer", "price": "149.00", "priceCurrency": "INR", "availability": "https://schema.org/InStock"}}',
                                'common_problems' => 'Failing to update structured data price when active sales or coupons change price, leading to Google Merchant Center suspensions.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'crawl-budget-canonicalization-and-faceted-search',
                        'name' => 'Faceted Navigation SEO & Crawl Budget Management',
                        'level' => 'advanced',
                        'description' => 'Preventing index bloat from filter query parameters while indexing high-intent combinations.',
                        'source' => [
                            'title' => 'Google Search Central: Faceted Navigation Best Practices',
                            'type' => 'official_provider',
                            'url' => 'https://developers.google.com/search/docs/crawling-indexing/faceted-navigation',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Canonicalization on Filtered E-Commerce Catalogs',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Filtering by price, rating, or sort order creates thousands of URL variations (e.g. `?sort=price_asc&filter=whole`). Without canonical tags pointing to the clean root category URL, search crawlers waste crawl budget on infinite duplicate URLs.',
                                'rules' => [
                                    'All sort and pagination query parameters must canonicalize to the parent category root.',
                                    'Disallow dynamic filter parameters in robots.txt if they lack unique search volume.',
                                ],
                                'workflows' => [
                                    'User filters catalog -> Render page with filtered results -> Emit `<link rel="canonical" href="https://flavourflow.test/category/whole-spices" />`.',
                                ],
                                'implementation_patterns' => 'FlavourFlow controllers pass canonical base routes to prevent search parameter fragmentation.',
                                'examples' => 'URL `/products?sort=price_asc` has canonical link `https://flavourflow.test/`.',
                                'common_problems' => 'Allowing millions of internal filter combinations to be indexed, exhausting Google crawl budget.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 12: Analytics and Business Intelligence
            [
                'code' => 'analytics_bi',
                'name' => 'Analytics and Business Intelligence',
                'description' => 'Sales and revenue analytics, AOV, conversion funnel drop-offs, inventory velocity, and forecasting.',
                'priority' => 120,
                'topics' => [
                    [
                        'slug' => 'core-ecommerce-kpis-and-dashboards',
                        'name' => 'Core E-Commerce Metrics & Executive Dashboarding',
                        'level' => 'beginner',
                        'description' => 'Understanding GMV, net revenue, AOV, order volumes, and customer conversion rates.',
                        'source' => [
                            'title' => 'McKinsey & Company: Retail E-Commerce Performance Dashboards',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.mckinsey.com',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Gross Merchandise Value (GMV) vs Net Revenue',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'GMV is the total value of all placed merchandise. Net Revenue subtracts cancellations, returns, discounts, and taxes. True business decisions must rely strictly on Net Revenue.',
                                'rules' => [
                                    'Never report GMV as confirmed revenue; always exclude unverified pending orders and cancellations.',
                                    'Track Average Order Value (AOV) on a rolling 30-day basis.',
                                ],
                                'workflows' => [
                                    'Query Orders -> Filter status in [Confirmed, Shipped, Delivered] -> Sum total_amount -> Derive net realized revenue.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiDomainConsultantService::analyzeBusinessMetrics() derives verified sales summary directly from the database.',
                                'examples' => 'If total orders are ₹50,000 but ₹10,000 are pending or cancelled, Net Realized Revenue is ₹40,000.',
                                'common_problems' => 'Celebrating high GMV spikes while ignoring high cancellation and payment failure rates.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'ecommerce-funnel-analytics-and-kpis',
                        'name' => 'E-commerce Conversion Funnel & KPI Architecture',
                        'level' => 'intermediate',
                        'description' => 'Tracking Store Sessions -> Product Views -> Add to Cart -> Checkout Started -> Purchase.',
                        'source' => [
                            'title' => 'Google Analytics 4 E-commerce Implementation Guide',
                            'type' => 'official_provider',
                            'url' => 'https://developers.google.com/analytics/devguides/collection/ga4/ecommerce',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'E-commerce Conversion Funnel Benchmarks',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Healthy e-commerce funnel benchmarks: Homepage to Product View (45-60%), Product View to Cart (8-12%), Cart to Checkout (50-60%), Checkout to Complete Purchase (70-80%). Overall Store Conversion Rate is typically 1.5% to 3.5%.',
                                'rules' => [
                                    'Never invent analytics metrics when actual store data is missing; report exact counts and flag sample size limitations.',
                                    'A steep drop-off between Cart and Checkout indicates unexpected costs (shipping/tax) or forced registration friction.',
                                ],
                                'workflows' => [
                                    'Track ai_events table -> Group by event_type -> Compute step conversion ratios -> Detect drop-off bottlenecks -> Recommend targeted UX fixes.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiAnalyzer computes global and user conversion metrics from the real ai_events table.',
                                'examples' => 'If FlavourFlow has 1,000 product views, 120 cart additions, and 30 completed orders, overall conversion is 3.0% (healthy).',
                                'common_problems' => 'Conflating total visitors with unique converting sessions; ignoring mobile vs desktop funnel divergence.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'cohort-retention-and-predictive-forecasting',
                        'name' => 'Cohort Retention Analysis & Demand Forecasting',
                        'level' => 'advanced',
                        'description' => 'Month-by-month cohort repurchase curves and seasonality-weighted demand forecasting.',
                        'source' => [
                            'title' => 'Wharton School of Business: Customer Cohort Retention Modeling',
                            'type' => 'reputable_industry',
                            'url' => 'https://knowledge.wharton.upenn.edu',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Cohort Repurchase Curves in Pantry Retail',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Grouping customers by acquisition month (e.g. October Cohort) reveals whether repeat purchase frequency is improving over time. Healthy food brands see 25-35% of a cohort repurchase within 90 days.',
                                'rules' => [
                                    'If 60-day repurchase rate drops below 15%, immediate product quality or post-purchase packaging audits are mandatory.',
                                    'Forecast festive season demand (Diwali, Eid, weddings) with minimum 1.8x multiplier over baseline summer months.',
                                ],
                                'workflows' => [
                                    'Query orders grouped by user_id and month -> Build triangle cohort retention matrix -> Apply seasonal trend multiplier.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiRecommendationEngine analyzes repeat customer purchase patterns across 30, 60, and 90 day windows.',
                                'examples' => 'Customers acquired during festive spice offers generate 40% repeat orders for everyday kitchen essentials.',
                                'common_problems' => 'Assuming summer sales velocity will predict winter holiday cooking demand without seasonal multipliers.',
                                'confidence' => 0.96,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 13: Security, Privacy and Fraud Prevention
            [
                'code' => 'security_fraud_privacy',
                'name' => 'Security, Privacy and Fraud Prevention',
                'description' => 'Authentication, RBAC, CSRF, XSS, SQLi, IDOR, payment security, rate limiting, and data retention.',
                'priority' => 130,
                'topics' => [
                    [
                        'slug' => 'basic-web-security-csrf-and-sanitization',
                        'name' => 'CSRF Protection, Input Sanitization & Session Security',
                        'level' => 'beginner',
                        'description' => 'Cross-Site Request Forgery tokens, HTML escaping, and session fixation defense.',
                        'source' => [
                            'title' => 'OWASP Foundation: Cross-Site Request Forgery Prevention Cheat Sheet',
                            'type' => 'official_framework',
                            'url' => 'https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'CSRF Tokens and State-Changing HTTP Verbs',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Any state-changing action (adding to cart, placing an order, submitting reviews) must use POST/PUT/DELETE with a validated CSRF token to prevent malicious third-party websites from executing unauthorized orders on behalf of a logged-in user.',
                                'rules' => [
                                    'Never execute state-changing business actions via GET HTTP requests.',
                                    'Session cookies must be marked HttpOnly, Secure, and SameSite=Lax or Strict.',
                                ],
                                'workflows' => [
                                    'Form render -> Include @csrf -> Incoming request -> VerifyCsrfToken middleware verifies token -> Controller processes order.',
                                ],
                                'implementation_patterns' => 'FlavourFlow enforces @csrf in all Blade checkout and account forms, and sets session security in config/session.php.',
                                'examples' => 'Checkout fetch API passes X-CSRF-TOKEN header read from `<meta name="csrf-token">`.',
                                'common_problems' => 'Disabling CSRF middleware globally or accepting payment confirmations via unverified GET callbacks.',
                                'confidence' => 1.0,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'rate-limiting-and-brute-force-protection',
                        'name' => 'Rate Limiting, Throttling & Sensitive Data Masking',
                        'level' => 'intermediate',
                        'description' => 'Defending against coupon brute-forcing, OTP abuse, and payment endpoint flooding.',
                        'source' => [
                            'title' => 'OWASP Automated Threats to Web Applications (Credential Stuffing & Carding)',
                            'type' => 'official_framework',
                            'url' => 'https://owasp.org/www-project-automated-threats-to-web-applications/',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'API Rate Limiting on Checkout & OTP Endpoints',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Payment attempts, OTP verification, and coupon validations must be rate-limited per IP and per user account to prevent carding attacks and SMS gateway budget exhaustion.',
                                'rules' => [
                                    'Rate limit checkout submissions to maximum 10 attempts per minute per IP.',
                                    'Never log full payment secrets, API keys, or raw bank responses in public or unencrypted log files.',
                                ],
                                'workflows' => [
                                    'Request hits route -> ThrottleRequests middleware checks Redis/Cache hit count -> If exceeded return 429 Too Many Requests.',
                                ],
                                'implementation_patterns' => 'FlavourFlow routes use `throttle:10,1` on AI provider testing and payment verification routes.',
                                'examples' => 'Submitting 12 fake UTR numbers in 10 seconds triggers an immediate HTTP 429 rate limit block.',
                                'common_problems' => 'Leaving SMS OTP endpoints unthrottled, allowing malicious bots to drain thousands of dollars in SMS fees.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'ecommerce-security-idor-csrf-rbac',
                        'name' => 'IDOR Prevention, RBAC & Secure Checkout Architecture',
                        'level' => 'advanced',
                        'description' => 'Protecting customer orders, admin operations, and payment endpoints against tampering.',
                        'source' => [
                            'title' => 'OWASP Top 10 Web Application Security Risks',
                            'type' => 'official_framework',
                            'url' => 'https://owasp.org/www-project-top-ten/',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'Insecure Direct Object Reference (IDOR) in Order APIs',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'An IDOR vulnerability allows attacker customer A to view or download customer B’s order or invoice by simply guessing or incrementing an order ID. Orders must always be scoped to auth()->id() or protected by unguessable UUIDs/tokens.',
                                'rules' => [
                                    'Always verify `$order->user_id === auth()->id()` or authorize via Policy before returning order details or PDF invoices.',
                                    'Never display customer PII (phone, address, email) to guests without explicit authentication.',
                                    'Use cryptographically random order numbers (e.g. ORD-YYYYMMDD-RANDOM) instead of auto-incrementing integer IDs in URLs.',
                                ],
                                'workflows' => [
                                    'Receive Order Request -> Check auth()->check() -> Authorize via Gate/Policy -> Query where user_id == auth()->id() -> Return response.',
                                ],
                                'implementation_patterns' => 'In FlavourFlow Account/OrderController, orders are scoped: `Order::where("user_id", auth()->id())->where("order_number", $number)->firstOrFail()`.',
                                'examples' => 'A guest requesting `/account/orders/FF-SECRET-999` is redirected to `/login` with zero customer data exposed.',
                                'common_problems' => 'Using Route Model Binding without verifying user ownership, allowing any authenticated user to view another user’s invoice.',
                                'confidence' => 1.0,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 14: Notifications and Workflow Automation
            [
                'code' => 'notifications_automation',
                'name' => 'Notifications and Workflow Automation',
                'description' => 'Transactional email, WhatsApp/SMS integrations, order lifecycle notifications, queue workers, and event-driven automation.',
                'priority' => 140,
                'topics' => [
                    [
                        'slug' => 'order-lifecycle-notifications-and-templates',
                        'name' => 'Transactional Email & Order Lifecycle Notifications',
                        'level' => 'beginner',
                        'description' => 'Order placed, payment confirmed, and shipping update notification templates.',
                        'source' => [
                            'title' => 'CAN-SPAM Act & Indian Telecom Commercial Communications Customer Preference Regulations',
                            'type' => 'government_regulatory',
                            'url' => 'https://trai.gov.in',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Transactional Notification Content Requirements',
                                'memory_type' => 'store_business',
                                'concept' => 'Transactional emails must contain clear order identifiers, complete itemised breakdowns, delivery addresses, and customer support contact details without requiring the customer to log in to understand what they bought.',
                                'rules' => [
                                    'Transactional order confirmations must never require an unsubscribe link (exempt under CAN-SPAM).',
                                    'Always provide store contact email and telephone in email footers.',
                                ],
                                'workflows' => [
                                    'Order Confirmed -> Compile OrderPlaced Mailable -> Transmit via configured SMTP -> Log message ID.',
                                ],
                                'implementation_patterns' => 'FlavourFlow OrderPlaced mailable uses clean Laravel Markdown components with item tables and tracking buttons.',
                                'examples' => 'OrderPlaced email sent to customer with order #ORD-20261030-XYZ and item summary.',
                                'common_problems' => 'Mixing marketing promotions into transactional order receipts, triggering spam filters.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'multi-channel-messaging-whatsapp-and-sms',
                        'name' => 'Multi-Channel Messaging: WhatsApp Business API & SMS Gateway',
                        'level' => 'intermediate',
                        'description' => 'Integrating WhatsApp Business Cloud API and DLT-approved SMS templates in India.',
                        'source' => [
                            'title' => 'TRAI Distributed Ledger Technology (DLT) Guidelines for Commercial SMS in India',
                            'type' => 'government_regulatory',
                            'url' => 'https://trai.gov.in',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'WhatsApp Order Updates & DLT Compliance',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'In India, commercial SMS requires pre-approved DLT Header and Content Templates. WhatsApp Business Cloud API provides interactive buttons ("Track Package", "Contact Support") and higher reliability.',
                                'rules' => [
                                    'SMS templates must match TRAI DLT approved text exactly, including variable placeholders {#var#}.',
                                    'WhatsApp messages must only use approved template categories (Utility, Authentication, Marketing).',
                                ],
                                'workflows' => [
                                    'Order Shipped -> Dispatch WhatsApp Utility Template with tracking URL -> Fallback to SMS if WhatsApp delivery fails.',
                                ],
                                'implementation_patterns' => 'FlavourFlow notification services are designed for multi-channel dispatch with database fallback.',
                                'examples' => '"Your FlavourFlow order #ORD-XYZ has shipped with Bluedart! Track here: [URL]".',
                                'common_problems' => 'Attempting to send unapproved SMS text in India, resulting in silent telecom operator blocking.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'transactional-messaging-and-queue-architecture',
                        'name' => 'Transactional Messaging & Queue Worker Reliability',
                        'level' => 'advanced',
                        'description' => 'Asynchronous email dispatch, SMTP failover, and queue failure handling.',
                        'source' => [
                            'title' => 'Laravel Queues & Mail Architecture Guide',
                            'type' => 'official_framework',
                            'url' => 'https://laravel.com/docs/queues',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Non-Blocking Transactional Notifications',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Synchronous SMTP calls during HTTP checkout add 1-4 seconds of latency and crash the customer request if the mail server encounters a transient socket timeout. Queueing notifications isolates user checkout from external network latency.',
                                'rules' => [
                                    'Never hold an HTTP database transaction open while waiting for third-party SMTP or SMS socket responses.',
                                    'Always configure dead-letter / failed_jobs tables to record and replay failed transactional messages.',
                                ],
                                'workflows' => [
                                    'Checkout Completed -> Order DB Committed -> Dispatch Mail / SMS Job -> Queue Worker processes job -> Log delivery or retry with backoff.',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses Mail::to()->send() wrapped in try/catch or queued jobs, preventing third-party mail network hiccups from reverting orders.',
                                'examples' => 'OrderPlaced notification sends HTML confirmation with item breakdown and estimated delivery dates.',
                                'common_problems' => 'Embedding heavy synchronous PDF invoice generation inside the mail dispatch pipeline, causing Gmail SMTP timeouts.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 15: Technical Architecture and Development
            [
                'code' => 'technical_architecture',
                'name' => 'Technical Architecture and Development',
                'description' => 'Laravel best practices, Eloquent query optimization, caching strategies, Redis, Docker, and modular architectures.',
                'priority' => 150,
                'topics' => [
                    [
                        'slug' => 'laravel-mvc-and-restful-architecture',
                        'name' => 'Laravel MVC, Form Requests & RESTful Conventions',
                        'level' => 'beginner',
                        'description' => 'Clean controller separation, Form Request validation, and resource routing.',
                        'source' => [
                            'title' => 'Laravel Official Documentation: Architecture Concepts & Routing',
                            'type' => 'official_framework',
                            'url' => 'https://laravel.com/docs',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'Thin Controllers & Dedicated Domain Services',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Controllers should only handle HTTP validation, authorization, and response formatting. Complex business operations (payment verification, coupon calculation, invoice generation) belong in dedicated Service classes.',
                                'rules' => [
                                    'Never place raw payment gateway API calls or PDF generation directly inside controller methods.',
                                    'Use Type-Hinted Dependency Injection in constructors for all business services.',
                                ],
                                'workflows' => [
                                    'HTTP Request -> Form Request / Controller Validation -> Domain Service executes business logic -> Return JSON or Blade View.',
                                ],
                                'implementation_patterns' => 'FlavourFlow delegates to dedicated services: QrCodeService, InvoiceService, CouponService, DemoPaymentService, CashfreeService, RazorpayService.',
                                'examples' => 'CheckoutController delegates coupon verification to $this->couponService->validateCoupon().',
                                'common_problems' => 'Creating 3,000-line "god controllers" that contain SQL, HTML formatting, payment gateway calls, and email logic.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'eloquent-n-plus-one-and-caching-strategies',
                        'name' => 'Eloquent N+1 Elimination & Multi-Tier Caching Strategies',
                        'level' => 'intermediate',
                        'description' => 'Eager loading relationships, Redis caching, and database indexing.',
                        'source' => [
                            'title' => 'High Performance Laravel: Database & Caching Optimization',
                            'type' => 'reputable_industry',
                            'url' => 'https://laravel.com/docs/eloquent-relationships#eager-loading',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'N+1 Query Elimination in E-commerce Catalogues',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Looping through products and accessing relationships (e.g. $product->category, $product->reviews) triggers N additional database queries. Eager loading via `with(["category", "reviews"])` reduces 101 queries to 2 queries.',
                                'rules' => [
                                    'Always eager load items when serializing orders or product collections.',
                                    'Use indexed lookup columns for high-frequency queries (slug, sku, status, user_id).',
                                ],
                                'workflows' => [
                                    'Product::query()->active()->with(["category", "reviews"])->orderByDesc("priority")->paginate(20).',
                                ],
                                'implementation_patterns' => 'FlavourFlow uses eager loading `with("items")` throughout OrderController and CustomerAiAssistantService.',
                                'examples' => 'Loading 50 customer orders with items executes 2 queries with `Order::with("items")` instead of 51 queries.',
                                'common_problems' => 'Calling `$order->items` inside Blade @foreach without eager loading in the controller.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'modular-reusable-ai-architecture',
                        'name' => 'Portable & Modular Reusable Subsystem Architecture',
                        'level' => 'advanced',
                        'description' => 'Decoupling domain engines from application business logic using contracts and adapters.',
                        'source' => [
                            'title' => 'Clean Architecture: A Craftsman\'s Guide (Robert C. Martin)',
                            'type' => 'reputable_industry',
                            'url' => 'https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html',
                            'authority_score' => 0.98,
                        ],
                        'items' => [
                            [
                                'title' => 'Adapter Pattern for E-Commerce AI Decoupling',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'The AI knowledge and reasoning core must not couple directly to FlavourFlow-specific database tables. By defining contracts (AiAdapterInterface, AiKnowledgeBaseInterface), the knowledge engine can be extracted and reused in future Laravel e-commerce stores without changing a single line of core logic.',
                                'rules' => [
                                    'Core AI reasoning must depend on interfaces, never concrete Eloquent models.',
                                    'Store-specific mappings belong in an Adapter layer (FlavourFlowAdapter).',
                                ],
                                'workflows' => [
                                    'Generic Request -> AiAdapter maps to store schema -> AiEngine / AiBrain processes -> AiAdapter maps back to unified response.',
                                ],
                                'implementation_patterns' => 'FlavourFlow organizes AI in `app/AI/Core`, `app/AI/Contracts`, `app/AI/Adapters`, and `app/AI/Services`.',
                                'examples' => 'FlavourFlowAdapter maps FlavourFlow Product models to generic e-commerce catalog items.',
                                'common_problems' => 'Hardcoding project-specific table names directly inside reusable AI services.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 16: Legal, Tax and Compliance
            [
                'code' => 'legal_tax_compliance',
                'name' => 'Legal, Tax and Compliance',
                'description' => 'E-commerce regulations, Consumer Protection Act, GST rules, tax invoice formats, and return policy compliance.',
                'priority' => 160,
                'topics' => [
                    [
                        'slug' => 'consumer-protection-and-e-commerce-rules',
                        'name' => 'Consumer Protection (E-Commerce) Rules & Legal Disclosures',
                        'level' => 'beginner',
                        'description' => 'Mandatory disclosures: country of origin, grievance officer, contact details, and expiry dates.',
                        'source' => [
                            'title' => 'Consumer Protection (E-Commerce) Rules, 2020 (Government of India)',
                            'type' => 'government_regulatory',
                            'url' => 'https://consumeraffairs.nic.in',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'Mandatory Disclosures for Indian E-Commerce Entities',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Under Indian E-Commerce Rules 2020, every online store must explicitly display: Legal entity name, principal physical address, website, contact email and customer care phone number, Grievance Officer details, and Country of Origin on every product.',
                                'rules' => [
                                    'Never hide customer support phone numbers or physical business addresses.',
                                    'Food products must display FSSAI license numbers and expiry / best-before dates.',
                                ],
                                'workflows' => [
                                    'Store Setup -> Fill Business Details in AdminBusinessSetting -> Render in storefront footer and contact page.',
                                ],
                                'implementation_patterns' => 'FlavourFlow BusinessSetting model provides admin-configurable business name, address, GSTIN, phone, and return policy text.',
                                'examples' => 'FlavourFlow footer displays: "FlavourFlow Spices, Ring Road, Surat, Gujarat 395002 | support@flavourflow.test | +91 98765 43210".',
                                'common_problems' => 'Operating an e-commerce website without a visible return policy or grievance officer contact, violating consumer laws.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'gst-invoicing-and-consumer-protection-india',
                        'name' => 'GST Tax Invoicing Rules & Consumer Protection Regulations',
                        'level' => 'intermediate',
                        'description' => 'Mandatory tax invoice fields under Central Goods and Services Tax (CGST) rules in India.',
                        'source' => [
                            'title' => 'Central Board of Indirect Taxes and Customs (CBIC) - GST Tax Invoice Rules',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.cbic.gov.in',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'Mandatory GST Tax Invoice Requirements',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Under Rule 46 of CGST Rules, a tax invoice must contain: Consecutive serial number (unique per financial year), date of issue, supplier name, address & GSTIN, recipient name & address, HSN/SAC code, item description, quantity, unit rate, taxable value, tax rates (CGST/SGST/IGST), and total value.',
                                'rules' => [
                                    'Never label an unverified or unpaid order as an official Tax Invoice; use Proforma Invoice or Order Confirmation until payment is settled.',
                                    'Invoice numbers must be sequential and never skipped or duplicated.',
                                ],
                                'workflows' => [
                                    'Order Placed -> (Proforma) -> Payment Verified -> Generate Sequential Invoice Number -> Render PDF with GSTIN, HSN, Tax Breakdown -> Archive.',
                                ],
                                'implementation_patterns' => 'FlavourFlow InvoiceService generates official PDF invoices with GSTIN, business details from BusinessSetting singleton, and atomic numbering.',
                                'examples' => 'Invoice #INV-2026-0001 with supplier GSTIN `24ABCDE1234F1Z5`, showing 5% GST breakdown on spices.',
                                'common_problems' => 'Fabricating tax amounts or issuing GST tax invoices without a verified GSTIN on record.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'dpdp-act-and-customer-privacy-consent',
                        'name' => 'Digital Personal Data Protection (DPDP) Act Compliance',
                        'level' => 'advanced',
                        'description' => 'Consent management, data minimization, right to erasure, and activity tracking compliance.',
                        'source' => [
                            'title' => 'Digital Personal Data Protection Act, 2023 (Ministry of Law and Justice, India)',
                            'type' => 'government_regulatory',
                            'url' => 'https://www.meity.gov.in',
                            'authority_score' => 1.0,
                        ],
                        'items' => [
                            [
                                'title' => 'Opt-In Privacy Consent and Activity Tracking',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Under the DPDP Act 2023, tracking user behavioral events (e.g. AI product view history, recommendation clicks) requires unambiguous, freely given consent. If a customer declines activity tracking, personal data collection must immediately halt.',
                                'rules' => [
                                    'Always verify PrivacyConsent status before recording behavioral events in ai_events table.',
                                    'If tracking is declined, fall back strictly to non-personalized catalog recommendations.',
                                ],
                                'workflows' => [
                                    'User arrives -> Display Privacy Consent banner -> If allowed: track ai_events -> If declined: record refusal and pause tracking.',
                                ],
                                'implementation_patterns' => 'FlavourFlow implements PrivacyConsent model and checks `allowsActivityTracking()` in AiEventTracker and AiRecommendationEngine.',
                                'examples' => 'When a user declines consent, AiRecommendationEngine falls back to curated non-personalized top items with full disclosure.',
                                'common_problems' => 'Silently tracking and profiling users who have explicitly opted out of marketing/behavioral tracking.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 17: AI-powered E-commerce
            [
                'code' => 'ai_powered_commerce',
                'name' => 'AI-Powered E-commerce',
                'description' => 'Personalized recommendations, vector search, customer assistants, demand forecasting, and autonomous decision copilots.',
                'priority' => 170,
                'topics' => [
                    [
                        'slug' => 'conversational-shopping-assistants',
                        'name' => 'Conversational AI Shopping Assistants & Natural Search',
                        'level' => 'beginner',
                        'description' => 'Multi-turn conversational commerce, handling greetings, intent parsing, and product discovery.',
                        'source' => [
                            'title' => 'Conversational Commerce: AI Assistants in Retail (Gartner Research)',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.gartner.com',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Multi-Turn Customer Shopping Assistance',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Conversational shopping assistants help customers discover products using natural queries ("I need a spice for biryani", "how to make chai masala") without requiring them to know exact product catalog names.',
                                'rules' => [
                                    'The assistant must maintain session memory across multi-turn queries.',
                                    'Always attach actionable shopping buttons (View Product, Add to Cart) to assistant recommendations.',
                                ],
                                'workflows' => [
                                    'Receive message -> Understand intent & language -> Retrieve matching products -> Format cards & follow-up suggestions.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CustomerAiAssistantService provides multi-turn session chat at `/ai/chat`.',
                                'examples' => 'Customer asks "what spices for chai?", assistant explains cardamom and clove and provides direct view links.',
                                'common_problems' => 'Returning long walls of plain text with no clickable product cards or action buttons.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'behavioral-recommendations-and-smart-bundling',
                        'name' => 'Behavioral Recommendation Engines & Smart Cross-Selling',
                        'level' => 'intermediate',
                        'description' => 'Collaborative filtering, complementary items (co-purchase graphs), and abandoned cart recovery.',
                        'source' => [
                            'title' => 'Amazon.com Recommendations: Item-to-Item Collaborative Filtering (IEEE)',
                            'type' => 'reputable_industry',
                            'url' => 'https://www.cs.umd.edu/~samir/498/Amazon-Recommendations.pdf',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Item-to-Item Co-Purchase Cross-Selling',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'Customers who purchase Royal Garam Masala frequently buy Organic Turmeric and Cumin Seeds. By analyzing past order items, the recommendation engine dynamically surfaces complementary items at checkout, increasing basket size.',
                                'rules' => [
                                    'Never recommend items the customer already has in their active cart.',
                                    'Exclude out-of-stock items from recommendation carousels.',
                                ],
                                'workflows' => [
                                    'Read cart items -> Query historical co-purchases from order_items table -> Exclude in-cart IDs -> Filter inStock -> Return top 4.',
                                ],
                                'implementation_patterns' => 'FlavourFlow AiRecommendationEngine implements buildComplementaryRecommendations() using historical order patterns.',
                                'examples' => 'Adding Red Chilli Powder triggers a complementary recommendation for Cumin Seeds.',
                                'common_problems' => 'Recommending the exact same product the customer is currently viewing or already added to cart.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'collaborative-filtering-and-grounded-ai',
                        'name' => 'Hybrid Recommendation Systems & Grounded AI Assistants',
                        'level' => 'advanced',
                        'description' => 'Combining behavioral event tracking with deterministic catalog grounding to eliminate hallucinations.',
                        'source' => [
                            'title' => 'ACM Conference on Recommender Systems (RecSys) & Grounded LLM Architecture',
                            'type' => 'reputable_industry',
                            'url' => 'https://recsys.acm.org/',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'Deterministic Database Grounding in E-Commerce Assistants',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'Generative AI assistants must be strictly grounded in actual database catalog records (names, prices, live inventory). When a user asks about pricing, stock, or order status, the assistant must verify against the database first, using the LLM only for conversational phrasing.',
                                'rules' => [
                                    'The assistant must NEVER invent a product, discount, or delivery date not present in verified database records.',
                                    'If a requested item is not found, honestly report that the store does not stock it rather than hallucinating its existence.',
                                ],
                                'workflows' => [
                                    'Parse User Intent & Language -> Query Real Database Products -> Format Truthful Grounded Payload -> Pass Grounded Context to Multilingual NLU -> Respond to Customer.',
                                ],
                                'implementation_patterns' => 'FlavourFlow CustomerAiAssistantService and AiLanguageUnderstanding follow this strict grounding rule with fallback to top active products.',
                                'examples' => 'When asked "How much is Kashmiri Saffron?", the system checks Product::where("name", "like", "%Saffron%"), finds ₹450.00, and returns exact stock.',
                                'common_problems' => 'Letting an unconstrained LLM quote fabricated prices or confirm delivery dates without querying the database.',
                                'confidence' => 0.99,
                            ],
                        ],
                    ],
                ],
            ],

            // Domain 18: Advanced E-commerce Architecture
            [
                'code' => 'advanced_architecture',
                'name' => 'Advanced E-commerce Architecture',
                'description' => 'Multi-vendor marketplaces, multi-warehouse routing, international commerce, multi-currency, and enterprise scalability.',
                'priority' => 180,
                'topics' => [
                    [
                        'slug' => 'headless-and-composable-commerce',
                        'name' => 'Headless & Composable E-Commerce Principles',
                        'level' => 'beginner',
                        'description' => 'Decoupling frontend presentation from backend commerce logic via clean JSON APIs.',
                        'source' => [
                            'title' => 'MACH Alliance (Microservices, API-first, Cloud-native, Headless)',
                            'type' => 'reputable_industry',
                            'url' => 'https://machalliance.org',
                            'authority_score' => 0.97,
                        ],
                        'items' => [
                            [
                                'title' => 'API-First Commerce Endpoints',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'In headless architecture, the backend exposes versioned REST/GraphQL endpoints for cart, catalog, and checkout, enabling independent mobile apps, smart kiosks, or IoT devices to share a single commerce core.',
                                'rules' => [
                                    'All commerce endpoints must support JSON request/response formats alongside server-rendered views.',
                                    'Maintain strict schema versioning so mobile apps do not break when web views evolve.',
                                ],
                                'workflows' => [
                                    'Expose /api/v1/products, /api/v1/cart, /api/v1/checkout -> Authenticate via Bearer token -> Execute same service layer.',
                                ],
                                'implementation_patterns' => 'FlavourFlow controllers inspect `$request->expectsJson()` to return JSON or View transparently.',
                                'examples' => 'Checkout endpoint returns JSON redirect_url when requested by fetch API and HTTP 302 on standard forms.',
                                'common_problems' => 'Duplicating business logic between Web controllers and API controllers.',
                                'confidence' => 0.98,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'multi-warehouse-and-enterprise-scalability',
                        'name' => 'Multi-Warehouse Routing & Nearest-Hub Allocation',
                        'level' => 'intermediate',
                        'description' => 'Geographic order splitting, nearest-warehouse routing, and transit optimization.',
                        'source' => [
                            'title' => 'AWS & Martin Fowler: Enterprise Microservices & Order Routing Patterns',
                            'type' => 'reputable_industry',
                            'url' => 'https://martinfowler.com/articles/microservices.html',
                            'authority_score' => 0.96,
                        ],
                        'items' => [
                            [
                                'title' => 'Geographic Warehouse Order Routing',
                                'memory_type' => 'general_ecommerce',
                                'concept' => 'In multi-warehouse operations, each order is routed to the fulfillment facility with the shortest transit time and lowest shipping cost. If an order contains items across separate warehouses, split fulfillment is initiated.',
                                'rules' => [
                                    'Split fulfillments must notify the customer with distinct tracking numbers for each parcel.',
                                    'Payment settlement remains unified at the master order level.',
                                ],
                                'workflows' => [
                                    'Evaluate Destination Pincode -> Match Inventory Availability per Warehouse -> Select Primary Node -> Generate Warehouse Dispatch Jobs.',
                                ],
                                'implementation_patterns' => 'FlavourFlow DeliverySetting model allows configuring deliverable states and cities, laying the foundation for multi-hub routing.',
                                'examples' => 'Deliveries in Gujarat are routed to the Surat distribution hub for 24-48h express transit.',
                                'common_problems' => 'Blindly splitting orders without calculating combined shipping economics, destroying contribution margins.',
                                'confidence' => 0.95,
                            ],
                        ],
                    ],
                    [
                        'slug' => 'multi-vendor-marketplace-and-commission-splitting',
                        'name' => 'Multi-Vendor Marketplaces, Split Settlements & Escrow',
                        'level' => 'advanced',
                        'description' => 'Multi-vendor architecture, merchant commission splitting, and escrow payout workflows.',
                        'source' => [
                            'title' => 'Stripe Connect & Razorpay Route: Marketplace Payout Architecture',
                            'type' => 'official_provider',
                            'url' => 'https://stripe.com/docs/connect',
                            'authority_score' => 0.99,
                        ],
                        'items' => [
                            [
                                'title' => 'Automated Vendor Commission Splitting and Escrow',
                                'memory_type' => 'technical_architecture',
                                'concept' => 'In multi-vendor marketplaces, an order containing items from multiple sellers splits payment into escrow accounts. The marketplace deducts its commission (e.g. 10%) and releases the net payout to the vendor only after the return window (e.g. 7 days) expires.',
                                'rules' => [
                                    'Vendor payouts must never be released immediately upon payment capture; hold in escrow until return window closes.',
                                    'Each vendor must maintain separate shipping policies, ratings, and inventory records.',
                                ],
                                'workflows' => [
                                    'Single Customer Checkout -> Payment Captured -> Split into Vendor Escrow Accounts -> Deduct Marketplace Platform Fee -> Hold 7 days -> Disburse via bank transfer.',
                                ],
                                'implementation_patterns' => 'Payment models record provider, amount, and gateway order IDs supporting multi-party reconciliation.',
                                'examples' => 'Customer buys ₹1,000 spices from two farm partners; platform retains ₹100 commission, holds ₹900 until delivery is verified.',
                                'common_problems' => 'Disbursing vendor payouts instantly, leaving the marketplace liable for customer returns and chargebacks.',
                                'confidence' => 0.97,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
