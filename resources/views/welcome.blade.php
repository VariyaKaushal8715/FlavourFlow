<x-site.layout :site="$site">
    <x-site.hero :brand="$site['brand']" :navigation="$site['navigation']" :hero="$site['hero']" :wishlist-product-ids="$wishlistProductIds" />
    <x-site.offers :offers="$offers" />
    <x-site.products :products="$products" :wishlist-product-ids="$wishlistProductIds" :sort="$sort" :min-price="$minPrice" :max-price="$maxPrice" :lowest-price="$lowestPrice" :highest-price="$highestPrice" />
    <x-site.company :company="$site['company']" />

    {{-- Personalized Welcome Greeting (once per session, authenticated users only) --}}
    @auth
        <div id="welcome-greeting-toast" style="
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9998;
            max-width: 340px;
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, #18181b 0%, #27272a 100%);
            color: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateY(120%) scale(0.95);
            opacity: 0;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        ">
            <span style="
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 0.625rem;
                background: linear-gradient(135deg, #b42318 0%, #dc2626 100%);
                flex-shrink: 0;
                font-size: 1.125rem;
            " id="welcome-greeting-emoji">👋</span>
            <div style="min-width: 0;">
                <p id="welcome-greeting-text" style="
                    margin: 0;
                    font-size: 0.8125rem;
                    font-weight: 700;
                    letter-spacing: -0.01em;
                    line-height: 1.3;
                    color: #fff;
                "></p>
                <p style="
                    margin: 0.125rem 0 0;
                    font-size: 0.6875rem;
                    font-weight: 500;
                    color: #a1a1aa;
                    letter-spacing: 0.01em;
                ">Welcome back.</p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const storageKey = 'ff_user_greeted_session';
                if (sessionStorage.getItem(storageKey)) return;

                const toast = document.getElementById('welcome-greeting-toast');
                const textEl = document.getElementById('welcome-greeting-text');
                if (!toast || !textEl) return;

                const hour = new Date().getHours();
                let period;
                if (hour >= 5 && hour < 12) period = 'Morning';
                else if (hour >= 12 && hour < 17) period = 'Afternoon';
                else if (hour >= 17 && hour < 21) period = 'Evening';
                else period = 'Night';

                const userName = @js(auth()->user()->name);
                const firstName = userName ? userName.split(' ')[0] : 'there';
                textEl.textContent = `Good ${period}, ${firstName}!`;

                sessionStorage.setItem(storageKey, '1');

                setTimeout(() => {
                    toast.style.transform = 'translateY(0) scale(1)';
                    toast.style.opacity = '1';
                }, 400);

                setTimeout(() => {
                    toast.style.transform = 'translateY(120%) scale(0.95)';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }, 3600);
            });
        </script>
    @endauth
</x-site.layout>
