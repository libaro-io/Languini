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
<body>
<header class="bg-gray-800 text-white">
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold">
            Languini
        </h2>
    </div>
</header>
<div class="container mx-auto p-4">
    <select class="border border-gray-300 rounded p-2"
            name="filename"
            id="filename"
            onchange="files()">
        <option value="directory">Select a directory</option>
        @foreach($fileNames as $fileName)
            <option value="{{ $fileName }}" {{ request()->get('filename') === $fileName ? 'selected' : '' }}>{{ $fileName }}</option>
        @endforeach
    </select>

    @if($translationKeys)
        <table class="table-auto">
            <thead>
            <tr>
                <th>Key</th>
                <th>{{ config('app.locale') }}</th>
                @foreach($translatableLanguages as $lang)
                    <th>{{ $lang }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
                @foreach($translationKeys as  $translationKeyItems)
                    <tr>
                        <td>{{ \Illuminate\Support\Arr::get($translationKeyItems, 'key') }}</td>
                        <td>{{ \Illuminate\Support\Arr::get($translationKeyItems, config('app.locale')) }}</td>
                        @foreach($translatableLanguages as $lang)
                            <td>{{ \Illuminate\Support\Arr::get($translationKeyItems, $lang) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

</body>
<script>
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