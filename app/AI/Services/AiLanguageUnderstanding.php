<?php

namespace App\AI\Services;

use App\AI\Contracts\AiLanguageUnderstandingInterface;
use App\AI\Core\AiParsedIntent;

class AiLanguageUnderstanding implements AiLanguageUnderstandingInterface
{
    private const SYNONYMS = [
        'Kashmiri Red Chilli' => [
            'marchu', 'marcha', 'mirch', 'chilli', 'chili', 'lal mirch', 'red chilli',
            'kashmiri chilli', 'kashmiri mirch', 'લાલ મરચું', 'મરચું', 'મરચાં', 'લાલ મરચાં',
            'लाल मिर्च', 'मिर्च', 'लाल मिर्ची', 'કશ્મીરી મરચું',
        ],
        'Organic Turmeric' => [
            'haldi', 'halder', 'turmeric', 'haldar', 'pili haldi', 'golden turmeric',
            'હળદર', 'પીળી હળદર', 'हल्दी', 'हल्दी पाउडर', 'ટર્મરિક',
        ],
        'Green Cardamom' => [
            'elaichi', 'elachi', 'cardamom', 'hari elaichi', 'nani elachi',
            'ચોખી એલચી', 'એલચી', 'નાની એલચી', 'इलायची', 'हरी इलायची', 'छोटी इलायची',
        ],
        'Kashmiri Saffron' => [
            'kesar', 'saffron', 'zafran', 'kashmir kesar',
            'કેસર', 'કાશ્મીરી કેસર', 'केसर', 'कश्मीरी केसर', 'ज़ाफ़रान',
        ],
        'Royal Garam Masala' => [
            'garam masala', 'hot spices', 'spicy mix', 'royal garam masala',
            'ગરમ મસાલો', 'ગરમ મસાલા', 'ગરમ મસાલા પાવડર', 'गरम मसाला', 'शाही गरम मसाला',
        ],
        'Coriander Powder' => [
            'dhaniya', 'coriander', 'dhana', 'dhania', 'dhaniya powder',
            'ધાણા', 'ધાણાજીરું', 'ધાણા પાવડર', 'धनिया', 'धनिया पाउडर',
        ],
        'Cumin Seed Select' => [
            'jeera', 'jira', 'cumin', 'cumin seeds', 'zeera',
            'જીરું', 'જીરા', 'જીરુ', 'जीरा', 'साबुत जीरा',
        ],
        'Kitchen King Mix' => [
            'kitchen king', 'sabzi masala', 'kitchen king mix',
            'શાક નો મસાલો', 'સબ્જી મસાલો', 'सब्जी मसाला', 'किचन किंग',
        ],
        'Tandoori Marinade Masala' => [
            'tandoori', 'tandoori masala', 'marinade',
            'તંદૂરી મસાલો', 'તંદૂરી', 'तंदूरी मसाला', 'तंदूरी',
        ],
        'Chaat Masala Zing' => [
            'chaat masala', 'chat masala', 'chaat',
            'ચાટ મસાલો', 'ચાટ', 'चाટ मसाला', 'चाટ',
        ],
    ];

    private const CATEGORY_KEYWORDS = [
        'Whole Spices' => ['whole spices', 'akak masala', 'sabut masala', 'akho masala', 'આખા મસાલા', 'साबुत मसाले'],
        'Ground Spices' => ['ground spices', 'pisa masala', 'powder', 'powdered spices', 'દળેલા મસાલા', 'પાવડર', 'पिसे मसाले'],
        'Blended Spices' => ['blended spices', 'mix masala', 'combo', 'signature blend', 'મિક્સ મસાલા', 'मिश्रित मसाले'],
        'Pure spice' => ['pure spice', 'pure spices', 'single spice', 'શુદ્ધ મસાલા', 'शुद्ध मसाले'],
    ];

    public function getSupportedLanguages(): array
    {
        return ['en', 'hi', 'gu', 'hinglish', 'gujenglish'];
    }

