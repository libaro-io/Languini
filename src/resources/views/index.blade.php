<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body >
<header class="bg-gray-800 text-white">
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold">
            Languini
        </h2>
    </div>
</header>
<div class="container mx-auto p-4">
    <select class="border border-gray-300 rounded p-2"
            name="directories"
            id="directories"
            onchange="directories()">
        <option value="directory">Select a directory</option>
        <option value="directory-1">Directory-1</option>
        <option value="directory-2">Directory-2</option>
        <option value="directory-3">Directory-3</option>
    </select>
</div>

</body>
<script>
    const directories = () => {
        const selectElement = document.getElementById('directories');
        const selectedValue = selectElement.value;
        if (selectedValue === 'directory') {
            alert('Please select a valid directory.');
        } else {
            //add query parameters to the URL
            const url = new URL(window.location.href);
            url.searchParams.set('directory', selectedValue);
            window.location.href = url.toString();
        }
    }
</script>
</html>