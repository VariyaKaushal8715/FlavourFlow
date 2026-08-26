<?php

namespace App\AI\Services;

use App\AI\Contracts\AiLanguageUnderstandingInterface;
use App\AI\Core\AiParsedIntent;

class AiLanguageUnderstanding implements AiLanguageUnderstandingInterface
{
    private const SYNONYMS = [
        'Kashmiri Red Chilli' => ['marchu', 'marcha', 'mirch', 'chilli', 'chili', 'lal mirch', 'red chilli'],
        'Organic Turmeric' => ['haldi', 'halder', 'turmeric', 'haldar', 'pili haldi'],
        'Green Cardamom' => ['elaichi', 'elachi', 'cardamom', 'hari elaichi', 'nani elachi'],
        'Kashmiri Saffron' => ['kesar', 'saffron', 'zafran', 'kashmir kesar'],
        'Garam Masala' => ['garam masala', 'hot spices', 'spicy mix'],
        'Coriander Powder' => ['dhaniya', 'coriander', 'dhana', 'dhania'],
    ];

    private const CATEGORY_KEYWORDS = [
        'Whole Spices' => ['whole spices', 'akak masala', 'sabut masala', 'akho masala'],
        'Ground Spices' => ['ground spices', 'pisa masala', 'powder', 'powdered spices'],
        'Blended Spices' => ['blended spices', 'mix masala', 'combo'],
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

        $lower = strtolower($text);

        // 3. GujEnglish Lexicon Markers
        $gujEnglishMarkers = [
            'kem cho', 'joiye', 'apso', 'aapo', 'tamari', 'vado', 'marchu', 'marcha',
            'halder', 'kesar', 'su', 'saru', 'che', 'ketla', 'ketla nu', 'batao', 'aapsu',
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
        $productName = $data['product_name'] ?? ($data['name'] ?? 'product');

        return match ($language) {
            'gu' => match ($intent) {
                'product_search' => "અહીં તમારા માટે શુદ્ધ {$productName} વિકલ્પો છે.",
                'recommendation' => 'અમારા ગ્રાહકો દ્વારા સૌથી વધુ પસંદ કરાયેલા મસાલા અહીં છે.',
                'cart_query' => 'તમારા કાર્ટમાં સાચવેલી વસ્તુઓ જોવા મળે છે.',
                'order_tracking' => 'તમારા ઓર્ડરનું સ્ટેટસ અકાઉન્ટ પેજ પર ઉપલબ્ધ છે.',
                default => 'તમારી પસંદગી મુજબ અહીં મસાલા શ્રેણી ઉપલબ્ધ છે.',
            },
            'gujenglish' => match ($intent) {
                'product_search' => "Tamar mate shuddh {$productName} options ahiya che.",
                'recommendation' => 'FlavourFlow ma sauthi saru masala collection ahiya che.',
                'cart_query' => 'Tamar cart ma items ready che.',
                'order_tracking' => 'Tamar order nu tracking status account ma joi sako cho.',
                default => 'Tamari recipe mate shuddh spices available che.',
            },
            'hi' => match ($intent) {
                'product_search' => "यहाँ आपके लिए शुद्ध {$productName} के बेहतरीन विकल्प हैं।",
                'recommendation' => 'हमारे सबसे लोकप्रिय और ताज़ा मसालों की सूची यहाँ है।',
                'cart_query' => 'आपकी कार्ट में सामान सुरक्षित है।',
                'order_tracking' => 'अपने ऑर्डर का ट्रैकिंग स्टेटस अकाउंट में चेक करें।',
                default => 'आपकी रसोई के लिए बेहतरीन मसाले उपलब्ध हैं।',
            },
            'hinglish' => match ($intent) {
                'product_search' => "Ye rahe aapke liye shuddh {$productName} ke options.",
                'recommendation' => 'FlavourFlow ke sabse best and top quality masala collection ye rahe.',
                'cart_query' => 'Aapke cart me items ready hain.',
                'order_tracking' => 'Aap apne order ka status Account section me track kar sakte hain.',
                default => 'Aapki recipe ke liye best organic spices available hain.',
            },
            default => match ($intent) {
                'product_search' => "Here are pure {$productName} options for you.",
                'recommendation' => 'Here is our top recommended spice selection based on your preferences.',
                'cart_query' => 'Your cart items are ready for checkout.',
                'order_tracking' => 'Track your active delivery status in your Account panel.',
                default => 'Explore our organic spice collection at FlavourFlow.',
            },
        };
    }

    private function normalizeText(string $text): string
    {
        $lower = mb_strtolower(trim($text), 'UTF-8');

        // Clean out extra punctuation
        return preg_replace('/[^\p{L}\p{N}\s₹]/u', ' ', $lower) ?? $lower;
    }

    private function extractIntent(string $text, string $language): string
    {
        if ($this->matchesKeywords($text, ['track', 'order', 'status', 'delivery', 'where is', 'pohchyo', 'kaha h', 'ક્યાં છે', 'ટ્રેકિંગ'])) {
            return 'order_tracking';
        }

        if ($this->matchesKeywords($text, ['cart', 'checkout', 'buy', 'kharidna', 'kharidvu', 'paisa', 'ખરીદવું', 'કાર્ટ'])) {
            return 'cart_query';
        }

        if ($this->matchesKeywords($text, ['price', 'cost', 'rate', 'bhav', 'kitne ka', 'ketla nu', 'ભાવ', 'કિંમત'])) {
            return 'price_query';
        }

        if ($this->matchesKeywords($text, ['recommend', 'suggest', 'best', 'saru', 'sabse accha', 'top', 'સૌથી સારું', 'શ્રેષ્ઠ'])) {
            return 'recommendation';
        }

        if ($this->matchesKeywords($text, ['category', 'whole', 'ground', 'masala', 'spices', 'મસાલા'])) {
            return 'category_explore';
        }

        foreach (self::SYNONYMS as $name => $aliases) {
            if ($this->matchesKeywords($text, $aliases)) {
                return 'product_search';
            }
        }

        return 'general';
    }

    /**
     * @return array{category: ?string, product_name: ?string, max_budget: ?float, keywords: list<string>, quantity: ?int}
     */
    private function extractEntities(string $text): array
    {
        $detectedProduct = null;
        $detectedCategory = null;
        $maxBudget = null;

        // 1. Detect Product
        foreach (self::SYNONYMS as $productName => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($text, $alias)) {
                    $detectedProduct = $productName;
                    break 2;
                }
            }
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