    public function detectLanguage(string $query): string
    {
        $text = trim($query);

        // 1. Gujarati Script Check
        if (preg_match('/\p{Gujarati}/u', $text) === 1) {
            return 'gu';
        }

        // 2. Hindi / Devanagari Script Check
        if (preg_match('/\p{Devanagari}/u', $text) === 1) {
            return 'hi';
        }

        $lower = mb_strtolower($text, 'UTF-8');

        // 3. GujEnglish Lexicon Markers
        $gujEnglishMarkers = [
            'kem cho', 'kem chho', 'joiye', 'apso', 'aapo', 'tamari', 'tamare', 'vado', 'marchu', 'marcha',
            'halder', 'kesar', 'su', 'saru', 'che', 'ketla', 'ketla nu', 'batao', 'aapsu',
            'amne', 'malse', 'karo', 'farak', 'antar', 'bhav', 'shuddh', 'jova', 'rit',
        ];
        foreach ($gujEnglishMarkers as $marker) {
            if (str_contains($lower, $marker)) {
                return 'gujenglish';
            }
        }

        // 4. Hinglish Lexicon Markers
        $hinglishMarkers = [
            'chahiye', 'kaunsa', 'accha', 'acche', 'bhai', 'kya', 'milega', 'daam',
            'dam', 'dikhao', 'mujhe', 'paisa', 'le', 'hai', 'kitne ka', 'bataiye',
            'batao', 'farak', 'bataye', 'kaisa', 'kaise', 'bata do', 'dikhaye', 'chahiye',
        ];
        foreach ($hinglishMarkers as $marker) {
            if (str_contains($lower, $marker)) {
                return 'hinglish';
            }
        }

        return 'en';
    }

    public function understand(string $query, array $context = []): AiParsedIntent
    {
        $language = $this->detectLanguage($query);
        $normalized = $this->normalizeText($query);

        $intent = $this->extractIntent($normalized, $language);
        $entities = $this->extractEntities($normalized);

        // If multiple comparison products found, ensure intent is comparison
        if (count($entities['compare_products'] ?? []) >= 2 && ! in_array($intent, ['order_tracking', 'cart_query'], true)) {
            $intent = 'product_comparison';
        }

        $confidence = $this->calculateConfidence($intent, $entities);

        return new AiParsedIntent(
            intent: $intent,
            language: $language,
            entities: $entities,
            confidence: $confidence,
            originalQuery: $query,
            normalizedQuery: $normalized
        );
    }

