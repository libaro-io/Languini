<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Languini</title>
</head>
<body class="min-h-dvh flex flex-col">
<header class="bg-gray-800 text-white">
    <div class=" p-4">
        <a href="{{route('languini.index')}}">
            <h1 class="text-2xl font-bold">
                Languini
            </h1>
        </a>
    </div>
</header>
<div class="p-4 flex-1">
    <div class="flex items-center justify-between mb-4">
        <select class="border border-gray-300 rounded p-2"
                name="filename"
                id="filename"
                onchange="files()">
            <option disabled="disabled" selected="selected" value="directory">Select a directory</option>
            @foreach($fileNames as $fileName)
                <option value="{{ $fileName }}" {{ request()->get('filename') === $fileName ? 'selected' : '' }}>{{ $fileName }}</option>
            @endforeach
        </select>
        <button onclick="translateUsingAi()" id="translate-button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-2 flex items-center justify-center">
            <svg id="translate-spinner" class="hidden animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span id="translate-label">Translate with AI</span>
        </button>
    </div>

    @if($translationKeys)
        <form
                method="POST" id="translationForm"
        >
            @csrf
            <table class="table-auto w-full mt-4 border-collapse border border-gray-300">
                <thead
                        class="bg-gray-200 sticky top-0 z-10"
                >
                <tr class="border-b border-gray-300">
                    <th class="px-4 py-2 font-bold text-gray-700 border-r border-gray-300">
                        Key
                    </th>
                    <th
                            class="px-4 py-2 uppercase font-bold text-gray-700 border-r border-gray-300"
                    >
                        {{ config('app.locale') }}
                    </th>
                    @foreach($translatableLanguages as $lang)
                        <th class="px-4 py-2 uppercase font-bold text-gray-700 border-r border-gray-300">
                            {{ $lang }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($translationKeys as  $translationKeyItems)
                    <tr class="border-b border-gray-300 hover:bg-gray-100 transition-colors duration-200">
                        <td
                                class="px-4 py-2 border-r border-gray-300 text-sm break-all w-3/12"
                        >{{ \Illuminate\Support\Arr::get($translationKeyItems, 'key') }}</td>
                        <td
                                class="px-4 py-2 border-r border-gray-300 text-sm w-2/12"
                        >{!! \Illuminate\Support\Arr::get($translationKeyItems, config('app.locale')) !!}</td>
                        @foreach($translatableLanguages as $lang)
                            <td
                                    class="px-4 py-2 border-r border-gray-300"
                            >
                                <textarea
                                        name="{{ request()->get('filename') }}[{{ \Illuminate\Support\Arr::get($translationKeyItems, 'key') }}][{{ $lang }}]"
                                        class="w-full border border-gray-300 rounded p-2 mt-1 resize-none"
                                        placeholder="Enter translation for {{ $lang }}"
                                >{!! \Illuminate\Support\Arr::get($translationKeyItems, $lang) !!}</textarea>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>

        </form>
    @endif
</div>
<div class="sticky bottom-0 bg-gray-800 text-white p-4 mt-4">
    <div class="flex justify-end">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:cursor-pointer"
                onclick="submitForm()">
            Submit
        </button>
    </div>
</div>
</body>
<script>
    const submitForm = () => {
        const form = document.getElementById('translationForm');
        if (form) {
           if(confirm('This will overwrite the existing translations (except for the default language). Are you sure you want to proceed?')) {
               form.submit();
            } else {
                return;
           }
        } else {
            alert('Form not found.');
        }
    }
    const files = () => {
        const selectElement = document.getElementById('filename');
        const selectedValue = selectElement.value;
        if (selectedValue === 'filename') {
            alert('Please select a valid file name.');
        } else {
            //add query parameters to the URL
            const url = new URL(window.location.href);
            url.searchParams.set('filename', selectedValue);
            window.location.href = url.toString();
        }
    }

    const translateUsingAi = async () => {
        const selectElement = document.getElementById('filename');
        const selectedValue = selectElement.value;
        const translateEndpoint = "{{ route('languini.ai-translate') }}"
        const translatableLanguages = {!! json_encode($translatableLanguages) !!};
        const button = document.getElementById('translate-button');
        const spinner = document.getElementById('translate-spinner');
        const label = document.getElementById('translate-label');

        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
        spinner.classList.remove('hidden');
        label.classList.add('hidden');

        try {
            const response = await fetch(translateEndpoint, {
                method: "POST",
                body: new URLSearchParams({ filename: selectedValue }),
            });

            const translationsJson = await response.json();

            translationsJson.translations.forEach(item => {
                translatableLanguages.forEach(language => {
                    const textarea = document.querySelector(`textarea[name="${selectedValue}[${item.key}][${language}]"]`);
                    if (textarea) {
                        textarea.value = item[language];
                    }
                });
            });
        } catch (error) {
            console.error('Translation failed', error);
        } finally {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
            spinner.classList.add('hidden');
            label.classList.remove('hidden');
        }
    }
</script>
</html>