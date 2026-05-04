<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-transparent bg-red-600 text-white text-sm font-semibold whitespace-nowrap shadow-sm hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
