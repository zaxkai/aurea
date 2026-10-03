<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex justify-center items-center px-6 py-3 bg-aurea border border-transparent rounded-full font-semibold text-sm text-navy hover:bg-aurea/90 focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
