@props(['model', 'selected' => null, 'placeholder' => 'Select date', 'min' => null, 'max' => null])

<div
    x-data="{
        value: @js($selected),
        open: false,
        mode: @js($selected) ? 'days' : 'years',
        min: @js($min),
        max: @js($max),
        viewYear: null,
        viewMonth: null,
        yearBlockStart: null,
        weekdays: ['Su','Mo','Tu','We','Th','Fr','Sa'],
        months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
        init() {
            const base = this.value ? new Date(this.value + 'T00:00:00') : (this.max ? new Date(this.max + 'T00:00:00') : new Date());
            this.viewYear = base.getFullYear();
            this.viewMonth = base.getMonth();
            this.yearBlockStart = this.viewYear - (this.viewYear % 12);
        },
        pad(n) { return n < 10 ? '0' + n : '' + n; },
        fmt(y, m, d) { return y + '-' + this.pad(m + 1) + '-' + this.pad(d); },
        display() {
            if (!this.value) return null;
            const [y, m, d] = this.value.split('-').map(Number);
            return this.months[m - 1].slice(0, 3) + ' ' + d + ', ' + y;
        },
        inRange(dateStr) {
            if (this.min && dateStr < this.min) return false;
            if (this.max && dateStr > this.max) return false;
            return true;
        },
        daysInMonth(y, m) { return new Date(y, m + 1, 0).getDate(); },
        grid() {
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const startOffset = first.getDay();
            const total = this.daysInMonth(this.viewYear, this.viewMonth);
            const cells = [];
            for (let i = 0; i < startOffset; i++) cells.push(null);
            for (let d = 1; d <= total; d++) cells.push(d);
            return cells;
        },
        pickDay(d) {
            if (!d) return;
            const dateStr = this.fmt(this.viewYear, this.viewMonth, d);
            if (!this.inRange(dateStr)) return;
            this.value = dateStr;
            $wire.set('{{ $model }}', dateStr);
            this.open = false;
        },
        prevMonth() { if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; } else { this.viewMonth--; } },
        nextMonth() { if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; } else { this.viewMonth++; } },
        pickMonth(m) { if (this.monthDisabled(m)) return; this.viewMonth = m; this.mode = 'days'; },
        pickYear(y) { if (this.yearDisabled(y)) return; this.viewYear = y; this.mode = 'months'; },
        prevYearBlock() { this.yearBlockStart -= 12; },
        nextYearBlock() { this.yearBlockStart += 12; },
        monthDisabled(m) {
            const lastDay = this.daysInMonth(this.viewYear, m);
            const monthStart = this.fmt(this.viewYear, m, 1);
            const monthEnd = this.fmt(this.viewYear, m, lastDay);
            if (this.max && monthStart > this.max) return true;
            if (this.min && monthEnd < this.min) return true;
            return false;
        },
        yearDisabled(y) {
            if (this.max && y > parseInt(this.max.slice(0, 4))) return true;
            if (this.min && y < parseInt(this.min.slice(0, 4))) return true;
            return false;
        },
    }"
    x-init="init()"
    @click.outside="open = false"
    class="relative">

    <button type="button" @click="open = !open; if (!value) mode = 'years';"
        class="w-full flex items-center justify-between border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm text-left">
        <span class="flex items-center gap-2" :class="!value && 'text-gray-400'">
            <x-icon name="calendar" class="w-4 h-4 text-gray-400 shrink-0" />
            <span x-text="display() || '{{ $placeholder }}'"></span>
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400 transition shrink-0" :class="open && 'rotate-180'">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition @click.outside="open = false"
        class="absolute z-20 mt-1 w-72 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-md shadow-lg p-3">

        {{-- Day grid --}}
        <template x-if="mode === 'days'">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" @click="prevMonth()" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="button" @click="mode = 'months'" class="text-sm font-medium hover:text-trenakt-primary transition">
                        <span x-text="months[viewMonth] + ' ' + viewYear"></span>
                    </button>
                    <button type="button" @click="nextMonth()" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-y-1 mb-1">
                    <template x-for="wd in weekdays" :key="wd">
                        <div class="text-[10px] text-center text-gray-400 font-medium" x-text="wd"></div>
                    </template>
                </div>
                <div class="grid grid-cols-7 gap-y-1">
                    <template x-for="(d, i) in grid()" :key="i">
                        <div class="flex items-center justify-center">
                            <button type="button" x-show="d !== null"
                                @click="pickDay(d)"
                                :disabled="d && !inRange(fmt(viewYear, viewMonth, d))"
                                :class="{
                                    'bg-trenakt-primary text-white': d && value === fmt(viewYear, viewMonth, d),
                                    'text-gray-300 dark:text-white/15 cursor-not-allowed': d && !inRange(fmt(viewYear, viewMonth, d)),
                                    'hover:bg-gray-100 dark:hover:bg-white/10': d && inRange(fmt(viewYear, viewMonth, d)) && value !== fmt(viewYear, viewMonth, d),
                                }"
                                class="w-8 h-8 rounded-md text-xs" x-text="d"></button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Month picker --}}
        <template x-if="mode === 'months'">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" @click="viewYear--" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="button" @click="mode = 'years'; yearBlockStart = viewYear - (viewYear % 12)" class="text-sm font-medium hover:text-trenakt-primary transition" x-text="viewYear"></button>
                    <button type="button" @click="viewYear++" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="(m, i) in months" :key="i">
                        <button type="button" @click="pickMonth(i)" :disabled="monthDisabled(i)"
                            :class="{
                                'bg-trenakt-primary text-white': i === viewMonth,
                                'text-gray-300 dark:text-white/15 cursor-not-allowed': monthDisabled(i),
                                'hover:bg-gray-100 dark:hover:bg-white/10': !monthDisabled(i) && i !== viewMonth,
                            }"
                            class="rounded-md py-2 text-xs" x-text="m.slice(0, 3)"></button>
                    </template>
                </div>
            </div>
        </template>

        {{-- Year picker --}}
        <template x-if="mode === 'years'">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" @click="prevYearBlock()" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <span class="text-sm font-medium" x-text="yearBlockStart + ' – ' + (yearBlockStart + 11)"></span>
                    <button type="button" @click="nextYearBlock()" class="p-1.5 rounded text-gray-400 hover:text-trenakt-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="y in Array.from({length: 12}, (_, i) => yearBlockStart + i)" :key="y">
                        <button type="button" @click="pickYear(y)" :disabled="yearDisabled(y)"
                            :class="{
                                'bg-trenakt-primary text-white': y === viewYear,
                                'text-gray-300 dark:text-white/15 cursor-not-allowed': yearDisabled(y),
                                'hover:bg-gray-100 dark:hover:bg-white/10': !yearDisabled(y) && y !== viewYear,
                            }"
                            class="rounded-md py-2 text-xs" x-text="y"></button>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
