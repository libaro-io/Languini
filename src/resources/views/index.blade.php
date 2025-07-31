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
    <select class="border border-gray-300 rounded p-2"
            name="filename"
            id="filename"
            onchange="files()">
        <option disabled="disabled" selected="selected" value="directory">Select a directory</option>
        @foreach($fileNames as $fileName)
            <option value="{{ $fileName }}" {{ request()->get('filename') === $fileName ? 'selected' : '' }}>{{ $fileName }}</option>
        @endforeach
    </select>

    @if($translationKeys)
        <form
                onkeyup="if(event.keyCode === 13) { submitForm(); }"
                action="{{route('languini.update')}}" method="POST" id="translationForm"
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
                        >{!! \Illuminate\Support\Arr::get($translationKeyItems, config('app.locale'))!!}</td>
                        @foreach($translatableLanguages as $lang)
                            <td
                                    class="px-4 py-2 border-r border-gray-300"
                            >
                                <textarea
                                        type="text"
                                        name="{{request()->get('filename')}}[{{ \Illuminate\Support\Arr::get($translationKeyItems, 'key') }}][{{ $lang }}]"
                                        class="w-full border border-gray-300 rounded p-2 mt-1 resize-none"
                                        placeholder="Enter translation for {{ $lang }}"
                                >{!!  \Illuminate\Support\Arr::get($translationKeyItems, $lang)  !!}</textarea>
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
    <div class="px-4 flex justify-end">
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
            form.submit();
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
</script>
</html>