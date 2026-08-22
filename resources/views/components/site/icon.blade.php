@props([
    'name',
    'class' => 'h-5 w-5',
])

@php
    $svgClass = trim('block shrink-0 ' . $class);
@endphp

@switch($name)
    @case('facebook')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'currentColor', 'aria-hidden' => 'true']) }}>
            <path d="M13.5 22v-8.2h2.7l.4-3.2h-3.1V8.6c0-.9.2-1.5 1.5-1.5h1.6V4.2c-.8-.1-1.7-.2-2.7-.2-2.6 0-4.5 1.6-4.5 4.6v2.2H7v3.2h2.4V22h4.1Z" />
        </svg>
        @break
    @case('instagram')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
            <circle cx="12" cy="12" r="4.1" />
            <circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none" />
        </svg>
        @break
    @case('x')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'currentColor', 'aria-hidden' => 'true']) }}>
            <path d="M6 4h4.1l3.2 4.5L17.3 4H21l-5.3 6.8L21 20h-4.1l-3.4-4.8L9.8 20H6l5.6-7.2L6 4Z" />
        </svg>
        @break
    @case('youtube')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'currentColor', 'aria-hidden' => 'true']) }}>
            <path d="M21.5 7.3c.2.8.5 2.2.5 4.7s-.3 3.9-.5 4.7c-.2.7-.7 1.3-1.4 1.5-1.2.3-8.1.3-8.1.3s-6.9 0-8.1-.3c-.7-.2-1.2-.8-1.4-1.5C2.3 15.9 2 14.5 2 12s.3-3.9.5-4.7c.2-.7.7-1.3 1.4-1.5C5.1 5.5 12 5.5 12 5.5s6.9 0 8.1.3c.7.2 1.2.8 1.4 1.5Zm-9.9 7.7 4.9-2.8-4.9-2.8v5.6Z" />
        </svg>
        @break
    @case('pinterest')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'currentColor', 'aria-hidden' => 'true']) }}>
            <path d="M12.1 2C6.9 2 3.5 5.4 3.5 10.1c0 3.3 1.8 5.2 4 5.2.9 0 1.4-2.4 1.4-3.1 0-.8-1.8-1.6-1.8-3.7 0-3.1 2.5-5.3 5.8-5.3 3 0 5.2 1.8 5.2 5.1 0 2.4-1 7-4 7-1.1 0-2-.8-2-2 0-1.8 1.2-3.5 1.2-5.3 0-2.8-4.1-2.3-4.1.7 0 .7.1 1.4.4 2.1L8.4 21c1.4.4 2.8.6 4.3.6 5.2 0 8.7-4.1 8.7-9.6C21.4 5.8 17.7 2 12.1 2Z" />
        </svg>
        @break
    @case('linkedin')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'currentColor', 'aria-hidden' => 'true']) }}>
            <path d="M6.3 8.8H2.9V21h3.4V8.8ZM4.6 3a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm6 5.8H7.3V21h3.4v-6.2c0-1.6.3-3.2 2.3-3.2 2 0 2 1.8 2 3.3V21h3.4v-6.8c0-3.3-.7-5.8-4.4-5.8-1.8 0-3 1-3.4 2h-.1V8.8Z" />
        </svg>
        @break
    @case('leaf')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M19.5 4.8c-6.5-.5-11.6 2.3-13.2 7.2-1 3-.5 6.1 1.2 8.2 2.7.2 5.5-.4 7.9-2.2 3.9-2.8 4.9-8.1 4.1-13.2Z" />
            <path d="M6 18c2.2-3.6 5.7-6.2 10.1-7.8" />
        </svg>
        @break
    @case('sprout')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M12 21v-9" />
            <path d="M12 12c0-4.4-3-7.2-8-7.5 0 4.7 2.6 8.2 8 8.2" />
            <path d="M12 12c0-4.1 2.8-6.7 7.2-7 0 4.4-2.4 7.7-7.2 8.2" />
        </svg>
        @break
    @case('shield-check')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M12 3 19 6v6c0 4.8-3.1 8.6-7 10-3.9-1.4-7-5.2-7-10V6l7-3Z" />
            <path d="m9.5 12.1 1.7 1.8 3.5-4" />
        </svg>
        @break
    @case('truck')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M3.5 7h10.2v10H3.5z" />
            <path d="M13.7 10H18l2.5 2.7V17H13.7" />
            <circle cx="7.2" cy="18" r="1.8" />
            <circle cx="17.5" cy="18" r="1.8" />
        </svg>
        @break
    @case('quality-check')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M12 2.9 14.8 6l4 .7-.7 4 2.1 3.5-3.5 2.1-.7 4-4-.7-3.5 2.1-2.1-3.5-4-.7.7-4L1 10.7 4.5 8.6l.7-4 4-.7L12 2.9Z" />
            <path d="m8.8 12.2 2 2 4.4-4.9" />
        </svg>
        @break
    @case('visa')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 52 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="50" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <text x="26" y="16" text-anchor="middle" fill="currentColor" font-size="10" font-weight="800" letter-spacing="1.2" font-family="ui-sans-serif, system-ui, sans-serif">VISA</text>
        </svg>
        @break
    @case('mastercard')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 52 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="50" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <circle cx="21" cy="12" r="6" fill="#eb001b" opacity="0.92" />
            <circle cx="31" cy="12" r="6" fill="#f79e1b" opacity="0.92" />
        </svg>
        @break
    @case('rupay')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 54 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="52" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <text x="27" y="16" text-anchor="middle" fill="currentColor" font-size="9" font-weight="800" letter-spacing="0.8" font-family="ui-sans-serif, system-ui, sans-serif">RuPay</text>
        </svg>
        @break
    @case('upi')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 44 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="42" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <path d="M13 16V8h2.1c2.4 0 3.8 1.2 3.8 3s-1.4 3-3.8 3H15v2h-2Zm2-3h.2c1.2 0 1.9-.5 1.9-1.6s-.7-1.6-1.9-1.6H15V13Zm7 3V8h2v8h-2Z" fill="currentColor" />
            <path d="m28 8 3 4-3 4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
        @break
    @case('gpay')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 58 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="56" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <text x="29" y="16" text-anchor="middle" fill="currentColor" font-size="10" font-weight="800" letter-spacing="0.8" font-family="ui-sans-serif, system-ui, sans-serif">GPay</text>
        </svg>
        @break
    @case('phonepe')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 54 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="52" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <text x="27" y="16" text-anchor="middle" fill="currentColor" font-size="11" font-weight="800" font-family="ui-sans-serif, system-ui, sans-serif">₹</text>
        </svg>
        @break
    @case('paytm')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 54 24', 'aria-hidden' => 'true']) }}>
            <rect x="1" y="1" width="52" height="22" rx="8" fill="currentColor" opacity="0.08" />
            <text x="27" y="16" text-anchor="middle" fill="currentColor" font-size="10" font-weight="800" letter-spacing="0.6" font-family="ui-sans-serif, system-ui, sans-serif">Paytm</text>
        </svg>
        @break
    @case('pin')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M12 21s5.5-5.3 5.5-10A5.5 5.5 0 1 0 6.5 11c0 4.7 5.5 10 5.5 10Z" />
            <circle cx="12" cy="11" r="1.9" fill="currentColor" stroke="none" />
        </svg>
        @break
    @case('phone')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <path d="M7 4.5h2.3c.4 0 .8.2 1 .6l1.2 2.7c.2.5 0 1-.4 1.3l-1.4 1.1a14.8 14.8 0 0 0 5.2 5.2l1.1-1.4c.3-.4.8-.6 1.3-.4l2.7 1.2c.4.2.6.6.6 1V19c0 .8-.6 1.5-1.4 1.5C9.8 20.5 3.5 14.2 3.5 6.4 3.5 5.6 4.2 5 5 5h2Z" />
        </svg>
        @break
    @case('mail')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <rect x="3" y="5" width="18" height="14" rx="3" />
            <path d="m4.5 7.2 7.5 6 7.5-6" />
        </svg>
        @break
    @case('clock')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <circle cx="12" cy="12" r="8.5" />
            <path d="M12 7.8V12l2.7 1.8" />
        </svg>
        @break
    @case('chevron-up')
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '2', 'aria-hidden' => 'true']) }}>
            <path d="m6 14 6-6 6 6" />
        </svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $svgClass, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
            <circle cx="12" cy="12" r="9" />
        </svg>
@endswitch
