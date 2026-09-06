<x-site.layout :site="$site">
    <x-site.hero :brand="$site['brand']" :navigation="$site['navigation']" :hero="$site['hero']" :wishlist-product-ids="$wishlistProductIds" />
    <x-site.offers :offers="$offers" />
    <x-site.products :products="$products" :wishlist-product-ids="$wishlistProductIds" :sort="$sort" :min-price="$minPrice" :max-price="$maxPrice" :lowest-price="$lowestPrice" :highest-price="$highestPrice" />
    <x-site.company :company="$site['company']" />
</x-site.layout>
