<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex justify-center items-center px-6 py-3 bg-navy border border-transparent rounded-full font-semibold text-sm text-white hover:bg-navy/90 focus:bg-navy/90 active:bg-black focus:outline-none focus:ring-2 focus:ring-aurea focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
