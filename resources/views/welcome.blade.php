<x-site.layout :site="$site">
    <x-site.hero :brand="$site['brand']" :navigation="$site['navigation']" :hero="$site['hero']" />
    <x-site.products :products="$products" />
</x-site.layout>
