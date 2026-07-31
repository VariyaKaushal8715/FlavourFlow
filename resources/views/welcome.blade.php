<x-site.layout :site="$site">
    <x-site.hero :brand="$site['brand']" :navigation="$site['navigation']" :hero="$site['hero']" />
    <x-site.offers :offers="$offers" />
    <x-site.products :products="$products" :wishlist-product-ids="$wishlistProductIds" />
    <x-site.company :company="$site['company']" />
</x-site.layout>