    public function formatResponse(string $intent, string $language, array $data = []): string
    {
        $productName = $data['product_name'] ?? ($data['name'] ?? 'spices');
        $price = $data['price'] ?? null;
        $unit = $data['unit'] ?? '100 g';
        $stockStatus = $data['stock_status'] ?? 'in stock';
        $stockQuantity = $data['stock_quantity'] ?? 0;
        $compareA = $data['compare_a'] ?? 'Product 1';
        $compareB = $data['compare_b'] ?? 'Product 2';
        $compareText = $data['compare_text'] ?? '';
        $usageInstructions = $data['usage_instructions'] ?? 'Add as required to recipes.';
        $orderNumber = $data['order_number'] ?? '';
        $orderStatus = $data['order_status'] ?? 'processing';
        $isGuest = (bool) ($data['is_guest'] ?? false);
        $found = (bool) ($data['found'] ?? true);

        return match ($language) {
            'gu' => match ($intent) {
                'greeting' => 'નમસ્તે! હું FlavourFlow નો AI શોપિંગ સહાયક છું. હું તમને મસાલા શોધવામાં, ભાવ જાણવામાં, સરખામણી કરવામાં કે ઓર્ડર ટ્રેકિંગમાં કેવી રીતે મદદ કરી શકું?',
                'product_search' => $found
                    ? "અહીં તમારા માટે શુદ્ધ {$productName} ના વિકલ્પો છે."
                    : "માફ કરશો, '{$productName}' હાલમાં અમારા કૅટેલોગમાં ઉપલબ્ધ નથી. પરંતુ તમે અમારા સૌથી લોકપ્રિય શુદ્ધ મસાલા નીચે જોઈ શકો છો:",
                'price_query' => $found && $price !== null
                    ? "{$productName} ({$unit}) ની વાસ્તવિક કિંમત ₹{$price} છે અને હાલમાં {$stockStatus} છે."
                    : ($found ? "{$productName} નો ભાવ અને વિગતો નીચે કાર્ડમાં ઉપલબ્ધ છે." : 'માફ કરશો, આ પ્રોડક્ટનો ભાવ હાલમાં ઉપલબ્ધ નથી.'),
                'stock_query' => $stockQuantity > 0
                    ? ($price !== null ? "હા, {$productName} ({$unit}) હાલમાં સ્ટોકમાં ઉપલબ્ધ છે ({$stockQuantity} પેક ઉપલબ્ધ છે) અને તેની કિંમત ₹{$price} છે." : "હા, {$productName} હાલમાં સ્ટોકમાં ઉપલબ્ધ છે ({$stockQuantity} પેક ઉપલબ્ધ છે).")
                    : "માફ કરશો, {$productName} હાલમાં આઉટ ઓફ સ્ટોક છે.",
                'product_comparison' => "અહીં {$compareA} અને {$compareB} ની વાસ્તવિક સરખામણી છે:\n{$compareText}",
                'cooking_usage' => "{$productName} વાપરવાની રીત: {$usageInstructions}",
                'recommendation' => 'અમારા ગ્રાહકો દ્વારા સૌથી વધુ પસંદ કરાયેલા શુદ્ધ અને તાજા મસાલા અહીં છે.',
                'order_tracking' => $isGuest
                    ? 'તમારા ખાનગી ઓર્ડરની માહિતી સુરક્ષિત છે. તમારો ઓર્ડર ટ્રેક કરવા માટે કૃપા કરીને તમારા અકાઉન્ટમાં લૉગ ઇન કરો.'
                    : ($orderNumber !== '' ? "તમારો તાજેતરનો ઓર્ડર #{$orderNumber} હાલમાં '{$orderStatus}' સ્થિતિમાં છે." : 'તમારા અકાઉન્ટમાં કોઈ સક્રિય ઓર્ડર મળ્યો નથી.'),
                'cart_query' => 'તમારી શોપિંગ કાર્ટમાં રહેલી વસ્તુઓ જોવા માટે કાર્ટ પેજ ખોલો.',
                default => 'FlavourFlow ના શુદ્ધ અને અધિકૃત મસાલા કલેક્શનમાંથી પસંદ કરો.',
            },
            'gujenglish' => match ($intent) {
                'greeting' => 'Kem cho! Hu FlavourFlow no AI shopping assistant chu. Hu tamne spices shodhma, price, comparison, ane order tracking ma madad kari saku chu.',
                'product_search' => $found
                    ? "Tamar mate shuddh {$productName} options ahiya che."
                    : "Sorry, '{$productName}' halma FlavourFlow par available nathi. Tame amara authentic spices collection explore kari sako cho:",
                'price_query' => $found && $price !== null
                    ? "{$productName} ({$unit}) no price ₹{$price} che ane currently {$stockStatus} che."
                    : ($found ? "{$productName} no price ane details niche card ma joi sako cho." : 'Sorry, aa product no price available nathi.'),
                'stock_query' => $stockQuantity > 0
                    ? ($price !== null ? "Ha, {$productName} ({$unit}) halma stock ma available che ({$stockQuantity} packs baki che) ane price ₹{$price} che." : "Ha, {$productName} halma stock ma available che ({$stockQuantity} packs baki che).")
                    : "Sorry, {$productName} halma out of stock che.",
                'product_comparison' => "Ahiya {$compareA} ane {$compareB} no real comparison che:\n{$compareText}",
                'cooking_usage' => "{$productName} use karvani rit: {$usageInstructions}",
                'recommendation' => 'FlavourFlow ma sauthi saru ane top rated spice collection ahiya che.',
                'order_tracking' => $isGuest
                    ? 'Tamar private order details protected che. Order status jova mate please account ma Log In karo.'
                    : ($orderNumber !== '' ? "Tamar latest order #{$orderNumber} currently '{$orderStatus}' state ma che." : 'Tamara account ma koi active order malyo nathi.'),
                'cart_query' => 'Tamar cart ma items ready che, checkout mate cart page visit karo.',
                default => 'Tamari recipe mate pure & authentic spices available che.',
            },
            'hi' => match ($intent) {
                'greeting' => 'नमस्ते! मैं FlavourFlow का AI शॉपिंग असिस्टेंट हूँ। मैं मसालों की खोज, दाम, तुलना या ऑर्डर ट्रैकिंग में आपकी सहायता कर सकता हूँ।',
                'product_search' => $found
                    ? "यहाँ आपके लिए शुद्ध {$productName} के बेहतरीन विकल्प हैं।"
                    : "क्षमा करें, '{$productName}' अभी हमारे कैटलॉग में उपलब्ध नहीं है। आप हमारे सबसे लोकप्रिय मसाले नीचे देख सकते हैं:",
                'price_query' => $found && $price !== null
                    ? "{$productName} ({$unit}) की वास्तविक कीमत ₹{$price} है और यह अभी {$stockStatus} है।"
                    : ($found ? "{$productName} का मूल्य और विवरण नीचे कार्ड में उपलब्ध है।" : 'क्षमा करें, इस उत्पाद की कीमत उपलब्ध नहीं है।'),
                'stock_query' => $stockQuantity > 0
                    ? ($price !== null ? "हाँ, {$productName} ({$unit}) अभी स्टॉक में उपलब्ध है ({$stockQuantity} पैकेट उपलब्ध हैं) और इसकी कीमत ₹{$price} है।" : "हाँ, {$productName} अभी स्टॉक में उपलब्ध है ({$stockQuantity} पैकेट उपलब्ध हैं)।")
                    : "क्षमा करें, {$productName} अभी आउट ऑफ स्टॉक है।",
                'product_comparison' => "यहाँ {$compareA} और {$compareB} की वास्तविक तुलना है:\n{$compareText}",
                'cooking_usage' => "{$productName} का उपयोग: {$usageInstructions}",
                'recommendation' => 'हमारे सबसे लोकप्रिय और ताज़ा मसालों की सूची यहाँ है।',
                'order_tracking' => $isGuest
                    ? 'आपकी निजी ऑर्डर जानकारी सुरक्षित है। अपने ऑर्डर का स्टेटस देखने के लिए कृपया लॉग इन करें।'
                    : ($orderNumber !== '' ? "आपका नवीनतम ऑर्डर #{$orderNumber} अभी '{$orderStatus}' स्थिति में है।" : 'आपके खाते में कोई सक्रिय ऑर्डर नहीं मिला।'),
                'cart_query' => 'आपकी कार्ट में सामान सुरक्षित है। कार्ट पेज पर जाकर चेकआउट करें।',
                default => 'आपकी रसोई के लिए बेहतरीन और शुद्ध मसाले उपलब्ध हैं।',
            },
            'hinglish' => match ($intent) {
                'greeting' => 'Namaste! Main FlavourFlow ka AI shopping assistant hoon. Spices search, price check, product comparison ya order tracking ke liye puchiye!',
                'product_search' => $found
                    ? "Ye rahe aapke liye shuddh {$productName} ke options."
                    : "Sorry, '{$productName}' abhi catalog me available nahi hai. Aap hamare best spices dekh sakte hain:",
                'price_query' => $found && $price !== null
                    ? "{$productName} ({$unit}) ka price ₹{$price} hai aur abhi {$stockStatus} hai."
                    : ($found ? "{$productName} ka price aur details niche card me check karein." : 'Sorry, is product ka price available nahi hai.'),
                'stock_query' => $stockQuantity > 0
                    ? ($price !== null ? "Haan, {$productName} ({$unit}) abhi stock me available hai ({$stockQuantity} packs left) aur price ₹{$price} hai." : "Haan, {$productName} abhi stock me available hai ({$stockQuantity} packs left).")
                    : "Sorry, {$productName} abhi out of stock hai.",
                'product_comparison' => "Ye raha {$compareA} aur {$compareB} ka live comparison:\n{$compareText}",
                'cooking_usage' => "{$productName} use karne ka tarika: {$usageInstructions}",
                'recommendation' => 'FlavourFlow ke sabse best and top quality spice collection ye rahe.',
                'order_tracking' => $isGuest
                    ? 'Aapki private order details protected hain. Order track karne ke liye please account me Log In karein.'
                    : ($orderNumber !== '' ? "Aapka latest order #{$orderNumber} abhi '{$orderStatus}' state me hai." : 'Aapke account me koi active order nahi mila.'),
                'cart_query' => 'Aapke cart me items ready hain. Cart page par jakar checkout karein.',
                default => 'Aapki recipe ke liye best organic spices available hain.',
            },
            default => match ($intent) {
                'greeting' => 'Hello! I am your FlavourFlow AI shopping assistant. Ask me about our spices, pricing, comparisons, recommendations, or order tracking!',
                'product_search' => $found
                    ? "Here are pure {$productName} options for you."
                    : "We currently don't have '{$productName}' in our spice catalogue. You can explore our authentic signature spices below:",
                'price_query' => $found && $price !== null
                    ? "{$productName} ({$unit}) is priced at ₹{$price} and is currently {$stockStatus}."
                    : ($found ? "Price and details for {$productName} are displayed below." : 'Sorry, price information for this product is not available.'),
                'stock_query' => $stockQuantity > 0
                    ? ($price !== null ? "Yes, {$productName} ({$unit}) is in stock with {$stockQuantity} units available at ₹{$price}." : "Yes, {$productName} is in stock with {$stockQuantity} units available.")
                    : "{$productName} is currently out of stock.",
                'product_comparison' => "Here is a side-by-side comparison between {$compareA} and {$compareB}:\n{$compareText}",
                'cooking_usage' => "Usage for {$productName}: {$usageInstructions}",
                'recommendation' => 'Here is our top recommended spice selection based on customer favourites.',
                'order_tracking' => $isGuest
                    ? 'Your private order information is protected. Please log in to your account to view and track your orders.'
                    : ($orderNumber !== '' ? "Your latest order #{$orderNumber} is currently '{$orderStatus}'." : 'No active orders found for your account.'),
                'cart_query' => 'Your shopping cart is ready for review and checkout.',
                default => 'Explore our authentic spice collection at FlavourFlow.',
            },
        };
    }

