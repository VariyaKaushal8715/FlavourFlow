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

        // 3. GujEnglish Lexicon Markers (check word boundaries for single-token markers)
        $gujEnglishMarkers = [
            'kem cho', 'kem chho', 'joiye', 'apso', 'aapo', 'tamari', 'tamare', 'vado', 'marchu', 'marcha',
            'halder', 'kesar', 'su', 'saru', 'che', 'ketla', 'ketla nu', 'batao', 'aapsu',
            'amne', 'malse', 'karo', 'farak', 'antar', 'bhav', 'shuddh', 'jova', 'rit',
        ];
        foreach ($gujEnglishMarkers as $marker) {
            if (str_contains($marker, ' ')) {
                if (str_contains($lower, $marker)) {
                    return 'gujenglish';
                }
            } else {
                if (preg_match('/\b'.preg_quote($marker, '/').'\b/iu', $lower) === 1) {
                    return 'gujenglish';
                }
            }
        }

        // 4. Hinglish Lexicon Markers (check word boundaries for single-token markers)
        $hinglishMarkers = [
            'chahiye', 'kaunsa', 'accha', 'acche', 'bhai', 'kya', 'milega', 'daam',
            'dam', 'dikhao', 'mujhe', 'paisa', 'le', 'hai', 'kitne ka', 'bataiye',
            'batao', 'farak', 'bataye', 'kaisa', 'kaise', 'bata do', 'dikhaye', 'chahiye',
        ];
        foreach ($hinglishMarkers as $marker) {
            if (str_contains($marker, ' ')) {
                if (str_contains($lower, $marker)) {
                    return 'hinglish';
                }
            } else {
                if (preg_match('/\b'.preg_quote($marker, '/').'\b/iu', $lower) === 1) {
                    return 'hinglish';
                }
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
                'greeting' => 'નમસ્તે! હું FlavourFlow નો AI શોપિંગ સહાયક છું. હું તમને મસાલા શોધવામાં, ભાવ જાણવામાં, ચુકવણી પદ્ધતિઓ, રીટર્ન પોલિસી કે ઓર્ડર ટ્રેકિંગમાં કેવી રીતે મદદ કરી શકું?',
                'payment_methods' => "FlavourFlow પર સુરક્ષિત ચુકવણી (Payment) માટે નીચેના વિકલ્પો ઉપલબ્ધ છે:\n1. UPI QR કોડ અને Apps: Google Pay, PhonePe, Paytm, CRED કે BHIM વડે ડાયનેમિક QR કોડ સ્કેન કરીને ચૂકવો. ચુકવણી પછી તમારો 12-અંકનો UTR / Reference નંબર દાખલ કરી 'I Have Paid' ક્લિક કરો.\n2. કેશ ઓન ડિલિવરી (COD): તમારા ઘરે મસાલા પહોંચે ત્યારે રોકડ ચૂકવણી કરો.\n3. ઓનલાઇન પેમેન્ટ: Razorpay અને Cashfree દ્વારા ક્રેડિટ/ડેબિટ કાર્ડ, નેટબેન્કિંગ અને વોલેટ્સ વડે સલામત ચુકવણી.\nજો પેમેન્ટ કપાઈ ગયું હોય પરંતુ ઓર્ડર કન્ફર્મ ન થયો હોય, તો ચિંતા કરશો નહીં; અમારી સિસ્ટમ તેને વેરિફાય કરે છે અથવા 3-5 દિવસમાં આપોઆપ રિફંડ થઈ જાય છે.",
                'refund_returns' => "FlavourFlow ની ગ્રાહક-કેન્દ્રિત રીટર્ન અને રીફંડ પોલિસી:\n• 7 દિવસની રીટર્ન સુવિધા: ડિલિવરી મળ્યાના 7 દિવસમાં તમે સીલબંધ પેકેટ્સ અથવા ક્ષતિગ્રસ્ત/તૂટેલા માલ માટે રીટર્ન કે બદલી (Replacement) ની વિનંતી કરી શકો છો.\n• સરળ રીફંડ: વેરિફિકેશન પછી 3 થી 5 કામકાજી દિવસોમાં તમારા મૂળ ખાતામાં કે UPI માં પૈસા પાછા જમા થઈ જાય છે.\n• સંપર્ક: તમારા ઓર્ડર નંબર સાથે અમારી સપોર્ટ ટીમનો સંપર્ક કરો.",
                'order_help' => "FlavourFlow ઓર્ડર અને ઇન્વોઇસ માહિતી:\n• ઓર્ડર કરવાની રીત: મસાલા કાર્ટમાં ઉમેરો, ચેકઆઉટ પર જાઓ, સરનામું ભરો અને મનપસંદ પેમેન્ટ રીત પસંદ કરો.\n• GST ટેક્સ ઇન્વોઇસ: દરેક કન્ફર્મ થયેલા ઓર્ડર માટે સત્તાવાર PDF ઇન્વોઇસ બને છે. તમે 'My Account > Orders' માંથી અથવા કન્ફર્મેશન ઈમેઇલમાંથી PDF ડાઉનલોડ કરી શકો છો.\n• ઓર્ડર રદ કરવો: સામાન ડિસ્પેચ થાય તે પહેલાં તમે તમારા અકાઉન્ટમાંથી ઓર્ડર કેન્સલ કરી શકો છો.",
                'shipping_delivery' => "FlavourFlow શિપિંગ અને ડિલિવરી સમયમર્યાદા:\n• ડિલિવરી સમય: સમગ્ર ભારતમાં 2 થી 5 કામકાજી દિવસોમાં ડિલિવરી મળે છે (ગુજરાતમાં 1-2 દિવસમાં એક્સપ્રેસ ડિલિવરી).\n• ડિલિવરી ચાર્જ: ₹500 કે તેથી વધુના ઓર્ડર પર ડિલિવરી બિલકુલ FREE છે! ₹500 થી ઓછા ઓર્ડર પર ₹50 ફ્લેટ ચાર્જ છે.\n• ટ્રેકિંગ: ઓર્ડર ડિસ્પેચ થતાં જ તમને ટ્રેકિંગ લિંક ઈમેઇલ અને અકાઉન્ટમાં મળી જાય છે.",
                'store_contact' => "FlavourFlow સંપર્ક વિગતો:\n• ઈમેઈલ: support@flavourflow.test\n• ફોન / WhatsApp: +91 98765 43210\n• કામકાજના કલાકો: સોમવાર થી શનિવાર, સવારે 9:00 થી સાંજે 7:00 સુધી\n• સરનામું: FlavourFlow Spices, રીંગ રોડ, સુરત, ગુજરાત 395002\nતમે અમારા કોન્ટેક્ટ પેજ પરથી પણ સીધો સંદેશ મોકલી શકો છો.",
                'cooking_recipe' => "FlavourFlow ના રસોઈ અને મસાલા ઉપયોગના સુઝાવો:\n• રોજિંદો વઘાર (તડકો): તેલ કે ઘી ગરમ કરી જીરું તતડે એટલે ધીમા તાપે હળદર અને ધાણાજીરું ઉમેરો જેથી સુગંધ જળવાઈ રહે અને મસાલો બળે નહીં.\n• ગરમ મસાલો: શાક કે દાળ તૈયાર થવાના છેલ્લા 2 મિનિટમાં ગરમ મસાલો ઉમેરવો જેથી તેની મૂળ સોડમ અને સ્વાદ પૂરેપૂરો અનુભવાય.\n• એલચી અને કેસર: સવારની મસાલા ચા કે ખીર માટે તાજી એલચી વાટીને વાપરો; કેસરના તાંતણાને નવશેકા દૂધમાં 10 મિનિટ પલાળીને બિરયાની કે મીઠાઈમાં ઉમેરો.",
                'promotions_discounts' => "FlavourFlow ડિસ્કાઉન્ટ અને ઓફર્સ:\n• ફ્રી શિપિંગ: ₹500 કે તેથી વધુના તમામ ઓર્ડર પર 100% મફત ડિલિવરી મળે છે!\n• કૂપન કોડ્સ: ચેકઆઉટ પેજ પર 'WELCOME10' અથવા ઉપલબ્ધ ડિસ્કાઉન્ટ કૂપન દાખલ કરી તુરંત બચત મેળવો.\n• નિયમિત ફેસ્ટિવ ઓફર્સ: અમારા શુદ્ધ અને પ્રીમિયમ મસાલા કોમ્બો પેક પર વિશેષ ડિસ્કાઉન્ટ ઉપલબ્ધ છે.",
                'spice_quality' => "FlavourFlow શુદ્ધતા અને ગુણવત્તા ગેરંટી:\n• 100% શુદ્ધ અને કુદરતી: અમારા મસાલા સીધા ખેતરોમાંથી મેળવીને પરંપરાગત રીતે સાફ અને દળવામાં આવે છે.\n• કોઈ ભેળસેળ કે કેમિકલ નહીં: તેમાં કોઈપણ કૃત્રિમ રંગ, પ્રિઝર્વેટિવ્સ કે ફિલર્સ ઉમેરવામાં આવતા નથી.\n• તાજી સોડમ: કુદરતી તેલ અને સુગંધ સંપૂર્ણપણે જળવાઈ રહે તેવું હર્મેટિક પેકિંગ.",
                'general_help' => "નમસ્તે! હું FlavourFlow સ્માર્ટ શોપિંગ સહાયક છું. હું તમને આ બાબતોમાં મદદ કરી શકું છું:\n• 🌿 શુદ્ધ મસાલા શોધવા, ભાવ જાણવા અને ભલામણો મેળવવા\n• 📦 ઓર્ડર ટ્રેક કરવા અને અગાઉના ઓર્ડર્સ જોવા\n• 💳 પેમેન્ટ વિકલ્પો (UPI QR, Cards, COD)\n• 🚚 ડિલિવરી સમય અને શિપિંગ ચાર્જની વિગતો\n• 📄 GST ટેક્સ ઇન્વોઇસ અને રીટર્ન પોલિસી\n• 👨‍🍳 રસોઈ ટિપ્સ અને રેસિપી\nતમે મને આમાંથી કોઈપણ પ્રશ્ન પૂછી શકો છો!",
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
                    ? 'તમારા ખાનગી ઓર્ડરની માહિતી સુરક્ષિત (protected) છે. તમારો ઓર્ડર ટ્રેક કરવા માટે કૃપા કરીને તમારા અકાઉન્ટમાં સાઇન ઇન (sign in) કરો.'
                    : ($orderNumber !== '' ? "તમારો તાજેતરનો ઓર્ડર #{$orderNumber} હાલમાં '{$orderStatus}' સ્થિતિમાં છે." : 'તમારા અકાઉન્ટમાં કોઈ સક્રિય ઓર્ડર મળ્યો નથી.'),
                'delivery_charges' => 'રૂ. 500 કે તેથી વધુના તમામ ઓર્ડર પર ડિલિવરી એકદમ FREE છે! રૂ. 500 થી ઓછા ઓર્ડર પર માત્ર રૂ. 50 ફ્લેટ ડિલિવરી ચાર્જ લાગુ થાય છે.',
                'cart_query' => 'તમારી શોપિંગ કાર્ટમાં રહેલી વસ્તુઓ જોવા માટે કાર્ટ પેજ ખોલો.',
                default => 'FlavourFlow ના શુદ્ધ અને અધિકૃત મસાલા કલેક્શનમાંથી પસંદ કરો.',
            },
            'gujenglish' => match ($intent) {
                'greeting' => 'Kem cho! Hu FlavourFlow no AI shopping assistant chu. Hu tamne spices shodhma, price, payment options, return policy, ane order tracking ma madad kari saku chu.',
                'payment_methods' => "FlavourFlow par payment mate safe and convenient options available che:\n1. UPI QR Code & Apps: GPay, PhonePe, Paytm, CRED, ya BHIM thi QR code scan kari payment karo. Payment thaya pachhi 12-digit UTR/Ref number nakhi ne 'I Have Paid' button click karo.\n2. Cash on Delivery (COD): Tamara door par spices deliver thay tyare cash pay karo.\n3. Online Gateways: Razorpay ane Cashfree through Cards, NetBanking, and Wallets thi securely pay kari sako cho.\nJo payment deduct thai gayu hoy pan order confirm na thayo hoy to chimta na karo, unverified transactions 3-5 business days ma automatically refund thai jaay che.",
                'refund_returns' => "FlavourFlow Return & Refund Policy:\n• 7-Day Easy Returns: Delivery na 7 divas ma tamne unopened packs ya damaged/wrong items mate return ya replacement malse.\n• Fast Refund: Verification pachhi 3-5 business days ma tamara original payment method ya UPI ma refund jama thai jaay che.\n• Return kem karvu: Tamara Order Number sathe customer support team no contact karo.",
                'order_help' => "FlavourFlow Order & Invoice Details:\n• Order Placing: Spices cart ma add karo, checkout page par details fill kari payment complete karo.\n• PDF Tax Invoice: Har confirmed order mate authentic GST invoice generate thay che. Tame 'My Account > Orders' page thi ya email attachment thi invoice download kari sako cho.\n• Order Cancellation: Dispatch thaya pela tame tamara account mathi order cancel kari sako cho.",
                'shipping_delivery' => "FlavourFlow Shipping & Delivery Details:\n• Delivery Time: All India 2 to 5 business days lage che (Gujarat ma 1-2 days express delivery).\n• Shipping Charges: Rs. 500 ya vadhare na order par Delivery FREE che! Rs. 500 thi ocha order par Rs. 50 flat charge che.\n• Tracking: Dispatch thaya pachhi live tracking link email ane account ma available thai jaay che.",
                'store_contact' => "FlavourFlow Support Team sathe contact karvani details:\n• Support Email: support@flavourflow.test\n• Phone / WhatsApp: +91 98765 43210\n• Timings: Monday to Saturday, 9:00 AM to 7:00 PM\n• Address: FlavourFlow Spices, Ring Road, Surat, Gujarat 395002\nTame contact page par thi pan direct message send kari sako cho!",
                'cooking_recipe' => "FlavourFlow Cooking & Spices Tips:\n• Daily Tadka: Oil/ghee garam thay tyare jeera tadkavo, pachhi slow flame par haldi ane dhana jeeru add karo jethi masala bale nai ane natural taste aave.\n• Garam Masala: Shak ya dal ready thavana last 2 minutes ma Garam Masala nakho jethi eni aroma fresh rahe.\n• Elaichi & Kesar: Masala chai ma fresh crushed elaichi nakho, ane kheer ya biryani mate kesar ne thoda garam doodh ma 10 minute palali ne use karo.",
                'promotions_discounts' => "FlavourFlow Discounts & Special Offers:\n• Free Shipping: Rs. 500 ya vadhare na order par 100% Free Delivery malse!\n• Coupon Code: Checkout page par 'WELCOME10' ya tamari discount coupon enter kari ne discount melvi sako cho.\n• Pure spice combo packs par regular seasonal savings available che.",
                'spice_quality' => "FlavourFlow Purity & Quality Assurance:\n• 100% Pure & Farm Sourced: Amara spices directly origin farms thi aave che ane authentic clean processing thay che.\n• Zero Adulteration: Koi artificial color, preservative ya chemical use thatu nathi.\n• Fresh Aroma: Natural essential oils packed and preserved intact.",
                'general_help' => "Kem cho! Hu FlavourFlow Shopping Assistant chu. Hu tamne aa badhi vastuoma guide kari saku chu:\n• 🌿 Spices search karva, rates ane recommendations leva\n• 📦 Active order track karva ane past orders jova\n• 💳 Payment methods (UPI QR, Cards, COD)\n• 🚚 Delivery timeline ane Free Shipping details\n• 📄 GST invoice download ane Easy Returns\n• 👨‍🍳 Cooking tips ane recipes\nTame mane koi pan query puchhi sako cho!",
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
                    ? 'Tamar private order details protected che. Please sign in to your FlavourFlow account to check your order details.'
                    : ($orderNumber !== '' ? "Tamar latest order #{$orderNumber} currently '{$orderStatus}' state ma che." : 'Tamara account ma koi active order malyo nathi.'),
                'delivery_charges' => 'Delivery is FREE on all orders of Rs. 500 or above! For orders under Rs. 500, a flat delivery charge of Rs. 50 applies.',
                'cart_query' => 'Tamar cart ma items ready che, checkout mate cart page visit karo.',
                default => 'Tamari recipe mate pure & authentic spices available che.',
            },
            'hi' => match ($intent) {
                'greeting' => 'नमस्ते! मैं FlavourFlow का AI शॉपिंग असिस्टेंट हूँ। मैं मसालों की खोज, दाम, भुगतान के तरीके, रिटर्न पॉलिसी या ऑर्डर ट्रैकिंग में आपकी सहायता कर सकता हूँ।',
                'payment_methods' => "FlavourFlow पर भुगतान के लिए कई सुरक्षित विकल्प उपलब्ध हैं:\n1. UPI QR कोड और Apps: Google Pay, PhonePe, Paytm, CRED या BHIM से QR कोड स्कैन करके भुगतान करें। भुगतान के बाद 12-अंकीय UTR / Reference नंबर दर्ज कर 'I Have Paid' पर क्लिक करें।\n2. कैश ऑन डिलीवरी (COD): घर पर मसाले पहुंचने पर नकद भुगतान करें।\n3. ऑनलाइन पेमेंट: Razorpay और Cashfree के माध्यम से कार्ड्स, नेटबैंकिंग और वॉलेट्स द्वारा सुरक्षित भुगतान।\nयदि पैसे कट गए हों लेकिन ऑर्डर कन्फर्म न हुआ हो, तो घबराएं नहीं; हमारी प्रणाली सत्यापन करती है या 3-5 कार्य दिवसों में राशि वापस कर दी जाती है।",
                'refund_returns' => "FlavourFlow रिटर्न और रिफंड नीति:\n• 7 दिन की रिटर्न सुविधा: डिलीवरी के 7 दिनों के भीतर आप सीलबंद पैकेट या क्षतिग्रस्त/गलत सामान के लिए रिटर्न या रिप्लेसमेंट का अनुरोध कर सकते हैं।\n• त्वरित रिफंड: सत्यापन के बाद 3 से 5 कार्य दिवसों में मूल भुगतान खाते या UPI में पैसे वापस भेज दिए जाते हैं।\n• रिटर्न कैसे करें: अपने ऑर्डर नंबर के साथ हमारी सहायता टीम से संपर्क करें।",
                'order_help' => "FlavourFlow ऑर्डर और इनवॉयस जानकारी:\n• ऑर्डर कैसे करें: मसाले कार्ट में जोड़ें, चेकआउट पर जाएं, पता दर्ज करें और भुगतान चुनें।\n• GST टैक्स इनवॉयस: प्रत्येक कन्फर्म ऑर्डर के लिए आधिकारिक PDF इनवॉयस तैयार होता है। आप 'My Account > Orders' से या ईमेल से PDF डाउनलोड कर सकते हैं।\n• कैंसिलेशन: डिस्पैच होने से पहले आप अपने अकाउंट से ऑर्डर कैंसिल कर सकते हैं।",
                'shipping_delivery' => "FlavourFlow शिपिंग और डिलीवरी समय:\n• डिलीवरी समय: पूरे भारत में 2 से 5 कार्य दिवसों में डिलीवरी होती है (गुजरात में 1-2 दिनों में एक्सप्रेस डिलीवरी)।\n• डिलीवरी शुल्क: ₹500 या उससे अधिक के सभी ऑर्डर पर डिलीवरी बिल्कुल FREE है! ₹500 से कम के ऑर्डर पर ₹50 फ्लैट शुल्क लागू होता है।\n• ट्रैकिंग: सामान निकलने के बाद लाइव ट्रैकिंग लिंक ईमेल और अकाउंट में उपलब्ध हो जाती है।",
                'store_contact' => "FlavourFlow संपर्क विवरण:\n• ईमेल: support@flavourflow.test\n• फोन / WhatsApp: +91 98765 43210\n• समय: सोमवार से शनिवार, सुबह 9:00 से शाम 7:00 बजे तक\n• पता: FlavourFlow Spices, रिंग रोड, सूरत, गुजरात 395002\nआप हमारे संपर्क पृष्ठ से भी सीधे संदेश भेज सकते हैं।",
                'cooking_recipe' => "FlavourFlow के रसोई और मसाले उपयोग के सुझाव:\n• रोज़ाना तड़का: तेल या घी गरम करके जीरा चटकाएं, फिर धीमी आंच पर हल्दी और धनिया पाउडर डालें ताकि मसाले जलें नहीं और ताज़ा सुगंध बनी रहे।\n• गरम मसाला: सब्जी या दाल बनने के अंतिम 2 मिनट में गरम मसाला डालें ताकि उसकी महक और स्वाद बना रहे।\n• इलायची और केसर: चाय या खीर में ताज़ा कुटी इलायची डालें; केसर के धागों को गुनगुने दूध में 10 मिनट भिगोकर बिरयानी या मिठाइयों में उपयोग करें।",
                'promotions_discounts' => "FlavourFlow डिस्काउंट और ऑफर्स:\n• फ्री डिलीवरी: ₹500 या उससे अधिक के सभी ऑर्डर पर 100% मुफ्त डिलीवरी पाएं!\n• कूपन कोड: चेकआउट पेज पर 'WELCOME10' या उपलब्ध कूपन कोड दर्ज करके तुरंत छूट पाएं।\n• कॉम्बो पैक पर विशेष ऑफर और बचत उपलब्ध है।",
                'spice_quality' => "FlavourFlow शुद्धता और गुणवत्ता आश्वासन:\n• 100% शुद्ध और प्राकृतिक: हमारे मसाले सीधे खेतों से लाकर पारंपरिक रूप से तैयार किए जाते हैं।\n• शून्य मिलावट: कोई कृत्रिम रंग, प्रिजर्वेटिव या रसायन नहीं।\n• प्राकृतिक सुगंध और ताज़गी सुरक्षित।",
                'general_help' => "नमस्ते! मैं FlavourFlow शॉपिंग असिस्टेंट हूँ। मैं आपकी इन सभी चीज़ों में सहायता कर सकता हूँ:\n• 🌿 शुद्ध मसाले खोजना, कीमतें देखना और सुझाव पाना\n• 📦 ऑर्डर ट्रैक करना और पुराने ऑर्डर देखना\n• 💳 पेमेंट के तरीके (UPI QR, कार्ड्स, COD)\n• 🚚 डिलीवरी का समय और फ्री शिपिंग की जानकारी\n• 📄 GST इनवॉयस डाउनलोड और रिटर्न पॉलिसी\n• 👨‍🍳 रेसिपी और कुकिंग टिप्स\nआप मुझसे कोई भी सवाल पूछ सकते हैं!",
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
                    ? 'आपकी निजी ऑर्डर जानकारी सुरक्षित (protected) है। अपने ऑर्डर का स्टेटस देखने के लिए कृपया साइन इन (sign in) करें।'
                    : ($orderNumber !== '' ? "आपका नवीनतम ऑर्डर #{$orderNumber} अभी '{$orderStatus}' स्थिति में है।" : 'आपके खाते में कोई सक्रिय ऑर्डर नहीं मिला।'),
                'delivery_charges' => '₹500 या उससे अधिक के सभी ऑर्डर पर डिलीवरी बिल्कुल FREE है! ₹500 से कम के ऑर्डर पर ₹50 फ्लैट डिलीवरी चार्ज लागू होता है।',
                'cart_query' => 'आपकी कार्ट में सामान सुरक्षित है। कार्ट पेज पर जाकर चेकआउट करें।',
                default => 'आपकी रसोई के लिए बेहतरीन और शुद्ध मसाले उपलब्ध हैं।',
            },
            'hinglish' => match ($intent) {
                'greeting' => 'Namaste! Main FlavourFlow ka AI shopping assistant hoon. Spices search, price check, payment options, return policy ya order tracking ke liye puchiye!',
                'payment_methods' => "FlavourFlow par payment karne ke multiple secure options hain:\n1. UPI QR Code & Apps: GPay, PhonePe, Paytm, CRED ya BHIM se QR scan karein. Payment hone ke baad 12-digit UTR/Ref number daal kar 'I Have Paid' click karein.\n2. Cash on Delivery (COD): Ghar par delivery milne par cash se payment karein.\n3. Online Payment: Razorpay aur Cashfree ke through Cards, NetBanking aur Wallets se payment karein.\nAgar paise kat gaye hain aur order confirm nahi hua, to fikar na karein; 3-5 business days me refund account me wapas aa jata hai.",
                'refund_returns' => "FlavourFlow Return & Refund Policy:\n• 7 Days Return: Delivery ke 7 din ke andar aap unopened ya damaged product ke liye return ya replacement request kar sakte hain.\n• Quick Refund: Verification ke baad 3-5 business days me aapke original payment mode ya UPI me refund mil jata hai.\n• Kaise karein: Apne Order Number ke saath customer care team se contact karein.",
                'order_help' => "FlavourFlow Order & Invoicing Guide:\n• Order kaise karein: Spices ko cart me add karein, checkout par address fill karein aur payment select karein.\n• PDF Tax Invoice: Confirmed order ka official GST invoice auto-generate hota hai. Aap 'My Account > Orders' se ya email attachment se download kar sakte hain.\n• Cancellation: Dispatch hone se pehle aap account orders dashboard se cancel kar sakte hain.",
                'shipping_delivery' => "FlavourFlow Delivery & Shipping Info:\n• Delivery Time: All India 2 se 5 business days me deliver hota hai (Gujarat me 1-2 days express).\n• Delivery Charges: Rs. 500 ya usse jyada par Free Delivery hai! Rs. 500 se kam par flat Rs. 50 charge lagta hai.\n• Tracking: Dispatch hone ke baad tracking link email aur account me mil jati hai.",
                'store_contact' => "FlavourFlow Support Team se connect karne ke details:\n• Support Email: support@flavourflow.test\n• Phone / WhatsApp: +91 98765 43210\n• Timings: Monday to Saturday, 9:00 AM to 7:00 PM\n• Store Address: FlavourFlow Spices, Ring Road, Surat, Gujarat 395002\nAap contact page par jakar bhi directly hume message kar sakte hain!",
                'cooking_recipe' => "FlavourFlow Cooking & Spices Tips:\n• Daily Tadka: Oil/ghee me jeera crackle karein, fir slow flame par haldi aur dhaniya powder dalein taaki jalne se bachein.\n• Garam Masala: Sabzi ya dal pakne ke aakhri 2 minute me garam masala add karein taaki uski aroma bani rahe.\n• Elaichi & Kesar: Masala chai me fresh crushed elaichi dalein, aur desserts/biryani ke liye kesar ko halke garam doodh me 10 minute soak karke use karein.",
                'promotions_discounts' => "FlavourFlow Discounts & Special Deals:\n• Free Delivery: Rs. 500 ya usse jyada ke orders par 100% Free Shipping hai!\n• Coupon Codes: Checkout page par 'WELCOME10' ya coupon code daal kar instant discount paayein.\n• Best value spice combo packs par festive savings available hain.",
                'spice_quality' => "FlavourFlow Purity & Authenticity Promise:\n• 100% Pure & Farm-Fresh: Directly origin farms se sourced genuine quality spices.\n• Zero Adulteration: Koi artificial color, preservative ya chemical nahi.\n• Rich Natural Aroma: Retaining full essential oils and natural taste.",
                'general_help' => "Hello! Main FlavourFlow Shopping Assistant hoon. Main aapki in sabhi cheezon me madad kar sakta hoon:\n• 🌿 Spices search karna, prices dekhna aur recommendations lena\n• 📦 Active order track karna aur past orders check karna\n• 💳 Payment options (UPI QR, Cards, COD)\n• 🚚 Delivery timeline aur Free Delivery info\n• 📄 GST tax invoice download aur Return policies\n• 👨‍🍳 Cooking tips aur spice usage\nAap mujhse koi bhi question pooch sakte hain!",
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
                    ? 'Aapki private order details protected hain. Order track karne ke liye please account me sign in karein.'
                    : ($orderNumber !== '' ? "Aapka latest order #{$orderNumber} abhi '{$orderStatus}' state me hai." : 'Aapke account me koi active order nahi mila.'),
                'delivery_charges' => 'Delivery is FREE on all orders of Rs. 500 or above! For orders under Rs. 500, a flat delivery charge of Rs. 50 applies.',
                'cart_query' => 'Aapke cart me items ready hain. Cart page par jakar checkout karein.',
                default => 'Aapki recipe ke liye best organic spices available hain.',
            },
            default => match ($intent) {
                'greeting' => 'Hello! I am your FlavourFlow AI shopping assistant. Ask me about our spices, pricing, payment options, return policy, cooking tips, or order tracking!',
                'payment_methods' => "FlavourFlow provides several safe and secure payment options:\n1. UPI QR Code & Apps: Scan the dynamic QR code with Google Pay, PhonePe, Paytm, CRED, or BHIM. After paying, simply enter your 12-digit UTR / reference number and click 'I Have Paid' for verified confirmation.\n2. Cash on Delivery (COD): Pay cash at your doorstep when your spices arrive.\n3. Online Payment: Secure Credit/Debit Cards, NetBanking, and Wallets via Razorpay and Cashfree.\nIf payment fails or an amount was debited without order confirmation, rest assured your payment reference is securely logged and unconfirmed transactions are automatically refunded within 3-5 business days.",
                'refund_returns' => "FlavourFlow Customer-First Return Policy:\n• 7-Day Return Window: You can request a return or replacement within 7 days of delivery for any unopened products or items received in damaged/tampered condition.\n• Hassle-Free Refunds: Once verified, refunds are processed back to your original payment method or bank/UPI within 3-5 business days.\n• How to Request: Simply reach out to customer support with your Order Number and item photo.",
                'order_help' => "FlavourFlow Order & Invoicing Information:\n• Ordering: Add your chosen spices to cart, proceed to checkout, enter shipping address, and select your preferred payment mode.\n• PDF Tax Invoices: An official GST Tax Invoice is automatically generated for every confirmed order. You can download it anytime from 'My Account > Orders' or from your order confirmation email.\n• Cancellation: You can cancel an order before it has been shipped directly from your account orders dashboard.",
                'shipping_delivery' => "FlavourFlow Shipping & Delivery Timelines:\n• Delivery Duration: Standard delivery takes 2 to 5 business days across India (Express delivery within 1-2 days in Gujarat).\n• Delivery Charges: FREE shipping on all orders of ₹500 or more! A flat ₹50 shipping fee applies for orders below ₹500.\n• Tracking: Once dispatched, you will receive real-time tracking updates via email and in your FlavourFlow account.",
                'store_contact' => "We are always happy to help! Here is how you can contact FlavourFlow:\n• Support Email: support@flavourflow.test\n• Phone / WhatsApp: +91 98765 43210\n• Support Hours: Monday to Saturday, 9:00 AM – 7:00 PM IST\n• Address: FlavourFlow Spices, Ring Road, Surat, Gujarat 395002\nFeel free to ask any question or leave a message on our Contact page!",
                'cooking_recipe' => "Cooking & Spice Tips from FlavourFlow:\n• Daily Tempering (Tadka): Heat oil/ghee, add cumin seeds (jeera) until they splutter, then add turmeric and coriander powder on low flame to protect their natural aroma and avoid burning.\n• Garam Masala: Add Royal Garam Masala in the last 2 minutes of cooking or as a finishing garnish so the essential oils remain fragrant and rich.\n• Cardamom & Saffron: Crush green cardamom pods fresh for morning masala chai or rich kheer; infuse saffron strands in warm milk for 10 minutes before adding to biryanis or desserts.",
                'promotions_discounts' => "FlavourFlow Discounts & Special Offers:\n• Free Shipping: Enjoy 100% FREE delivery on all orders of ₹500 or more!\n• Coupon Codes: Apply coupon code 'WELCOME10' or available coupons at checkout for immediate discounts.\n• Combo Value Packs: Save more with our curated spice assortments and kitchen bundles.",
                'spice_quality' => "FlavourFlow Purity & Authenticity Promise:\n• 100% Pure & Farm-Fresh: Sourced directly from origin farms and prepared using traditional slow grinding methods.\n• Zero Adulteration: Free from artificial colors, chemical preservatives, MSG, or fillers.\n• Retained Essential Oils: Hermetically packed to lock in authentic natural aroma and flavour.",
                'general_help' => "Hello! I am your FlavourFlow Shopping Assistant. Here is what I can assist you with:\n• 🌿 Explore pure spices, price checks, and curated recommendations\n• 📦 Track active shipments and review your past order history\n• 💳 Payment options (UPI QR, Credit/Debit cards, NetBanking, COD)\n• 🚚 Delivery timelines, pincode coverage, and free shipping info\n• 📄 Download official GST Tax Invoices and understand easy returns\n• 👨‍🍳 Culinary advice, masala blending, and authentic recipes\nFeel free to ask me anything!",
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
                    ? 'Your private order information is protected. Please sign in to your FlavourFlow account to check your order details and delivery status.'
                    : ($orderNumber !== '' ? "Your latest order #{$orderNumber} is currently '{$orderStatus}'." : 'No active orders found for your account.'),
                'delivery_charges' => 'Delivery is FREE on all orders of Rs. 500 or above! For orders under Rs. 500, a flat delivery charge of Rs. 50 applies.',
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
        // 0. Delivery Charges Intent (check before order_tracking so shipping fees questions don't trigger order tracking)
        if ($this->matchesKeywords($text, [
            'delivery charge', 'delivery charges', 'delivery fee', 'delivery fees',
            'shipping fee', 'shipping fees', 'shipping charge', 'shipping charges',
            'shipping cost', 'delivery cost', 'free delivery', 'delivery price',
            'shipping price', 'shipping rate', 'delivery rate', 'charges on product',
            'ડિલિવરી ચાર્જ', 'ડિલિવરી ફી', 'ડિલિવરી ખર્ચ', 'મફત ડિલિવરી',
            'डिलीवरी चार्ज', 'डिलीवरी शुल्क', 'डिलीवरी फीस', 'मुफ्त डिलीवरी',
        ]) || preg_match('/(?:delivery|shipping).*(?:charge|fee|cost|price|rate)|(?:charge|fee|cost|price|rate).*(?:delivery|shipping)/iu', $text)) {
            return 'delivery_charges';
        }

        // 1. Payment Methods Intent
        if ($this->matchesKeywords($text, [
            'payment', 'payment method', 'payment methods', 'payment option', 'payment options',
            'how to pay', 'pay', 'upi', 'qr', 'qr code', 'cash on delivery', 'cod', 'razorpay',
            'cashfree', 'gpay', 'google pay', 'phonepe', 'paytm', 'bhim', 'cred', 'netbanking',
            'credit card', 'debit card', 'card payment', 'utr', 'ref number', 'reference id',
            'payment failed', 'money debited', 'i have paid', 'money cut', 'paise cut',
            'પેમેન્ટ', 'ચૂકવણી', 'કઈ રીતે ચૂકવવું', 'કેવી રીતે પે કરવું', 'યુપીઆઈ', 'ક્યુઆર',
            'ક્યુઆર કોડ', 'કેશ ઓન ડિલિવરી', 'પેટીએમ', 'ફોનપે', 'ગુગલ પે', 'પૈસા કપાયા', 'પેમેન્ટ ફેલ',
            'payment kem karvanu', 'payment option', 'upi payment', 'qr scan', 'cod available che',
            'cash on delivery malse', 'payment fail thay to', 'rupiya kapai gaya', 'utr number',
            'i have paid', 'pay kevi rite karvu',
            'भुगतान', 'पेमेंट', 'पैसे कैसे दें', 'कैश ऑन डिलीवरी', 'यूपीआई', 'क्यूआर कोड',
            'फोनपे', 'पेटीएम', 'गूगल पे', 'पैसे कट गए', 'पेमेंट फेल', 'भुगतान कैसे करें',
            'payment kaise kare', 'cod available hai', 'upi se payment', 'qr code scan',
            'paise kat gaye', 'payment fail ho gaya',
        ])) {
            return 'payment_methods';
        }

        // 2. Refund & Returns Intent
        if ($this->matchesKeywords($text, [
            'return', 'returns', 'refund', 'refunds', 'return policy', 'how to return',
            'replacement', 'exchange', 'damaged', 'broken', 'money back', 'wrong item', 'return request',
            'રીટર્ન', 'પરત', 'રીફંડ', 'પૈસા પાછા', 'રીટર્ન પોલિસી', 'માલ પાછો', 'ખરાબ માલ', 'નુકસાની',
            'refund kyare malse', 'paisa pacha malse', 'return kem karvu', 'return karvu che',
            'damaged item', 'wrong item aavyu', 'mal pachho levano', 'refund time',
            'वापसी', 'रिफंड', 'पैसे वापस', 'रिटर्न पॉलिसी', 'सामान वापस', 'टूटा हुआ सामान', 'खराब सामान',
            'return kaise kare', 'refund kab aayega', 'return policy kya hai', 'paise wapas kab honge',
            'damaged product mila', 'exchange ho sakta hai',
        ])) {
            return 'refund_returns';
        }

        // 3. Order Placement, Cancellation & Invoices
        if ($this->matchesKeywords($text, [
            'invoice', 'bill', 'tax invoice', 'gst invoice', 'gst bill', 'download invoice',
            'receipt', 'cancel order', 'how to cancel', 'how to order', 'how to place order',
            'order confirmation', 'order confirm', 'order otp',
            'ઇન્વોઇસ', 'બિલ', 'જીએસટી બિલ', 'રસીદ', 'ઓર્ડર કેન્સલ', 'ઓર્ડર રદ કરવો',
            'ઓર્ડર કેવી રીતે કરવો', 'ઓર્ડર કન્ફર્મ',
            'invoice download', 'bill malse', 'gst bill', 'order cancel kem karvo',
            'order cancel thay sake', 'order kem karvo', 'order confirm kem thay', 'invoice kem male',
            'इनवॉयस', 'बिल', 'जीएसटी बिल', 'रसीद', 'ऑर्डर कैंसिल', 'रद्द करें', 'ऑर्डर कैसे करें', 'ऑर्डर कन्फर्म',
            'invoice download kaise kare', 'bill milega', 'gst bill milega', 'order cancel kaise kare',
            'order cancel ho sakta hai', 'order kaise kare', 'order confirm kaise hoga',
        ])) {
            return 'order_help';
        }

        // 4. Shipping & Delivery Timeline
        if ($this->matchesKeywords($text, [
            'delivery time', 'how long for delivery', 'when will it arrive', 'how many days',
            'shipping time', 'pincode', 'express delivery', 'delivery area', 'all india delivery',
            'gujarat delivery', 'delivery duration',
            'કેટલા દિવસમાં પહોંચશે', 'ડિલિવરી સમય', 'ક્યારે પહોંચશે', 'કેટલા દિવસ થશે', 'પિનકોડ પર ડિલિવરી',
            'delivery ketla divas ma thay', 'kyare aavse', 'ketla divas lagse', 'delivery time su che',
            'pincode par delivery che', 'delivery ketla divas ma aavse',
            'डिलीवरी का समय', 'कितने दिन लगेंगे', 'कब पहुंचेगा', 'डिलीवरी कितने दिन में होगी', 'कब तक मिलेगा',
            'delivery kitne din me hogi', 'kab tak aayega', 'kitna time lagega', 'pincode par deliver hoga',
        ])) {
            return 'shipping_delivery';
        }

        // 5. Store Contact & Customer Support
        if ($this->matchesKeywords($text, [
            'contact', 'support', 'customer care', 'phone number', 'email address',
            'store address', 'office address', 'helpdesk', 'call support', 'contact us', 'customer support',
            'સંપર્ક', 'ફોન નંબર', 'ઈમેલ', 'ગ્રાહક સેવા', 'ઓફિસ ક્યાં છે', 'દુકાનનું સરનામું', 'સંપર્ક કરો',
            'contact number su che', 'tamari office kya che', 'customer care number', 'support email',
            'phone number su che', 'kya thi contact karvo', 'tamari shop kya che',
            'संपर्क', 'फोन नंबर', 'ईमेल', 'कस्टमर केयर', 'ऑफिस कहाँ है', 'दुकान का पता', 'कस्टमर सपोर्ट',
            'contact number kya hai', 'customer care number', 'support email kya hai', 'office kahan hai',
            'shop kahan hai', 'call kaise kare',
        ])) {
            return 'store_contact';
        }

        // 6. Cooking, Recipes & Culinary Usage
        if ($this->matchesKeywords($text, [
            'how to use', 'recipe', 'recipes', 'how to cook', 'how to make', 'cooking',
            'ingredients', 'tadka', 'tempering', 'chai masala', 'garam masala recipe', 'curry', 'sabzi', 'dal tadka',
            'રેસિપી', 'બનાવવાની રીત', 'કેવી રીતે બનાવવું', 'રસોઈ', 'ચા મસાલો', 'વઘાર', 'શાકમાં', 'દાળમાં',
            'recipe su che', 'kevi rite banavvu', 'rasoi ma kevi rite use thay', 'chai ma su nakhvu',
            'tadka kem lagavvo', 'masala kevi rite vaparvo', 'shak ma kai rite vaparvu',
            'रेसिपी', 'बनाने की विधि', 'कैसे बनाएं', 'रसोई', 'चाय मसाला', 'तड़का कैसे लगाएं', 'सब्जी में', 'दाल में',
            'recipe kya hai', 'kaise banaye', 'khana banane ka tarika', 'tadka kaise lagaye',
            'chai masala kaise banaye', 'masala kaise use kare',
        ])) {
            return 'cooking_recipe';
        }

        // 7. Comparison Intent
        if ($this->matchesKeywords($text, ['compare', 'difference', 'vs', 'versus', 'farak', 'farq', 'antar', 'સરખામણી', 'તફાવત', 'તુલના', 'अंतर', 'કયો સારો'])) {
            return 'product_comparison';
        }

        // 8. Price Intent
        if ($this->matchesKeywords($text, [
            'price', 'cost', 'rate', 'how much', 'bhav', 'dam', 'daam', 'kitne ka', 'ketla nu', 'ketlo',
            'ભાવ', 'કિંમત', 'રૂપિયા', 'દર', 'કિમત', 'કેટલા', 'કેટલું',
            'दाम', 'भाव', 'कीमत', 'मूल्य', 'रुपये', 'दर', 'कितने का', 'कितना', 'कितने',
        ])) {
            return 'price_query';
        }

        // 9. Stock / Availability Intent
        if ($this->matchesKeywords($text, [
            'stock', 'available', 'availability', 'in stock', 'hazir',
            'ઉપલબ્ધ', 'સ્ટોક', 'સ્ટોકમાં', 'સ્ટોક માં', 'મળશે', 'હાજર',
            'उपलब्ध', 'स्टॉक', 'स्टॉक में', 'मिलेगा', 'हाजिर',
        ])) {
            return 'stock_query';
        }

        // 10. Order Tracking & Order Inquiries
        if ($this->matchesKeywords($text, [
            'track', 'track order', 'tracking', 'where is my order', 'status', 'delivery status',
            'where is', 'where is order', 'check order', 'order status', 'my order', 'my orders',
            'past order', 'past orders', 'previous order', 'previous orders', 'order history',
            'all orders', 'recent order', 'recent orders', 'my purchases', 'order', 'orders',
            'ઓર્ડર', 'ઓર્ડર્સ', 'ઓર્ડરનું સ્ટેટસ', 'મારો ઓર્ડર', 'મારા ઓર્ડર', 'મારા ઓર્ડર્સ',
            'જુના ઓર્ડર', 'જુના ઓર્ડર્સ', 'જુનણ ઓર્ડર્સ', 'જૂના ઓર્ડર', 'જૂના ઓર્ડર્સ',
            'અગાઉના ઓર્ડર', 'અગાઉના ઓર્ડર્સ', 'ઓર્ડર હિસ્ટ્રી', 'ઓર્ડર સ્ટેટસ', 'ઓર્ડર ટ્રેકિંગ',
            'ઓર્ડર ક્યાં પહોંચ્યો', 'ઓર્ડર ક્યાં છે', 'ક્યાં છે', 'ટ્રેકિંગ', 'ઓર્ડર બતાવો', 'મારા બધા ઓર્ડર',
            'maro order', 'mara order', 'mara orders', 'juna order', 'juna orders', 'junan orders',
            'pela na order', 'order status', 'order kya che', 'order pohchyo', 'order tracking',
            'check order', 'order batao', 'past orders',
            'ऑर्डर', 'ऑर्डर्स', 'मेरा ऑर्डर', 'मेरे ऑर्डर', 'पुराने ऑर्डर', 'पिछला ऑर्डर',
            'पिछले ऑर्डर', 'ऑर्डर स्थिति', 'ऑर्डर स्टेटस', 'ऑर्डर हिस्ट्री', 'सारे ऑर्डर',
            'mera order', 'mere orders', 'purane order', 'pichhle order', 'kaha h', 'pohchyo',
        ])) {
            return 'order_tracking';
        }

        // 11. Cart / Checkout Intent
        if ($this->matchesKeywords($text, ['cart', 'checkout', 'buy', 'kharidna', 'kharidvu', 'ખરીદવું', 'કાર્ટ', 'खरीदना'])) {
            return 'cart_query';
        }

        // 12. Greeting Intent
        if ($this->matchesKeywords($text, ['hi', 'hello', 'hey', 'kem cho', 'namaste', 'kem chho', 'pranam', 'halo', 'કેમ છો', 'નમસ્તે', 'नमस्ते', 'प्रणाम'])) {
            return 'greeting';
        }

        // 13. Promotions, Discounts & Coupons
        if ($this->matchesKeywords($text, [
            'discount', 'discounts', 'coupon', 'coupons', 'promo code', 'promocode', 'offer', 'offers', 'deal', 'deals', 'sale',
            'ડિસ્કાઉન્ટ', 'કૂપન', 'ઓફર', 'ઑફર', 'ડિસ્કાઉન્ટ કોડ', 'છૂટ',
            'discount malse', 'coupon code su che', 'offer che', 'discount che', 'saste dam',
            'छूट', 'कूपन', 'ऑफर', 'डिस्काउंट', 'ऑफर क्या है', 'कूपन कोड',
            'discount milega', 'offer hai kya', 'coupon code kya hai',
        ])) {
            return 'promotions_discounts';
        }

        // 14. Spice Quality & Authenticity
        if ($this->matchesKeywords($text, [
            'quality', 'pure', 'purity', 'organic', 'natural', 'unadulterated', 'fresh', 'freshness', 'adulteration',
            'ગુણવત્તા', 'શુદ્ધ', 'શુદ્ધતા', 'કુદરતી', 'ઓર્ગેનિક', 'તાજા', 'ભેળસેળ',
            'quality kevi che', 'shuddh che', 'purity su che', 'original che',
            'शुद्ध', 'शुद्धता', 'क्वालिटी', 'प्राकृतिक', 'असली', 'ताज़ा', 'मिलावट',
            'quality kaisi hai', 'shuddh hai', 'original hai kya',
        ])) {
            return 'spice_quality';
        }

        // 15. General Help & Services Guide
        if ($this->matchesKeywords($text, [
            'help', 'support guide', 'services', 'what can you do', 'how can you help', 'features', 'options', 'assistance',
            'મદદ', 'સહાય', 'તમે શું કરી શકો', 'કેવી રીતે મદદ કરશો', 'સેવાઓ', 'સુવિધા', 'સુવિધાઓ',
            'help karo', 'su kari sako', 'kevi rite help karso', 'help joiye', 'shu madat',
            'मदद', 'सहायता', 'आप क्या कर सकते हैं', 'कैसे मदद करेंगे', 'सेवाएं', 'सुविधाएं',
            'kya kar sakte ho', 'kaise madad karoge', 'help chahiye',
        ])) {
            return 'general_help';
        }

        // 16. Recommendation Intent
        if ($this->matchesKeywords($text, ['recommend', 'suggest', 'best', 'saru', 'sabse accha', 'top', 'popular', 'સૌથી સારું', 'શ્રેષ્ઠ', 'સુઝાવ', 'सबसे अच्छा', 'सुझाव'])) {
            return 'recommendation';
        }

        // 17. Specific Product Search
        foreach (self::SYNONYMS as $name => $aliases) {
            if ($this->matchesKeywords($text, $aliases)) {
                return 'product_search';
            }
        }

        // 18. Category Explore
        if ($this->matchesKeywords($text, ['category', 'whole spices', 'ground spices', 'blended spices', 'spice collection', 'spices'])) {
            return 'category_explore';
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
            if (str_contains($kw, ' ')) {
                if (str_contains($text, $kw)) {
                    return true;
                }
            } else {
                if (preg_match('/(*UCP)\b'.preg_quote($kw, '/').'\b/iu', $text) === 1) {
                    return true;
                }
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
