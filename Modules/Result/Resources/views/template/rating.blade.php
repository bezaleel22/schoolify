<!-- resources/views/template/rating.blade.php -->
<section class="mb-4">
    <h2 class="text-sm font-bold mr-1 text-slate-700 opacity-75">Student Ratings</h2>
    <div class="text-sm opacity-50 flex items-center">
        <div class="i-mdi:account-switch text-xl mr-1" aria-hidden="true"></div>
        <span>Social and Personal Development</span>
    </div>
    <hr class="my-2" />

    @foreach ($ratings as $rating)
    <div class="border-b grid grid-cols-6 py-1">
        <div class="col-span-2">
            <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">
                {{ $rating->attribute }}</span>
        </div>

        <div class="col-span-3">
            <input type="range" min="0" max="100" value="{{ $rating->rate }}" class="range range-xs {{ $rating->color }}" />
            {{-- <div class="bg-slate-200 relative h-[16px] w-full max-w-[384px] rounded-2xl">
                <div class="bg-primary absolute top-0 left-0 flex h-full items-center justify-center rounded-2xl text-xs font-semibold text-white {{ $rating->color }}">
                    {{ $rating->rate }}
                </div>
            </div> --}}
        </div>

        <span class="col-span-1 text-xs text-center text-slate-500">
            <span class="bg-purple-200 text-purple-600 py-1 px-3 rounded-full text-xs uppercase">
                {{ $rating->remark }}
            </span>
        </span>
    </div>
    @endforeach
</section>