    private function normalizeText(string $text): string
    {
        $lower = mb_strtolower(trim($text), 'UTF-8');

        // Clean out extra punctuation while keeping unicode letters, marks (matras), numbers, and currency symbols
        return preg_replace('/[^\p{L}\p{M}\p{N}\s₹]/u', ' ', $lower) ?? $lower;
    }

    private function extractIntent(string $text, string $language): string
    {
        // 1. Comparison Intent
        if ($this->matchesKeywords($text, ['compare', 'difference', 'vs', 'versus', 'farak', 'farq', 'antar', 'સરખામણી', 'તફાવત', 'તુલના', 'अंतर', 'કયો સારો'])) {
            return 'product_comparison';
        }

        // 2. Price Intent
        if ($this->matchesKeywords($text, [
            'price', 'cost', 'rate', 'how much', 'bhav', 'dam', 'daam', 'kitne ka', 'ketla nu', 'ketlo',
            'ભાવ', 'કિંમત', 'રૂપિયા', 'દર', 'કિમત', 'કેટલા', 'કેટલું',
            'दाम', 'भाव', 'कीमत', 'मूल्य', 'रुपये', 'दर', 'कितने का', 'कितना', 'कितने',
        ])) {
            return 'price_query';
        }

        // 3. Stock / Availability Intent
        if ($this->matchesKeywords($text, [
            'stock', 'available', 'availability', 'in stock', 'hazir',
            'ઉપલબ્ધ', 'સ્ટોક', 'સ્ટોકમાં', 'સ્ટોક માં', 'મળશે', 'હાજર',
            'उपलब्ध', 'स्टॉक', 'स्टॉक में', 'मिलेगा', 'हाजिर',
        ])) {
            return 'stock_query';
        }

        // 4. Order Tracking Intent
        if ($this->matchesKeywords($text, ['track', 'order', 'status', 'delivery', 'where is', 'pohchyo', 'kaha h', 'ક્યાં છે', 'ટ્રેકિંગ', 'મારો ઓર્ડર', 'मेरा ऑर्डर', 'ऑर्डर'])) {
            return 'order_tracking';
        }

        // 5. Cart / Checkout Intent
        if ($this->matchesKeywords($text, ['cart', 'checkout', 'buy', 'kharidna', 'kharidvu', 'ખરીદવું', 'કાર્ટ', 'खरीदना'])) {
            return 'cart_query';
        }

        // 6. Cooking / Usage Intent
        if ($this->matchesKeywords($text, ['how to use', 'recipe', 'usage', 'ingredients', 'banavvu', 'kaise use', 'વાપરવું', 'બનાવવું', 'उपयोग', 'કેવી રીતે'])) {
            return 'cooking_usage';
        }

        // 7. Greeting Intent
        if ($this->matchesKeywords($text, ['hi', 'hello', 'hey', 'kem cho', 'namaste', 'kem chho', 'pranam', 'halo', 'કેમ છો', 'નમસ્તે', 'नमस्ते', 'प्रणाम'])) {
            return 'greeting';
        }

        // 8. Recommendation Intent
        if ($this->matchesKeywords($text, ['recommend', 'suggest', 'best', 'saru', 'sabse accha', 'top', 'popular', 'સૌથી સારું', 'શ્રેષ્ઠ', 'સુઝાવ', 'सबसे अच्छा', 'सुझाव'])) {
            return 'recommendation';
        }

        // 9. Category Explore
        if ($this->matchesKeywords($text, ['category', 'whole', 'ground', 'masala', 'spices', 'મસાલા', 'મસાલો', 'मसाले'])) {
            return 'category_explore';
        }

        // 10. Specific Product Search
        foreach (self::SYNONYMS as $name => $aliases) {
            if ($this->matchesKeywords($text, $aliases)) {
                return 'product_search';
            }
        }

        return 'general';
    }

