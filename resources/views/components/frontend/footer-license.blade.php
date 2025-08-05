@props(["license" => "", "author" => app_name(), "author_url" => app_url()])

<div class="pt-6">
    @switch($license)
        @case("cc-by-sa")
            <div class="flex flex-col items-center justify-center text-center">
                <div class="text-sm text-gray-500 sm:w-1/2">

                    <a class="hover:underline" href="{{ $author_url }}" rel="cc:attributionURL dct:creator">

                    </a>

                    <a class="hover:underline" href="https://creativecommons.org/licenses/by-sa/4.0" target="_blank">

                    </a>
                </div>
            </div>

            @break
        @default
            <div class="flex items-center justify-center text-center">
                <div class="w-1/2 text-sm text-gray-500">
                    &copy; {{ date("Y") }}
                    <a href="{{ $author_url }}" rel="cc:attributionURL dct:creator">{{ $author }}</a>
                    All Right Reserved.
                </div>
            </div>
    @endswitch
</div>
