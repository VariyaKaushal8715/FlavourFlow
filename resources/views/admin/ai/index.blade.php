<x-admin.layout title="AI Engine Diagnostics">
    <div class="space-y-6">
        <!-- Page Title & Overall Readiness Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-amber-800">
                        Step 1 - Core Verification
                    </span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-950 sm:text-3xl">
                    AI Engine Health & Verification
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                    Automated, non-fake verification of the reusable AI Core, Contracts, FlavourFlow Adapter, and Container Bindings.
                </p>
            </div>

            <!-- Overall System Readiness Indicator -->
            <div class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Overall System Status</p>
                    <p class="text-sm font-bold {{ $isReady ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $isReady ? 'Ready for Operation' : 'Action Required' }}
                    </p>
                </div>
                <span @class([
                    'inline-flex h-10 px-4 items-center justify-center rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm',
                    'bg-emerald-600 text-white' => $isReady,
                    'bg-rose-600 text-white' => ! $isReady,
                ])>
                    {{ $isReady ? 'Ready' : 'Not Ready' }}
                </span>
            </div>
        </div>

        <!-- Temporary Isolated Notice Banner -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-amber-900 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-xs leading-relaxed">
                    <p class="font-bold">Development & Diagnostic Isolation Notice (Step 1)</p>
                    <p class="mt-0.5 text-amber-800">
                        This AI Engine status page and its sidebar navigation item are isolated for step 1 architecture validation. All checks are verified against actual file presence, PHP class resolution, and Laravel container bindings on disk.
                    </p>
                </div>
            </div>
        </div>

        <!-- Verification Diagnostic Checks Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
            @foreach($checks as $key => $check)
                <div @class([
                    'rounded-2xl border p-5 transition shadow-sm bg-white',
                    'border-zinc-200 hover:border-zinc-300' => $check['passed'],
                    'border-rose-300 bg-rose-50/20' => ! $check['passed'],
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950">{{ $check['name'] }}</h3>
                            <p class="mt-1 text-xs text-zinc-500 leading-relaxed">{{ $check['description'] }}</p>
                        </div>
                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold shadow-sm flex-shrink-0',
                            'bg-emerald-100 text-emerald-800 border border-emerald-200' => $check['passed'],
                            'bg-rose-100 text-rose-800 border border-rose-200' => ! $check['passed'],
                        ])>
                            <span>{{ $check['passed'] ? '✓' : '✕' }}</span>
                            <span>{{ $check['passed'] ? 'Verified' : 'Failed' }}</span>
                        </span>
                    </div>

                    <div class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50 p-3 text-[11px] font-mono text-zinc-700 break-all">
                        {{ $check['details'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Active Core Architecture Summary Card -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-zinc-950">Active AI Core Architecture</h2>
            <p class="mt-1 text-xs text-zinc-500">Overview of active interfaces, core implementations, and domain adapters.</p>

            <div class="mt-4 grid gap-4 text-xs sm:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <p class="font-bold text-zinc-400 uppercase tracking-wider text-[10px]">Bound Core Engine</p>
                    <p class="mt-1 font-semibold text-zinc-950">App\AI\Core\AiEngine</p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Implements AiEngineInterface</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <p class="font-bold text-zinc-400 uppercase tracking-wider text-[10px]">Bound Domain Adapter</p>
                    <p class="mt-1 font-semibold text-zinc-950">App\AI\Adapters\FlavourFlowAdapter</p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Implements AiAdapterInterface</p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <p class="font-bold text-zinc-400 uppercase tracking-wider text-[10px]">Configuration Mode</p>
                    <p class="mt-1 font-semibold text-zinc-950">{{ config('ai.mode', 'development') }}</p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Provider-independent architecture</p>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