    /**
     * @return array{category: ?string, product_name: ?string, compare_products: list<string>, max_budget: ?float, keywords: list<string>, quantity: ?int}
     */
    private function extractEntities(string $text): array
    {
        $detectedProduct = null;
        $detectedCategory = null;
        $maxBudget = null;
        $matchedProducts = [];

        // 1. Detect All Matched Products for Comparison or Search
        foreach (self::SYNONYMS as $productName => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($text, $alias)) {
                    if (! in_array($productName, $matchedProducts, true)) {
                        $matchedProducts[] = $productName;
                    }
                    break;
                }
            }
        }

        if (! empty($matchedProducts)) {
            $detectedProduct = $matchedProducts[0];
        }

        // 2. Detect Category
        foreach (self::CATEGORY_KEYWORDS as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $detectedCategory = $catName;
                    break 2;
                }
            }
        }

        // 3. Detect Budget (e.g. "under 500", "500 rs", "500 nu", "500 tak", "₹300")
        if (preg_match('/(?:under|below|less than|tak|nu|rs|rupees|₹|\$)\s*(\d+)/i', $text, $matches) === 1) {
            $maxBudget = (float) $matches[1];
        } elseif (preg_match('/(\d+)\s*(?:rs|rupees|nu|tak)/i', $text, $matches) === 1) {
            $maxBudget = (float) $matches[1];
        }

        // 4. Extract Keywords
        $words = array_values(array_filter(explode(' ', $text), fn ($w) => mb_strlen($w) > 2));

        return [
            'category' => $detectedCategory,
            'product_name' => $detectedProduct,
            'compare_products' => $matchedProducts,
            'max_budget' => $maxBudget,
            'keywords' => $words,
            'quantity' => 1,
        ];
    }

    private function matchesKeywords(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function calculateConfidence(string $intent, array $entities): float
    {
        if ($intent !== 'general' && ($entities['product_name'] !== null || $entities['category'] !== null)) {
            return 0.95;
        }

        if ($intent !== 'general') {
            return 0.80;
        }

        if (! empty($entities['keywords'])) {
            return 0.60;
        }

        return 0.40;
    }
}
