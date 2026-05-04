<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-slate-300 bg-white text-slate-700 text-sm font-semibold whitespace-nowrap shadow-sm hover:bg-slate-50 active:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
