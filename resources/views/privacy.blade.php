<x-site.layout :site="config('personal_site')" page-title="Privacy Policy | FlavourFlow" :preserve-on-refresh="true">
    @php($site = config('personal_site'))

    <div class="bg-zinc-950">
        <x-site.nav :brand="$site['brand']" :navigation="$site['navigation']" />
    </div>

    <section class="border-b border-amber-200/60 bg-[radial-gradient(circle_at_top_left,rgba(244,185,66,0.24),transparent_34%),linear-gradient(135deg,#fff9ed_0%,#fff_52%,#fff3df_100%)] py-12 sm:py-16">
        <div class="mx-auto w-full max-w-4xl px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-primary">FlavourFlow</p>
            <h1 class="mt-2 text-3xl font-semibold text-zinc-950 sm:text-4xl">Privacy Policy</h1>
            <p class="mt-4 text-base leading-7 text-zinc-600">This page explains what information FlavourFlow uses to provide the store experience and how you can control optional activity tracking.</p>
        </div>
    </section>

    <main class="bg-[linear-gradient(180deg,#fff_0%,#fff9ed_48%,#fff_100%)] py-12 sm:py-16">
        <article class="mx-auto max-w-4xl space-y-8 px-6 text-sm leading-7 text-zinc-700 lg:px-8">
            <section class="rounded-3xl border border-amber-200/70 bg-white p-6 shadow-[0_18px_60px_rgba(120,53,15,0.08)] sm:p-8">
                <h2 class="text-xl font-semibold text-zinc-950">Information FlavourFlow uses</h2>
                <p class="mt-3">When you create an account or place an order, FlavourFlow uses the details you provide, such as your name, contact details, delivery address, cart items, and order information, to operate the store and support your order.</p>
                <p class="mt-3">We use account information for sign-in, account management, customer support, delivery communication, order records, and service improvements. We do not need your password in plain text; passwords are handled by the application authentication system.</p>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-zinc-950">Cookies and essential storage</h2>
                <p class="mt-3">Essential cookies and session storage help keep you signed in, preserve cart and checkout state, protect forms, and maintain security. These functions are required for the store to work and are not disabled when optional tracking is declined.</p>
                <p class="mt-3">FlavourFlow may also use necessary browser storage for interface preferences. These values are not used to collect payment credentials.</p>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-zinc-950">Optional activity tracking and AI features</h2>
                <p class="mt-3">If you allow optional activity tracking, permitted product and shopping interactions may be recorded in the existing FlavourFlow activity system to improve recommendations and understand how the store is used. Examples may include product views, searches, category views, wishlist changes, cart changes, checkout starts, and completed orders.</p>
                <p class="mt-3">If you decline, FlavourFlow must not create new non-essential activity records for your account. Account access, cart, checkout, order processing, and other essential features continue to work normally.</p>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-zinc-950">Orders and payment information</h2>
                <p class="mt-3">Order records include the information needed to calculate, fulfil, communicate, and support your purchase. Payment status and method may be recorded for the order.</p>
                <p class="mt-3">FlavourFlow does not intentionally store full card numbers, CVV, PINs, OTPs, UPI PINs, net-banking passwords, or other authentication secrets in activity records. Never enter these secrets into a non-payment form.</p>
            </section>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-zinc-950">Changing your preference</h2>
                <p class="mt-3">You can review or change optional activity tracking from the Privacy & Cookies section of your account profile. Revoking permission applies to future non-essential activity tracking and does not delete existing order records or previously stored activity records.</p>
                <p class="mt-3">For privacy questions or requests, use the store contact options in the footer and include enough information for us to identify your request without sending passwords or payment credentials.</p>
            </section>
        </article>
    </main>
</x-site.layout>
